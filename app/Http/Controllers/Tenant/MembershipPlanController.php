<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\GymMembershipPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MembershipPlanController extends Controller
{
    public function index(Request $request){
        abort_unless($request->user()->canAccess('members.view|members.add|members.edit|members.delete'), 403);

        $tenant = $request->user()->tenant;

        $query = GymMembershipPlan::forTenant($tenant->id)
            ->withCount(['members as active_members_count' => fn ($q) => $q
                ->where('status', 'active')
                ->where(fn ($q2) => $q2->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->toDateString()))
            ])
            ->withCount('members as total_members_count');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', 'archived');
        }

        if ($search = $request->get('search')) {
            $query->where(fn ($q) => $q
                ->where('name', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%")
            );
        }

        $plans    = $query->orderBy('status')->orderBy('name')->get();
        $branches = Branch::forTenant($tenant->id)->active()->orderByRaw('is_primary DESC, name ASC')->get();

        $counts = [
            'all'      => GymMembershipPlan::forTenant($tenant->id)->where('status', '!=', 'archived')->count(),
            'active'   => GymMembershipPlan::forTenant($tenant->id)->where('status', 'active')->count(),
            'inactive' => GymMembershipPlan::forTenant($tenant->id)->where('status', 'inactive')->count(),
            'archived' => GymMembershipPlan::forTenant($tenant->id)->where('status', 'archived')->count(),
        ];

        return Inertia::render('Tenant/MembershipPlans/Index', [
            'plans' => $plans,
            'branches' => $branches,
            'counts' => $counts,
            'canAdd' => $request->user()->canAccess('members.add'),
            'canEdit' => $request->user()->canAccess('members.edit'),
            'canDelete' => $request->user()->canAccess('members.delete'),
        ]);
    }

    public function create(Request $request){
        abort_unless($request->user()->canAccess('members.add'), 403);

        $tenant   = $request->user()->tenant;
        $branches = Branch::forTenant($tenant->id)->active()->orderByRaw('is_primary DESC, name ASC')->get();
        $defaultGstRate = (float) config('gym.default_gst_rate', 18);
        $defaultBranchIds = $this->defaultBranchIds($request, $branches);
        $selectedBranchId = $request->user()->effectiveBranchId();
        $hasSpecificBranchContext = $selectedBranchId && $branches->contains('id', $selectedBranchId);
        $branchOptions = $hasSpecificBranchContext
            ? $branches->where('id', $selectedBranchId)->values()
            : $branches;
        $showBranchSelector = ($branches->count() > 1 && $defaultBranchIds->isEmpty()) || $hasSpecificBranchContext;

        return Inertia::render('Tenant/MembershipPlans/Form', [
            'branches' => $branchOptions,
            'defaultGstRate' => $defaultGstRate,
            'defaultBranchIds' => $defaultBranchIds->values()->all(),
            'showBranchSelector' => $showBranchSelector,
        ]);
    }

    public function edit(Request $request, GymMembershipPlan $plan){
        abort_unless($request->user()->canAccess('members.edit'), 403);
        $this->authorizePlan($request, $plan);
        $tenant   = $request->user()->tenant;
        $branches = Branch::forTenant($tenant->id)->active()->orderByRaw('is_primary DESC, name ASC')->get();
        $plan->load('branches');
        $defaultGstRate = (float) config('gym.default_gst_rate', 18);

        return Inertia::render('Tenant/MembershipPlans/Form', compact('plan', 'branches', 'defaultGstRate'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canAccess('members.add'), 403);

        $tenant   = $request->user()->tenant;
        $validated = $this->validatePlan($request, $tenant->id);

        $plan = GymMembershipPlan::create([
            ...$validated,
            'tenant_id'    => $tenant->id,
            'duration_days' => $this->toDays($validated['duration_type'], (int) $validated['duration_value']),
            'inclusions'   => $this->parseInclusions($request->input('inclusions', '')),
            'tags'         => $this->parseTags($validated['tags'] ?? []),
        ]);

        if ($request->filled('branch_ids')) {
            $plan->branches()->sync(
                Branch::forTenant($tenant->id)->whereIn('id', $request->input('branch_ids'))->pluck('id')
            );
        } else {
            $plan->branches()->sync($this->defaultBranchIds(
                $request,
                Branch::forTenant($tenant->id)->active()->orderByRaw('is_primary DESC, name ASC')->get()
            ));
        }

        return redirect()->route('tenant.plans.index')
            ->with('status', "Plan \"{$plan->name}\" created successfully.");
    }

    public function update(Request $request, GymMembershipPlan $plan): RedirectResponse
    {
        abort_unless($request->user()->canAccess('members.edit'), 403);
        $this->authorizePlan($request, $plan);
        $tenant    = $request->user()->tenant;
        $validated = $this->validatePlan($request, $tenant->id, $plan->id);

        $plan->update([
            ...$validated,
            'duration_days' => $this->toDays($validated['duration_type'], (int) $validated['duration_value']),
            'inclusions'    => $this->parseInclusions($request->input('inclusions', '')),
            'tags'          => $this->parseTags($validated['tags'] ?? []),
        ]);

        $branchIds = $request->filled('branch_ids')
            ? Branch::forTenant($tenant->id)->whereIn('id', $request->input('branch_ids'))->pluck('id')
            : [];
        $plan->branches()->sync($branchIds);

        return redirect()->route('tenant.plans.index')
            ->with('status', "Plan \"{$plan->name}\" updated successfully.");
    }

    public function duplicate(Request $request, GymMembershipPlan $plan): RedirectResponse
    {
        abort_unless($request->user()->canAccess('members.add'), 403);
        $this->authorizePlan($request, $plan);
        $tenant = $request->user()->tenant;

        $baseName = 'Copy of ' . $plan->name;
        $name     = $baseName;
        $i        = 2;
        while (GymMembershipPlan::forTenant($tenant->id)->whereRaw('LOWER(name) = ?', [strtolower($name)])->exists()) {
            $name = $baseName . ' ' . $i++;
        }

        $copy = $plan->replicate(['id', 'created_at', 'updated_at']);
        $copy->name   = $name;
        $copy->status = 'inactive';
        $copy->save();

        $copy->branches()->sync($plan->branches()->pluck('id'));

        return redirect()->route('tenant.plans.index')
            ->with('status', "Plan duplicated as \"{$name}\".");
    }

    public function archive(Request $request, GymMembershipPlan $plan): RedirectResponse
    {
        abort_unless($request->user()->canAccess('members.delete'), 403);
        $this->authorizePlan($request, $plan);

        $activeCount = $plan->active_member_count;

        if ($activeCount > 0 && !$request->boolean('confirm')) {
            return back()->withErrors([
                'archive' => "Cannot archive â€” {$activeCount} member(s) are currently on this plan. Archive anyway to stop new enrolments (existing members are unaffected).",
                'archive_plan_id' => $plan->id,
            ]);
        }

        $plan->update(['status' => 'archived']);

        return redirect()->route('tenant.plans.index')
            ->with('status', "Plan \"{$plan->name}\" archived. Existing members are unaffected.");
    }

    // â”€â”€ Private helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function validatePlan(Request $request, int $tenantId, ?int $excludeId = null): array
    {
        $validityMode = $request->input('validity_mode', $request->filled('session_limit') ? 'sessions' : 'duration');
        $durationType = $request->input('duration_type', 'days');
        $maxDuration  = $durationType === 'months' ? 24 : 730;
        $name         = $request->input('name', '');

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'min:2', 'max:80',
                Rule::unique('gym_membership_plans')
                    ->where(fn ($q) => $q
                        ->where('tenant_id', $tenantId)
                        ->whereRaw('LOWER(name) = LOWER(?)', [$name])
                    )
                    ->ignore($excludeId),
            ],
            'description'    => 'nullable|string|max:500',
            'validity_mode'  => 'nullable|in:duration,sessions,both',
            'duration_type'  => [Rule::requiredIf($validityMode !== 'sessions'), 'nullable', 'in:days,months'],
            'duration_value' => [Rule::requiredIf($validityMode !== 'sessions'), 'nullable', 'integer', 'min:1', "max:{$maxDuration}"],
            'session_limit'  => [Rule::requiredIf($validityMode === 'sessions' || $validityMode === 'both'), 'nullable', 'integer', 'min:1', 'max:10000'],
            'price_paise'    => 'required|integer|min:0|max:99999900',
            'gst_applicable' => 'boolean',
            'gst_rate'       => [Rule::requiredIf($request->boolean('gst_applicable')), 'nullable', 'numeric', 'min:0', 'max:100'],
            'max_members'    => 'nullable|integer|min:0',
            'grace_days'     => 'nullable|integer|min:0|max:30',
            'allow_freeze'   => 'boolean',
            'max_freeze_days'=> 'nullable|integer|min:1|max:90',
            // Transfer fields
            'is_transferable' => 'boolean',
            'has_transfer_fee' => 'boolean',
            'transfer_fee_amount' => [Rule::requiredIf($request->boolean('has_transfer_fee')), 'nullable', 'integer', 'min:0', 'max:99999900'],
            'transfer_fee_gst_applicable' => 'boolean',
            'transfer_notes' => 'nullable|string|max:500',
            // Upgrade fields
            'is_upgradable' => 'boolean',
            'has_upgrade_charge' => 'boolean',
            'upgrade_charge_type' => [Rule::requiredIf($request->boolean('has_upgrade_charge')), 'nullable', 'in:full_new_plan,difference_amount,custom_amount'],
            'upgrade_custom_amount' => [Rule::requiredIf(fn() => $request->boolean('has_upgrade_charge') && $request->input('upgrade_charge_type') === 'custom_amount'), 'nullable', 'integer', 'min:0', 'max:99999900'],
            'upgrade_notes' => 'nullable|string|max:500',
            'status'         => 'required|in:active,inactive',
            'tags'           => 'nullable|array|max:10',
            'tags.*'         => 'string|max:30',
        ]);

        $validated['gst_rate'] = $request->boolean('gst_applicable')
            ? (float) ($validated['gst_rate'] ?? config('gym.default_gst_rate', 18))
            : null;
        $validated['gst_applicable'] = $request->boolean('gst_applicable');
        $validated['allow_freeze'] = $request->boolean('allow_freeze');
        $validated['max_freeze_days'] = $validated['allow_freeze']
            ? (int) ($validated['max_freeze_days'] ?? 30)
            : 0;
        $validated['duration_type'] = $validityMode === 'sessions' ? 'days' : ($validated['duration_type'] ?? 'days');
        $validated['duration_value'] = $validityMode === 'sessions' ? 1 : (int) ($validated['duration_value'] ?? 1);
        $validated['session_limit'] = ($validityMode === 'sessions' || $validityMode === 'both') ? (int) $validated['session_limit'] : null;
        unset($validated['validity_mode']);

        // Transfer fields
        $validated['is_transferable'] = $request->boolean('is_transferable');
        $validated['has_transfer_fee'] = $request->boolean('has_transfer_fee');
        $validated['transfer_fee_amount'] = $validated['has_transfer_fee']
            ? (int) ($validated['transfer_fee_amount'] ?? 0)
            : null;
        $validated['transfer_fee_gst_applicable'] = $request->boolean('transfer_fee_gst_applicable');

        // Upgrade fields
        $validated['is_upgradable'] = $request->boolean('is_upgradable');
        $validated['has_upgrade_charge'] = $request->boolean('has_upgrade_charge');
        $validated['upgrade_custom_amount'] = ($validated['has_upgrade_charge'] && ($validated['upgrade_charge_type'] ?? null) === 'custom_amount')
            ? (int) ($validated['upgrade_custom_amount'] ?? 0)
            : null;

        return $validated;
    }

    private function toDays(string $type, int $value): int
    {
        return $type === 'months' ? $value * 30 : $value;
    }

    private function parseInclusions(?string $raw): array
    {
        return array_values(array_filter(
            array_map('trim', explode(',', $raw ?? '')),
            fn ($s) => $s !== ''
        ));
    }

    private function parseTags(array $raw): array
    {
        return array_values(array_filter(
            array_map('trim', $raw),
            fn ($s) => $s !== ''
        ));
    }

    private function authorizePlan(Request $request, GymMembershipPlan $plan): void
    {
        abort_unless($plan->tenant_id === $request->user()->tenant?->id, 403);
    }

    private function defaultBranchIds(Request $request, $branches)
    {
        if ($branches->count() === 1) {
            return collect([$branches->first()->id]);
        }

        $selectedBranchId = $request->user()->effectiveBranchId();

        if ($selectedBranchId && $branches->contains('id', $selectedBranchId)) {
            return collect([$selectedBranchId]);
        }

        return collect();
    }
}

