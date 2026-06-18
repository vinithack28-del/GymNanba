<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Member;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BranchController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant;

        $branches = Branch::forTenant($tenant->id)
            ->withCount(['members'])
            ->orderByRaw("is_primary DESC, created_at ASC")
            ->get();

        [$planLimit, $planName] = $this->planLimit($tenant);

        return view('tenant.branches.index', [
            'branches'    => $branches,
            'planLimit'   => $planLimit,
            'planName'    => $planName,
            'activeCount' => $branches->where('status', 'active')->count(),
            'states'      => Branch::indianStates(),
            'amenityOpts' => Branch::amenityOptions(),
        ]);
    }

    public function create(Request $request): View
    {
        $tenant = $request->user()->tenant;
        [$planLimit] = $this->planLimit($tenant);
        $activeCount = Branch::forTenant($tenant->id)->active()->count();

        if ($planLimit > 0 && $activeCount >= $planLimit) {
            return redirect()->route('tenant.branches.index')
                ->withErrors(['limit' => "Branch limit reached. Upgrade your plan to add more branches."]);
        }

        return view('tenant.branches.form', [
            'states'      => Branch::indianStates(),
            'amenityOpts' => Branch::amenityOptions(),
        ]);
    }

    public function edit(Request $request, Branch $branch): View
    {
        $this->authorizeBranch($request, $branch);

        return view('tenant.branches.form', [
            'branch'      => $branch,
            'states'      => Branch::indianStates(),
            'amenityOpts' => Branch::amenityOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;

        [$planLimit] = $this->planLimit($tenant);
        $activeBranches = Branch::forTenant($tenant->id)->active()->count();

        if ($planLimit > 0 && $activeBranches >= $planLimit) {
            return back()->withErrors(['limit' => "Branch limit reached. You can have at most {$planLimit} branches on your current plan."]);
        }

        $validated = $this->validateBranch($request);

        $isFirst = Branch::forTenant($tenant->id)->count() === 0;

        $branch = Branch::create([
            ...$validated,
            'tenant_id'       => $tenant->id,
            'operating_hours' => $this->buildOperatingHours($request),
            'amenities'       => $request->input('amenities', []),
            'is_primary'      => $isFirst,
        ]);

        $credentials = $this->createBranchAdmin($branch, $tenant->id);

        $msg = "Branch \"{$branch->name}\" created successfully.";
        if ($credentials) {
            $msg .= " Branch admin login — Email: {$credentials['email']} | Password: {$credentials['password']}";
        } else {
            $msg .= " No branch email provided, so no admin login was created.";
        }

        return redirect()
            ->route('tenant.branches.index')
            ->with('status', $msg)
            ->with('branch_credentials', $credentials);
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorizeBranch($request, $branch);

        $validated = $this->validateBranch($request, $branch->id);

        $oldEmail = $branch->email;

        $branch->update([
            ...$validated,
            'operating_hours' => $this->buildOperatingHours($request),
            'amenities'       => $request->input('amenities', []),
        ]);

        // Sync branch admin email if branch email changed
        if ($oldEmail !== $branch->email && $branch->email) {
            $admin = $branch->adminUser;
            if ($admin) {
                $admin->update(['email' => $branch->email]);
            } elseif (!User::where('email', $branch->email)->exists()) {
                $this->createBranchAdmin($branch, $branch->tenant_id);
            }
        }

        return redirect()
            ->route('tenant.branches.index')
            ->with('status', "Branch \"{$branch->name}\" updated successfully.");
    }

    public function deactivate(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorizeBranch($request, $branch);

        $tenant = $request->user()->tenant;
        $activeCount = Branch::forTenant($tenant->id)->active()->count();

        if ($activeCount <= 1) {
            return back()->withErrors(['deactivate' => 'Cannot deactivate the last active branch.']);
        }

        if ($branch->is_primary) {
            return back()->withErrors(['deactivate' => 'Cannot deactivate the primary branch. Set another branch as primary first.']);
        }

        $reassignId = $request->input('reassign_branch_id');

        if ($reassignId) {
            $target = Branch::forTenant($tenant->id)->active()->find($reassignId);
            if ($target && $target->id !== $branch->id) {
                Member::where('branch_id', $branch->id)->update(['branch_id' => $target->id]);
            }
        }

        $branch->update(['status' => 'inactive']);

        return redirect()
            ->route('tenant.branches.index')
            ->with('status', "Branch \"{$branch->name}\" deactivated.");
    }

    public function reactivate(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorizeBranch($request, $branch);

        $tenant = $request->user()->tenant;
        [$planLimit] = $this->planLimit($tenant);
        $activeCount = Branch::forTenant($tenant->id)->active()->count();

        if ($planLimit > 0 && $activeCount >= $planLimit) {
            return back()->withErrors(['limit' => "Cannot reactivate — branch limit of {$planLimit} reached on your current plan."]);
        }

        $branch->update(['status' => 'active']);

        return redirect()
            ->route('tenant.branches.index')
            ->with('status', "Branch \"{$branch->name}\" reactivated.");
    }

    private function validateBranch(Request $request, ?int $excludeId = null): array
    {
        $tenant = $request->user()->tenant;

        return $request->validate([
            'name'         => [
                'required', 'string', 'min:2', 'max:80',
                \Illuminate\Validation\Rule::unique('branches')
                    ->where('tenant_id', $tenant->id)
                    ->ignore($excludeId),
            ],
            'address1'     => 'required|string|min:5|max:100',
            'address2'     => 'nullable|string|max:100',
            'city'         => 'required|string|min:2|max:50',
            'state'        => 'required|string|max:50',
            'pin'          => 'required|digits:6',
            'phone'        => 'required|string|max:20',
            'email'        => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:100',
            'gst_number'   => 'nullable|string|max:15',
            'status'       => 'required|in:active,inactive',
        ]);
    }

    private function buildOperatingHours(Request $request): array
    {
        $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $hours = [];

        foreach ($days as $day) {
            $hours[$day] = [
                'open'   => $request->input("hours_{$day}_open", '06:00'),
                'close'  => $request->input("hours_{$day}_close", '22:00'),
                'closed' => $request->boolean("hours_{$day}_closed"),
            ];
        }

        return $hours;
    }

    private function planLimit($tenant): array
    {
        $sub = $tenant->subscriptions()
            ->with('plan')
            ->where('status', 'active')
            ->orWhere('status', 'trial')
            ->first();

        $limit = (int) ($sub?->plan?->max_branches ?? 0);
        $name  = $sub?->plan?->name ?? null;

        return [$limit, $name];
    }

    private function authorizeBranch(Request $request, Branch $branch): void
    {
        abort_unless($branch->tenant_id === $request->user()->tenant?->id, 403);
    }

    private function createBranchAdmin(Branch $branch, int $tenantId): ?array
    {
        if (empty($branch->email)) {
            return null;
        }

        // Skip if email already registered
        if (User::where('email', $branch->email)->exists()) {
            return null;
        }

        $localPart   = strstr($branch->email, '@', true) ?: $branch->email;
        $emailPrefix = substr($localPart, 0, 4);
        $digitsOnly  = preg_replace('/\D/', '', $branch->phone ?? '');
        $phoneSuffix = substr($digitsOnly, -4);
        $password    = $emailPrefix . $phoneSuffix;

        User::create([
            'tenant_id' => $tenantId,
            'branch_id' => $branch->id,
            'role'      => 'branch_admin',
            'name'      => $branch->manager_name ?: $branch->name . ' Admin',
            'email'     => $branch->email,
            'password'  => Hash::make($password),
        ]);

        return ['email' => $branch->email, 'password' => $password];
    }
}
