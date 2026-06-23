<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Staff;
use App\Services\Tenant\StaffService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffController extends Controller
{
    public function __construct(private readonly StaffService $staffService)
    {
    }

    public function index(Request $request): View
    {
        if (!$request->filled('branch_id') && $id = session('gymos_selected_branch_id')) {
            $request->merge(['branch_id' => $id]);
        }
        return view('tenant.staff.index', $this->staffService->list($request->user(), $request));
    }

    public function create(Request $request): View
    {
        abort_unless($this->staffService->canManage($request->user()), 403);

        return view('tenant.staff.form', [
            'branches'         => Branch::forTenant($request->user()->tenant_id)->active()->orderBy('name')->get(),
            'roles'            => $this->staffService->roleOptions($request->user()->tenant_id),
            'proofTypes'       => Staff::ID_PROOF_TYPES,
            'selectedBranchId' => session('gymos_selected_branch_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->staffService->canManage($request->user()), 403);

        $validated = $this->validateStaff($request, null, true);
        $result = $this->staffService->create($request->user(), $validated, [
            'id_proof' => $request->file('id_proof'),
            'photo' => $request->file('photo'),
        ]);

        return redirect()
            ->route('tenant.staff.show', $result['staff'])
            ->with('status', "Staff member {$result['staff']->name} created. Temporary password: {$result['password']}");
    }

    public function show(Request $request, Staff $staff): View
    {
        return view('tenant.staff.show', $this->staffService->profile($request->user(), $staff));
    }

    public function edit(Request $request, Staff $staff): View
    {
        $this->staffService->ensureVisible($request->user(), $staff);
        abort_unless($this->staffService->canManage($request->user()), 403);

        return view('tenant.staff.form', [
            'staff' => $staff->load('branch'),
            'branches' => Branch::forTenant($request->user()->tenant_id)->active()->orderBy('name')->get(),
            'roles' => $this->staffService->roleOptions($request->user()->tenant_id),
            'proofTypes' => Staff::ID_PROOF_TYPES,
        ]);
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $this->staffService->ensureVisible($request->user(), $staff);
        abort_unless($this->staffService->canManage($request->user()), 403);

        $validated = $this->validateStaff($request, $staff, false);
        $this->staffService->update($request->user(), $staff, $validated, [
            'id_proof' => $request->file('id_proof'),
            'photo' => $request->file('photo'),
        ]);

        return redirect()->route('tenant.staff.show', $staff)->with('status', "Staff member {$staff->name} updated.");
    }

    public function deactivate(Request $request, Staff $staff): RedirectResponse
    {
        $this->staffService->ensureVisible($request->user(), $staff);
        abort_unless($this->staffService->canManage($request->user()), 403);

        $this->staffService->deactivate($request->user(), $staff);

        return back()->with('status', "{$staff->name} deactivated.");
    }

    public function destroy(Request $request, Staff $staff): RedirectResponse
    {
        $this->staffService->ensureVisible($request->user(), $staff);
        abort_unless($this->staffService->canManage($request->user()), 403);

        $request->validate([
            'confirm_name' => ['required', 'in:'.$staff->name],
        ]);

        $result = $this->staffService->delete($request->user(), $staff);

        return redirect()->route('tenant.staff.index')
            ->with('status', $result['soft_deleted']
                ? "{$staff->name} soft deleted because linked records exist."
                : "{$staff->name} deleted.");
    }

    public function roles(Request $request): View
    {
        abort_unless($this->staffService->canManage($request->user()), 403);

        return view('tenant.staff.roles', [
            'roles'       => $this->staffService->rolePermissions($request->user()),
            'staffCounts' => $this->staffService->staffCountsByRole($request->user()),
        ]);
    }

    public function createRole(Request $request): View
    {
        abort_unless($this->staffService->canManage($request->user()), 403);

        return view('tenant.staff.roles-form', [
            'roleRow'        => null,
            'defaultModules' => $this->staffService->defaultPermissionModules(),
        ]);
    }

    public function storeRole(Request $request): RedirectResponse
    {
        abort_unless($this->staffService->canManage($request->user()), 403);

        $validated = $request->validate([
            'role_name'   => ['required', 'string', 'min:2', 'max:50', 'regex:/^[a-z_]+$/'],
            'permissions' => ['nullable', 'array'],
        ]);

        $this->staffService->storeCustomRole(
            $request->user(),
            $validated['role_name'],
            $validated['permissions'] ?? []
        );

        return redirect()->route('tenant.staff.roles')->with('status', 'Role created.');
    }

    public function editRole(Request $request, string $role): View
    {
        abort_unless($this->staffService->canManage($request->user()), 403);

        $roleRow = $this->staffService->singleRolePermission($request->user(), $role);

        return view('tenant.staff.roles-form', [
            'roleRow'        => $roleRow,
            'defaultModules' => $this->staffService->permissionModulesForRole($role),
            'staffCount'     => $this->staffService->staffCountsByRole($request->user())[$roleRow->role] ?? 0,
        ]);
    }

    public function updateRolePermissions(Request $request, string $role): RedirectResponse
    {
        abort_unless($this->staffService->canManage($request->user()), 403);

        $permissions = $request->input('permissions', []);
        $this->staffService->updateRolePermissions($request->user(), $role, $permissions);

        return redirect()->route('tenant.staff.roles')->with('status', 'Permissions saved.');
    }

    public function resetRolePermissions(Request $request, string $role): RedirectResponse
    {
        abort_unless($this->staffService->canManage($request->user()), 403);

        $this->staffService->resetRolePermissions($request->user(), $role);

        return redirect()->route('tenant.staff.roles')->with('status', 'Permissions reset to defaults.');
    }

    public function destroyRole(Request $request, string $role): RedirectResponse
    {
        abort_unless($this->staffService->canManage($request->user()), 403);

        $this->staffService->destroyCustomRole($request->user(), $role);

        return redirect()->route('tenant.staff.roles')->with('status', 'Role deleted.');
    }

    public function attendance(Request $request): View|StreamedResponse
    {
        if (!$request->filled('branch_id') && $id = session('gymos_selected_branch_id')) {
            $request->merge(['branch_id' => $id]);
        }
        if ($request->get('export') === 'csv') {
            $csv = $this->staffService->exportAttendanceCsv($request->user(), $request);

            return response()->streamDownload(function () use ($csv): void {
                echo $csv;
            }, 'staff_attendance.csv', ['Content-Type' => 'text/csv']);
        }

        return view('tenant.staff.attendance', $this->staffService->attendance($request->user(), $request));
    }

    public function storeAttendance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', Rule::exists('staff', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'attendance_date' => ['required', 'date', 'before_or_equal:today'],
            'checked_in_at' => ['required', 'date_format:H:i'],
            'checked_out_at' => ['required', 'date_format:H:i', 'after:checked_in_at'],
            'reason' => ['required', 'string', 'max:300'],
        ]);

        $this->staffService->addAttendance($request->user(), $validated);

        return back()->with('status', 'Attendance entry added.');
    }

    private function validateStaff(Request $request, ?Staff $staff, bool $creating): array
    {
        $tenantId = $request->user()->tenant_id;

        return $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => [
                'required',
                'regex:/^\+?[0-9]{7,15}$/',
                Rule::unique('staff')->where('tenant_id', $tenantId)->ignore($staff?->id),
            ],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('staff')->where('tenant_id', $tenantId)->ignore($staff?->id),
                Rule::unique('users', 'email')->ignore($staff?->user_id),
            ],
            'role' => ['required', Rule::in($this->staffService->assignableRoleSlugs($tenantId))],
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('tenant_id', $tenantId)],
            'salary_paise' => ['nullable', 'integer', 'min:1'],
            'join_date' => ['required', 'date', 'before_or_equal:today'],
            'id_proof_type' => ['nullable', Rule::in(Staff::ID_PROOF_TYPES)],
            'id_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:500'],
            'status' => [$creating ? 'nullable' : 'required', Rule::in(Staff::STATUSES)],
            'password' => [$creating ? 'nullable' : 'nullable', 'string', 'min:8', 'confirmed'],
        ]);
    }
}
