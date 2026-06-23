<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permission_modules')->updateOrInsert(
            ['slug' => 'locker'],
            ['name' => 'Lockers', 'icon' => 'locker', 'sort_order' => 126]
        );

        $actions = ['view', 'add', 'assign', 'edit', 'delete'];
        foreach ($actions as $action) {
            DB::table('permissions')->updateOrInsert(
                ['name' => "locker.{$action}", 'guard_name' => 'web'],
                ['name' => "locker.{$action}", 'guard_name' => 'web']
            );
        }

        $rolePermissions = [
            'branch_manager' => ['view', 'add', 'assign', 'edit', 'delete'],
            'receptionist' => ['view', 'assign'],
            'trainer' => ['view'],
            'accountant' => ['view'],
        ];

        $permissionIds = DB::table('permissions')
            ->whereIn('name', collect($rolePermissions)->flatMap(fn ($actions) => collect($actions)->map(fn ($action) => "locker.{$action}"))->all())
            ->pluck('id', 'name');

        $roles = DB::table('roles')->whereIn('name', array_keys($rolePermissions))->get(['id', 'name', 'default_permissions']);

        foreach ($roles as $role) {
            $defaults = is_string($role->default_permissions)
                ? json_decode($role->default_permissions, true)
                : ($role->default_permissions ?? []);

            foreach ($rolePermissions[$role->name] ?? [] as $action) {
                $permId = $permissionIds["locker.{$action}"] ?? null;
                if ($permId) {
                    DB::table('role_has_permissions')->updateOrInsert(
                        ['permission_id' => $permId, 'role_id' => $role->id],
                        ['permission_id' => $permId, 'role_id' => $role->id]
                    );
                }

                $defaults['locker'][$action] = true;
            }

            DB::table('roles')->where('id', $role->id)->update(['default_permissions' => json_encode($defaults)]);
        }
    }

    public function down(): void
    {
        $permIds = DB::table('permissions')->where('name', 'like', 'locker.%')->pluck('id');
        if ($permIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permIds)->delete();
        }

        DB::table('permissions')->where('name', 'like', 'locker.%')->delete();
        DB::table('permission_modules')->where('slug', 'locker')->delete();
    }
};
