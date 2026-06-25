<?php

namespace App\Services\Admin;

use App\Models\Branch;
use App\Models\Plan;
use App\Models\PlatformLanguage;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantPayment;
use App\Models\User;
use App\Services\Tenancy\TenantDatabaseManager;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenantService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly TenantDatabaseManager $tenantDatabaseManager,
    )
    {
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Tenant::query()->with(['subscriptions.plan', 'ownerUser']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search): void {
                $builder->where('gym_name', 'like', '%'.$search.'%')
                    ->orWhere('owner_name', 'like', '%'.$search.'%')
                    ->orWhere('owner_email', 'like', '%'.$search.'%')
                    ->orWhere('subdomain', 'like', '%'.$search.'%')
                    ->orWhere('custom_domain', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['business_type'])) {
            $query->where('business_type', $filters['business_type']);
        }

        $perPage = in_array((int) ($filters['per_page'] ?? 0), [10, 25, 50, 100], true)
            ? (int) $filters['per_page']
            : 10;

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function getCreationMeta(): array
    {
        return [
            'plans' => Plan::query()->where('status', 'active')->orderBy('price_paise')->get(),
        ];
    }

    public function getEditMeta(Tenant $tenant): array
    {
        return [
            'tenant'        => $tenant->load(['subscriptions.plan', 'ownerUser']),
            'plans'         => Plan::query()->where('status', 'active')->orderBy('price_paise')->get(),
            'languages'     => PlatformLanguage::query()->orderByDesc('is_active')->orderBy('display_name')->get(),
            'statuses'      => ['active', 'trial', 'trial_ended', 'subscription_expired', 'suspended', 'archived'],
            'businessTypes' => ['Gym', 'Yoga', 'Turf'],
        ];
    }

    public function getDetails(Tenant $tenant): Tenant
    {
        return $tenant->load(['subscriptions.plan', 'payments.admin', 'ownerUser']);
    }

    public function create(array $validated): Tenant
    {
        $plan = Plan::query()->findOrFail($validated['plan_id']);
        $trialEnabled = (bool) $plan->is_trial;

        $tenant = DB::transaction(function () use ($validated, $trialEnabled, $plan): Tenant {
            $primaryOwner = $validated['owners'][0];

            $tenant = Tenant::query()->create([
                'gym_name'      => $validated['gym_name'],
                'business_type' => $validated['business_type'],
                'owner_name'    => $primaryOwner['name'],
                'owner_email'   => strtolower($primaryOwner['email']),
                'phone'         => $validated['phone'],
                'city'          => $validated['city'],
                'state'         => $validated['state'],
                'address'       => $validated['address'],
                'gst_number'    => $validated['gst_number'] ?? null,
                'subdomain'     => strtolower($validated['subdomain']),
                'domain_mode'   => $validated['domain_mode'],
                'custom_domain' => $validated['domain_mode'] === 'separate' ? $validated['custom_domain'] : null,
                'database_mode' => $validated['database_mode'],
                'database_name' => $validated['database_mode'] === 'separate' ? $this->databaseNameFromSubdomain($validated['subdomain']) : null,
                'status'        => $trialEnabled ? 'trial' : 'active',
                'default_language' => 'en-IN',
                'notes'         => $validated['notes'] ?? null,
            ]);

            $ownerUserId = null;
            foreach ($validated['owners'] as $ownerData) {
                $password = $this->generateOwnerPassword($ownerData['email'], $ownerData['phone']);
                $user = User::query()->create([
                    'tenant_id'          => $tenant->id,
                    'name'               => $ownerData['name'],
                    'email'              => strtolower($ownerData['email']),
                    'phone'              => $ownerData['phone'],
                    'preferred_language' => 'en-IN',
                    'role'               => 'tenant_owner',
                    'password'           => $password,
                ]);
                $ownerUserId ??= $user->id;
            }

            $tenant->forceFill(['owner_user_id' => $ownerUserId])->save();

            // For trial plans use plan->trial_days; otherwise fall back to manual trial_end_date or 14 days
            $trialEndDate = $trialEnabled
                ? ($plan->is_trial
                    ? now()->addDays($plan->trial_days)->toDateString()
                    : ($validated['trial_end_date'] ?? now()->addDays(14)->toDateString()))
                : null;

            // Parse optional payment splits
            $splitRows   = array_filter($validated['payment_splits'] ?? [], fn ($r) => ! empty($r['amount']) && (float) $r['amount'] > 0);
            $totalPaise  = (int) array_sum(array_map(fn ($r) => round((float) $r['amount'] * 100), $splitRows));
            $isPartial   = $totalPaise > 0 && $totalPaise < $plan->price_paise;

            $subscription = Subscription::query()->create([
                'tenant_id'      => $tenant->id,
                'plan_id'        => $plan->id,
                'status'         => $trialEnabled ? 'trial' : ($isPartial ? 'partial' : 'active'),
                'start_date'     => now()->toDateString(),
                'end_date'       => $trialEnabled ? null : now()->addDays($this->billingCycleDays($plan->billing_cycle))->toDateString(),
                'trial_end_date' => $trialEndDate,
                'price_paise'    => $plan->price_paise,
                'created_by'     => Auth::id(),
            ]);

            if ($totalPaise > 0) {
                $primaryRow = array_values($splitRows)[0];
                TenantPayment::query()->create([
                    'tenant_id'       => $tenant->id,
                    'admin_id'        => Auth::id(),
                    'subscription_id' => $subscription->id,
                    'amount_paise'    => $totalPaise,
                    'payment_method'  => $primaryRow['method'],
                    'transaction_ref' => $primaryRow['reference'] ?? null,
                    'splits'          => count($splitRows) > 1 ? array_values($splitRows) : null,
                    'paid_at'         => $validated['payment_paid_at'] ?? now()->toDateString(),
                    'payment_type'    => 'renewal',
                    'notes'           => $validated['payment_notes'] ?? null,
                ]);
            }

            // Auto-create a default primary branch from available tenant details
            Branch::query()->create([
                'tenant_id'  => $tenant->id,
                'name'       => $tenant->gym_name . ' (Main Branch)',
                'address1'   => $tenant->address ?? '',
                'city'       => $tenant->city,
                'state'      => $tenant->state,
                'pin'        => $tenant->pin ?? '',
                'phone'      => $tenant->phone,
                'email'      => $tenant->owner_email,
                'gst_number' => $tenant->gst_number,
                'status'     => 'active',
                'is_primary' => true,
            ]);

            $this->auditLogService->log(
                'TENANT_CREATE',
                'TENANT',
                (string) $tenant->id,
                $tenant->gym_name,
                ['status' => ['old' => null, 'new' => $tenant->status]],
            );

            return $tenant;
        });

        $this->tenantDatabaseManager->provision($tenant);

        return $tenant;
    }

    public function update(Tenant $tenant, array $validated): Tenant
    {
        $original = $tenant->only([
            'gym_name',
            'business_type',
            'owner_name',
            'owner_email',
            'phone',
            'city',
            'state',
            'address',
            'gst_number',
            'subdomain',
            'domain_mode',
            'custom_domain',
            'database_mode',
            'database_name',
            'status',
            'default_language',
            'notes',
        ]);

        $requiresProvisioning = $validated['database_mode'] === 'separate'
            && ($tenant->database_mode !== 'separate' || empty($tenant->database_name));

        $tenant->update([
            'gym_name' => $validated['gym_name'],
            'business_type' => $validated['business_type'],
            'owner_name' => $validated['owner_name'],
            'owner_email' => strtolower($validated['owner_email']),
            'phone' => $validated['phone'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'address' => $validated['address'],
            'gst_number' => $validated['gst_number'] ?? null,
            'subdomain' => strtolower($validated['subdomain']),
            'domain_mode' => $validated['domain_mode'],
            'custom_domain' => $validated['domain_mode'] === 'separate' ? $validated['custom_domain'] : null,
            'database_mode' => $validated['database_mode'],
            'database_name' => $validated['database_mode'] === 'separate'
                ? ($tenant->database_name ?: $this->databaseNameFromSubdomain($validated['subdomain']))
                : null,
            'status' => $validated['status'],
            'default_language' => $validated['default_language'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $tenant->ownerUser?->update([
            'name' => $validated['owner_name'],
            'email' => strtolower($validated['owner_email']),
            'preferred_language' => $validated['default_language'],
            ...(! empty($validated['owner_password']) ? ['password' => $validated['owner_password']] : []),
        ]);

        // Update subscription plan / trial fields if provided
        if (! empty($validated['plan_id'])) {
            $plan = Plan::query()->findOrFail($validated['plan_id']);
            $trialEnabled = (bool) $plan->is_trial;
            $latestSub = $tenant->subscriptions()->latest()->first();

            if ($latestSub) {
                $subUpdate = ['plan_id' => $plan->id];
                if (! empty($validated['trial_end_date'])) {
                    $subUpdate['trial_end_date'] = $validated['trial_end_date'];
                }
                if ($trialEnabled) {
                    $subUpdate['status'] = 'trial';
                }
                $latestSub->update($subUpdate);
            } else {
                $tenant->subscriptions()->create([
                    'plan_id'        => $plan->id,
                    'status'         => $trialEnabled ? 'trial' : 'active',
                    'start_date'     => now()->toDateString(),
                    'end_date'       => $trialEnabled ? null : now()->addDays($this->billingCycleDays($plan->billing_cycle))->toDateString(),
                    'trial_end_date' => $trialEnabled ? ($validated['trial_end_date'] ?? now()->addDays($plan->trial_days ?? 14)->toDateString()) : null,
                    'price_paise'    => $plan->price_paise,
                    'created_by'     => Auth::id(),
                ]);
            }
        }

        $difference = [];
        foreach ($original as $key => $value) {
            if ($tenant->{$key} !== $value) {
                $difference[$key] = ['old' => $value, 'new' => $tenant->{$key}];
            }
        }

        $this->auditLogService->log(
            'TENANT_UPDATE',
            'TENANT',
            (string) $tenant->id,
            $tenant->gym_name,
            $difference,
        );

        if ($requiresProvisioning) {
            $this->tenantDatabaseManager->provision($tenant);
        }

        return $tenant;
    }

    public function delete(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant): void {
            $snapshot = $tenant->only([
                'gym_name',
                'owner_email',
                'subdomain',
                'status',
            ]);

            $tenant->subscriptions()->delete();
            $tenant->payments()->delete();
            $tenant->ownerUser()?->delete();
            $tenant->delete();

            $this->auditLogService->log(
                'TENANT_DELETE',
                'TENANT',
                (string) $tenant->id,
                $snapshot['gym_name'],
                ['deleted' => $snapshot],
            );
        });
    }

    private function billingCycleDays(string $billingCycle): int
    {
        return match (strtolower($billingCycle)) {
            'annual' => 365,
            'quarterly' => 90,
            default => 30,
        };
    }

    private function databaseNameFromSubdomain(string $subdomain): string
    {
        return 'tenant_'.str_replace('-', '_', strtolower($subdomain));
    }

    /**
     * Auto-generate a password: first 4 chars of email local-part + "@" + last 4 digits of phone.
     * Example: arjun@gmail.com + 9876543210 → arju@3210
     */
    public function generateOwnerPassword(string $email, string $phone): string
    {
        $local     = explode('@', strtolower($email))[0];
        $emailPart = substr($local, 0, 4);
        $digits    = preg_replace('/\D/', '', $phone);
        $phonePart = substr($digits, -4);

        return $emailPart . '@' . $phonePart;
    }
}
