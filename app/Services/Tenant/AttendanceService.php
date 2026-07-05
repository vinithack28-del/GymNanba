<?php

namespace App\Services\Tenant;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\GymMembershipPlan;
use App\Models\Member;
use App\Models\Payment;
use App\Models\PaymentSplit;
use App\Models\Staff;
use App\Models\WalkIn;
use App\Models\WalkInFollowup;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    // â”€â”€ Check-in log â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function listCheckins(object $user, Request $request): array
    {
        $date     = $request->get('date', now()->toDateString());
        $branchId = $this->resolveBranch($user, $request->get('branch_id'));
        $method   = $request->get('method');
        $search   = trim((string) $request->get('search'));

        $query = AttendanceLog::query()
            ->forTenant($user->tenant_id)
            ->with(['member', 'branch', 'checkedInBy'])
            ->whereDate('checked_in_at', $date)
            ->orderByDesc('checked_in_at');

        if ($branchId) {
            $query->forBranch($branchId);
        }

        if ($method) {
            $query->where('method', $method);
        }

        if ($search) {
            $query->whereHas('member', fn ($q) => $q
                ->where('name', 'ilike', "%{$search}%")
                ->orWhere('member_code', 'ilike', "%{$search}%")
                ->orWhere('phone', 'ilike', "%{$search}%")
            );
        }

        $perPage = min(max((int) $request->get('per_page', 25), 10), 100);
        $logs = $query->paginate($perPage)->withQueryString();

        return [
            'logs'        => $logs,
            'stats'       => $this->checkinStats($user->tenant_id, $date, $branchId),
            'branches'    => Branch::forTenant($user->tenant_id)->active()->orderBy('name')->get(),
            'methods'     => AttendanceLog::METHODS,
            'filters'     => [
                'date' => $date,
                'branch_id' => $branchId,
                'method' => $method,
                'search' => $search,
                'per_page' => $perPage,
            ],
            'canManage'   => $this->canManage($user),
            'canCheckin'  => $this->canCheckin($user),
        ];
    }

    public function storeCheckin(object $user, array $validated): AttendanceLog
    {
        $member = Member::query()
            ->forTenant($user->tenant_id)
            ->with('plan')
            ->findOrFail($validated['member_id']);

        if ($member->status === 'inactive') {
            throw ValidationException::withMessages([
                'member_id' => 'Member is inactive and cannot check in.',
            ]);
        }

        if ($member->expiry_date && $member->expiry_date->isPast()) {
            throw ValidationException::withMessages([
                'member_id' => 'Membership has expired. Renew before check-in.',
            ]);
        }

        // Check for already open check-in today
        $openToday = AttendanceLog::query()
            ->forTenant($user->tenant_id)
            ->where('member_id', $member->id)
            ->whereDate('checked_in_at', now()->toDateString())
            ->whereNull('checked_out_at')
            ->exists();

        if ($openToday && ! ($validated['force'] ?? false)) {
            throw ValidationException::withMessages([
                'member_id' => 'Member already has an open check-in today.',
            ]);
        }

        if ($member->plan?->isSessionBased()) {
            $sessionLimit = (int) $member->plan->session_limit;
            $usedSessions = $this->memberUsedSessions($member);

            if ($usedSessions >= $sessionLimit) {
                throw ValidationException::withMessages([
                    'member_id' => "Session limit reached ({$usedSessions}/{$sessionLimit}). Renew before check-in.",
                ]);
            }
        }

        $branchId = $this->resolveBranch($user, $validated['branch_id'] ?? null)
            ?? $user->branch_id
            ?? Branch::forTenant($user->tenant_id)->active()->value('id');
        $checkedInBy = Staff::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->value('id');

        return AttendanceLog::query()->create([
            'tenant_id'     => $user->tenant_id,
            'member_id'     => $member->id,
            'branch_id'     => $branchId,
            'method'        => $validated['method'] ?? 'manual',
            'checked_in_at' => now(),
            'reason'        => $validated['reason'] ?? null,
            'checked_in_by' => $checkedInBy,
            'created_at'    => now(),
        ]);
    }

    public function checkout(object $user, AttendanceLog $log): AttendanceLog
    {
        abort_unless($log->tenant_id === $user->tenant_id, 403);
        abort_unless(is_null($log->checked_out_at), 422, 'Already checked out.');

        $log->update(['checked_out_at' => now()]);

        return $log->fresh();
    }

    public function destroy(object $user, AttendanceLog $log): void
    {
        abort_unless($log->tenant_id === $user->tenant_id, 403);
        abort_unless($this->canManage($user), 403);

        $log->delete();
    }

    public function memberSearch(object $user, string $query): Collection
    {
        if (strlen($query) < 2) {
            return collect();
        }

        return Member::query()
            ->forTenant($user->tenant_id)
            ->where('status', '!=', 'inactive')
            ->where(fn ($q) => $q
                ->where('name', 'ilike', "%{$query}%")
                ->orWhere('phone', 'ilike', "%{$query}%")
                ->orWhere('member_code', 'ilike', "%{$query}%")
            )
            ->with('plan')
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'member_code', 'photo_url', 'plan_name', 'expiry_date', 'status']);
    }

    public function exportCheckinsCsv(object $user, Request $request): string
    {
        $date     = $request->get('date', now()->toDateString());
        $branchId = $this->resolveBranch($user, $request->get('branch_id'));
        $method   = $request->get('method');

        $query = AttendanceLog::query()
            ->forTenant($user->tenant_id)
            ->with(['member', 'branch', 'checkedInBy'])
            ->whereDate('checked_in_at', $date)
            ->orderByDesc('checked_in_at');

        if ($branchId) {
            $query->forBranch($branchId);
        }
        if ($method) {
            $query->where('method', $method);
        }

        $rows = $query->get();

        $lines = ["Date,Time,Member name,Member ID,Plan,Branch,Method,Check-out,Duration,Logged by"];

        foreach ($rows as $log) {
            $lines[] = implode(',', [
                $log->checked_in_at->toDateString(),
                $log->checked_in_at->format('H:i'),
                '"'.str_replace('"', '""', $log->member?->name ?? '').'"',
                $log->member?->member_code ?? '',
                '"'.str_replace('"', '""', $log->member?->plan_name ?? '').'"',
                '"'.str_replace('"', '""', $log->branch?->name ?? '').'"',
                $log->method,
                $log->checked_out_at?->format('H:i') ?? '',
                $log->duration ?? '',
                '"'.str_replace('"', '""', $log->checkedInBy?->name ?? '').'"',
            ]);
        }

        return implode("\n", $lines);
    }

    // â”€â”€ Walk-ins â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function listWalkins(object $user, Request $request): array
    {
        $date          = $request->get('date', now()->toDateString());
        $branchId      = $this->resolveBranch($user, $request->get('branch_id'));
        $todayFollowup = (bool) $request->get('today_followup', false);
        $followupDate  = $request->get('followup_date');

        $base = WalkIn::query()
            ->forTenant($user->tenant_id)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        if ($todayFollowup) {
            $logs = (clone $base)
                ->enquiries()
                ->whereHas('followups', fn ($q) => $q->whereDate('next_followup_date', now()->toDateString()))
                ->with(['branch', 'followups'])
                ->orderByDesc('created_at')
                ->paginate(30)
                ->withQueryString();
        } elseif ($followupDate) {
            $logs = (clone $base)
                ->enquiries()
                ->whereHas('followups', fn ($q) => $q->whereDate('next_followup_date', $followupDate))
                ->with(['branch', 'followups'])
                ->orderByDesc('created_at')
                ->paginate(30)
                ->withQueryString();
        } else {
            $logs = (clone $base)
                ->with(['branch', 'guestOf', 'followups'])
                ->whereDate('created_at', $date)
                ->orderByDesc('created_at')
                ->paginate(30)
                ->withQueryString();
        }

        $todayTotal   = (clone $base)->whereDate('created_at', $date)->count();
        $todayRevenue = (clone $base)->whereDate('created_at', $date)->sum('fee_paise');

        $todayFollowupCount = (clone $base)
            ->enquiries()
            ->whereHas('followups', fn ($q) => $q->whereDate('next_followup_date', now()->toDateString()))
            ->count();

        $dayPassPlans = GymMembershipPlan::query()
            ->forTenant($user->tenant_id)
            ->active()
            ->get()
            ->filter(fn (GymMembershipPlan $plan) => $plan->isOneDayPass())
            ->values();
        $dayPassPlans->each->append(['duration_label', 'total_price_paise', 'gst_amount_paise']);

        return [
            'logs'               => $logs,
            'branches'           => Branch::forTenant($user->tenant_id)->active()->orderBy('name')->get(),
            'members'            => Member::forTenant($user->tenant_id)->where('status', 'active')->orderBy('name')->get(['id', 'name', 'member_code']),
            'dayPassPlans'       => $dayPassPlans,
            'purposes'           => WalkIn::PURPOSES,
            'methods'            => WalkIn::METHODS,
            'date'               => $date,
            'followupDate'       => $followupDate,
            'todayTotal'         => $todayTotal,
            'todayRevenue'       => $todayRevenue,
            'todayFollowup'      => $todayFollowup,
            'todayFollowupCount' => $todayFollowupCount,
            'canManage'          => $this->canManage($user),
            'canAddMembers'      => method_exists($user, 'canAccess') ? $user->canAccess('members.add') : $this->canManage($user),
        ];
    }

    public function storeFollowup(object $user, WalkIn $walkIn, array $validated): WalkInFollowup
    {
        abort_unless($walkIn->tenant_id === $user->tenant_id, 403);

        $followup = WalkInFollowup::query()->create([
            'walk_in_id'        => $walkIn->id,
            'tenant_id'         => $user->tenant_id,
            'outcome'           => $validated['outcome'],
            'notes'             => $validated['notes'] ?? null,
            'next_followup_date'=> $validated['next_followup_date'] ?? null,
            'logged_by'         => $user->id,
            'created_at'        => now(),
        ]);

        // Update enquiry_status on the parent walk-in
        $newStatus = match ($validated['outcome']) {
            'converted'      => 'converted',
            'not_interested' => 'closed',
            default          => 'followed_up',
        };

        $walkIn->update(['enquiry_status' => $newStatus]);

        return $followup;
    }

    public function storeWalkin(object $user, array $validated): WalkIn
    {
        $branchId = $this->resolveBranch($user, $validated['branch_id'] ?? null)
            ?? $user->branch_id
            ?? Branch::forTenant($user->tenant_id)->active()->value('id');

        return DB::transaction(function () use ($user, $validated, $branchId): WalkIn {
            $plan = null;
            $feePaise = (int) ($validated['fee_paise'] ?? 0);
            $member = null;
            $paymentMethods = array_values(array_unique($validated['payment_methods'] ?? []));
            $paymentMeta = [];
            $paymentMethodSummary = null;
            $referenceSummary = null;

            if (($validated['purpose'] ?? null) === 'day_pass') {
                abort_if(empty($validated['plan_id']), 422, 'Please select a one-day membership plan.');
                abort_if(empty($paymentMethods), 422, 'Please select at least one payment method for day pass.');

                $plan = GymMembershipPlan::query()
                    ->forTenant($user->tenant_id)
                    ->active()
                    ->findOrFail($validated['plan_id']);

                abort_unless($plan->isOneDayPass(), 422, 'Selected plan is not a one-day membership plan.');

                $feePaise = $plan->total_price_paise;
                [$paymentMeta, $paymentMethodSummary, $referenceSummary, $paidPaise] = $this->buildWalkinPaymentMeta(
                    $paymentMethods,
                    $validated['amounts'] ?? [],
                    $validated['references'] ?? []
                );
                abort_if($paidPaise !== $feePaise, 422, 'Payment method amounts must exactly match the day pass fee.');
                $member = $this->createDayPassMember($user, $validated, $branchId, $plan);
                $this->createDayPassPayment($user, $validated, $branchId, $plan, $member, $feePaise, $paymentMeta);
            }

            return WalkIn::query()->create([
                'tenant_id'      => $user->tenant_id,
                'branch_id'      => $branchId,
                'name'           => $validated['name'],
                'phone'          => $validated['phone'],
                'purpose'        => $validated['purpose'],
                'fee_paise'      => $feePaise,
                'plan_id'        => $plan?->id,
                'member_id'      => $member?->id,
                'payment_method' => $paymentMethodSummary,
                'payment_meta'   => $paymentMeta ?: null,
                'reference'      => $referenceSummary,
                'notes'          => $validated['notes'] ?? null,
                'guest_of_id'    => $validated['guest_of_id'] ?? null,
                'logged_by'      => null,
                'created_at'     => now(),
            ]);
        });
    }

    // â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function checkinStats(int $tenantId, string $date, ?int $branchId): array
    {
        $base = AttendanceLog::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('checked_in_at', $date);

        if ($branchId) {
            $base->where('branch_id', $branchId);
        }

        $total  = (clone $base)->count();
        $unique = (clone $base)->distinct('member_id')->count('member_id');

        // Peak hour: group by hour
        $byHour = (clone $base)
            ->selectRaw("date_part('hour', checked_in_at) as hour, COUNT(*) as cnt")
            ->groupByRaw("date_part('hour', checked_in_at)")
            ->orderByDesc('cnt')
            ->first();

        $peakHour = $byHour
            ? sprintf('%02d:00â€“%02d:00 (%d)', $byHour->hour, $byHour->hour + 1, $byHour->cnt)
            : 'â€”';

        return [
            'total'     => $total,
            'unique'    => $unique,
            'peak_hour' => $peakHour,
        ];
    }

    private function memberUsedSessions(Member $member): int
    {
        return AttendanceLog::query()
            ->where('tenant_id', $member->tenant_id)
            ->where('member_id', $member->id)
            ->when($member->start_date, fn ($q) => $q->whereDate('checked_in_at', '>=', $member->start_date->toDateString()))
            ->count();
    }

    public function canManage(object $user): bool
    {
        return in_array($user->role, ['tenant_owner'], true);
    }

    public function canCheckin(object $user): bool
    {
        return in_array($user->role, ['tenant_owner', 'branch_manager', 'receptionist'], true);
    }

    private function resolveBranch(object $user, mixed $branchId): ?int
    {
        if (in_array($user->role, ['branch_manager', 'branch_admin'], true)) {
            return (int) $user->branch_id;
        }

        return filled($branchId) ? (int) $branchId : null;
    }

    private function createDayPassMember(object $user, array $validated, int $branchId, GymMembershipPlan $plan): Member
    {
        $existingMember = Member::query()
            ->forTenant($user->tenant_id)
            ->where('phone', $validated['phone'])
            ->first();

        if ($existingMember) {
            return $existingMember;
        }

        $startDate = now()->toDateString();
        $expiryDate = $plan->computeExpiryDate($startDate);

        $member = Member::query()->create([
            'tenant_id'      => $user->tenant_id,
            'branch_id'      => $branchId,
            'member_code'    => Member::generateCode($user->tenant_id),
            'name'           => $validated['name'],
            'phone'          => $validated['phone'],
            'plan_id'        => $plan->id,
            'plan_name'      => $plan->name,
            'start_date'     => $startDate,
            'expiry_date'    => $expiryDate,
            'status'         => 'active',
            'balance_paise'  => 0,
            'notes'          => $validated['notes'] ?? null,
            'created_by'     => $user->id ?? null,
        ]);

        return $member;
    }

    private function createDayPassPayment(object $user, array $validated, int $branchId, GymMembershipPlan $plan, Member $member, int $feePaise, array $paymentMeta): void
    {
        if ($feePaise <= 0) {
            return;
        }

        $staff = Staff::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id ?? 0)
            ->first();

        $payment = Payment::query()->create([
            'tenant_id'      => $user->tenant_id,
            'member_id'      => $member->id,
            'branch_id'      => $branchId,
            'plan_id'        => $plan->id,
            'receipt_number' => 'RCP-' . strtoupper(substr(uniqid(), -6)),
            'amount_paise'   => $plan->price_paise,
            'gst_paise'      => $plan->gst_amount_paise,
            'total_paise'    => $feePaise,
            'paid_paise'     => $feePaise,
            'is_partial'     => false,
            'due_paise'      => 0,
            'due_date'       => null,
            'reminder_sent'  => false,
            'method'         => count($paymentMeta) > 1 ? 'split' : ($paymentMeta[0]['method'] ?? 'cash'),
            'reference'      => $paymentMeta[0]['reference'] ?? null,
            'payment_date'   => now()->toDateString(),
            'notes'          => $validated['notes'] ?? null,
            'status'         => 'active',
            'collected_by'   => $staff?->id,
        ]);

        foreach ($paymentMeta as $row) {
            PaymentSplit::query()->create([
                'payment_id'   => $payment->id,
                'method'       => $row['method'],
                'amount_paise' => $row['amount_paise'],
                'reference'    => $row['reference'],
            ]);
        }
    }

    private function buildWalkinPaymentMeta(array $paymentMethods, array $amounts, array $references): array
    {
        $meta = [];
        $paidPaise = 0;

        foreach ($paymentMethods as $method) {
            $amountPaise = (int) round(((float) ($amounts[$method] ?? 0)) * 100);
            $reference = trim((string) ($references[$method] ?? ''));
            abort_if($amountPaise <= 0, 422, 'Each selected payment method must include an amount greater than zero.');

            $meta[] = [
                'method' => $method,
                'amount_paise' => $amountPaise,
                'reference' => $reference !== '' ? $reference : null,
            ];
            $paidPaise += $amountPaise;
        }

        $methodSummary = count($paymentMethods) > 0
            ? implode(', ', $paymentMethods)
            : null;

        $referenceSummary = collect($meta)
            ->map(fn (array $row) => strtoupper($row['method']) . ': Rs. ' . number_format($row['amount_paise'] / 100, 2) . (filled($row['reference']) ? ' (' . $row['reference'] . ')' : ''))
            ->implode(', ');

        return [$meta, $methodSummary, $referenceSummary !== '' ? mb_substr($referenceSummary, 0, 100) : null, $paidPaise];
    }

    // â”€â”€ Sheet view â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function sheetView(object $user, Request $request): array
    {
        $tenantId = $user->tenant_id;
        $monthStr = $request->get('month', now()->format('Y-m'));
        $perPage  = max(5, min(100, (int) $request->get('per_page', 20)));
        $search   = trim((string) $request->get('search'));
        $status   = $request->get('status');
        $branchId = $this->resolveBranch($user, $request->get('branch_id'));

        $start = \Carbon\Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth();
        $end   = $start->copy()->endOfMonth();
        $today = today();
        $days  = $start->daysInMonth;

        // â”€â”€ Members query â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $query = Member::where('tenant_id', $tenantId)->orderBy('name');

        if ($search) {
            $query->where(fn ($q) => $q
                ->where('name', 'ilike', "%{$search}%")
                ->orWhere('member_code', 'ilike', "%{$search}%")
                ->orWhere('phone', 'ilike', "%{$search}%")
            );
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $members = $query->paginate($perPage)->withQueryString();

        // â”€â”€ Global stats across ALL matching members for the month â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $allMemberIds = (clone $query)->pluck('id');

        $pastDaysInMonth = $start->gt($today) ? 0 : (int) min($days, $today->diffInDays($start) + 1);

        $totalCheckins = AttendanceLog::where('tenant_id', $tenantId)
            ->whereIn('member_id', $allMemberIds)
            ->whereBetween('checked_in_at', [$start->toDateTimeString(), $end->endOfDay()->toDateTimeString()])
            ->count();

        $uniqueCheckinDays = AttendanceLog::where('tenant_id', $tenantId)
            ->whereIn('member_id', $allMemberIds)
            ->whereBetween('checked_in_at', [$start->toDateTimeString(), $end->endOfDay()->toDateTimeString()])
            ->selectRaw('member_id, DATE(checked_in_at) AS day')
            ->distinct()
            ->count();

        $totalMembersCount = $allMemberIds->count();
        $totalPossibleDays = $totalMembersCount * $pastDaysInMonth;
        $attendanceRate    = $totalPossibleDays > 0
            ? round(($uniqueCheckinDays / $totalPossibleDays) * 100, 1)
            : 0;

        $sheetStats = [
            'total_members'   => $totalMembersCount,
            'total_checkins'  => $totalCheckins,
            'attendance_rate' => $attendanceRate,
            'past_days'       => $pastDaysInMonth,
        ];

        // â”€â”€ Fetch check-in dates for this page's members in the month â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $memberIds = $members->getCollection()->pluck('id');

        $checkinDates = AttendanceLog::where('tenant_id', $tenantId)
            ->whereIn('member_id', $memberIds)
            ->whereBetween('checked_in_at', [$start->toDateTimeString(), $end->endOfDay()->toDateTimeString()])
            ->selectRaw('member_id, DATE(checked_in_at) AS day')
            ->distinct()
            ->get()
            ->groupBy('member_id')
            ->map(fn ($rows) => $rows->pluck('day')->toArray());

        // â”€â”€ Build per-member grid â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $grid = [];
        foreach ($members as $member) {
            $memberDays = $checkinDates->get($member->id, []);
            $present = 0;
            $absent  = 0;
            $cells   = [];

            for ($d = 1; $d <= $days; $d++) {
                $dateStr = $start->copy()->day($d)->toDateString();
                $isPast  = \Carbon\Carbon::parse($dateStr)->lte($today);

                if (in_array($dateStr, $memberDays)) {
                    $cells[$d] = 'P';
                    $present++;
                } elseif ($isPast) {
                    $cells[$d] = 'A';
                    $absent++;
                } else {
                    $cells[$d] = '-';
                }
            }

            $grid[$member->id] = compact('present', 'absent', 'cells');
        }

        return [
            'members'    => $members,
            'grid'       => $grid,
            'month'      => $monthStr,
            'daysCount'  => $days,
            'branches'   => Branch::forTenant($tenantId)->active()->orderBy('name')->get(),
            'branchId'   => $branchId,
            'perPage'    => $perPage,
            'sheetStats' => $sheetStats,
        ];
    }
}
