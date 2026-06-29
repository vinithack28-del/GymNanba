<?php

namespace App\Services\Tenant;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\Member;
use App\Models\Payment;
use App\Models\WalkIn;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function build(object $user): array
    {
        $tenantId = (int) $user->tenant_id;
        $branchId = $this->resolveBranchId($user);
        $today = now()->startOfDay();
        $weekEnd = now()->copy()->addDays(6)->endOfDay();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $memberBase = Member::query()
            ->forTenant($tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $paymentBase = Payment::query()
            ->forTenant($tenantId)
            ->active()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $attendanceBase = AttendanceLog::query()
            ->forTenant($tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $walkinBase = WalkIn::query()
            ->forTenant($tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $stats = [
            'total_clients' => [
                'label' => 'Total Clients',
                'value' => number_format((clone $memberBase)->count()),
                'sub' => number_format((clone $memberBase)->withStatus('active')->count()) . ' active members',
                'route' => route('tenant.members.index'),
                'visible' => $user->canAccess('members.view|members.add|members.edit|members.delete'),
            ],
            'monthly_revenue' => [
                'label' => 'Monthly Revenue',
                'value' => $this->money((clone $paymentBase)->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])->sum('total_paise')),
                'sub' => now()->format('F Y'),
                'route' => route('tenant.reports.revenue'),
                'visible' => $user->canAccess('payments.collect|payments.history|payments.void|reports.view|reports.revenue_only'),
            ],
            'today_attendance' => [
                'label' => "Today's Attendance",
                'value' => number_format((clone $attendanceBase)->whereDate('checked_in_at', $today->toDateString())->count()),
                'sub' => number_format((clone $attendanceBase)->whereDate('checked_in_at', $today->toDateString())->distinct('member_id')->count('member_id')) . ' unique check-ins',
                'route' => route('tenant.attendance.checkins', ['date' => $today->toDateString()]),
                'visible' => $user->canAccess('attendance.check_in|attendance.view_log'),
            ],
            'active_leads' => [
                'label' => 'Active Leads',
                'value' => number_format((clone $walkinBase)->enquiries()->whereIn('enquiry_status', ['open', 'followed_up'])->count()),
                'sub' => 'Open and followed up enquiries',
                'route' => route('tenant.walkins.index'),
                'visible' => $user->canAccess('attendance.check_in|attendance.view_log'),
            ],
            'expired_not_renewed' => [
                'label' => 'Expired – Not Renewed',
                'value' => number_format((clone $memberBase)->whereNotNull('expiry_date')->whereDate('expiry_date', '<', $today->toDateString())->count()),
                'sub' => 'Already expired memberships',
                'route' => route('tenant.renewals.index', ['tab' => 'expired']),
                'visible' => $user->canAccess('renewals.view'),
            ],
            'today_revenue' => [
                'label' => 'Today Revenue',
                'value' => $this->money((clone $paymentBase)->whereDate('payment_date', $today->toDateString())->sum('total_paise')),
                'sub' => number_format((clone $paymentBase)->whereDate('payment_date', $today->toDateString())->count()) . ' payments today',
                'route' => route('tenant.payments.history'),
                'visible' => $user->canAccess('payments.collect|payments.history|payments.void|reports.view|reports.revenue_only'),
            ],
            'expires_today' => [
                'label' => 'Expires Today',
                'value' => number_format((clone $memberBase)->whereDate('expiry_date', $today->toDateString())->count()),
                'sub' => 'Members due for renewal today',
                'route' => route('tenant.renewals.index', ['tab' => 'today']),
                'visible' => $user->canAccess('renewals.view'),
            ],
            'month_clients' => [
                'label' => 'This Month Clients',
                'value' => number_format((clone $memberBase)->whereBetween('created_at', [$monthStart, $monthEnd])->count()),
                'sub' => 'New members added this month',
                'route' => route('tenant.members.index'),
                'visible' => $user->canAccess('members.view|members.add|members.edit|members.delete'),
            ],
        ];

        $recentPayments = (clone $paymentBase)
            ->with(['member:id,name', 'plan:id,name'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $expiredMembers = (clone $memberBase)
            ->with('plan:id,name')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', $today->toDateString())
            ->orderBy('expiry_date')
            ->limit(12)
            ->get();

        $birthdays = $this->birthdayMembers($memberBase, $today, $weekEnd);

        $renewalTabs = [
            'today' => ['label' => 'Today', 'days' => 0],
            '3d' => ['label' => '3d', 'days' => 3],
            '5d' => ['label' => '5d', 'days' => 5],
            '7d' => ['label' => '7d', 'days' => 7],
            '10d' => ['label' => '10d', 'days' => 10],
        ];

        $upcomingRenewals = [];
        foreach ($renewalTabs as $key => $tab) {
            $query = (clone $memberBase)
                ->with('plan:id,name')
                ->whereNotNull('expiry_date');

            if ($tab['days'] === 0) {
                $query->whereDate('expiry_date', $today->toDateString());
            } else {
                $query->whereDate('expiry_date', '>', $today->toDateString())
                    ->whereDate('expiry_date', '<=', $today->copy()->addDays($tab['days'])->toDateString());
            }

            $upcomingRenewals[$key] = $query
                ->orderBy('expiry_date')
                ->limit(20)
                ->get();
        }

        return [
            'branch' => $this->selectedBranch($tenantId, $branchId),
            'stats' => array_values(array_filter($stats, fn (array $card) => $card['visible'])),
            'revenueChart' => $this->monthlyRevenueChart($paymentBase),
            'checkinChart' => $this->weeklyCheckinChart($attendanceBase),
            'recentPayments' => $recentPayments,
            'birthdays' => $birthdays,
            'expiredMembers' => $expiredMembers,
            'renewalTabs' => $renewalTabs,
            'upcomingRenewals' => $upcomingRenewals,
            'canViewRevenue' => $user->canAccess('payments.collect|payments.history|payments.void|reports.view|reports.revenue_only'),
            'canViewAttendance' => $user->canAccess('attendance.check_in|attendance.view_log'),
            'canViewRenewals' => $user->canAccess('renewals.view'),
            'canViewMembers' => $user->canAccess('members.view|members.add|members.edit|members.delete'),
            'canViewWalkins' => $user->canAccess('attendance.check_in|attendance.view_log'),
        ];
    }

    private function resolveBranchId(object $user): ?int
    {
        if (method_exists($user, 'effectiveBranchId')) {
            $id = $user->effectiveBranchId();
            return $id ? (int) $id : null;
        }

        return null;
    }

    private function selectedBranch(int $tenantId, ?int $branchId): ?Branch
    {
        if (!$branchId) {
            return null;
        }

        return Branch::query()->forTenant($tenantId)->find($branchId);
    }

    private function monthlyRevenueChart($paymentBase): array
    {
        $months = collect(range(5, 0))
            ->map(fn ($offset) => now()->copy()->startOfMonth()->subMonths($offset))
            ->sortBy(fn (Carbon $month) => $month->timestamp)
            ->values();

        $rows = (clone $paymentBase)
            ->selectRaw("DATE_TRUNC('month', payment_date)::date as month_date, COALESCE(SUM(total_paise),0) as total")
            ->whereDate('payment_date', '>=', now()->copy()->startOfMonth()->subMonths(5)->toDateString())
            ->groupByRaw("DATE_TRUNC('month', payment_date)::date")
            ->orderByRaw("DATE_TRUNC('month', payment_date)::date")
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->month_date)->format('Y-m'));

        return [
            'labels' => $months->map(fn (Carbon $month) => $month->format('M Y'))->all(),
            'values' => $months->map(fn (Carbon $month) => (int) ($rows[$month->format('Y-m')]->total ?? 0))->all(),
        ];
    }

    private function weeklyCheckinChart($attendanceBase): array
    {
        $days = collect(range(6, 0))
            ->map(fn ($offset) => now()->copy()->startOfDay()->subDays($offset))
            ->sortBy(fn (Carbon $day) => $day->timestamp)
            ->values();

        $rows = (clone $attendanceBase)
            ->selectRaw("DATE(checked_in_at) as day_date, COUNT(*) as total")
            ->whereDate('checked_in_at', '>=', now()->copy()->startOfDay()->subDays(6)->toDateString())
            ->groupByRaw("DATE(checked_in_at)")
            ->orderByRaw("DATE(checked_in_at)")
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->day_date)->format('Y-m-d'));

        return [
            'labels' => $days->map(fn (Carbon $day) => $day->format('D'))->all(),
            'values' => $days->map(fn (Carbon $day) => (int) ($rows[$day->format('Y-m-d')]->total ?? 0))->all(),
        ];
    }

    private function birthdayMembers($memberBase, Carbon $today, Carbon $weekEnd): Collection
    {
        return (clone $memberBase)
            ->whereNotNull('dob')
            ->get()
            ->map(function (Member $member) use ($today) {
                $nextBirthday = $member->dob->copy()->year($today->year);
                if ($nextBirthday->lt($today->copy()->startOfDay())) {
                    $nextBirthday->addYear();
                }

                $member->next_birthday = $nextBirthday;
                $member->birthday_bucket = $nextBirthday->isSameDay($today) ? 'today' : 'week';
                return $member;
            })
            ->filter(fn (Member $member) => $member->next_birthday->between($today, $weekEnd))
            ->sortBy(fn (Member $member) => $member->next_birthday->timestamp)
            ->values();
    }

    private function money(int $paise): string
    {
        return '₹' . number_format($paise / 100, 2);
    }
}
