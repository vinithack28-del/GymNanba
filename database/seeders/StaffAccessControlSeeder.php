<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionModule;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class StaffAccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            ['slug' => 'dashboard', 'name' => 'Dashboard', 'icon' => 'dashboard', 'sort_order' => 10, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10]]],
            ['slug' => 'members', 'name' => 'Members', 'icon' => 'members', 'sort_order' => 20, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'add', 'name' => 'Add', 'sort_order' => 20], ['slug' => 'edit', 'name' => 'Edit', 'sort_order' => 30], ['slug' => 'delete', 'name' => 'Delete', 'sort_order' => 40]]],
            ['slug' => 'renewals', 'name' => 'Renewals', 'icon' => 'renewals', 'sort_order' => 30, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10]]],
            ['slug' => 'attendance', 'name' => 'Attendance', 'icon' => 'attendance', 'sort_order' => 40, 'actions' => [['slug' => 'check_in', 'name' => 'Check In', 'sort_order' => 10], ['slug' => 'view_log', 'name' => 'View Log', 'sort_order' => 20]]],
            ['slug' => 'classes', 'name' => 'Classes', 'icon' => 'classes', 'sort_order' => 50, 'actions' => [['slug' => 'view_timetable', 'name' => 'View Timetable', 'sort_order' => 10], ['slug' => 'manage', 'name' => 'Manage', 'sort_order' => 20], ['slug' => 'book', 'name' => 'Book', 'sort_order' => 30]]],
            ['slug' => 'branches', 'name' => 'Branches', 'icon' => 'branches', 'sort_order' => 60, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'manage', 'name' => 'Manage', 'sort_order' => 20]]],
            ['slug' => 'staff', 'name' => 'Staff', 'icon' => 'staff', 'sort_order' => 70, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'manage', 'name' => 'Manage', 'sort_order' => 20]]],
            ['slug' => 'payments', 'name' => 'Payments', 'icon' => 'payments', 'sort_order' => 80, 'actions' => [['slug' => 'collect', 'name' => 'Collect', 'sort_order' => 10], ['slug' => 'history', 'name' => 'History', 'sort_order' => 20], ['slug' => 'void', 'name' => 'Void', 'sort_order' => 30]]],
            ['slug' => 'invoices', 'name' => 'Invoices', 'icon' => 'invoices', 'sort_order' => 90, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'manage', 'name' => 'Manage', 'sort_order' => 20]]],
            ['slug' => 'expenses', 'name' => 'Expenses', 'icon' => 'expenses', 'sort_order' => 100, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'manage', 'name' => 'Manage', 'sort_order' => 20]]],
            ['slug' => 'pos', 'name' => 'POS', 'icon' => 'pos', 'sort_order' => 110, 'actions' => [['slug' => 'billing', 'name' => 'Billing', 'sort_order' => 10], ['slug' => 'products_stock_view', 'name' => 'Products / Stock View', 'sort_order' => 20], ['slug' => 'manage_products', 'name' => 'Manage Products', 'sort_order' => 30]]],
            ['slug' => 'reports', 'name' => 'Reports', 'icon' => 'reports', 'sort_order' => 120, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'revenue_only', 'name' => 'Revenue Only', 'sort_order' => 20], ['slug' => 'branch_only', 'name' => 'Branch Only', 'sort_order' => 30], ['slug' => 'own_data', 'name' => 'Own Data', 'sort_order' => 40]]],
            ['slug' => 'equipment', 'name' => 'Equipment', 'icon' => 'equipment', 'sort_order' => 125, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'add', 'name' => 'Add', 'sort_order' => 20], ['slug' => 'edit', 'name' => 'Edit', 'sort_order' => 30], ['slug' => 'delete', 'name' => 'Delete', 'sort_order' => 40], ['slug' => 'service_record', 'name' => 'Service Record', 'sort_order' => 50]]],
            ['slug' => 'locker', 'name' => 'Lockers', 'icon' => 'locker', 'sort_order' => 126, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'add', 'name' => 'Add', 'sort_order' => 20], ['slug' => 'assign', 'name' => 'Assign / Reassign / Release', 'sort_order' => 30], ['slug' => 'edit', 'name' => 'Edit', 'sort_order' => 40], ['slug' => 'delete', 'name' => 'Delete', 'sort_order' => 50]]],
            ['slug' => 'assessment_report', 'name' => 'Assessment Report', 'icon' => 'reports', 'sort_order' => 130, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10]]],
            ['slug' => 'parq', 'name' => 'Questionnaire (PAR-Q+)', 'icon' => 'reports', 'sort_order' => 131, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'add', 'name' => 'Add', 'sort_order' => 20], ['slug' => 'edit', 'name' => 'Edit', 'sort_order' => 30], ['slug' => 'delete', 'name' => 'Delete', 'sort_order' => 40]]],
            ['slug' => 'nutrition', 'name' => 'Nutritional Assessment', 'icon' => 'reports', 'sort_order' => 132, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'add', 'name' => 'Add', 'sort_order' => 20], ['slug' => 'edit', 'name' => 'Edit', 'sort_order' => 30], ['slug' => 'delete', 'name' => 'Delete', 'sort_order' => 40]]],
            ['slug' => 'body_metrics', 'name' => 'Body Metrics', 'icon' => 'reports', 'sort_order' => 133, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'add', 'name' => 'Add', 'sort_order' => 20], ['slug' => 'edit', 'name' => 'Edit', 'sort_order' => 30], ['slug' => 'delete', 'name' => 'Delete', 'sort_order' => 40]]],
            ['slug' => 'posture', 'name' => 'Posture Assessment', 'icon' => 'reports', 'sort_order' => 134, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'add', 'name' => 'Add', 'sort_order' => 20], ['slug' => 'edit', 'name' => 'Edit', 'sort_order' => 30], ['slug' => 'delete', 'name' => 'Delete', 'sort_order' => 40]]],
            ['slug' => 'balance', 'name' => 'Balance Assessment', 'icon' => 'reports', 'sort_order' => 135, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'add', 'name' => 'Add', 'sort_order' => 20], ['slug' => 'edit', 'name' => 'Edit', 'sort_order' => 30], ['slug' => 'delete', 'name' => 'Delete', 'sort_order' => 40]]],
            ['slug' => 'vitals', 'name' => 'Vitals Check', 'icon' => 'reports', 'sort_order' => 136, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'add', 'name' => 'Add', 'sort_order' => 20], ['slug' => 'edit', 'name' => 'Edit', 'sort_order' => 30], ['slug' => 'delete', 'name' => 'Delete', 'sort_order' => 40]]],
            ['slug' => 'fitness', 'name' => 'Fitness Assessment', 'icon' => 'reports', 'sort_order' => 137, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10], ['slug' => 'add', 'name' => 'Add', 'sort_order' => 20], ['slug' => 'edit', 'name' => 'Edit', 'sort_order' => 30], ['slug' => 'delete', 'name' => 'Delete', 'sort_order' => 40]]],
            ['slug' => 'goal_forecasting', 'name' => 'Goal Forecasting', 'icon' => 'reports', 'sort_order' => 138, 'actions' => [['slug' => 'view', 'name' => 'View', 'sort_order' => 10]]],
        ];

        if (function_exists('setPermissionsTeamId')) {
            setPermissionsTeamId(null);
        }

        foreach ($modules as $moduleRow) {
            PermissionModule::query()->updateOrCreate(
                ['slug' => $moduleRow['slug']],
                [
                    'name' => $moduleRow['name'],
                    'icon' => $moduleRow['icon'],
                    'sort_order' => $moduleRow['sort_order'],
                ]
            );

            foreach ($moduleRow['actions'] as $actionRow) {
                Permission::query()->firstOrCreate([
                    'name' => $moduleRow['slug'].'.'.$actionRow['slug'],
                    'guard_name' => 'web',
                ]);
            }
        }

        $roles = [
            [
                'role' => 'receptionist',
                'name' => 'Receptionist',
                'sort_order' => 10,
                'permissions' => [
                    'dashboard' => ['view' => true],
                    'members' => ['view' => true, 'add' => true, 'edit' => false, 'delete' => false],
                    'renewals' => ['view' => true],
                    'attendance' => ['check_in' => true, 'view_log' => true],
                    'classes' => ['view_timetable' => true, 'book' => true],
                    'payments' => ['collect' => true, 'history' => true],
                    'equipment' => ['view' => true],
                    'locker' => ['view' => true, 'assign' => true],
                ],
            ],
            [
                'role' => 'trainer',
                'name' => 'Trainer',
                'sort_order' => 20,
                'permissions' => [
                    'dashboard' => ['view' => true],
                    'members' => ['view' => true],
                    'classes' => ['view_timetable' => true, 'manage' => true, 'book' => true],
                    'reports' => ['own_data' => true],
                    'equipment' => ['view' => true],
                    'locker' => ['view' => true],
                    'assessment_report' => ['view' => true],
                    'parq' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'nutrition' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'body_metrics' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'posture' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'balance' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'vitals' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'fitness' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'goal_forecasting' => ['view' => true],
                ],
            ],
            [
                'role' => 'accountant',
                'name' => 'Accountant',
                'sort_order' => 30,
                'permissions' => [
                    'dashboard' => ['view' => true],
                    'members' => ['view' => true],
                    'renewals' => ['view' => true],
                    'attendance' => ['view_log' => true],
                    'payments' => ['collect' => true, 'history' => true, 'void' => true],
                    'invoices' => ['view' => true],
                    'expenses' => ['view' => true, 'manage' => true],
                    'reports' => ['revenue_only' => true],
                    'equipment' => ['view' => true],
                    'locker' => ['view' => true],
                ],
            ],
            [
                'role' => 'pos',
                'name' => 'POS',
                'sort_order' => 40,
                'permissions' => [
                    'dashboard' => ['view' => true],
                    'pos' => ['billing' => true, 'products_stock_view' => true],
                ],
            ],
            [
                'role' => 'branch_manager',
                'name' => 'Branch Manager',
                'sort_order' => 50,
                'permissions' => [
                    'dashboard' => ['view' => true],
                    'members' => ['view' => true, 'add' => true, 'edit' => true],
                    'renewals' => ['view' => true],
                    'attendance' => ['check_in' => true, 'view_log' => true],
                    'classes' => ['view_timetable' => true, 'manage' => true, 'book' => true],
                    'branches' => ['view' => true, 'manage' => true],
                    'staff' => ['view' => true],
                    'payments' => ['collect' => true, 'history' => true],
                    'invoices' => ['view' => true],
                    'expenses' => ['view' => true],
                    'reports' => ['branch_only' => true],
                    'equipment' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true, 'service_record' => true],
                    'locker' => ['view' => true, 'add' => true, 'assign' => true, 'edit' => true, 'delete' => true],
                    'assessment_report' => ['view' => true],
                    'parq' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'nutrition' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'body_metrics' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'posture' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'balance' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'vitals' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'fitness' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'goal_forecasting' => ['view' => true],
                ],
            ],
        ];

        foreach ($roles as $roleRow) {
            $role = Role::query()->updateOrCreate(
                ['tenant_id' => null, 'name' => $roleRow['role'], 'guard_name' => 'web'],
                [
                    'is_system' => true,
                    'sort_order' => $roleRow['sort_order'],
                    'default_permissions' => $roleRow['permissions'],
                ]
            );

            $role->syncPermissions($this->flattenPermissionPayload($roleRow['permissions']));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function flattenPermissionPayload(array $permissions): array
    {
        $names = [];

        foreach ($permissions as $module => $actions) {
            foreach ($actions as $action => $enabled) {
                if ($enabled) {
                    $names[] = $module.'.'.$action;
                }
            }
        }

        return $names;
    }
}
