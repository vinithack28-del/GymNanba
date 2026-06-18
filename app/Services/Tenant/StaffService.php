<?php

namespace App\Services\Tenant;

use App\Models\Branch;
use App\Models\OwnerAuditLog;
use App\Models\Staff;
use App\Models\StaffAttendanceLog;
use App\Models\StaffLoginActivity;
use App\Models\StaffRolePermission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

        $staff = $query->orderBy('name')->paginate(20)->withQueryString();

        return [
            'staff' => $staff,
            'stats' => $this->staffStats($user),
            'branches' => Branch::forTenant($tenant->id)->active()->orderBy('name')->get(),
            'roles' => Staff::ROLES,
            'statuses' => Staff::STATUSES,
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

    public function profile(User $user, Staff $staff): array
    {
        $this->ensureVisible($user, $staff);

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $attendanceLogs = $staff->attendanceLogs()
            ->whereBetween('attendance_date', [$monthStart, $monthEnd])
            ->orderByDesc('attendance_date')
            ->get();

        return [
            'staff' => $staff->load(['branch', 'user']),
            'loginActivities' => $staff->loginActivities()->latest('logged_in_at')->limit(10)->get(),
            'attendanceLogs' => $attendanceLogs,
            'attendanceSummary' => [
                'days_present' => $attendanceLogs->count(),
                'days_absent' => max(0, now()->day - $attendanceLogs->count()),
                'hours_worked' => round($attendanceLogs->sum('hours_worked_minutes') / 60, 1),
            ],
            'canManage' => $this->canManage($user),
        ];
    }

    public function attendance(User $user, Request $request): array
    {
        $tenant = $user->tenant;
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->endOfMonth()->toDateString());

        $query = $this->buildAttendanceQuery($user, $request, $from, $to);

        $logs = $query->orderByDesc('attendance_date')->orderBy('checked_in_at')->paginate(25)->withQueryString();

        $summaryBase = clone $query;
        $summaryRows = $summaryBase->get();

        return [
            'logs' => $logs,
            'branches' => Branch::forTenant($tenant->id)->active()->orderBy('name')->get(),
            'staffOptions' => Staff::query()->forTenant($tenant->id)->visibleTo($user)->orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
            'summary' => [
                'days_present' => $summaryRows->count(),
                'hours_worked' => round($summaryRows->sum('hours_worked_minutes') / 60, 1),
                'leaves_marked' => $summaryRows->where('source', 'manual')->whereNotNull('reason')->count(),
            ],
            'canManage' => $this->canManage($user),
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
            'reason' => $validated['reason'],
            'recorded_by' => $user->id,
        ]);

        $this->logOwnerAction($staff->tenant_id, $user, 'STAFF_ATTENDANCE_ADD', 'STAFF', $staff->id, $staff->name, [
            'date' => $validated['attendance_date'],
        ]);
    }

    public function rolePermissions(User $user): Collection
    {
        $tenantId = $user->tenant_id;
        $this->ensureDefaultRolePermissions($tenantId, $user->id);

        return StaffRolePermission::query()
            ->where('tenant_id', $tenantId)
            ->orderByRaw("CASE role
                WHEN 'receptionist' THEN 1
                WHEN 'trainer' THEN 2
                WHEN 'accountant' THEN 3
                WHEN 'pos' THEN 4
                WHEN 'branch_manager' THEN 5
                ELSE 99 END")
            ->get();
    }

    public function singleRolePermission(User $user, string $role): StaffRolePermission
    {
        $this->ensureDefaultRolePermissions($user->tenant_id, $user->id);

        return StaffRolePermission::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('role', $role)
            ->firstOrFail();
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

    public function defaultPermissionModules(): array
    {
        // Returns the full module+action scaffold for the permissions form
        return [
            'dashboard'  => ['view'],
            'members'    => ['view', 'add', 'edit', 'delete'],
            'renewals'   => ['view'],
            'attendance' => ['check_in', 'view_log'],
            'classes'    => ['view_timetable', 'manage', 'book'],
            'branches'   => ['view', 'manage'],
            'staff'      => ['view', 'manage'],
            'payments'   => ['collect', 'history', 'void'],
            'invoices'   => ['view', 'manage'],
            'expenses'   => ['view', 'manage'],
            'pos'        => ['billing', 'products_stock_view', 'manage_products'],
            'reports'    => ['view', 'revenue_only', 'branch_only', 'own_data'],
        ];
    }

    public function storeCustomRole(User $user, string $roleName, array $permissions): void
    {
        abort_if(
            StaffRolePermission::query()
                ->where('tenant_id', $user->tenant_id)
                ->where('role', $roleName)
                ->exists(),
            422
        );

        StaffRolePermission::query()->create([
            'tenant_id'  => $user->tenant_id,
            'role'       => $roleName,
            'permissions'=> $permissions,
            'updated_by' => $user->id,
            'updated_at' => now(),
        ]);

        $this->logOwnerAction($user->tenant_id, $user, 'STAFF_ROLE_CREATE', 'ROLE', null, $roleName, []);
    }

    public function destroyCustomRole(User $user, string $role): void
    {
        // Block deletion of built-in system roles
        abort_if(in_array($role, Staff::ROLES, true), 422);

        StaffRolePermission::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('role', $role)
            ->delete();

        $this->logOwnerAction($user->tenant_id, $user, 'STAFF_ROLE_DELETE', 'ROLE', null, $role, []);
    }

    public function updateRolePermissions(User $user, string $role, array $permissions): void
    {
        StaffRolePermission::query()->updateOrCreate(
            ['tenant_id' => $user->tenant_id, 'role' => $role],
            ['permissions' => $permissions, 'updated_by' => $user->id, 'updated_at' => now()]
        );

        $this->logOwnerAction($user->tenant_id, $user, 'STAFF_ROLE_UPDATE', 'ROLE', null, $role, [
            'permissions' => $permissions,
        ]);
    }

    public function resetRolePermissions(User $user, string $role): void
    {
        $defaults = $this->defaultPermissions()[$role] ?? [];

        StaffRolePermission::query()->updateOrCreate(
            ['tenant_id' => $user->tenant_id, 'role' => $role],
            ['permissions' => $defaults, 'updated_by' => $user->id, 'updated_at' => now()]
        );

        $this->logOwnerAction($user->tenant_id, $user, 'STAFF_ROLE_RESET', 'ROLE', null, $role, []);
    }

    public function exportAttendanceCsv(User $user, Request $request): string
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->endOfMonth()->toDateString());
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
        } catch (\Throwable) {
            // Keep staff creation non-blocking when mail is unavailable.
        }
    }

    private function ensureDefaultRolePermissions(int $tenantId, int $updatedBy): void
    {
        foreach ($this->defaultPermissions() as $role => $permissions) {
            StaffRolePermission::query()->firstOrCreate(
                ['tenant_id' => $tenantId, 'role' => $role],
                ['permissions' => $permissions, 'updated_by' => $updatedBy, 'updated_at' => now()]
            );
        }
    }

    private function defaultPermissions(): array
    {
        return [
            'receptionist' => [
                'dashboard' => ['view' => true],
                'members' => ['view' => true, 'add' => true, 'edit' => false, 'delete' => false],
                'renewals' => ['view' => true],
                'attendance' => ['check_in' => true, 'view_log' => true],
                'classes' => ['view_timetable' => true, 'book' => true],
                'payments' => ['collect' => true, 'history' => true],
            ],
            'trainer' => [
                'dashboard' => ['view' => true],
                'members' => ['view_own_clients' => true],
                'classes' => ['view_timetable' => true, 'manage_own' => true, 'book' => true],
                'reports' => ['own_data' => true],
            ],
            'accountant' => [
                'dashboard' => ['view' => true],
                'members' => ['view' => true],
                'renewals' => ['view' => true],
                'attendance' => ['view_log' => true],
                'payments' => ['collect' => true, 'history' => true, 'void' => true],
                'invoices' => ['view' => true],
                'expenses' => ['view' => true, 'manage' => true],
                'reports' => ['revenue_only' => true],
            ],
            'pos' => [
                'dashboard' => ['view' => true],
                'pos' => ['billing' => true, 'products_stock_view' => true],
            ],
            'branch_manager' => [
                'dashboard' => ['view_own_branch' => true],
                'members' => ['view' => true, 'add' => true, 'edit' => true],
                'renewals' => ['view' => true],
                'attendance' => ['check_in' => true, 'view_log' => true],
                'classes' => ['view_timetable' => true, 'manage' => true, 'book' => true],
                'branches' => ['view_own' => true],
                'staff' => ['view_own_branch' => true],
                'payments' => ['collect' => true, 'history' => true],
                'invoices' => ['view' => true],
                'expenses' => ['view_own_branch' => true],
                'reports' => ['branch_only' => true],
            ],
        ];
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
