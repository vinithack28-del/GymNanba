<?php

namespace App\Services\Admin;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

class PlanService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function all(): Collection
    {
        return Plan::query()->latest()->get();
    }

    public function create(array $validated): Plan
    {
        $plan = Plan::query()->create([
            'name' => $validated['name'],
            'billing_cycle' => $validated['billing_cycle'],
            'price_paise' => (int) round(((float) $validated['price_inr']) * 100),
            'max_members' => $validated['max_members'],
            'max_branches' => $validated['max_branches'],
            'max_staff_accounts' => $validated['max_staff_accounts'],
            'feature_flags' => collect($validated['features'] ?? [])->mapWithKeys(fn ($feature) => [$feature => true])->all(),
            'trial_eligible' => (bool) ($validated['trial_eligible'] ?? false),
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        $this->auditLogService->log(
            'PLAN_CREATE',
            'PLAN',
            (string) $plan->id,
            $plan->name,
            ['status' => ['old' => null, 'new' => $plan->status]],
        );

        return $plan;
    }
}
