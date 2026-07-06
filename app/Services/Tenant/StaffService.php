<?php

namespace App\Services\Tenant;

use App\Models\Branch;
use App\Models\OwnerAuditLog;
use App\Models\Permission;
use App\Models\PermissionModule;
use App\Models\Role;
use App\Models\Staff;
use App\Models\StaffAttendanceLog;
use App\Models\StaffLoginActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class StaffService
{
    public function list(User $user, Request $request): array
    {
        $tenant = $user->tenant;
        $query = Staff::query()
            ->forTenant($tenant->id)
            ->visibleTo($user)
            ->with(['branch', 'user']);

        if ($search = trim((string) $request->get('search'))) {
            $query->search($search);
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($branchId = $this->resolveBranchFilter($user, $request->get('branch_id'))) {
            $query->where('branch_id', $branchId);
        }

        $perPage = min(max((int) $request->get('per_page', 25), 10), 100);
        $staff = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return [
            'staff' => $staff,
            'stats' => $this->staffStats($user),
            'branches' => Branch::forTenant($tenant->id)->active()->orderBy('name')->get(),
            'roles' => $this->assignableRoleSlugs($user->tenant_id),
            'statuses' => Staff::STATUSES,
            'filters' => [
                'search' => $request->get('search'),
                'role' => $request->get('role'),
                'branch_id' => $request->get('branch_id'),
                'status' => $request->get('status'),
                'per_page' => $perPage,
            ],
            'canManage' => $this->canManage($user),
        ];
    }

    public function create(User $user, array $validated, array $files = []): array
    {
        $tenant = $user->tenant;
        $password = Str::password(12);

        $staff = DB::transaction(function () use ($tenant, $user, $validated, $files, $password): Staff {
            $uploaded = $this->storeFiles($files);

            $loginUser = User::query()->create([
                'tenant_id' => $tenant->id,
                'branch_id' => $validated['branch_id'],
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'preferred_language' => $user->preferred_language ?? $tenant->default_language,
                'role' => $validated['role'],
                'password' => $password,
                'must_change_password' => true,
            ]);

            $this->syncUserRole($loginUser, $validated['role'], $tenant->id);

            $staff = Staff::query()->create([
                'tenant_id' => $tenant->id,
                'user_id' => $loginUser->id,
                'branch_id' => $validated['branch_id'],
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => strtolower($validated['email']),
                'role' => $validated['role'],
                'salary_paise' => $validated['salary_paise'] ?? null,
                'join_date' => $validated['join_date'],
                'id_proof_type' => $validated['id_proof_type'] ?? null,
                'id_proof_url' => $uploaded['id_proof_url'] ?? null,
                'photo_url' => $uploaded['photo_url'] ?? null,
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->logOwnerAction($tenant->id, $user, 'STAFF_CREATE', 'STAFF', $staff->id, $staff->name, [
                'role' => $staff->role,
                'branch_id' => $staff->branch_id,
            ]);

            return $staff;
        });

        $this->sendWelcomeMail($staff, $password);

        return ['staff' => $staff->load(['branch', 'user']), 'password' => $password];
    }

    public function update(User $user, Staff $staff, array $validated, array $files = []): Staff
    {
        return DB::transaction(function () use ($user, $staff, $validated, $files): Staff {
            $uploaded = $this->storeFiles($files);

            $staff->update([
                'branch_id' => $validated['branch_id'],
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => strtolower($validated['email']),
                'role' => $validated['role'],
                'salary_paise' => $validated['salary_paise'] ?? null,
                'join_date' => $validated['join_date'],
                'id_proof_type' => $validated['id_proof_type'] ?? null,
                'id_proof_url' => $uploaded['id_proof_url'] ?? $staff->id_proof_url,
                'photo_url' => $uploaded['photo_url'] ?? $staff->photo_url,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $staff->user?->update([
                'branch_id' => $validated['branch_id'],
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'role' => $validated['role'],
                ...(! empty($validated['password']) ? [
                    'password' => $validated['password'],
                    'must_change_password' => true,
                ] : []),
            ]);

            if ($staff->user) {
                $this->syncUserRole($staff->user, $validated['role'], $staff->tenant_id);
            }

            $this->logOwnerAction($staff->tenant_id, $user, 'STAFF_UPDATE', 'STAFF', $staff->id, $staff->name, [
                'status' => $staff->status,
                'role' => $staff->role,
            ]);

            return $staff->load(['branch', 'user']);
        });
    }

    public function deactivate(User $user, Staff $staff): void
    {
        DB::transaction(function () use ($user, $staff): void {
            $staff->update([
                'status' => 'inactive',
                'deactivated_at' => now(),
            ]);

            $staff->user?->update([
                'must_change_password' => false,
            ]);

            DB::table('sessions')->where('user_id', $staff->user_id)->delete();

            $this->logOwnerAction($staff->tenant_id, $user, 'STAFF_DEACTIVATE', 'STAFF', $staff->id, $staff->name, []);
        });
    }

    public function resetPassword(User $user, Staff $staff): void
    {
        abort_unless($staff->user, 422);

        DB::transaction(function () use ($user, $staff): void {
            $staff->user->forceFill([
                'password' => '123456',
                'must_change_password' => true,
            ])->save();

            DB::table('sessions')->where('user_id', $staff->user_id)->delete();

            $this->logOwnerAction($staff->tenant_id, $user, 'STAFF_PASSWORD_RESET', 'STAFF', $staff->id, $staff->name, [
                'default_password' => true,
            ]);
        });
    }

    public function delete(User $user, Staff $staff): array
    {
        $hasRecords = $staff->attendanceLogs()->exists() || $staff->loginActivities()->exists();

        DB::transaction(function () use ($staff, $hasRecords, $user): void {
            if ($hasRecords) {
                $staff->update([
                    'status' => 'inactive',
                    'deactivated_at' => now(),
                ]);
                $staff->delete();
            } else {
                $staff->user?->delete();
                $staff->delete();
            }

            $this->logOwnerAction($staff->tenant_id, $user, 'STAFF_DELETE', 'STAFF', $staff->id, $staff->name, [
                'soft_deleted' => $hasRecords,
            ]);
        });

        return ['soft_deleted' => $hasRecords];
    }

    public function profile(User $user, Staff $staff, ?Request $request = null): array
    {
        $this->ensureVisible($user, $staff);

        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request?->get('month'))
            ? $request->get('month')
            : now()->format('Y-m');
        $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthStart = $monthDate->toDateString();
        $monthEnd = $monthDate->copy()->endOfMonth()->toDateString();
        $attendanceLogs = $staff->attendanceLogs()
            ->whereBetween('attendance_date', [$monthStart, $monthEnd])
            ->orderByDesc('attendance_date')
            ->orderBy('checked_in_at')
            ->get()
            ->map(fn (StaffAttendanceLog $log) => [
                'id' => $log->id,
                'attendance_date' => $log->attendance_date?->format('d-m-Y'),
                'checked_in_at' => $log->checked_in_at?->format('H:i'),
                'checked_out_at' => $log->checked_out_at?->format('H:i'),
                'hours_worked' => $log->hours_worked_minutes !== null ? round($log->hours_worked_minutes / 60, 2) : null,
                'source' => $log->source,
                'reason' => $log->reason,
            ]);

        return [
            'staff' => $staff->load(['branch', 'user']),
            'loginActivities' => $staff->loginActivities()->latest('logged_in_at')->limit(10)->get(),
            'attendanceLogs' => $attendanceLogs,
            'attendanceFilters' => [
                'month' => $month,
            ],
            'attendanceSummary' => [
                'days_present' => $attendanceLogs->count(),
                'days_absent' => max(0, (int) $monthDate->daysInMonth - $attendanceLogs->count()),
                'hours_worked' => round($attendanceLogs->sum('hours_worked') ?? 0, 1),
            ],
            'tab' => $request?->get('tab', 'details') ?? 'details',
            'canManage' => $this->canManage($user),
        ];
    }

    public function attendance(User $user, Request $request): array
    {
        $tenant = $user->tenant;
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request->get('month'))
            ? $request->get('month')
            : now()->format('Y-m');
        $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $from = $monthDate->toDateString();
        $to = $monthDate->copy()->endOfMonth()->toDateString();

        $query = $this->buildAttendanceQuery($user, $request, $from, $to);
        $summaryRows = (clone $query)->get();

        $perPage = min(max((int) $request->get('per_page', 25), 10), 100);
        $logs = $query
            ->orderByDesc('attendance_date')
            ->orderBy('checked_in_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (StaffAttendanceLog $log) => [
                'id' => $log->id,
                'staff_name' => $log->staff?->name,
                'role_label' => $log->staff?->role_label,
                'branch_name' => $log->branch?->name,
                'attendance_date' => $log->attendance_date?->format('d-m-Y'),
                'checked_in_at' => $log->checked_in_at?->format('H:i'),
                'checked_out_at' => $log->checked_out_at?->format('H:i'),
                'hours_worked' => $log->hours_worked_minutes !== null ? round($log->hours_worked_minutes / 60, 2) : null,
                'reason' => $log->reason,
            ]);

        return [
            'logs' => $logs,
            'branches' => Branch::forTenant($tenant->id)->active()->orderBy('name')->get(),
            'staffOptions' => Staff::query()->forTenant($tenant->id)->visibleTo($user)->orderBy('name')->get(),
            'filters' => [
                'month' => $month,
                'branch_id' => $request->get('branch_id'),
                'staff_id' => $request->get('staff_id'),
                'per_page' => $perPage,
            ],
            'summary' => [
                'days_present' => $summaryRows->count(),
                'hours_worked' => round($summaryRows->sum('hours_worked_minutes') / 60, 1),
                'leaves_marked' => $summaryRows->where('source', 'manual')->whereNotNull('reason')->count(),
            ],
            'canManage' => $this->canManage($user),
        ];
    }

    public function attendanceForm(User $user, Request $request): array
    {
        $tenant = $user->tenant;

        return [
            'staffOptions' => Staff::query()
                ->forTenant($tenant->id)
                ->visibleTo($user)
                ->with('branch')
                ->orderBy('name')
                ->get(['id', 'tenant_id', 'branch_id', 'name', 'role', 'phone', 'email']),
            'selectedStaffId' => $request->get('staff_id'),
            'today' => now()->toDateString(),
        ];
    }

    public function addAttendance(User $user, array $validated): void
    {
        $staff = Staff::query()->findOrFail($validated['staff_id']);
        $this->ensureVisible($user, $staff);

        $checkIn = $validated['attendance_date'].' '.$validated['checked_in_at'];
        $checkOut = $validated['attendance_date'].' '.$validated['checked_out_at'];
        $minutes = max(0, Carbon::parse($checkIn)->diffInMinutes(Carbon::parse($checkOut), false));

        StaffAttendanceLog::query()->create([
            'tenant_id' => $staff->tenant_id,
            'staff_id' => $staff->id,
            'branch_id' => $staff->branch_id,
            'attendance_date' => $validated['attendance_date'],
            'checked_in_at' => $checkIn,
            'checked_out_at' => $checkOut,
            'hours_worked_minutes' => $minutes,
            'source' => 'manual',
            'reason' => $validated['reason'] ?? null,
            'recorded_by' => $user->id,
        ]);

        $this->logOwnerAction($staff->tenant_id, $user, 'STAFF_ATTENDANCE_ADD', 'STAFF', $staff->id, $staff->name, [
            'date' => $validated['attendance_date'],
        ]);
    }

    public function rolePermissions(User $user): Collection
    {
        $tenantId = $user->tenant_id;
        $this->initializeRolePermissionsForTenant($tenantId, $user->id);

        return Role::query()
            ->where('tenant_id', $tenantId)
            ->with('permissions')
            ->orderByDesc('is_system')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->each(function (Role $role): void {
                $this->decorateRole($role);
            });
    }

    public function singleRolePermission(User $user, string $role): Role
    {
        $this->initializeRolePermissionsForTenant($user->tenant_id, $user->id);

        $rolePermission = Role::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('name', $role)
            ->with('permissions')
            ->firstOrFail();

        $this->decorateRole($rolePermission);

        return $rolePermission;
    }

    public function staffCountsByRole(User $user): array
    {
        return Staff::query()
            ->forTenant($user->tenant_id)
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();
    }

    public function roleOptions(int $tenantId): Collection
    {
        $this->initializeRolePermissionsForTenant($tenantId);

        return Role::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('is_system')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->each(fn (Role $role) => $this->decorateRole($role));
    }

    public function assignableRoleSlugs(int $tenantId): array
    {
        return $this->roleOptions($tenantId)->pluck('role')->all();
    }

    public function defaultPermissionModules(): Collection
    {
        if (! $this->permissionTablesReady()) {
            return collect();
        }

        $moduleMeta = PermissionModule::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->keyBy('slug');

        return Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => explode('.', $permission->name, 2)[0])
            ->map(function (Collection $permissions, string $module) use ($moduleMeta) {
                $meta = $moduleMeta->get($module);

                return (object) [
                    'slug' => $module,
                    'name' => $meta?->name ?? str($module)->replace('_', ' ')->title()->toString(),
                    'icon' => $meta?->icon,
                    'sort_order' => $meta?->sort_order ?? 9999,
                    'actions' => $permissions
                        ->map(function (Permission $permission) {
                            [, $action] = explode('.', $permission->name, 2);

                            return (object) [
                                'slug' => $action,
                                'name' => str($action)->replace('_', ' ')->title()->toString(),
                            ];
                        })
                        ->values(),
                ];
            })
            ->sortBy(fn (object $module) => $module->sort_order)
            ->values();
    }

    public function permissionModulesForRole(?string $role = null): Collection
    {
        return $this->defaultPermissionModules();
    }

    public function storeCustomRole(User $user, string $roleName, array $permissions): void
    {
        abort_if(
            Role::query()
                ->where('tenant_id', $user->tenant_id)
                ->where('name', $roleName)
                ->exists(),
            422
        );

        $role = Role::query()->create([
            'tenant_id' => $user->tenant_id,
            'name' => $roleName,
            'guard_name' => 'web',
            'is_system' => false,
            'sort_order' => ((int) Role::query()->where('tenant_id', $user->tenant_id)->max('sort_order')) + 10,
            'default_permissions' => null,
        ]);

        $this->setRoleTeamContext($user->tenant_id);
        $role->syncPermissions($this->flattenPermissionPayload($this->normalizePermissionPayload($permissions)));

        $this->logOwnerAction($user->tenant_id, $user, 'STAFF_ROLE_CREATE', 'ROLE', null, $roleName, []);
    }

    public function destroyCustomRole(User $user, string $role): void
    {
        $roleModel = Role::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('name', $role)
            ->firstOrFail();

        abort_if((bool) $roleModel->is_system, 422);

        $roleModel->delete();

        $this->logOwnerAction($user->tenant_id, $user, 'STAFF_ROLE_DELETE', 'ROLE', null, $role, []);
    }

    public function updateRolePermissions(User $user, string $role, array $permissions): void
    {
        $permissions = $this->normalizePermissionPayload($permissions);
        $roleModel = Role::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('name', $role)
            ->firstOrFail();

        $this->setRoleTeamContext($user->tenant_id);
        $roleModel->syncPermissions($this->flattenPermissionPayload($permissions));

        $this->logOwnerAction($user->tenant_id, $user, 'STAFF_ROLE_UPDATE', 'ROLE', null, $role, [
            'permissions' => $permissions,
        ]);
    }

    public function resetRolePermissions(User $user, string $role): void
    {
        $roleModel = Role::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('name', $role)
            ->firstOrFail();

        $defaults = $this->normalizeStoredPermissions($roleModel->default_permissions ?? $this->defaultPermissionsForRole($role));

        $this->setRoleTeamContext($user->tenant_id);
        $roleModel->syncPermissions($this->flattenPermissionPayload($defaults));

        $this->logOwnerAction($user->tenant_id, $user, 'STAFF_ROLE_RESET', 'ROLE', null, $role, []);
    }

    public function exportAttendanceCsv(User $user, Request $request): string
    {
        if (preg_match('/^\d{4}-\d{2}$/', (string) $request->get('month'))) {
            $monthDate = Carbon::createFromFormat('Y-m', $request->get('month'))->startOfMonth();
            $from = $monthDate->toDateString();
            $to = $monthDate->copy()->endOfMonth()->toDateString();
        } else {
            $from = $request->get('from', now()->startOfMonth()->toDateString());
            $to = $request->get('to', now()->endOfMonth()->toDateString());
        }

        $rows = $this->buildAttendanceQuery($user, $request, $from, $to)
            ->orderByDesc('attendance_date')
            ->orderBy('checked_in_at')
            ->get();

        $lines = collect([
            ['Date', 'Staff name', 'Role', 'Branch', 'Check-in', 'Check-out', 'Hours worked', 'Source', 'Reason'],
            ...$rows->map(fn (StaffAttendanceLog $log) => [
                $log->attendance_date?->format('Y-m-d'),
                $log->staff?->name,
                $log->staff?->role_label,
                $log->staff?->branch?->name,
                $log->checked_in_at?->format('H:i'),
                $log->checked_out_at?->format('H:i'),
                round($log->hours_worked_minutes / 60, 2),
                strtoupper($log->source),
                $log->reason,
            ])->all(),
        ]);

        return $lines->map(fn (array $row) => collect($row)->map(function ($value) {
            $text = str_replace('"', '""', (string) $value);
            return "\"{$text}\"";
        })->implode(','))->implode("\n");
    }

    public function ensureVisible(User $user, Staff $staff): void
    {
        abort_unless($staff->tenant_id === $user->tenant_id, 403);

        if (in_array($user->role, ['branch_manager', 'branch_admin'], true)) {
            abort_unless($staff->branch_id === $user->branch_id, 403);
        }
    }

    public function canManage(User $user): bool
    {
        return in_array($user->role, ['tenant_owner'], true);
    }

    private function resolveBranchFilter(User $user, mixed $branchId): ?int
    {
        if (in_array($user->role, ['branch_manager', 'branch_admin'], true)) {
            return $user->branch_id;
        }

        return filled($branchId) ? (int) $branchId : null;
    }

    private function staffStats(User $user): array
    {
        $base = Staff::query()->forTenant($user->tenant_id)->visibleTo($user);

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'inactive' => (clone $base)->where('status', 'inactive')->count(),
            'late_logins' => (clone $base)->whereHas('user', fn ($q) => $q
                ->where(function ($q) {
                    $q->whereNull('last_login_at')->orWhere('last_login_at', '<', now()->subDays(30));
                }))->count(),
        ];
    }

    private function storeFiles(array $files): array
    {
        $stored = [];

        if (! empty($files['id_proof'])) {
            $stored['id_proof_url'] = $files['id_proof']->store('staff/id-proofs', 'public');
        }

        if (! empty($files['photo'])) {
            $stored['photo_url'] = $files['photo']->store('staff/photos', 'public');
        }

        return $stored;
    }

    private function sendWelcomeMail(Staff $staff, string $password): void
    {
        try {
            Mail::raw(
                "Welcome to GymOS.\nLogin URL: ".route('login')."\nEmail: {$staff->email}\nTemporary password: {$password}\nPlease change your password after first login.",
                fn ($message) => $message->to($staff->email)->subject('Your GymOS staff login')
            );
        } catch (\Throwable $e) {
            Log::warning('Staff welcome email failed', [
                'staff_id' => $staff->id,
                'email' => $staff->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function initializeRolePermissionsForTenant(int $tenantId, ?int $updatedBy = null): void
    {
        foreach (Role::query()->whereNull('tenant_id')->where('is_system', true)->orderBy('sort_order')->orderBy('id')->get() as $template) {
            $role = Role::query()->firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $template->name, 'guard_name' => 'web'],
                [
                    'is_system' => true,
                    'sort_order' => $template->sort_order ?? 9999,
                    'default_permissions' => $template->default_permissions ?? [],
                ]
            );

            $role->forceFill([
                'is_system' => true,
                'sort_order' => $template->sort_order ?? 9999,
                'default_permissions' => $template->default_permissions ?? [],
            ])->save();

            if ($role->permissions()->count() === 0) {
                $this->setRoleTeamContext($tenantId);
                $role->syncPermissions($this->flattenPermissionPayload($template->default_permissions ?? []));
            }
        }
    }

    private function defaultPermissionsForRole(string $role): array
    {
        return Role::query()
            ->whereNull('tenant_id')
            ->where('name', $role)
            ->value('default_permissions') ?? [];
    }

    private function normalizePermissionPayload(array $permissions): array
    {
        $permissions = $this->normalizeStoredPermissions($permissions);
        $normalized = [];

        foreach ($permissions as $module => $actions) {
            if (! is_array($actions)) {
                continue;
            }

            foreach ($actions as $action => $value) {
                $normalized[$module][$action] = in_array((string) $value, ['1', 'true', 'on'], true);
            }
        }

        return $normalized;
    }

    private function normalizeStoredPermissions(array $permissions): array
    {
        $aliases = [
            'dashboard' => ['view_own_branch' => 'view'],
            'members' => ['view_own_clients' => 'view'],
            'classes' => ['manage_own' => 'manage'],
            'branches' => ['view_own' => 'view'],
            'staff' => ['view_own_branch' => 'view'],
            'expenses' => ['view_own_branch' => 'view'],
        ];

        foreach ($aliases as $module => $actionAliases) {
            if (! isset($permissions[$module]) || ! is_array($permissions[$module])) {
                continue;
            }

            foreach ($actionAliases as $legacy => $canonical) {
                if (array_key_exists($legacy, $permissions[$module]) && ! array_key_exists($canonical, $permissions[$module])) {
                    $permissions[$module][$canonical] = $permissions[$module][$legacy];
                }

                unset($permissions[$module][$legacy]);
            }
        }

        return $permissions;
    }

    private function permissionTablesReady(): bool
    {
        return Schema::hasTable('roles')
            && Schema::hasTable('permissions')
            && Schema::hasTable('permission_modules');
    }

    private function flattenPermissionPayload(array $permissions): array
    {
        $names = [];

        foreach ($permissions as $module => $actions) {
            if (! is_array($actions)) {
                continue;
            }

            foreach ($actions as $action => $enabled) {
                if ($enabled) {
                    $names[] = $module.'.'.$action;
                }
            }
        }

        return array_values(array_unique($names));
    }

    private function permissionMapForRole(Role $role): array
    {
        $permissions = [];

        foreach ($role->permissions as $permission) {
            [$module, $action] = array_pad(explode('.', $permission->name, 2), 2, null);

            if ($module && $action) {
                $permissions[$module][$action] = true;
            }
        }

        return $this->normalizeStoredPermissions($permissions);
    }

    private function decorateRole(Role $role): void
    {
        $permissions = $this->permissionMapForRole($role->relationLoaded('permissions') ? $role : $role->load('permissions'));

        $role->unsetRelation('permissions');
        $role->setAttribute('role', $role->name);
        $role->setAttribute('display_name', str($role->name)->replace('_', ' ')->title()->toString());
        $role->setAttribute('permissions', $permissions);
    }

    private function syncUserRole(User $user, string $roleName, int $tenantId): void
    {
        $this->setRoleTeamContext($tenantId);

        // Always resolve the tenant-specific role object so Spatie doesn't pick up a
        // global role (tenant_id=NULL) that shares the same name.
        $role = Role::where('name', $roleName)
            ->where('tenant_id', $tenantId)
            ->where('guard_name', 'web')
            ->first();

        $user->syncRoles($role ? [$role] : [$roleName]);
    }

    private function setRoleTeamContext(int $tenantId): void
    {
        if (function_exists('setPermissionsTeamId')) {
            setPermissionsTeamId($tenantId);
        }
    }

    private function logOwnerAction(int $tenantId, User $user, string $actionType, ?string $targetType, mixed $targetId, ?string $targetName, array $payload): void
    {
        OwnerAuditLog::query()->create([
            'tenant_id' => $tenantId,
            'actor_user_id' => $user->id,
            'action_type' => $actionType,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'target_name' => $targetName,
            'payload' => $payload,
        ]);
    }

    private function buildAttendanceQuery(User $user, Request $request, string $from, string $to)
    {
        return StaffAttendanceLog::query()
            ->where('tenant_id', $user->tenant_id)
            ->with(['staff.branch'])
            ->whereBetween('attendance_date', [$from, $to])
            ->when(in_array($user->role, ['branch_manager', 'branch_admin'], true), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
            ->when($request->filled('staff_id'), fn ($q) => $q->where('staff_id', $request->integer('staff_id')));
    }
}
