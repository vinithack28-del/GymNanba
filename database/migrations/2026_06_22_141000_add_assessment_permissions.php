<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $modules = [
            ['slug' => 'assessment_report', 'name' => 'Assessment Report', 'icon' => 'reports', 'sort_order' => 130],
            ['slug' => 'parq', 'name' => 'Questionnaire (PAR-Q+)', 'icon' => 'reports', 'sort_order' => 131],
            ['slug' => 'nutrition', 'name' => 'Nutritional Assessment', 'icon' => 'reports', 'sort_order' => 132],
            ['slug' => 'body_metrics', 'name' => 'Body Metrics', 'icon' => 'reports', 'sort_order' => 133],
            ['slug' => 'posture', 'name' => 'Posture Assessment', 'icon' => 'reports', 'sort_order' => 134],
            ['slug' => 'balance', 'name' => 'Balance Assessment', 'icon' => 'reports', 'sort_order' => 135],
            ['slug' => 'vitals', 'name' => 'Vitals Check', 'icon' => 'reports', 'sort_order' => 136],
            ['slug' => 'fitness', 'name' => 'Fitness Assessment', 'icon' => 'reports', 'sort_order' => 137],
            ['slug' => 'goal_forecasting', 'name' => 'Goal Forecasting', 'icon' => 'reports', 'sort_order' => 138],
        ];

        $moduleActions = [
            'assessment_report' => ['view'],
            'parq' => ['view', 'add', 'edit', 'delete'],
            'nutrition' => ['view', 'add', 'edit', 'delete'],
            'body_metrics' => ['view', 'add', 'edit', 'delete'],
            'posture' => ['view', 'add', 'edit', 'delete'],
            'balance' => ['view', 'add', 'edit', 'delete'],
            'vitals' => ['view', 'add', 'edit', 'delete'],
            'fitness' => ['view', 'add', 'edit', 'delete'],
            'goal_forecasting' => ['view'],
        ];

        foreach ($modules as $module) {
            DB::table('permission_modules')->updateOrInsert(
                ['slug' => $module['slug']],
                [
                    'name' => $module['name'],
                    'icon' => $module['icon'],
                    'sort_order' => $module['sort_order'],
                ]
            );
        }

        foreach ($moduleActions as $module => $actions) {
            foreach ($actions as $action) {
                DB::table('permissions')->updateOrInsert(
                    ['name' => "{$module}.{$action}", 'guard_name' => 'web'],
                    ['name' => "{$module}.{$action}", 'guard_name' => 'web']
                );
            }
        }

        $rolePermissions = [
            'trainer' => [
                'assessment_report.view',
                'parq.view', 'parq.add', 'parq.edit', 'parq.delete',
                'nutrition.view', 'nutrition.add', 'nutrition.edit', 'nutrition.delete',
                'body_metrics.view', 'body_metrics.add', 'body_metrics.edit', 'body_metrics.delete',
                'posture.view', 'posture.add', 'posture.edit', 'posture.delete',
                'balance.view', 'balance.add', 'balance.edit', 'balance.delete',
                'vitals.view', 'vitals.add', 'vitals.edit', 'vitals.delete',
                'fitness.view', 'fitness.add', 'fitness.edit', 'fitness.delete',
                'goal_forecasting.view',
            ],
            'branch_manager' => [
                'assessment_report.view',
                'parq.view', 'parq.add', 'parq.edit', 'parq.delete',
                'nutrition.view', 'nutrition.add', 'nutrition.edit', 'nutrition.delete',
                'body_metrics.view', 'body_metrics.add', 'body_metrics.edit', 'body_metrics.delete',
                'posture.view', 'posture.add', 'posture.edit', 'posture.delete',
                'balance.view', 'balance.add', 'balance.edit', 'balance.delete',
                'vitals.view', 'vitals.add', 'vitals.edit', 'vitals.delete',
                'fitness.view', 'fitness.add', 'fitness.edit', 'fitness.delete',
                'goal_forecasting.view',
            ],
        ];

        $permissionIds = DB::table('permissions')
            ->whereIn('name', collect($rolePermissions)->flatten()->unique()->values())
            ->pluck('id', 'name');

        $roles = DB::table('roles')
            ->whereIn('name', array_keys($rolePermissions))
            ->get(['id', 'tenant_id', 'name', 'default_permissions']);

        foreach ($roles as $role) {
            $names = $rolePermissions[$role->name] ?? [];

            foreach ($names as $name) {
                $permissionId = $permissionIds[$name] ?? null;
                if ($permissionId) {
                    DB::table('role_has_permissions')->updateOrInsert([
                        'permission_id' => $permissionId,
                        'role_id' => $role->id,
                    ], [
                        'permission_id' => $permissionId,
                        'role_id' => $role->id,
                    ]);
                }
            }

            $defaults = is_string($role->default_permissions)
                ? json_decode($role->default_permissions, true)
                : ($role->default_permissions ?? []);

            foreach ($names as $name) {
                [$module, $action] = explode('.', $name, 2);
                $defaults[$module][$action] = true;
            }

            DB::table('roles')
                ->where('id', $role->id)
                ->update(['default_permissions' => json_encode($defaults)]);
        }
    }

    public function down(): void
    {
        $modules = ['assessment_report', 'parq', 'nutrition', 'body_metrics', 'posture', 'balance', 'vitals', 'fitness', 'goal_forecasting'];
        $permissionIds = DB::table('permissions')
            ->where(function ($query) use ($modules): void {
                foreach ($modules as $module) {
                    $query->orWhere('name', 'like', $module . '.%');
                }
            })
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
        }

        DB::table('permissions')
            ->where(function ($query) use ($modules): void {
                foreach ($modules as $module) {
                    $query->orWhere('name', 'like', $module . '.%');
                }
            })
            ->delete();

        DB::table('permission_modules')->whereIn('slug', $modules)->delete();
    }
};
