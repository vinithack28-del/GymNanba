<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\GymMembershipPlan;
use App\Models\Member;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
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
        $members = $query->paginate($perPage)->withQueryString();

        $plans = GymMembershipPlan::forTenant($tenant->id)->active()->orderBy('name')->get();

        return view('tenant.members.index', compact('stats', 'members', 'plans', 'today', 'selectedBranch'));
    }

    public function create(Request $request): View
    {
        $tenant = $request->user()->tenant;
        $plans    = GymMembershipPlan::forTenant($tenant->id)->active()->orderBy('name')->get();
        $branches = Branch::forTenant($tenant->id)->active()->orderByRaw('is_primary DESC, name ASC')->get();
        $selectedBranchId = session('gymos_selected_branch_id');
        return view('tenant.members.form', compact('plans', 'branches', 'selectedBranchId'));
    }

    public function edit(Request $request, Member $member): View
    {
        $this->authorizeMember($request, $member);
        $tenant = $request->user()->tenant;
        $plans    = GymMembershipPlan::forTenant($tenant->id)->active()->orderBy('name')->get();
        $branches = Branch::forTenant($tenant->id)->active()->orderByRaw('is_primary DESC, name ASC')->get();
        return view('tenant.members.form', compact('member', 'plans', 'branches'));
    }

    public function store(Request $request): RedirectResponse
    {
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
            'plan_id'        => 'required|exists:gym_membership_plans,id',
            'start_date'     => 'required|date|before_or_equal:' . now()->addDays(30)->toDateString(),
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,upi,card,bank',
            'notes'          => 'nullable|string|max:500',
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
            'plan_id'        => $plan?->id,
            'plan_name'      => $plan?->name,
            'start_date'     => $validated['start_date'],
            'expiry_date'    => $expiryDate,
            'status'         => 'active',
            'balance_paise'  => 0,
            'notes'          => $validated['notes'] ?? null,
            'created_by'     => $request->user()->id,
        ]);

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
            'expiry_date'     => $plan
                                    ? $plan->computeExpiryDate($validated['start_date'])
                                    : $member->expiry_date,
            'status'          => $validated['status'],
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

    public function destroy(Request $request, Member $member): RedirectResponse
    {
        $this->authorizeMember($request, $member);

        if ($member->balance_paise > 0) {
            return back()->withErrors(['delete' => 'Cannot delete — member has outstanding balance. Collect payment first.']);
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
