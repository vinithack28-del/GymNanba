<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\GymMembershipPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MembershipPlanController extends Controller
{
    public function index(Request $request): View
    {
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

        return view('tenant.plans.index', compact('plans', 'branches', 'counts'));
    }

    public function create(Request $request): View
    {
        $tenant   = $request->user()->tenant;
        $branches = Branch::forTenant($tenant->id)->active()->orderByRaw('is_primary DESC, name ASC')->get();
        return view('tenant.plans.form', compact('branches'));
    }

    public function edit(Request $request, GymMembershipPlan $plan): View
    {
        $this->authorizePlan($request, $plan);
        $tenant   = $request->user()->tenant;
        $branches = Branch::forTenant($tenant->id)->active()->orderByRaw('is_primary DESC, name ASC')->get();
        $plan->load('branches');
        return view('tenant.plans.form', compact('plan', 'branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant   = $request->user()->tenant;
        $validated = $this->validatePlan($request, $tenant->id);

        $plan = GymMembershipPlan::create([
            ...$validated,
            'tenant_id'    => $tenant->id,
            'duration_days' => $this->toDays($validated['duration_type'], (int) $validated['duration_value']),
            'inclusions'   => $this->parseInclusions($request->input('inclusions', '')),
        ]);

        if ($request->filled('branch_ids')) {
            $plan->branches()->sync(
                Branch::forTenant($tenant->id)->whereIn('id', $request->input('branch_ids'))->pluck('id')
            );
        }

        return redirect()->route('tenant.plans.index')
            ->with('status', "Plan \"{$plan->name}\" created successfully.");
    }

    public function update(Request $request, GymMembershipPlan $plan): RedirectResponse
    {
        $this->authorizePlan($request, $plan);
        $tenant    = $request->user()->tenant;
        $validated = $this->validatePlan($request, $tenant->id, $plan->id);

        $plan->update([
            ...$validated,
            'duration_days' => $this->toDays($validated['duration_type'], (int) $validated['duration_value']),
            'inclusions'    => $this->parseInclusions($request->input('inclusions', '')),
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
        $this->authorizePlan($request, $plan);

        $activeCount = $plan->active_member_count;

        if ($activeCount > 0 && !$request->boolean('confirm')) {
            return back()->withErrors([
                'archive' => "Cannot archive — {$activeCount} member(s) are currently on this plan. Archive anyway to stop new enrolments (existing members are unaffected).",
                'archive_plan_id' => $plan->id,
            ]);
        }

        $plan->update(['status' => 'archived']);

        return redirect()->route('tenant.plans.index')
            ->with('status', "Plan \"{$plan->name}\" archived. Existing members are unaffected.");
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function validatePlan(Request $request, int $tenantId, ?int $excludeId = null): array
    {
        $durationType = $request->input('duration_type', 'days');
        $maxDuration  = $durationType === 'months' ? 24 : 730;
        $name         = $request->input('name', '');

        return $request->validate([
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
            'duration_type'  => 'required|in:days,months',
            'duration_value' => "required|integer|min:1|max:{$maxDuration}",
            'price_paise'    => 'required|integer|min:0|max:99999900',
            'gst_applicable' => 'boolean',
            'gst_rate'       => 'nullable|in:0,5,12,18,28',
            'max_members'    => 'nullable|integer|min:0',
            'grace_days'     => 'nullable|integer|min:0|max:30',
            'allow_freeze'   => 'boolean',
            'max_freeze_days'=> 'nullable|integer|min:1|max:90',
            'status'         => 'required|in:active,inactive',
        ]);
    }

    private function toDays(string $type, int $value): int
    {
        return $type === 'months' ? $value * 30 : $value;
    }

    private function parseInclusions(string $raw): array
    {
        return array_values(array_filter(
            array_map('trim', explode(',', $raw)),
            fn ($s) => $s !== ''
        ));
    }

    private function authorizePlan(Request $request, GymMembershipPlan $plan): void
    {
        abort_unless($plan->tenant_id === $request->user()->tenant?->id, 403);
    }
}
