<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permission_modules')->updateOrInsert(
            ['slug' => 'equipment'],
            ['name' => 'Equipment', 'icon' => 'equipment', 'sort_order' => 200]
        );

        $actions = ['view', 'add', 'edit', 'delete', 'service_record'];
        foreach ($actions as $action) {
            DB::table('permissions')->updateOrInsert(
                ['name' => "equipment.{$action}", 'guard_name' => 'web'],
                ['name' => "equipment.{$action}", 'guard_name' => 'web']
            );
        }

        $rolePermissions = [
            'branch_manager' => ['view', 'add', 'edit', 'delete', 'service_record'],
            'receptionist'   => ['view'],
            'trainer'        => ['view'],
            'accountant'     => ['view'],
        ];

        $permNames = collect($rolePermissions)->flatMap(fn ($a) => array_map(fn ($x) => "equipment.{$x}", $a))->unique()->values()->all();
        $permissionIds = DB::table('permissions')->whereIn('name', $permNames)->pluck('id', 'name');
        $roles = DB::table('roles')->whereIn('name', array_keys($rolePermissions))->get(['id', 'name', 'default_permissions']);

        foreach ($roles as $role) {
            $roleActions = $rolePermissions[$role->name] ?? [];
            foreach ($roleActions as $action) {
                $permId = $permissionIds["equipment.{$action}"] ?? null;
                if ($permId) {
                    DB::table('role_has_permissions')->updateOrInsert(
                        ['permission_id' => $permId, 'role_id' => $role->id],
                        ['permission_id' => $permId, 'role_id' => $role->id]
                    );
                }
            }

            $defaults = is_string($role->default_permissions)
                ? json_decode($role->default_permissions, true)
                : ($role->default_permissions ?? []);

            foreach ($roleActions as $action) {
                $defaults['equipment'][$action] = true;
            }

            DB::table('roles')->where('id', $role->id)->update(['default_permissions' => json_encode($defaults)]);
        }
    }

    public function down(): void
    {
        $permIds = DB::table('permissions')->where('name', 'like', 'equipment.%')->pluck('id');
        if ($permIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permIds)->delete();
        }
        DB::table('permissions')->where('name', 'like', 'equipment.%')->delete();
        DB::table('permission_modules')->where('slug', 'equipment')->delete();
    }
};
