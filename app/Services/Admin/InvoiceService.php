<?php

namespace App\Services\Admin;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    // ── Index data ────────────────────────────────────────────────────────────

    public function getIndexData(): array
    {
        return [
            'renewalsDue'  => $this->getRenewalsDue(),
            'payments'     => TenantPayment::query()
                ->with(['tenant', 'admin', 'subscription.plan'])
                ->orderByDesc('paid_at')
                ->orderByDesc('id')
                ->paginate(15),
            'plans'        => Plan::query()->where('status', 'active')->where('is_trial', false)->orderBy('name')->get(),
            'tenants'      => Tenant::query()->orderBy('gym_name')->get(),
        ];
    }

    // ── Renewals Due ──────────────────────────────────────────────────────────

    public function getRenewalsDue(): \Illuminate\Database\Eloquent\Collection
    {
        // Tenants whose latest subscription has expired or is expiring within 30 days
        return Tenant::query()
            ->with(['subscriptions' => fn ($q) => $q->with('plan')->latest()->limit(1)])
            ->whereIn('status', ['active', 'trial', 'trial_ended', 'subscription_expired'])
            ->get()
            ->filter(function (Tenant $tenant): bool {
                $sub = $tenant->subscriptions->first();
                if (! $sub) {
                    return false;
                }
                // Trial ended or subscription expired
                if (in_array($sub->status, ['trial_ended', 'expired'], true)) {
                    return true;
                }
                // Expiring within 30 days
                if ($sub->end_date && $sub->end_date->diffInDays(now(), false) >= -30) {
                    return true;
                }
                // Trial ending within 7 days
                if ($sub->trial_end_date && $sub->trial_end_date->diffInDays(now(), false) >= -7) {
                    return true;
                }

                return false;
            })
            ->values()
            ->map(function (Tenant $tenant): Tenant {
                $sub = $tenant->subscriptions->first();
                $totalPaid = TenantPayment::query()
                    ->where('subscription_id', $sub?->id)
                    ->sum('amount_paise');
                $tenant->_sub = $sub;
                $tenant->_total_paid_paise = (int) $totalPaid;
                $tenant->_balance_paise = max(0, ($sub?->plan?->price_paise ?? 0) - $totalPaid);

                return $tenant;
            });
    }

    // ── Process Renewal ───────────────────────────────────────────────────────

    public function processRenewal(array $validated): TenantPayment
    {
        $tenant = Tenant::query()->findOrFail($validated['tenant_id']);
        $plan   = Plan::query()->findOrFail($validated['plan_id']);

        $splitsParsed = $this->parseSplits($validated['splits'] ?? []);
        $amountPaise  = $splitsParsed['total_paise'];
        $isPartial    = $amountPaise < $plan->price_paise;

        // If the tenant has a future-expiring subscription, stack the new period on top of it.
        $latestSub = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->whereNotNull('end_date')
            ->orderByDesc('end_date')
            ->first();

        $isFirstPayment = $latestSub === null;

        $baseDate = ($latestSub?->end_date && $latestSub->end_date->isFuture())
            ? $latestSub->end_date
            : now()->startOfDay();

        return DB::transaction(function () use ($tenant, $plan, $validated, $amountPaise, $isPartial, $splitsParsed, $baseDate, $isFirstPayment): TenantPayment {
            $sub = Subscription::query()->create([
                'tenant_id'      => $tenant->id,
                'plan_id'        => $plan->id,
                'status'         => $isPartial ? 'partial' : 'active',
                'start_date'     => $baseDate->toDateString(),
                'end_date'       => $baseDate->copy()->addDays($this->billingCycleDays($plan->billing_cycle))->toDateString(),
                'trial_end_date' => null,
                'price_paise'    => $plan->price_paise,
                'created_by'     => Auth::id(),
            ]);

            $tenant->update(['status' => 'active']);

            $payment = TenantPayment::query()->create([
                'tenant_id'       => $tenant->id,
                'admin_id'        => Auth::id(),
                'subscription_id' => $sub->id,
                'amount_paise'    => $amountPaise,
                'payment_method'  => $splitsParsed['primary_method'],
                'transaction_ref' => $splitsParsed['primary_ref'],
                'splits'          => count($splitsParsed['rows']) > 1 ? $splitsParsed['rows'] : null,
                'paid_at'         => $validated['paid_at'],
                'payment_type'    => $isFirstPayment ? 'new' : 'renewal',
                'notes'           => $validated['notes'] ?? null,
            ]);

            $this->auditLogService->log('RENEWAL_PAYMENT', 'SUBSCRIPTION', (string) $sub->id, $tenant->gym_name, [
                'plan' => $plan->name, 'amount_paise' => $amountPaise, 'is_partial' => $isPartial,
            ]);

            return $payment;
        });
    }

    // ── Part Payment against existing subscription ────────────────────────────

    public function recordPartPayment(array $validated): TenantPayment
    {
        $sub    = Subscription::query()->findOrFail($validated['subscription_id']);
        $tenant = Tenant::query()->findOrFail($sub->tenant_id);

        $splitsParsed = $this->parseSplits($validated['splits'] ?? []);
        $amountPaise  = $splitsParsed['total_paise'];
        $totalPaid    = TenantPayment::query()->where('subscription_id', $sub->id)->sum('amount_paise') + $amountPaise;
        $fullyPaid    = $totalPaid >= $sub->price_paise;

        return DB::transaction(function () use ($tenant, $sub, $validated, $amountPaise, $fullyPaid, $splitsParsed): TenantPayment {
            if ($fullyPaid && $sub->status === 'partial') {
                $sub->update(['status' => 'active']);
            }

            $payment = TenantPayment::query()->create([
                'tenant_id'       => $tenant->id,
                'admin_id'        => Auth::id(),
                'subscription_id' => $sub->id,
                'amount_paise'    => $amountPaise,
                'payment_method'  => $splitsParsed['primary_method'],
                'transaction_ref' => $splitsParsed['primary_ref'],
                'splits'          => count($splitsParsed['rows']) > 1 ? $splitsParsed['rows'] : null,
                'paid_at'         => $validated['paid_at'],
                'payment_type'    => 'part_payment',
                'notes'           => $validated['notes'] ?? null,
            ]);

            $this->auditLogService->log('PART_PAYMENT', 'SUBSCRIPTION', (string) $sub->id, $tenant->gym_name,
                ['amount_paise' => $amountPaise, 'fully_paid' => $fullyPaid]);

            return $payment;
        });
    }

    // ── Manual payment (legacy) ───────────────────────────────────────────────

    public function recordPayment(array $validated): TenantPayment
    {
        $splitsParsed = $this->parseSplits($validated['splits'] ?? []);

        $payment = TenantPayment::query()->create([
            'tenant_id'      => $validated['tenant_id'],
            'admin_id'       => Auth::id(),
            'amount_paise'   => $splitsParsed['total_paise'] ?: (int) round(((float) ($validated['amount_inr'] ?? 0)) * 100),
            'payment_method' => $splitsParsed['primary_method'],
            'transaction_ref'=> $splitsParsed['primary_ref'],
            'splits'         => count($splitsParsed['rows']) > 1 ? $splitsParsed['rows'] : null,
            'paid_at'        => $validated['paid_at'],
            'payment_type'   => 'manual',
            'notes'          => $validated['notes'] ?? null,
        ]);

        $tenant = Tenant::query()->findOrFail($validated['tenant_id']);

        $this->auditLogService->log(
            'PAYMENT_RECORD',
            'INVOICE',
            (string) $payment->id,
            $tenant->gym_name,
            ['amount_paise' => $payment->amount_paise],
        );

        return $payment;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function billingCycleDays(?string $billingCycle): int
    {
        return match (strtolower((string) $billingCycle)) {
            'annual'    => 365,
            'quarterly' => 90,
            default     => 30,
        };
    }

    /**
     * Parse splits array from form input into a normalised structure.
     *
     * @param  array<int, array{method: string, amount: string|float, reference?: string}>  $splits
     * @return array{total_paise: int, primary_method: string, primary_ref: string|null, rows: list<array>}
     */
    private function parseSplits(array $splits): array
    {
        $rows        = [];
        $totalPaise  = 0;

        foreach ($splits as $row) {
            $paise = (int) round(((float) ($row['amount'] ?? 0)) * 100);
            if ($paise <= 0) {
                continue;
            }
            $rows[]     = [
                'method'    => $row['method'],
                'amount'    => (float) ($row['amount'] ?? 0),
                'reference' => $row['reference'] ?? null,
            ];
            $totalPaise += $paise;
        }

        $primary = $rows[0] ?? [];

        return [
            'total_paise'    => $totalPaise,
            'primary_method' => $primary['method'] ?? 'Cash',
            'primary_ref'    => $primary['reference'] ?? null,
            'rows'           => $rows,
        ];
    }
}
