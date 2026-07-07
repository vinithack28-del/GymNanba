<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlanUpgrade;
use App\Models\Member;
use App\Models\GymMembershipPlan;
use App\Services\Tenant\PlanUpgradeService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PlanUpgradeController extends Controller
{
    public function __construct(
        private PlanUpgradeService $upgradeService
    ) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()->canAccess('members.view|members.edit'), 403);

        $tenant = $request->user()->tenant;
        $query = MembershipPlanUpgrade::forTenant($tenant->id)
            ->with(['member', 'oldPlan', 'newPlan', 'invoice', 'payment', 'creator']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('upgrade_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('upgrade_date', '<=', $request->date_to);
        }

        if ($request->search) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->whereHas('member', fn ($m) => $m->where('name', 'ilike', $s)
                    ->orWhere('member_code', 'ilike', $s));
            });
        }

        $upgrades = $query->orderByDesc('upgrade_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Tenant/PlanUpgrades/Index', [
            'upgrades' => $upgrades,
        ]);
    }

    public function create(Request $request, Member $member): Response
    {
        abort_unless($request->user()->canAccess('members.edit'), 403);

        $tenant = $request->user()->tenant;
        abort_unless($member->tenant_id === $tenant->id, 403);

        $member->load('plan');

        // Check if plan is upgradable
        if (!$member->plan || !$member->plan->is_upgradable) {
            abort(422, 'This plan is not upgradable.');
        }

        // Check member eligibility
        if ($member->effective_status !== 'active') {
            abort(422, 'Member must have an active plan.');
        }

        if ($member->expiry_date && $member->expiry_date->isPast()) {
            abort(422, 'Member plan has expired.');
        }

        // Get available upgrade plans (active plans, different from current)
        $availablePlans = GymMembershipPlan::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->where('id', '!=', $member->plan_id)
            ->orderBy('name')
            ->get(['id', 'name', 'price_paise', 'duration_type', 'duration_value', 'duration_days', 'is_upgradable', 'has_upgrade_charge', 'upgrade_charge_type', 'upgrade_custom_amount']);

        $availablePlans->each->append(['price_formatted', 'total_price_paise', 'duration_label']);

        return Inertia::render('Tenant/PlanUpgrades/Create', [
            'member' => $member,
            'currentPlan' => $member->plan,
            'availablePlans' => $availablePlans,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canAccess('members.edit'), 403);

        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'new_plan_id' => 'required|exists:gym_membership_plans,id',
            'upgrade_charge_type' => 'nullable|in:full_new_plan,difference_amount,custom_amount',
            'notes' => 'nullable|string|max:500',
        ]);

        $tenant = $request->user()->tenant;
        $upgrade = $this->upgradeService->initiateUpgrade($validated, $tenant->id);

        return redirect()->route('tenant.upgrades.show', $upgrade->id)
            ->with('status', 'Upgrade initiated successfully.');
    }

    public function show(Request $request, MembershipPlanUpgrade $upgrade): Response
    {
        abort_unless($request->user()->canAccess('members.view|members.edit'), 403);
        abort_unless($upgrade->tenant_id === $request->user()->tenant->id, 403);

        $upgrade->load(['member', 'oldPlan', 'newPlan', 'invoice', 'payment', 'creator']);

        return Inertia::render('Tenant/PlanUpgrades/Show', [
            'upgrade' => $upgrade,
        ]);
    }

    public function complete(Request $request, MembershipPlanUpgrade $upgrade): RedirectResponse
    {
        abort_unless($request->user()->canAccess('members.edit'), 403);
        abort_unless($upgrade->tenant_id === $request->user()->tenant->id, 403);
        abort_unless($upgrade->status === 'pending_payment', 422, 'Upgrade must be in pending payment status.');

        $this->upgradeService->completeUpgrade($upgrade);

        return redirect()->route('tenant.upgrades.show', $upgrade->id)
            ->with('status', 'Upgrade completed successfully.');
    }

    public function cancel(Request $request, MembershipPlanUpgrade $upgrade): RedirectResponse
    {
        abort_unless($request->user()->canAccess('members.edit'), 403);
        abort_unless($upgrade->tenant_id === $request->user()->tenant->id, 403);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->upgradeService->cancelUpgrade($upgrade, $validated['reason']);

        return redirect()->route('tenant.upgrades.show', $upgrade->id)
            ->with('status', 'Upgrade cancelled successfully.');
    }
}
