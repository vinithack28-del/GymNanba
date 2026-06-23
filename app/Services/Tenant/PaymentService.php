<?php

namespace App\Services\Tenant;

use App\Models\Member;
use App\Models\OwnerAuditLog;
use App\Models\Payment;
use App\Models\PaymentSplit;
use App\Models\GymMembershipPlan;
use App\Models\Branch;
use App\Models\Staff;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    // ── Access control ────────────────────────────────────────────────────────

    public function canVoid(): bool
    {
        return in_array(Auth::user()->role, ['tenant_owner', 'accountant']);
    }

    public function canCollect(): bool
    {
        return in_array(Auth::user()->role, ['tenant_owner', 'accountant', 'receptionist', 'branch_manager', 'branch_admin']);
    }

    // ── Collect page ──────────────────────────────────────────────────────────

    public function collectPage(int $tenantId): array
    {
        $plans = GymMembershipPlan::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $branches = Branch::forTenant($tenantId)->active()->orderBy('name')->get();

        return compact('plans', 'branches');
    }

    // ── Store payment ─────────────────────────────────────────────────────────

    public function storePayment(Request $request, int $tenantId): Payment
    {
        $user   = Auth::user();
        $member = Member::where('tenant_id', $tenantId)->findOrFail($request->member_id);
        $plan   = $request->plan_id
            ? GymMembershipPlan::where('tenant_id', $tenantId)->findOrFail($request->plan_id)
            : null;

        // Payment splits
        $splits = $request->input('splits', []);
        $paidPaise = array_sum(array_map(fn ($s) => (int) round(($s['amount'] ?? 0) * 100), $splits));
        $amountPaise = (int) round($request->amount * 100);
        $gstPaise = 0;
        $collectingPendingDue = ! $plan && $member->balance_paise < 0;

        if ($plan && $member->balance_paise < 0) {
            throw ValidationException::withMessages([
                'plan_id' => __('payments.collect.pending_due_block'),
            ]);
        }

        if ($plan) {
            $amountPaise = $plan->price_paise;
            $gstPaise = $plan->gst_amount_paise;
        } elseif ($collectingPendingDue) {
            $outstandingDuePaise = abs((int) $member->balance_paise);
            if ($paidPaise > $outstandingDuePaise) {
                throw ValidationException::withMessages([
                    'amount' => 'Collected amount cannot exceed the pending due amount.',
                ]);
            }
            // For due settlements, record only the amount actually collected.
            $amountPaise = $paidPaise;
        }

        $totalPaise = $amountPaise + $gstPaise;

        // Due / partial
        $isPartial = ! $collectingPendingDue && $request->boolean('is_partial') && $paidPaise < $totalPaise;
        $duePaise  = 0;
        $dueDate   = null;
        if ($isPartial) {
            $duePaise = max(0, $totalPaise - $paidPaise);
            $dueDate = $request->due_date ?: null;
        }

        // Primary method: single method name or 'split'
        $primaryMethod = count($splits) === 1
            ? ($splits[0]['method'] ?? 'cash')
            : (count($splits) > 1 ? 'split' : 'cash');

        return DB::transaction(function () use (
            $request, $tenantId, $member, $plan,
            $amountPaise, $gstPaise, $totalPaise,
            $paidPaise, $isPartial, $duePaise, $dueDate,
            $primaryMethod, $splits, $user, $collectingPendingDue
        ) {
            $receipt = $this->generateReceiptNumber($tenantId);
            $staff   = Staff::where('user_id', $user->id)->where('tenant_id', $tenantId)->first();

            $payment = Payment::create([
                'tenant_id'      => $tenantId,
                'member_id'      => $member->id,
                'branch_id'      => $request->branch_id ?? $member->branch_id,
                'plan_id'        => $request->plan_id ?: null,
                'receipt_number' => $receipt,
                'amount_paise'   => $amountPaise,
                'gst_paise'      => $gstPaise,
                'total_paise'    => $totalPaise,
                'paid_paise'     => $paidPaise,
                'is_partial'     => $isPartial,
                'due_paise'      => $duePaise,
                'due_date'       => $dueDate,
                'method'         => $primaryMethod,
                'reference'      => null,
                'payment_date'   => $request->payment_date ?: today()->toDateString(),
                'notes'          => $request->notes ?: null,
                'status'         => 'active',
                'collected_by'   => $staff?->id,
            ]);

            // Save splits
            foreach ($splits as $split) {
                $splitPaise = (int) round(($split['amount'] ?? 0) * 100);
                if ($splitPaise > 0) {
                    PaymentSplit::create([
                        'payment_id'   => $payment->id,
                        'method'       => $split['method'],
                        'amount_paise' => $splitPaise,
                        'reference'    => $split['reference'] ?: null,
                    ]);
                }
            }

            if ($collectingPendingDue && $paidPaise > 0) {
                $this->applyPaymentToOutstandingDues($member, $paidPaise);
            }

            // When paying for a specific plan, upgrade the member's active plan
            if ($plan) {
                $paymentDate = $request->payment_date ?: today()->toDateString();
                // If member has a future expiry, new plan starts from there (chain renewal)
                $member->refresh();
                $startDate = ($member->expiry_date && $member->expiry_date->toDateString() > $paymentDate)
                    ? $member->expiry_date->toDateString()
                    : $paymentDate;
                $member->update([
                    'plan_id'     => $plan->id,
                    'plan_name'   => $plan->name,
                    'start_date'  => $startDate,
                    'expiry_date' => $plan->computeExpiryDate($startDate),
                    'status'      => 'active',
                ]);
            }

            $this->syncMemberBalance($member);

            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                (new \App\Services\Tenant\InvoiceService())->createFromPayment($payment, $tenant);
            }

            return $payment;
        });
    }

    private function applyPaymentToOutstandingDues(Member $member, int $paidPaise): void
    {
        $remaining = $paidPaise;

        $duePayments = Payment::where('tenant_id', $member->tenant_id)
            ->where('member_id', $member->id)
            ->where('status', 'active')
            ->where('due_paise', '>', 0)
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->orderBy('payment_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($duePayments as $duePayment) {
            if ($remaining <= 0) {
                break;
            }

            $openPaise = (int) $duePayment->due_paise;

            if ($openPaise <= 0) {
                continue;
            }

            $applied = min($remaining, $openPaise);
            $duePayment->due_paise = max(0, $openPaise - $applied);
            $duePayment->is_partial = $duePayment->due_paise > 0;
            if ($duePayment->due_paise === 0) {
                $duePayment->due_date = null;
            }
            $duePayment->save();

            $remaining -= $applied;
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'amount' => 'Unable to apply the full payment amount against pending dues.',
            ]);
        }
    }

    // ── Payment history ───────────────────────────────────────────────────────

    public function history(Request $request, int $tenantId): array
    {
        $user = Auth::user();

        $query = Payment::with(['member', 'branch', 'plan', 'collectedBy'])
            ->where('payments.tenant_id', $tenantId);

        // Branch restriction for branch roles
        if (in_array($user->role, ['branch_manager', 'branch_admin'])) {
            $staff = Staff::where('user_id', $user->id)->where('tenant_id', $tenantId)->first();
            if ($staff?->branch_id) {
                $query->where('payments.branch_id', $staff->branch_id);
            }
        }

        if ($request->branch_id) {
            $query->where('payments.branch_id', $request->branch_id);
        }

        if ($request->status) {
            $query->where('payments.status', $request->status);
        }

        if ($request->method) {
            $query->where('payments.method', $request->method);
        }

        if ($request->date_from) {
            $query->whereDate('payments.payment_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('payments.payment_date', '<=', $request->date_to);
        }

        if ($request->search) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s): void {
                $q->where('payments.receipt_number', 'ilike', $s)
                    ->orWhereHas('member', fn ($m) => $m->where('name', 'ilike', $s)
                        ->orWhere('phone', 'ilike', $s));
            });
        }

        $payments = $query->orderByDesc('payments.payment_date')
            ->orderByDesc('payments.id')
            ->paginate(25, ['*'], 'history_page')
            ->withQueryString();

        $branches = Branch::forTenant($tenantId)->active()->orderBy('name')->get();

        // Daily summary for current filter
        $summaryQuery = Payment::where('payments.tenant_id', $tenantId)
            ->where('payments.status', 'active')
            ->whereDate('payments.payment_date', today());

        if ($request->branch_id) {
            $summaryQuery->where('payments.branch_id', $request->branch_id);
        }

        $todaySummary = [
            'count'       => $summaryQuery->count(),
            'total_paise' => $summaryQuery->sum('total_paise'),
        ];

        $activeTab = $request->get('tab') === 'history' ? 'history' : 'dues';

        // Dues tab data
        $duePayments   = null;
        $totalDuePaise = 0;
        if ($activeTab === 'dues') {
            $dueQuery = Payment::with(['member', 'member.branch', 'plan'])
                ->where('payments.tenant_id', $tenantId)
                ->where('payments.status', 'active')
                ->where('payments.is_partial', true)
                ->where('payments.due_paise', '>', 0);

            if (in_array($user->role, ['branch_manager', 'branch_admin'])) {
                $staff = $staff ?? Staff::where('user_id', $user->id)->where('tenant_id', $tenantId)->first();
                if ($staff?->branch_id) {
                    $dueQuery->where('payments.branch_id', $staff->branch_id);
                }
            }

            if ($request->branch_id) {
                $dueQuery->where('payments.branch_id', $request->branch_id);
            }

            if ($request->search) {
                $s = '%' . $request->search . '%';
                $dueQuery->where(function ($q) use ($s): void {
                    $q->where('payments.receipt_number', 'ilike', $s)
                        ->orWhereHas('member', fn ($m) => $m
                            ->where('name', 'ilike', $s)
                            ->orWhere('phone', 'ilike', $s)
                            ->orWhere('member_code', 'ilike', $s));
                });
            }

            $duePayments = $dueQuery
                ->orderByRaw('payments.due_date IS NULL, payments.due_date ASC')
                ->orderByDesc('payments.due_paise')
                ->paginate(25, ['*'], 'dues_page')
                ->withQueryString();

            $totalDuePaise = Payment::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->where('is_partial', true)
                ->where('due_paise', '>', 0)
                ->sum('due_paise');
        }

        return compact('payments', 'branches', 'todaySummary', 'activeTab', 'duePayments', 'totalDuePaise');
    }

    // ── Pending dues ──────────────────────────────────────────────────────────

    public function dues(Request $request, int $tenantId): array
    {
        $user = Auth::user();

        $query = Payment::with(['member', 'member.branch', 'plan'])
            ->where('payments.tenant_id', $tenantId)
            ->where('payments.status', 'active')
            ->where('payments.is_partial', true)
            ->where('payments.due_paise', '>', 0);

        if (in_array($user->role, ['branch_manager', 'branch_admin'])) {
            $staff = Staff::where('user_id', $user->id)->where('tenant_id', $tenantId)->first();
            if ($staff?->branch_id) {
                $query->where('payments.branch_id', $staff->branch_id);
            }
        }

        if ($request->branch_id) {
            $query->where('payments.branch_id', $request->branch_id);
        }

        if ($request->search) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s): void {
                $q->where('payments.receipt_number', 'ilike', $s)
                    ->orWhereHas('member', fn ($m) => $m
                        ->where('name', 'ilike', $s)
                        ->orWhere('phone', 'ilike', $s)
                        ->orWhere('member_code', 'ilike', $s));
            });
        }

        $payments = $query
            ->orderByRaw('payments.due_date IS NULL, payments.due_date ASC')
            ->orderByDesc('payments.due_paise')
            ->paginate(25)
            ->withQueryString();

        $branches = Branch::forTenant($tenantId)->active()->orderBy('name')->get();

        $totalDuePaise = Payment::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('is_partial', true)
            ->where('due_paise', '>', 0)
            ->sum('due_paise');

        return compact('payments', 'branches', 'totalDuePaise');
    }

    // ── Void payment ──────────────────────────────────────────────────────────

    public function voidPayment(Payment $payment, Request $request, int $tenantId): void
    {
        $user = Auth::user();

        abort_if(! $this->canVoid(), 403);
        abort_if($payment->status === 'voided', 422, __('payments.flash.already_voided'));
        abort_if(
            $payment->payment_date->lt(today()->subDays(90)),
            422,
            __('payments.flash.void_too_old')
        );

        $staff = Staff::where('user_id', $user->id)->where('tenant_id', $tenantId)->first();

        DB::transaction(function () use ($payment, $request, $staff, $tenantId, $user): void {
            $payment->update([
                'status'      => 'voided',
                'voided_at'   => now(),
                'void_reason' => $request->void_reason,
                'voided_by'   => $staff?->id,
            ]);
            $this->syncMemberBalance($payment->member);

            // Audit log
            OwnerAuditLog::create([
                'tenant_id'      => $tenantId,
                'actor_user_id'  => $user->id,
                'action_type'    => 'payment_voided',
                'target_type'    => 'payment',
                'target_id'      => $payment->id,
                'target_name'    => $payment->receipt_number,
                'payload'        => [
                    'void_reason'  => $request->void_reason,
                    'total_paise'  => $payment->total_paise,
                    'member_id'    => $payment->member_id,
                    'member_name'  => $payment->member->name,
                ],
            ]);
        });
    }

    // ── Receipt data ──────────────────────────────────────────────────────────

    public function receiptData(Payment $payment, int $tenantId): array
    {
        abort_if($payment->tenant_id !== $tenantId, 404);

        $payment->load(['member', 'branch', 'plan', 'collectedBy', 'splits']);

        $tenant = \App\Models\Tenant::findOrFail($tenantId);

        return compact('payment', 'tenant');
    }

    // ── Member search (AJAX) ──────────────────────────────────────────────────

    public function memberSearch(string $term, int $tenantId): array
    {
        return Member::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where(function ($q) use ($term): void {
                $q->where('name', 'ilike', '%' . $term . '%')
                    ->orWhere('phone', 'ilike', '%' . $term . '%')
                    ->orWhere('member_code', 'ilike', '%' . $term . '%');
            })
            ->limit(10)
            ->get()
            ->map(fn ($m) => [
                'id'            => $m->id,
                'name'          => $m->name,
                'phone'         => $m->phone,
                'member_code'   => $m->member_code,
                'plan_id'       => $m->plan_id,
                'branch_id'     => $m->branch_id,
                'balance_paise' => $m->balance_paise,
                'pending_due_paise' => max(0, (int) -$m->balance_paise),
            ])
            ->all();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function generateReceiptNumber(int $tenantId): string
    {
        $max = Payment::where('tenant_id', $tenantId)->max('id') ?? 0;
        return 'REC-' . str_pad($max + 1, 5, '0', STR_PAD_LEFT);
    }

    public function syncMemberBalance(Member $member): void
    {
        $duePaise = Payment::where('tenant_id', $member->tenant_id)
            ->where('member_id', $member->id)
            ->where('status', 'active')
            ->sum('due_paise');

        $member->update([
            'balance_paise' => $duePaise > 0 ? -$duePaise : 0,
        ]);
    }
}
