<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlanTransfer;
use App\Models\Member;
use App\Models\GymMembershipPlan;
use App\Services\Tenant\PlanTransferService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PlanTransferController extends Controller
{
    public function __construct(
        private PlanTransferService $transferService
    ) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()->canAccess('members.view|members.edit'), 403);

        $tenant = $request->user()->tenant;
        $query = MembershipPlanTransfer::forTenant($tenant->id)
            ->with(['sourceMember', 'targetMember', 'membershipPlan', 'invoice', 'payment', 'creator']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }

        if ($request->search) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->whereHas('sourceMember', fn ($m) => $m->where('name', 'ilike', $s)
                    ->orWhere('member_code', 'ilike', $s))
                    ->orWhereHas('targetMember', fn ($m) => $m->where('name', 'ilike', $s)
                        ->orWhere('member_code', 'ilike', $s));
            });
        }

        $transfers = $query->orderByDesc('transfer_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Tenant/PlanTransfers/Index', [
            'transfers' => $transfers,
        ]);
    }

    public function create(Request $request, Member $member): Response
    {
        abort_unless($request->user()->canAccess('members.edit'), 403);

        $tenant = $request->user()->tenant;
        abort_unless($member->tenant_id === $tenant->id, 403);

        $member->load('plan');

        // Check if plan is transferable
        if (!$member->plan || !$member->plan->is_transferable) {
            abort(422, 'This plan is not transferable.');
        }

        // Check member eligibility
        if ($member->effective_status !== 'active') {
            abort(422, 'Member must have an active plan.');
        }

        if ($member->expiry_date && $member->expiry_date->isPast()) {
            abort(422, 'Member plan has expired.');
        }

        if ($member->isFrozen()) {
            abort(422, 'Member plan is frozen.');
        }

        // Get eligible target members (active members)
        $eligibleTargets = Member::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->where('id', '!=', $member->id)
            ->orderBy('name')
            ->get(['id', 'name', 'member_code', 'phone']);

        return Inertia::render('Tenant/PlanTransfers/Create', [
            'sourceMember' => $member,
            'eligibleTargets' => $eligibleTargets,
            'plan' => $member->plan,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canAccess('members.edit'), 403);

        $validated = $request->validate([
            'source_member_id' => 'required|exists:members,id',
            'target_member_id' => 'required|exists:members,id|different:source_member_id',
            'notes' => 'nullable|string|max:500',
        ]);

        $tenant = $request->user()->tenant;
        
        try {
            $transfer = $this->transferService->initiateTransfer($validated, $tenant->id);
        } catch (\Exception $e) {
            return back()->with('error', 'Transfer failed: ' . $e->getMessage());
        }

        return redirect()->route('tenant.transfers.show', $transfer->id)
            ->with('status', 'Transfer initiated successfully.');
    }

    public function show(Request $request, MembershipPlanTransfer $transfer): Response
    {
        abort_unless($request->user()->canAccess('members.view|members.edit'), 403);
        abort_unless($transfer->tenant_id === $request->user()->tenant->id, 403);

        $transfer->load(['sourceMember', 'targetMember', 'membershipPlan', 'invoice', 'payment', 'creator']);

        return Inertia::render('Tenant/PlanTransfers/Show', [
            'transfer' => $transfer,
        ]);
    }

    public function complete(Request $request, MembershipPlanTransfer $transfer): RedirectResponse
    {
        abort_unless($request->user()->canAccess('members.edit'), 403);
        abort_unless($transfer->tenant_id === $request->user()->tenant->id, 403);
        abort_unless($transfer->status === 'pending_payment', 422, 'Transfer must be in pending payment status.');

        $this->transferService->completeTransfer($transfer);

        return redirect()->route('tenant.transfers.show', $transfer->id)
            ->with('status', 'Transfer completed successfully.');
    }

    public function cancel(Request $request, MembershipPlanTransfer $transfer): RedirectResponse
    {
        abort_unless($request->user()->canAccess('members.edit'), 403);
        abort_unless($transfer->tenant_id === $request->user()->tenant->id, 403);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->transferService->cancelTransfer($transfer, $validated['reason']);

        return redirect()->route('tenant.transfers.show', $transfer->id)
            ->with('status', 'Transfer cancelled successfully.');
    }
}
