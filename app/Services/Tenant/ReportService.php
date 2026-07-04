<?php

namespace App\Services\Tenant;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\GymMembershipPlan;
use App\Models\Member;
use App\Models\Payment;
use App\Models\WalkIn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportService
{
    // â”€â”€ Access control â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function canRevenue(): bool
    {
        return in_array(request()->user()->role, ['tenant_owner', 'accountant'], true);
    }

    public function canMembers(): bool
    {
        return request()->user()->role === 'tenant_owner';
    }

    public function canAttendance(): bool
    {
        return in_array(request()->user()->role, ['tenant_owner', 'branch_manager', 'branch_admin'], true);
    }

    public function canStaff(): bool
    {
        return in_array(request()->user()->role, ['tenant_owner', 'branch_manager', 'branch_admin'], true);
    }

    // â”€â”€ Date range resolver â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function resolveRange(Request $request): array
    {
        $preset = $request->get('range', 'this_month');
        $now    = now()->setTimezone('Asia/Kolkata');

        switch ($preset) {
            case 'today':
                $from = $now->copy()->startOfDay();
                $to   = $now->copy()->endOfDay();
                $prevFrom = $from->copy()->subDay()->startOfDay();
                $prevTo   = $from->copy()->subDay()->endOfDay();
                break;
            case 'this_week':
                $from = $now->copy()->startOfWeek(Carbon::MONDAY);
                $to   = $now->copy()->endOfWeek(Carbon::SUNDAY);
                $prevFrom = $from->copy()->subWeek();
                $prevTo   = $to->copy()->subWeek();
                break;
            case 'last_month':
                $from = $now->copy()->subMonth()->startOfMonth();
                $to   = $from->copy()->endOfMonth();
                $prevFrom = $from->copy()->subMonth()->startOfMonth();
                $prevTo   = $prevFrom->copy()->endOfMonth();
                break;
            case 'q1':
                $from = $now->copy()->month(1)->startOfMonth();
                $to   = $now->copy()->month(3)->endOfMonth();
                $prevFrom = $from->copy()->subYear();
                $prevTo   = $to->copy()->subYear();
                break;
            case 'q2':
                $from = $now->copy()->month(4)->startOfMonth();
                $to   = $now->copy()->month(6)->endOfMonth();
                $prevFrom = $from->copy()->subYear();
                $prevTo   = $to->copy()->subYear();
                break;
            case 'q3':
                $from = $now->copy()->month(7)->startOfMonth();
                $to   = $now->copy()->month(9)->endOfMonth();
                $prevFrom = $from->copy()->subYear();
                $prevTo   = $to->copy()->subYear();
                break;
            case 'q4':
                $from = $now->copy()->month(10)->startOfMonth();
                $to   = $now->copy()->month(12)->endOfMonth();
                $prevFrom = $from->copy()->subYear();
                $prevTo   = $to->copy()->subYear();
                break;
            case 'last_3_months':
                $from = $now->copy()->subMonths(3)->startOfDay();
                $to   = $now->copy()->endOfDay();
                $prevFrom = $from->copy()->subMonths(3);
                $prevTo   = $from->copy()->subDay()->endOfDay();
                break;
            case 'last_6_months':
                $from = $now->copy()->subMonths(6)->startOfDay();
                $to   = $now->copy()->endOfDay();
                $prevFrom = $from->copy()->subMonths(6);
                $prevTo   = $from->copy()->subDay()->endOfDay();
                break;
            case 'last_year':
                $from = $now->copy()->subYear()->startOfDay();
                $to   = $now->copy()->endOfDay();
                $prevFrom = $from->copy()->subYear();
                $prevTo   = $from->copy()->subDay()->endOfDay();
                break;
            case 'custom':
                $from     = $request->filled('from') ? Carbon::parse($request->get('from'))->startOfDay() : $now->copy()->startOfMonth();
                $to       = $request->filled('to')   ? Carbon::parse($request->get('to'))->endOfDay()    : $now->copy()->endOfDay();
                $days     = max(1, (int) $from->diffInDays($to) + 1);
                $prevFrom = $from->copy()->subDays($days)->startOfDay();
                $prevTo   = $from->copy()->subDay()->endOfDay();
                break;
            default: // this_month
                $from = $now->copy()->startOfMonth();
                $to   = $now->copy()->endOfMonth();
                $prevFrom = $from->copy()->subMonth()->startOfMonth();
                $prevTo   = $prevFrom->copy()->endOfMonth();
                break;
        }

        return compact('preset', 'from', 'to', 'prevFrom', 'prevTo');
    }

    // â”€â”€ Revenue report â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function revenue(Request $request, int $tenantId): array
    {
        $range    = $this->resolveRange($request);
        $branchId = $this->resolveBranchId($request);
        $planId   = $request->get('plan_id');

        $base = Payment::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($planId, fn ($q) => $q->where('plan_id', $planId));

        // KPIs
        $current = (clone $base)
            ->whereBetween('payment_date', [$range['from']->toDateString(), $range['to']->toDateString()])
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(total_paise),0) as total, COALESCE(SUM(gst_paise),0) as gst')
            ->first();

        $prevTotal = (clone $base)
            ->whereBetween('payment_date', [$range['prevFrom']->toDateString(), $range['prevTo']->toDateString()])
            ->sum('total_paise');

        $vsChange = ($prevTotal > 0) ? round((($current->total - $prevTotal) / $prevTotal) * 100, 1) : null;

        $pendingDues = Member::where('tenant_id', $tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('balance_paise', '<', 0)
            ->sum('balance_paise');

        // Trend (daily)
        $trend = (clone $base)
            ->whereBetween('payment_date', [$range['from']->toDateString(), $range['to']->toDateString()])
            ->selectRaw("payment_date::text as date, COUNT(*) as cnt, SUM(total_paise) as total")
            ->groupBy('payment_date')
            ->orderBy('payment_date')
            ->get();

        // By plan
        $planNames = GymMembershipPlan::where('tenant_id', $tenantId)->pluck('name', 'id');
        $byPlan = (clone $base)
            ->whereBetween('payment_date', [$range['from']->toDateString(), $range['to']->toDateString()])
            ->whereNotNull('plan_id')
            ->selectRaw('plan_id, SUM(total_paise) as total')
            ->groupBy('plan_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['label' => $planNames->get($r->plan_id, 'Unknown'), 'total' => (int) $r->total]);

        // By method
        $byMethod = (clone $base)
            ->whereBetween('payment_date', [$range['from']->toDateString(), $range['to']->toDateString()])
            ->selectRaw('method, COUNT(*) as cnt, SUM(total_paise) as total')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['method' => $r->method, 'cnt' => (int) $r->cnt, 'total' => (int) $r->total]);

        // By branch (only for multi-branch without branch filter)
        $branches = Branch::forTenant($tenantId)->orderBy('name')->get();
        $byBranch = null;
        if ($branches->count() > 1 && !$branchId) {
            $branchNames = $branches->pluck('name', 'id');
            $byBranch = (clone $base)
                ->whereBetween('payment_date', [$range['from']->toDateString(), $range['to']->toDateString()])
                ->selectRaw('branch_id, SUM(total_paise) as total')
                ->groupBy('branch_id')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($r) => ['label' => $branchNames->get($r->branch_id, 'Unknown'), 'total' => (int) $r->total]);
        }

        // Daily breakdown (for table)
        $daily = (clone $base)
            ->whereBetween('payment_date', [$range['from']->toDateString(), $range['to']->toDateString()])
            ->selectRaw("payment_date::text as date, COUNT(*) as cnt, SUM(amount_paise) as subtotal, SUM(gst_paise) as gst, SUM(total_paise) as total")
            ->groupBy('payment_date')
            ->orderByDesc('payment_date')
            ->get();

        // Top 10 paying members
        $topRows = (clone $base)
            ->whereBetween('payment_date', [$range['from']->toDateString(), $range['to']->toDateString()])
            ->selectRaw('member_id, COUNT(*) as cnt, SUM(total_paise) as total')
            ->groupBy('member_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $memberNames = Member::where('tenant_id', $tenantId)
            ->whereIn('id', $topRows->pluck('member_id'))
            ->pluck('name', 'id');
        $memberPlans = Member::where('tenant_id', $tenantId)
            ->whereIn('id', $topRows->pluck('member_id'))
            ->pluck('plan_name', 'id');

        $topMembers = $topRows->map(fn ($r) => [
            'name'  => $memberNames->get($r->member_id, 'â€”'),
            'plan'  => $memberPlans->get($r->member_id, 'â€”'),
            'cnt'   => (int) $r->cnt,
            'total' => (int) $r->total,
        ]);

        return [
            'range'      => $range,
            'branches'   => $branches,
            'plans'      => GymMembershipPlan::where('tenant_id', $tenantId)->active()->orderBy('name')->get(),
            'branchId'   => $branchId,
            'planId'     => $planId,
            'kpis'       => [
                'total'       => (int) $current->total,
                'count'       => (int) $current->cnt,
                'avg'         => $current->cnt > 0 ? (int) round($current->total / $current->cnt) : 0,
                'gst'         => (int) $current->gst,
                'vsChange'    => $vsChange,
                'pendingDues' => abs((int) $pendingDues),
            ],
            'trend'      => $trend,
            'byPlan'     => $byPlan,
            'byMethod'   => $byMethod,
            'byBranch'   => $byBranch,
            'daily'      => $daily,
            'topMembers' => $topMembers,
        ];
    }

    // â”€â”€ Members report â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function members(Request $request, int $tenantId): array
    {
        $range    = $this->resolveRange($request);
        $branchId = $this->resolveBranchId($request);
        $planId   = $request->get('plan_id');

        $base = Member::where('tenant_id', $tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($planId, fn ($q) => $q->where('plan_id', $planId));

        $newCount = (clone $base)->whereBetween('created_at', [$range['from'], $range['to']])->count();
        $prevNew  = (clone $base)->whereBetween('created_at', [$range['prevFrom'], $range['prevTo']])->count();

        $churned  = (clone $base)
            ->whereBetween('expiry_date', [$range['from']->toDateString(), $range['to']->toDateString()])
            ->where('status', '!=', 'active')
            ->count();

        $activeAtStart = (clone $base)->where('created_at', '<', $range['from'])->where('status', 'active')->count();
        $churnRate     = $activeAtStart > 0 ? round(($churned / $activeAtStart) * 100, 1) : 0.0;

        // Trend: new registrations per day
        $trend = (clone $base)
            ->whereBetween('created_at', [$range['from'], $range['to']])
            ->selectRaw("DATE(created_at) as date, COUNT(*) as cnt")
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->get();

        // By plan
        $planNames = GymMembershipPlan::where('tenant_id', $tenantId)->pluck('name', 'id');
        $byPlan = (clone $base)
            ->whereNotNull('plan_id')
            ->selectRaw('plan_id, COUNT(*) as cnt')
            ->groupBy('plan_id')
            ->orderByDesc('cnt')
            ->get()
            ->map(fn ($r) => ['label' => $planNames->get($r->plan_id, 'Unknown'), 'cnt' => (int) $r->cnt]);

        // By gender
        $byGender = (clone $base)
            ->whereNotNull('gender')
            ->selectRaw("gender, COUNT(*) as cnt")
            ->groupBy('gender')
            ->get()
            ->map(fn ($r) => ['label' => ucfirst($r->gender ?: 'Unknown'), 'cnt' => (int) $r->cnt]);

        // By age group (PostgreSQL)
        $branchClause = $branchId ? "AND branch_id = {$branchId}" : '';
        $byAge = collect(DB::select(
            "SELECT
                CASE
                    WHEN dob IS NULL THEN 'Unknown'
                    WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, dob)) < 18 THEN '<18'
                    WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, dob)) BETWEEN 18 AND 25 THEN '18â€“25'
                    WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, dob)) BETWEEN 26 AND 35 THEN '26â€“35'
                    WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, dob)) BETWEEN 36 AND 45 THEN '36â€“45'
                    WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, dob)) BETWEEN 46 AND 60 THEN '46â€“60'
                    ELSE '60+'
                END as age_group,
                COUNT(*) as cnt
             FROM members
             WHERE tenant_id = ? {$branchClause}
             GROUP BY age_group
             ORDER BY cnt DESC",
            [$tenantId]
        ))->map(fn ($r) => ['label' => $r->age_group, 'cnt' => (int) $r->cnt]);

        // By branch
        $branches  = Branch::forTenant($tenantId)->orderBy('name')->get();
        $byBranch  = (clone $base)
            ->selectRaw('branch_id, COUNT(*) as cnt')
            ->groupBy('branch_id')
            ->orderByDesc('cnt')
            ->get()
            ->map(fn ($r) => ['label' => $branches->find($r->branch_id)?->name ?? 'Unknown', 'cnt' => (int) $r->cnt])
            ->filter(fn ($r) => $r['cnt'] > 0)
            ->values();

        // Month-on-month (last 12 months)
        $monthlyNew = (clone $base)
            ->where('created_at', '>=', now()->subYear()->startOfMonth())
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as cnt")
            ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM')"))
            ->orderBy('month')
            ->get();

        $churnedByMonth = collect(DB::select(
            "SELECT TO_CHAR(expiry_date, 'YYYY-MM') as month, COUNT(*) as cnt
             FROM members
             WHERE tenant_id = ? AND status != 'active' {$branchClause}
               AND expiry_date >= ?
             GROUP BY TO_CHAR(expiry_date, 'YYYY-MM')
             ORDER BY month",
            [$tenantId, now()->subYear()->startOfMonth()->toDateString()]
        ))->pluck('cnt', 'month');

        $monthlyComparison = $monthlyNew->map(fn ($r) => [
            'month'   => $r->month,
            'new'     => (int) $r->cnt,
            'churned' => (int) ($churnedByMonth->get($r->month, 0)),
            'net'     => (int) $r->cnt - (int) ($churnedByMonth->get($r->month, 0)),
        ]);

        return [
            'range'             => $range,
            'branches'          => $branches,
            'plans'             => GymMembershipPlan::where('tenant_id', $tenantId)->active()->orderBy('name')->get(),
            'branchId'          => $branchId,
            'planId'            => $planId,
            'kpis'              => [
                'new'           => $newCount,
                'prevNew'       => $prevNew,
                'churned'       => $churned,
                'churnRate'     => $churnRate,
                'retentionRate' => round(100 - $churnRate, 1),
                'netGrowth'     => $newCount - $churned,
            ],
            'trend'             => $trend,
            'byPlan'            => $byPlan,
            'byGender'          => $byGender,
            'byAge'             => $byAge,
            'byBranch'          => $byBranch,
            'monthlyComparison' => $monthlyComparison,
        ];
    }

    // â”€â”€ Attendance report â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function attendance(Request $request, int $tenantId): array
    {
        $range    = $this->resolveRange($request);
        $branchId = $this->resolveBranchId($request);

        $base = AttendanceLog::where('tenant_id', $tenantId)
            ->whereBetween('checked_in_at', [$range['from'], $range['to']])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $total  = (clone $base)->count();
        $unique = (clone $base)->distinct('member_id')->count('member_id');

        $walkinsBase = WalkIn::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$range['from'], $range['to']])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
        $walkins = $walkinsBase->count();

        $months       = max(1, (int) round($range['from']->diffInMonths($range['to'])) + 1);
        $avgPerMember = $unique > 0 ? round($total / $unique / $months, 1) : 0;

        // Daily trend
        $trend = (clone $base)
            ->selectRaw("DATE(checked_in_at) as date, COUNT(*) as cnt")
            ->groupBy(DB::raw('DATE(checked_in_at)'))
            ->orderBy(DB::raw('DATE(checked_in_at)'))
            ->get();

        // Peak hours heatmap (PG DOW: 0=Sun â€¦ 6=Sat â†’ remap to 0=Mon)
        $branchClause = $branchId ? "AND branch_id = {$branchId}" : '';
        $heatmapRaw = DB::select(
            "SELECT
                EXTRACT(DOW FROM checked_in_at)::int AS dow,
                EXTRACT(HOUR FROM checked_in_at)::int AS hour,
                COUNT(*) AS cnt
             FROM attendance_logs
             WHERE tenant_id = ?
               AND checked_in_at BETWEEN ? AND ?
               {$branchClause}
             GROUP BY dow, hour",
            [$tenantId, $range['from'], $range['to']]
        );

        $heatmap = array_fill(0, 7, array_fill(0, 24, 0));
        $maxCell = 1;
        foreach ($heatmapRaw as $row) {
            $dow = ($row->dow + 6) % 7; // PG 0=Sun â†’ 0=Mon
            $heatmap[$dow][$row->hour] = (int) $row->cnt;
            $maxCell = max($maxCell, (int) $row->cnt);
        }

        // By check-in method
        $byMethod = (clone $base)
            ->selectRaw('method, COUNT(*) as cnt')
            ->groupBy('method')
            ->get()
            ->map(fn ($r) => ['label' => ucfirst($r->method ?? 'Unknown'), 'cnt' => (int) $r->cnt]);

        // Stacked bar: member check-ins vs walk-ins by day
        $membersByDay = (clone $base)
            ->selectRaw("DATE(checked_in_at) as date, COUNT(*) as cnt")
            ->groupBy(DB::raw('DATE(checked_in_at)'))
            ->pluck('cnt', 'date');

        $walkinsByDay = (clone $walkinsBase)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as cnt")
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('cnt', 'date');

        $allDates  = $membersByDay->keys()->merge($walkinsByDay->keys())->unique()->sort()->values();
        $stackedBar = $allDates->map(fn ($d) => [
            'date'    => $d,
            'members' => (int) ($membersByDay->get($d, 0)),
            'walkins' => (int) ($walkinsByDay->get($d, 0)),
        ]);

        // Class attendance summary
        $classSummary = collect(DB::select(
            "SELECT c.name, COUNT(b.id) as booked
             FROM classes c
             LEFT JOIN class_bookings b ON b.class_id = c.id AND b.status = 'confirmed'
             WHERE c.tenant_id = ?
               AND c.class_date BETWEEN ? AND ?
               {$branchClause}
             GROUP BY c.id, c.name
             ORDER BY booked DESC
             LIMIT 20",
            [$tenantId, $range['from']->toDateString(), $range['to']->toDateString()]
        ));

        return [
            'range'        => $range,
            'branches'     => Branch::forTenant($tenantId)->orderBy('name')->get(),
            'branchId'     => $branchId,
            'kpis'         => [
                'total'        => $total,
                'unique'       => $unique,
                'walkins'      => $walkins,
                'avgPerMember' => $avgPerMember,
            ],
            'trend'        => $trend,
            'heatmap'      => $heatmap,
            'heatmapMax'   => $maxCell,
            'byMethod'     => $byMethod,
            'stackedBar'   => $stackedBar,
            'classSummary' => $classSummary,
        ];
    }

    // â”€â”€ Staff report â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function staff(Request $request, int $tenantId): array
    {
        $range    = $this->resolveRange($request);
        $branchId = $this->resolveBranchId($request);
        $branchClause = $branchId ? "AND s.branch_id = {$branchId}" : '';

        $attendanceSummary = collect(DB::select(
            "SELECT
                s.name,
                s.role,
                COUNT(sal.id) as days_present,
                COALESCE(SUM(sal.hours_worked_minutes), 0) as total_minutes
             FROM staff s
             LEFT JOIN staff_attendance_logs sal
                 ON sal.staff_id = s.id
                 AND sal.attendance_date BETWEEN ? AND ?
             WHERE s.tenant_id = ? AND s.status = 'active' {$branchClause}
             GROUP BY s.id, s.name, s.role
             ORDER BY s.name",
            [$range['from']->toDateString(), $range['to']->toDateString(), $tenantId]
        ));

        $classesByTrainer = collect(DB::select(
            "SELECT
                u.name as trainer_name,
                COUNT(c.id) as scheduled,
                COUNT(CASE WHEN c.status != 'cancelled' THEN 1 END) as held,
                COUNT(CASE WHEN c.status = 'cancelled' THEN 1 END) as cancelled
             FROM classes c
             JOIN users u ON u.id = c.trainer_id
             WHERE c.tenant_id = ?
               AND c.class_date BETWEEN ? AND ?
               " . ($branchId ? "AND c.branch_id = {$branchId}" : '') . "
             GROUP BY c.trainer_id, u.name
             ORDER BY scheduled DESC",
            [$tenantId, $range['from']->toDateString(), $range['to']->toDateString()]
        ))->map(function ($r) {
            $r->pct_held = $r->scheduled > 0 ? round(($r->held / $r->scheduled) * 100) : 0;
            return $r;
        });

        $feesCollected = collect(DB::select(
            "SELECT
                COALESCE(s.name, u.name) as name,
                COALESCE(s.role, 'owner') as role,
                COUNT(p.id) as payment_count,
                COALESCE(SUM(p.total_paise), 0) as total
             FROM payments p
             JOIN users u ON u.id = p.collected_by
             LEFT JOIN staff s ON s.user_id = u.id AND s.tenant_id = p.tenant_id
             WHERE p.tenant_id = ?
               AND p.status = 'active'
               AND p.payment_date BETWEEN ? AND ?
               " . ($branchId ? "AND p.branch_id = {$branchId}" : '') . "
             GROUP BY p.collected_by, u.name, s.name, s.role
             ORDER BY total DESC",
            [$tenantId, $range['from']->toDateString(), $range['to']->toDateString()]
        ));

        $posSales = collect(DB::select(
            "SELECT
                COALESCE(s.name, u.name) as name,
                COUNT(ps.id) as bill_count,
                COALESCE(SUM(ps.total_paise), 0) as total
             FROM pos_sales ps
             JOIN users u ON u.id = ps.sold_by
             LEFT JOIN staff s ON s.user_id = u.id AND s.tenant_id = ps.tenant_id
             WHERE ps.tenant_id = ?
               AND ps.refunded_at IS NULL
               AND ps.created_at BETWEEN ? AND ?
               " . ($branchId ? "AND ps.branch_id = {$branchId}" : '') . "
             GROUP BY ps.sold_by, u.name, s.name
             ORDER BY total DESC",
            [$tenantId, $range['from'], $range['to']]
        ));

        return [
            'range'             => $range,
            'branches'          => Branch::forTenant($tenantId)->orderBy('name')->get(),
            'branchId'          => $branchId,
            'attendanceSummary' => $attendanceSummary,
            'classesByTrainer'  => $classesByTrainer,
            'feesCollected'     => $feesCollected,
            'posSales'          => $posSales,
        ];
    }

    // â”€â”€ CSV exports â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function exportRevenueCsv(Request $request, int $tenantId): string
    {
        $range    = $this->resolveRange($request);
        $branchId = $this->resolveBranchId($request);

        $rows = Payment::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereBetween('payment_date', [$range['from']->toDateString(), $range['to']->toDateString()])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with(['member', 'branch'])
            ->orderBy('payment_date')
            ->get();

        $csv = "Date,Receipt,Member,Branch,Method,Amount,GST,Total,Reference\n";
        foreach ($rows as $r) {
            $csv .= implode(',', [
                $r->payment_date,
                $r->receipt_number,
                '"' . str_replace('"', '""', $r->member?->name ?? '') . '"',
                '"' . str_replace('"', '""', $r->branch?->name ?? '') . '"',
                $r->method,
                number_format($r->amount_paise / 100, 2),
                number_format($r->gst_paise / 100, 2),
                number_format($r->total_paise / 100, 2),
                '"' . str_replace('"', '""', $r->reference ?? '') . '"',
            ]) . "\n";
        }
        return $csv;
    }

    public function exportMembersCsv(Request $request, int $tenantId): string
    {
        $range    = $this->resolveRange($request);
        $branchId = $this->resolveBranchId($request);

        $rows = Member::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$range['from'], $range['to']])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with('branch')
            ->orderBy('created_at')
            ->get();

        $csv = "Member Code,Name,Phone,Email,Gender,Plan,Branch,Join Date,Expiry,Status\n";
        foreach ($rows as $r) {
            $csv .= implode(',', [
                $r->member_code,
                '"' . str_replace('"', '""', $r->name) . '"',
                $r->phone,
                $r->email ?? '',
                $r->gender ?? '',
                '"' . str_replace('"', '""', $r->plan_name ?? '') . '"',
                '"' . str_replace('"', '""', $r->branch?->name ?? '') . '"',
                $r->created_at->toDateString(),
                $r->expiry_date ?? '',
                $r->status,
            ]) . "\n";
        }
        return $csv;
    }

    public function exportAttendanceCsv(Request $request, int $tenantId): string
    {
        $range    = $this->resolveRange($request);
        $branchId = $this->resolveBranchId($request);

        $rows = AttendanceLog::where('tenant_id', $tenantId)
            ->whereBetween('checked_in_at', [$range['from'], $range['to']])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with(['member', 'branch'])
            ->orderBy('checked_in_at')
            ->get();

        $csv = "Date,Time,Member,Branch,Method\n";
        foreach ($rows as $r) {
            $csv .= implode(',', [
                $r->checked_in_at->toDateString(),
                $r->checked_in_at->format('H:i'),
                '"' . str_replace('"', '""', $r->member?->name ?? '') . '"',
                '"' . str_replace('"', '""', $r->branch?->name ?? '') . '"',
                $r->method ?? '',
            ]) . "\n";
        }
        return $csv;
    }

    public function exportStaffCsv(Request $request, int $tenantId): string
    {
        $data = $this->staff($request, $tenantId);
        $csv  = "Staff Name,Role,Days Present,Total Hours\n";
        foreach ($data['attendanceSummary'] as $r) {
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $r->name) . '"',
                $r->role,
                $r->days_present,
                number_format($r->total_minutes / 60, 1),
            ]) . "\n";
        }
        return $csv;
    }

    // â”€â”€ Private helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function resolveBranchId(Request $request): ?int
    {
        $user = $request->user();
        if (in_array($user->role, ['branch_manager', 'branch_admin'], true)) {
            return (int) $user->branch_id;
        }
        $id = $request->get('branch_id') ?: session('gymos_selected_branch_id');
        return $id ? (int) $id : null;
    }
}

