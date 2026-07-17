<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Concerns\InteractsWithTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Staff;
use App\Services\Tenant\StaffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class StaffController extends Controller
{
    use InteractsWithTenant;

    public function __construct(private readonly StaffService $staffService)
    {
    }

    public function index(Request $request){
        $this->applySelectedBranch($request);
        return Inertia::render('Tenant/Staff/Index', $this->staffService->list($request->user(), $request));
    }

    public function create(Request $request){
        abort_unless($this->staffService->canManage($request->user()), 403);

        return Inertia::render('Tenant/Staff/Create', [
            'branches'         => Branch::forTenant($request->user()->tenant_id)->active()->orderBy('name')->get(),
            'roles'            => $this->staffService->roleOptions($request->user()->tenant_id),
            'proofTypes'       => Staff::ID_PROOF_TYPES,
            'selectedBranchId' => $this->selectedBranchId(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->staffService->canManage($request->user()), 403);

        if ($branchId = $this->fixedBranchId($request)) {
            $request->merge(['branch_id' => $branchId]);
        }

        $validated = $this->validateStaff($request, null, true);
        $result = $this->staffService->create($request->user(), $validated, [
            'id_proof' => $request->file('id_proof'),
            'photo' => $request->file('photo'),
        ]);

        return redirect()
            ->route('tenant.staff.show', $result['staff'])
            ->with('status', "Staff member {$result['staff']->name} created. Temporary password: {$result['password']}");
    }

    public function show(Request $request, Staff $staff){
        return Inertia::render('Tenant/Staff/Show', $this->staffService->profile($request->user(), $staff, $request));
    }

    public function edit(Request $request, Staff $staff){
        $this->staffService->ensureVisible($request->user(), $staff);
        abort_unless($this->staffService->canManage($request->user()), 403);

        return Inertia::render('Tenant/Staff/Edit', [
            'staff' => $staff->load('branch'),
            'branches' => Branch::forTenant($request->user()->tenant_id)->active()->orderBy('name')->get(),
            'roles' => $this->staffService->roleOptions($request->user()->tenant_id),
            'proofTypes' => Staff::ID_PROOF_TYPES,
            'selectedBranchId' => $this->selectedBranchId(),
        ]);
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $this->staffService->ensureVisible($request->user(), $staff);
        abort_unless($this->staffService->canManage($request->user()), 403);

        if ($branchId = $this->fixedBranchId($request)) {
            $request->merge(['branch_id' => $branchId]);
        }

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

    public function resetPassword(Request $request, Staff $staff): RedirectResponse
    {
        $this->staffService->ensureVisible($request->user(), $staff);
        abort_unless($this->staffService->canManage($request->user()), 403);

        $this->staffService->resetPassword($request->user(), $staff);

        return back()->with('status', "{$staff->name}'s password reset to 123456.");
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

    public function roles(Request $request){
        abort_unless($this->staffService->canManage($request->user()), 403);

        return Inertia::render('Tenant/Staff/Permissions', [
            'roles'       => $this->staffService->rolePermissions($request->user()),
            'staffCounts' => $this->staffService->staffCountsByRole($request->user()),
        ]);
    }

    public function createRole(Request $request){
        abort_unless($this->staffService->canManage($request->user()), 403);

        return Inertia::render('Tenant/Staff/RolesForm', [
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

    public function editRole(Request $request, string $role){
        abort_unless($this->staffService->canManage($request->user()), 403);

        $roleRow = $this->staffService->singleRolePermission($request->user(), $role);

        return Inertia::render('Tenant/Staff/RolesForm', [
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

    public function attendance(Request $request): InertiaResponse|StreamedResponse
    {
        $this->applySelectedBranch($request);
        if ($request->get('export') === 'csv') {
            $csv = $this->staffService->exportAttendanceCsv($request->user(), $request);

            return response()->streamDownload(function () use ($csv): void {
                echo $csv;
            }, 'staff_attendance.csv', ['Content-Type' => 'text/csv']);
        }

        return Inertia::render('Tenant/Staff/Attendance', $this->staffService->attendance($request->user(), $request));
    }

    public function createAttendance(Request $request): InertiaResponse
    {
        return Inertia::render('Tenant/Staff/AttendanceForm', $this->staffService->attendanceForm($request->user(), $request));
    }

    public function storeAttendance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', Rule::exists('staff', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'attendance_date' => ['required', 'date', 'before_or_equal:today'],
            'checked_in_at' => ['required', 'date_format:H:i'],
            'checked_out_at' => ['required', 'date_format:H:i', 'after:checked_in_at'],
            'reason' => ['nullable', 'string', 'max:300'],
        ]);

        $this->staffService->addAttendance($request->user(), $validated);

        return redirect()->route('tenant.staff.attendance', [
            'month' => substr($validated['attendance_date'], 0, 7),
        ])->with('status', 'Attendance entry added.');
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

    private function fixedBranchId(Request $request): ?int
    {
        if ($id = $this->selectedBranchId()) {
            return $id;
        }

        $branches = Branch::forTenant($request->user()->tenant_id)->active()->pluck('id');

        return $branches->count() === 1 ? (int) $branches->first() : null;
    }
}
