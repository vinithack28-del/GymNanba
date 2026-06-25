<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $tenant = $user?->tenant;

        if ($tenant) {
            $subscription = $tenant->subscriptions()->latest()->first();

            // Lazily expire trial
            if ($tenant->status === 'trial') {
                if ($subscription?->trial_end_date && now()->gt($subscription->trial_end_date)) {
                    $tenant->update(['status' => 'trial_ended']);
                    $subscription->update(['status' => 'trial_ended']);
                    $tenant->refresh();
                }
            }

            // Lazily expire active paid subscriptions whose end_date has passed
            if ($tenant->status === 'active') {
                if ($subscription?->end_date && now()->gt($subscription->end_date)) {
                    $tenant->update(['status' => 'subscription_expired']);
                    $subscription->update(['status' => 'expired']);
                    $tenant->refresh();
                }
            }

            if (in_array($tenant->status, ['trial_ended', 'subscription_expired', 'suspended', 'archived'], true)) {
                return response()->view('tenant.blocked', [
                    'status'           => $tenant->status,
                    'subscription'     => $subscription,
                ], 403);
            }
        }

        return $next($request);
    }
}
