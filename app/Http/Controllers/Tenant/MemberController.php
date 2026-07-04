<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\GymMembershipPlan;
use App\Models\Member;
use App\Models\MemberRegistration;
use App\Models\Payment;
use App\Models\PaymentSplit;
use App\Models\WalkIn;
use App\Models\WalkInFollowup;
use App\Services\Tenant\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function index(Request $request){
        $tenant = $request->user()->tenant;
        $today  = now()->toDateString();

        // Resolve active branch filter from session
        $selectedBranchId = session('gymos_selected_branch_id');
        $selectedBranch   = null;
        if ($selectedBranchId) {
            $selectedBranch = Branch::forTenant($tenant->id)->active()->find($selectedBranchId);
            if (!$selectedBranch) {
                session()->forget('gymos_selected_branch_id');
                $selectedBranchId = null;
            }
        }

        $base = fn () => Member::forTenant($tenant->id)
            ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId));

        $stats = [
            'total'    => $base()->count(),
            'active'   => $base()->withStatus('active')->count(),
            'inactive' => $base()->where('status', 'inactive')->count(),
            'expired'  => $base()->withStatus('expired')->count(),
        ];

        $query = $base()->orderBy(
            $this->allowedSort($request->get('sort_by', 'created_at')),
            $request->get('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc'
        );

        if ($search = $request->get('search')) {
            $query->search($search);
        }

        if ($status = $request->get('status')) {
            $query->withStatus($status);
        }

        if ($gender = $request->get('gender')) {
            $query->where('gender', $gender);
        }

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 25;
        $members = $query->with('plan:id,name,allow_freeze,max_freeze_days')->paginate($perPage)->withQueryString();
        $members->getCollection()->transform(function (Member $member) {
            return $member->append(['effective_status', 'status_label', 'balance_rupees', 'initials']);
        });

        $plans = GymMembershipPlan::forTenant($tenant->id)->active()->orderBy('name')->get();

        $canManageRegistrations  = $request->user()->canAccess('members.add');
        $registrationUrl         = $canManageRegistrations ? $tenant->registration_url : null;
        $pendingRegistrationCount = $canManageRegistrations
            ? MemberRegistration::forTenant($tenant->id)->pending()->count()
            : 0;

        return Inertia::render('Tenant/Members/Index', compact(
            'stats', 'members', 'plans', 'today', 'selectedBranch',
            'registrationUrl', 'pendingRegistrationCount'
        ));
    }

    public function show(Request $request, Member $member){
        $this->authorizeMember($request, $member);

        $member->load([
            'branch:id,name',
            'plan:id,name,allow_freeze,max_freeze_days,duration_type,duration_value,duration_days',
        ])->append(['effective_status', 'status_label', 'balance_rupees', 'initials']);

        $payments = Payment::with(['plan', 'splits', 'collectedBy'])
            ->where('member_id', $member->id)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Tenant/Members/Show', compact('member', 'payments'));
    }

    public function create(Request $request){
        $tenant = $request->user()->tenant;
        $plans    = GymMembershipPlan::forTenant($tenant->id)->active()->orderBy('name')->get();
        $branches = Branch::forTenant($tenant->id)->active()->orderByRaw('is_primary DESC, name ASC')->get();
        $selectedBranchId = session('gymos_selected_branch_id');

        $prefill = null;
        if ($walkinId = $request->get('walkin_id')) {
            $prefill = WalkIn::where('tenant_id', $tenant->id)
                ->where('purpose', 'inquiry')
                ->find($walkinId);
        }

        return Inertia::render('Tenant/Members/Form', compact('plans', 'branches', 'selectedBranchId', 'prefill'));
    }

    public function walkinLookup(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $phone  = trim($request->get('phone', ''));

        if (strlen($phone) < 7) {
            return response()->json(['found' => false]);
        }

        $walkin = WalkIn::where('tenant_id', $tenant->id)
            ->where('purpose', 'inquiry')
            ->where('phone', $phone)
            ->whereNotIn('enquiry_status', ['converted', 'closed'])
            ->latest('created_at')
            ->first();

        if (! $walkin) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'     => true,
            'walkin_id' => $walkin->id,
            'name'      => $walkin->name,
            'phone'     => $walkin->phone,
            'branch_id' => $walkin->branch_id,
        ]);
    }

    public function edit(Request $request, Member $member){
        $this->authorizeMember($request, $member);
        $tenant = $request->user()->tenant;
        $plans    = GymMembershipPlan::forTenant($tenant->id)->active()->orderBy('name')->get();
        $branches = Branch::forTenant($tenant->id)->active()->orderByRaw('is_primary DESC, name ASC')->get();
        $selectedBranchId = session('gymos_selected_branch_id');
        return Inertia::render('Tenant/Members/Form', compact('member', 'plans', 'branches', 'selectedBranchId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'name'             => 'required|string|min:2|max:100',
            'phone'            => 'required|string|max:20',
            'email'            => 'nullable|email|max:255',
            'gender'           => 'nullable|in:male,female,other',
            'dob'              => 'nullable|date|before:-5 years',
            'address'          => 'nullable|string|max:300',
            'id_proof_type'    => 'nullable|in:aadhaar,pan,passport,voter_id,dl',
            'id_proof_number'  => 'nullable|string|max:50',
            'branch_id'        => 'required|exists:branches,id',
            'plan_id'          => 'required|exists:gym_membership_plans,id',
            'start_date'       => 'required|date|before_or_equal:' . now()->addDays(30)->toDateString(),
            'splits'           => 'nullable|array',
            'splits.*.method'  => 'required_with:splits|in:cash,upi,card,bank,cheque',
            'splits.*.amount'  => 'required_with:splits|numeric|min:0',
            'splits.*.reference' => 'nullable|string|max:100',
            'is_partial'       => 'nullable|boolean',
            'due_amount'       => 'nullable|numeric|min:0',
            'due_date'         => 'nullable|date',
            'notes'            => 'nullable|string|max:500',
            'walkin_id'        => 'nullable|integer',
        ]);

        if (Member::forTenant($tenant->id)->where('phone', $validated['phone'])->exists()) {
            return back()->withErrors(['phone' => 'A member with this phone number already exists.'])->withInput();
        }

        if (!empty($validated['email']) && Member::forTenant($tenant->id)->where('email', $validated['email'])->exists()) {
            return back()->withErrors(['email' => 'This email is already registered to another member.'])->withInput();
        }

        $plan = null;
        $expiryDate = null;

        if (!empty($validated['plan_id'])) {
            $plan = GymMembershipPlan::find($validated['plan_id']);
            if ($plan && $plan->tenant_id === $tenant->id) {
                $expiryDate = $plan->computeExpiryDate($validated['start_date']);
            }
        }

        $member = Member::create([
            'tenant_id'      => $tenant->id,
            'member_code'    => Member::generateCode($tenant->id),
            'name'           => $validated['name'],
            'phone'          => $validated['phone'],
            'email'          => $validated['email'] ?? null,
            'gender'         => $validated['gender'] ?? null,
            'dob'            => $validated['dob'] ?? null,
            'address'        => $validated['address'] ?? null,
            'id_proof_type'  => $validated['id_proof_type'] ?? null,
            'id_proof_number'=> $validated['id_proof_number'] ?? null,
            'branch_id'      => $validated['branch_id'],
            'plan_id'        => $plan?->id,
            'plan_name'      => $plan?->name,
            'start_date'     => $validated['start_date'],
            'expiry_date'    => $expiryDate,
            'status'         => 'active',
            'balance_paise'  => 0,
            'notes'          => $validated['notes'] ?? null,
            'created_by'     => $request->user()->id,
        ]);

        // Create payment if any splits have an amount
        $splits = collect($validated['splits'] ?? [])
            ->filter(fn($s) => ($s['amount'] ?? 0) > 0);

        if ($splits->isNotEmpty()) {
            $paidPaise     = (int) round($splits->sum('amount') * 100);
            $planPaise     = $plan ? $plan->total_price_paise : $paidPaise;
            $isPartial     = $paidPaise < $planPaise;
            $duePaise      = $isPartial ? max(0, $planPaise - $paidPaise) : 0;
            $primaryMethod = $splits->count() === 1 ? $splits->first()['method'] : 'split';

            $payment = Payment::create([
                'tenant_id'      => $tenant->id,
                'member_id'      => $member->id,
                'branch_id'      => $member->branch_id,
                'plan_id'        => $plan?->id,
                'receipt_number' => 'RCP-' . strtoupper(substr(uniqid(), -6)),
                'amount_paise'   => $plan?->price_paise ?? $paidPaise,
                'gst_paise'      => $plan?->gst_amount_paise ?? 0,
                'total_paise'    => $planPaise,
                'paid_paise'     => $paidPaise,
                'is_partial'     => $isPartial,
                'due_paise'      => $duePaise,
                'due_date'       => $isPartial ? ($validated['due_date'] ?? null) : null,
                'reminder_sent'  => false,
                'method'         => $primaryMethod,
                'payment_date'   => today()->toDateString(),
                'status'         => 'active',
                'collected_by'   => $request->user()->id,
            ]);

            foreach ($splits as $s) {
                PaymentSplit::create([
                    'payment_id'   => $payment->id,
                    'method'       => $s['method'],
                    'amount_paise' => (int) round($s['amount'] * 100),
                    'reference'    => $s['reference'] ?? null,
                ]);
            }

            $this->paymentService->syncMemberBalance($member);
        }

        if (!empty($validated['walkin_id'])) {
            $walkIn = WalkIn::where('tenant_id', $tenant->id)
                ->where('purpose', 'inquiry')
                ->find($validated['walkin_id']);

            if ($walkIn) {
                $walkIn->update([
                    'enquiry_status' => 'converted',
                    'member_id'      => $member->id,
                ]);

                WalkInFollowup::create([
                    'walk_in_id'         => $walkIn->id,
                    'tenant_id'          => $tenant->id,
                    'outcome'            => 'converted',
                    'notes'              => "Converted to member {$member->member_code}.",
                    'next_followup_date' => null,
                    'logged_by'          => $request->user()->id,
                    'created_at'         => now(),
                ]);
            }
        }

        return redirect()
            ->route('tenant.members.index')
            ->with('status', "Member {$member->member_code} ({$member->name}) added successfully.");
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $this->authorizeMember($request, $member);
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'name'           => 'required|string|min:2|max:100',
            'phone'          => 'required|string|max:20',
            'email'          => 'nullable|email|max:255',
            'gender'         => 'nullable|in:male,female,other',
            'dob'            => 'nullable|date|before:-5 years',
            'address'        => 'nullable|string|max:300',
            'id_proof_type'  => 'nullable|in:aadhaar,pan,passport,voter_id,dl',
            'id_proof_number'=> 'nullable|string|max:50',
            'branch_id'      => 'nullable|exists:branches,id',
            'plan_id'        => 'required|exists:gym_membership_plans,id',
            'start_date'     => 'required|date',
            'notes'          => 'nullable|string|max:500',
            'status'         => 'required|in:active,inactive,frozen',
            'freeze_days'    => 'nullable|integer|min:1|max:3650',
        ]);

        if (Member::forTenant($tenant->id)->where('phone', $validated['phone'])->where('id', '!=', $member->id)->exists()) {
            return back()->withErrors(['phone' => 'Another member already has this phone number.'])->withInput();
        }

        if (!empty($validated['email']) && Member::forTenant($tenant->id)->where('email', $validated['email'])->where('id', '!=', $member->id)->exists()) {
            return back()->withErrors(['email' => 'This email is already registered to another member.'])->withInput();
        }

        $plan = null;
        if (!empty($validated['plan_id'])) {
            $plan = GymMembershipPlan::find($validated['plan_id']);
        }

        // Resolve frozen_until and expiry adjustment when status changes to/from frozen
        $newStatus   = $validated['status'];
        $frozenUntil = $member->frozen_until;
        $newExpiry   = $plan ? $plan->computeExpiryDate($validated['start_date']) : $member->expiry_date;

        if ($newStatus === 'frozen') {
            $freezeDays = max(1, (int) ($validated['freeze_days'] ?? 1));

            // Enforce plan limits
            if ($plan && ! $plan->allow_freeze) {
                return back()->withErrors(['status' => "The plan \"{$plan->name}\" does not allow membership freeze."])->withInput();
            }
            if ($plan && $plan->max_freeze_days > 0 && $freezeDays > $plan->max_freeze_days) {
                return back()->withErrors(['freeze_days' => "Freeze days cannot exceed {$plan->max_freeze_days} days allowed by the plan."])->withInput();
            }

            $frozenUntil = now()->addDays($freezeDays)->toDateString();

            // Extend expiry by freeze days (preserves membership time)
            if ($member->status !== 'frozen' && $newExpiry) {
                $newExpiry = \Carbon\Carbon::parse($newExpiry)->addDays($freezeDays)->toDateString();
            }
        } elseif ($member->status === 'frozen' && $newStatus !== 'frozen') {
            // Unfreezing via edit â€” reverse any remaining freeze extension
            if ($member->frozen_until && $member->frozen_until->isFuture() && $newExpiry) {
                $remainingDays = (int) now()->diffInDays($member->frozen_until, false);
                if ($remainingDays > 0) {
                    $newExpiry = \Carbon\Carbon::parse($newExpiry)->subDays($remainingDays)->toDateString();
                }
            }
            $frozenUntil = null;
        }

        $member->update([
            'name'            => $validated['name'],
            'phone'           => $validated['phone'],
            'email'           => $validated['email'] ?? null,
            'gender'          => $validated['gender'] ?? null,
            'dob'             => $validated['dob'] ?? null,
            'address'         => $validated['address'] ?? null,
            'id_proof_type'   => $validated['id_proof_type'] ?? null,
            'id_proof_number' => $validated['id_proof_number'] ?? null,
            'branch_id'       => $validated['branch_id'] ?? null,
            'plan_id'         => $plan?->id,
            'plan_name'       => $plan?->name,
            'start_date'      => $validated['start_date'],
            'expiry_date'     => $newExpiry,
            'status'          => $newStatus,
            'frozen_until'    => $frozenUntil,
            'notes'           => $validated['notes'] ?? null,
        ]);

        return redirect()->route('tenant.members.index')
            ->with('status', "Member {$member->name} updated successfully.");
    }

    public function toggleStatus(Request $request, Member $member): RedirectResponse
    {
        $this->authorizeMember($request, $member);
        $newStatus = $member->status === 'active' ? 'inactive' : 'active';
        $member->update(['status' => $newStatus]);

        return back()->with('status', "{$member->name} marked as {$newStatus}.");
    }

    public function freeze(Request $request, Member $member): RedirectResponse
    {
        $this->authorizeMember($request, $member);

        if ($member->effective_status !== 'active') {
            return back()->withErrors(['freeze' => 'Only active members can be frozen.']);
        }

        $plan = $member->plan;

        if ($plan && ! $plan->allow_freeze) {
            return back()->withErrors(['freeze' => "The plan \"{$plan->name}\" does not allow membership freeze."]);
        }

        $validated = $request->validate([
            'freeze_days' => 'required|integer|min:1|max:3650',
        ]);

        $days = (int) $validated['freeze_days'];

        if ($plan && $plan->max_freeze_days > 0 && $days > $plan->max_freeze_days) {
            return back()->withErrors(['freeze' => "Freeze days cannot exceed {$plan->max_freeze_days} days allowed by the plan."]);
        }

        $updates = [
            'status'       => 'frozen',
            'frozen_until' => now()->addDays($days)->toDateString(),
        ];

        // Extend expiry_date so freeze time doesn't consume membership
        if ($member->expiry_date) {
            $updates['expiry_date'] = $member->expiry_date->addDays($days)->toDateString();
        }

        $member->update($updates);

        $msg = "Membership frozen for {$days} days. Auto-unfreezes on " . now()->addDays($days)->format('d-m-Y') . '.';

        return back()->with('status', $msg);
    }

    public function unfreeze(Request $request, Member $member): RedirectResponse
    {
        $this->authorizeMember($request, $member);

        if ($member->status !== 'frozen') {
            return back()->withErrors(['freeze' => 'Member is not currently frozen.']);
        }

        $updates = ['status' => 'active', 'frozen_until' => null];

        // If unfreezing early (frozen_until is still in the future), reverse the unused extension on expiry
        if ($member->frozen_until && $member->frozen_until->isFuture() && $member->expiry_date) {
            $remainingDays = (int) now()->diffInDays($member->frozen_until, false);
            if ($remainingDays > 0) {
                $updates['expiry_date'] = $member->expiry_date->subDays($remainingDays)->toDateString();
            }
        }

        $member->update($updates);

        return back()->with('status', "{$member->name}'s membership has been unfrozen.");
    }

    public function destroy(Request $request, Member $member): RedirectResponse
    {
        $this->authorizeMember($request, $member);

        if ($member->balance_paise < 0) {
            return back()->withErrors(['delete' => 'Cannot delete â€” member has outstanding balance. Collect payment first.']);
        }

        $member->delete();

        return redirect()->route('tenant.members.index')->with('status', "Member {$member->name} deleted.");
    }

    private function allowedSort(string $column): string
    {
        $allowed = ['name', 'created_at', 'expiry_date', 'status', 'balance_paise', 'member_code', 'plan_name'];

        return in_array($column, $allowed) ? $column : 'created_at';
    }

    private function authorizeMember(Request $request, Member $member): void
    {
        abort_unless($member->tenant_id === $request->user()->tenant?->id, 403);
    }
}

