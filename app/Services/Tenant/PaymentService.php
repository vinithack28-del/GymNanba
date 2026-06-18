<?php

namespace App\Services\Tenant;

use App\Models\Member;
use App\Models\OwnerAuditLog;
use App\Models\Payment;
use App\Models\GymMembershipPlan;
use App\Models\Branch;
use App\Models\Staff;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $user = Auth::user();
        $member = Member::where('tenant_id', $tenantId)->findOrFail($request->member_id);
        $plan   = $request->plan_id
            ? GymMembershipPlan::where('tenant_id', $tenantId)->findOrFail($request->plan_id)
            : null;

        $amountPaise = (int) round($request->amount * 100);

        // Compute GST
        $gstPaise = 0;
        if ($plan && $plan->gst_applicable && $plan->gst_rate > 0) {
            $gstPaise = (int) round($amountPaise * ($plan->gst_rate / 100));
        } elseif ($request->apply_gst && $request->gst_rate > 0) {
            $gstPaise = (int) round($amountPaise * ($request->gst_rate / 100));
        }

        $totalPaise = $amountPaise + $gstPaise;

        return DB::transaction(function () use ($request, $tenantId, $member, $plan, $amountPaise, $gstPaise, $totalPaise, $user) {
            $receipt = $this->generateReceiptNumber($tenantId);

            $staff = Staff::where('user_id', $user->id)->where('tenant_id', $tenantId)->first();

            $payment = Payment::create([
                'tenant_id'      => $tenantId,
                'member_id'      => $member->id,
                'branch_id'      => $request->branch_id ?? $member->branch_id,
                'plan_id'        => $request->plan_id ?: null,
                'receipt_number' => $receipt,
                'amount_paise'   => $amountPaise,
                'gst_paise'      => $gstPaise,
                'total_paise'    => $totalPaise,
                'method'         => $request->method,
                'reference'      => $request->reference ?: null,
                'payment_date'   => $request->payment_date ?: today()->toDateString(),
                'notes'          => $request->notes ?: null,
                'status'         => 'active',
                'collected_by'   => $staff?->id,
            ]);

            // Update member balance
            $member->increment('balance_paise', $totalPaise);

            // Auto-generate invoice
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                (new \App\Services\Tenant\InvoiceService())->createFromPayment($payment, $tenant);
            }

            return $payment;
        });
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
            ->paginate(25)
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

        return compact('payments', 'branches', 'todaySummary');
    }

    // ── Pending dues ──────────────────────────────────────────────────────────

    public function dues(Request $request, int $tenantId): array
    {
        $user = Auth::user();

        $query = Member::where('members.tenant_id', $tenantId)
            ->where('members.status', 'active')
            ->where('members.balance_paise', '<', 0);

        if (in_array($user->role, ['branch_manager', 'branch_admin'])) {
            $staff = Staff::where('user_id', $user->id)->where('tenant_id', $tenantId)->first();
            if ($staff?->branch_id) {
                $query->where('members.branch_id', $staff->branch_id);
            }
        }

        if ($request->branch_id) {
            $query->where('members.branch_id', $request->branch_id);
        }

        if ($request->search) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s): void {
                $q->where('members.name', 'ilike', $s)->orWhere('members.phone', 'ilike', $s);
            });
        }

        $members = $query->with('branch')
            ->orderBy('members.balance_paise') // most negative first
            ->paginate(25)
            ->withQueryString();

        $branches = Branch::forTenant($tenantId)->active()->orderBy('name')->get();

        $totalDuePaise = Member::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('balance_paise', '<', 0)
            ->sum('balance_paise');

        return compact('members', 'branches', 'totalDuePaise');
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

            // Reverse member balance
            $payment->member->decrement('balance_paise', $payment->total_paise);

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

        $payment->load(['member', 'branch', 'plan', 'collectedBy']);

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
                'branch_id'     => $m->branch_id,
                'balance_paise' => $m->balance_paise,
            ])
            ->all();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function generateReceiptNumber(int $tenantId): string
    {
        $max = Payment::where('tenant_id', $tenantId)->max('id') ?? 0;
        return 'REC-' . str_pad($max + 1, 5, '0', STR_PAD_LEFT);
    }
}
