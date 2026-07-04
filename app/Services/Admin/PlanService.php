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
        $isTrial = (bool) ($validated['is_trial'] ?? false);

        $plan = Plan::query()->create([
            'name'               => $validated['name'],
            'is_trial'           => $isTrial,
            'trial_days'         => $isTrial ? (int) $validated['trial_days'] : null,
            'billing_cycle'      => $isTrial ? null : $validated['billing_cycle'],
            'price_paise'        => $isTrial ? 0 : (int) round(((float) $validated['price_inr']) * 100),
            'max_members'        => $isTrial ? 0 : ($validated['max_members'] ?? 0),
            'max_branches'       => $isTrial ? 0 : ($validated['max_branches'] ?? 0),
            'max_staff_accounts' => $isTrial ? 0 : ($validated['max_staff_accounts'] ?? 0),
            'feature_flags'      => $isTrial ? [] : collect($validated['features'] ?? [])->mapWithKeys(fn ($feature) => [$feature => true])->all(),
            'trial_eligible'     => $isTrial ? true : (bool) ($validated['trial_eligible'] ?? false),
            'description'        => $validated['description'] ?? null,
            'status'             => $validated['status'],
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

    public function update(Plan $plan, array $validated): Plan
    {
        $original = $plan->only(['name', 'status', 'is_trial']);
        $isTrial = (bool) ($validated['is_trial'] ?? false);

        $plan->update([
            'name'               => $validated['name'],
            'is_trial'           => $isTrial,
            'trial_days'         => $isTrial ? (int) $validated['trial_days'] : null,
            'billing_cycle'      => $isTrial ? null : $validated['billing_cycle'],
            'price_paise'        => $isTrial ? 0 : (int) round(((float) $validated['price_inr']) * 100),
            'max_members'        => $isTrial ? 0 : ($validated['max_members'] ?? 0),
            'max_branches'       => $isTrial ? 0 : ($validated['max_branches'] ?? 0),
            'max_staff_accounts' => $isTrial ? 0 : ($validated['max_staff_accounts'] ?? 0),
            'feature_flags'      => $isTrial ? [] : collect($validated['features'] ?? [])->mapWithKeys(fn ($feature) => [$feature => true])->all(),
            'trial_eligible'     => $isTrial ? true : (bool) ($validated['trial_eligible'] ?? false),
            'description'        => $validated['description'] ?? null,
            'status'             => $validated['status'],
        ]);

        $difference = [];
        foreach ($original as $key => $value) {
            if ($plan->{$key} !== $value) {
                $difference[$key] = ['old' => $value, 'new' => $plan->{$key}];
            }
        }

        $this->auditLogService->log(
            'PLAN_UPDATE',
            'PLAN',
            (string) $plan->id,
            $plan->name,
            $difference,
        );

        return $plan;
    }

    public function delete(Plan $plan): void
    {
        $snapshot = $plan->only(['name', 'status']);

        $plan->delete();

        $this->auditLogService->log(
            'PLAN_DELETE',
            'PLAN',
            (string) $plan->id,
            $snapshot['name'],
            ['deleted' => $snapshot],
        );
    }
}

