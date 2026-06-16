<?php

namespace App\Services\Admin;

use App\Models\AdminAuditLog;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Collection;

class DashboardService
{
    public function getOverview(): array
    {
        $totalTenants = Tenant::query()->where('status', '!=', 'archived')->count();
        $activeTenants = Tenant::query()->where('status', 'active')->count();
        $trialTenants = Subscription::query()
            ->where('status', 'trial')
            ->whereDate('trial_end_date', '>=', now()->toDateString())
            ->count();
        $renewalsThisWeek = Subscription::query()
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->count();
        $trialsExpiring = Subscription::query()
            ->whereNotNull('trial_end_date')
            ->whereBetween('trial_end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->count();

        $mrr = Subscription::query()
            ->where('status', 'active')
            ->with('plan')
            ->get()
            ->sum(fn (Subscription $subscription): float => $subscription->price_paise / $this->billingCycleMonths($subscription));

        $mrrTrend = $this->buildMrrTrend();

        return [
            'totalTenants' => $totalTenants,
            'activeTenants' => $activeTenants,
            'trialTenants' => $trialTenants,
            'mrr' => $mrr,
            'renewalsThisWeek' => $renewalsThisWeek,
            'trialsExpiring' => $trialsExpiring,
            'mrrTrend' => $mrrTrend,
            'maxTrend' => max(1, $mrrTrend->max('value')),
            'recentActivities' => AdminAuditLog::query()->latest('created_at')->limit(8)->get(),
            'renewalsDue' => Subscription::query()
                ->with(['tenant', 'plan'])
                ->whereNotNull('end_date')
                ->orderBy('end_date')
                ->limit(6)
                ->get(),
        ];
    }

    private function buildMrrTrend(): Collection
    {
        return collect(range(11, 0))->map(function (int $monthsAgo): array {
            $month = now()->subMonths($monthsAgo);

            $value = Subscription::query()
                ->whereDate('created_at', '<=', $month->copy()->endOfMonth()->toDateString())
                ->whereIn('status', ['active', 'trial'])
                ->with('plan')
                ->get()
                ->sum(fn (Subscription $subscription): float => $subscription->price_paise / $this->billingCycleMonths($subscription));

            return [
                'label' => $month->format('M'),
                'value' => (int) round($value / 100),
            ];
        });
    }

    private function billingCycleMonths(Subscription $subscription): int
    {
        return match (strtolower($subscription->plan->billing_cycle)) {
            'annual' => 12,
            'quarterly' => 3,
            default => 1,
        };
    }
}
