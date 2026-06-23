<?php

namespace App\Services\Admin;

use App\Models\Branch;
use App\Models\Plan;
use App\Models\PlatformLanguage;
use App\Models\Subscription;
use App\Models\Tenant;
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

        return $query->latest()->paginate(10)->withQueryString();
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
            'tenant' => $tenant->load(['subscriptions.plan', 'ownerUser']),
            'languages' => PlatformLanguage::query()->orderByDesc('is_active')->orderBy('display_name')->get(),
            'statuses' => ['active', 'trial', 'suspended', 'archived'],
            'businessTypes' => ['Gym', 'Yoga', 'Turf'],
        ];
    }

    public function getDetails(Tenant $tenant): Tenant
    {
        return $tenant->load(['subscriptions.plan', 'payments.admin', 'ownerUser']);
    }

    public function create(array $validated, bool $trialEnabled): Tenant
    {
        $plan = Plan::query()->findOrFail($validated['plan_id']);
        $trialEnabled = $trialEnabled && $plan->trial_eligible;

        $tenant = DB::transaction(function () use ($validated, $trialEnabled, $plan): Tenant {
            $tenant = Tenant::query()->create([
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
                'database_name' => $validated['database_mode'] === 'separate' ? $this->databaseNameFromSubdomain($validated['subdomain']) : null,
                'status' => $trialEnabled ? 'trial' : 'active',
                'default_language' => 'en-IN',
                'notes' => $validated['notes'] ?? null,
            ]);

            $ownerUser = User::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $validated['owner_name'],
                'email' => strtolower($validated['owner_email']),
                'preferred_language' => 'en-IN',
                'role' => 'tenant_owner',
                'password' => $validated['owner_password'],
            ]);

            $tenant->forceFill([
                'owner_user_id' => $ownerUser->id,
            ])->save();

            Subscription::query()->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => $trialEnabled ? 'trial' : 'active',
                'start_date' => now()->toDateString(),
                'end_date' => $trialEnabled ? null : now()->addDays($this->billingCycleDays($plan->billing_cycle))->toDateString(),
                'trial_end_date' => $trialEnabled ? ($validated['trial_end_date'] ?? now()->addDays(14)->toDateString()) : null,
                'price_paise' => $plan->price_paise,
                'created_by' => Auth::id(),
            ]);

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
}
