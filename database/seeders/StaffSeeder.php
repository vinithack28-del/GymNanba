<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Staff;
use App\Models\StaffAttendanceLog;
use App\Models\StaffRolePermission;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'irontemple')->first() ?? Tenant::first();

        if (! $tenant) {
            return;
        }

        $branches = Branch::forTenant($tenant->id)->active()->orderBy('id')->get();
        if ($branches->isEmpty()) {
            return;
        }

        $owner = User::query()->where('tenant_id', $tenant->id)->where('role', 'tenant_owner')->first();

        $rows = [
            ['name' => 'Sathish Kumar', 'phone' => '+919811100001', 'email' => 'sathish.staff@irontemple.in', 'role' => 'branch_manager', 'salary_paise' => 6500000, 'join_date' => now()->subMonths(16)->toDateString(), 'branch_id' => $branches[0]->id],
            ['name' => 'Priya Das', 'phone' => '+919811100002', 'email' => 'priya.staff@irontemple.in', 'role' => 'receptionist', 'salary_paise' => 2800000, 'join_date' => now()->subMonths(8)->toDateString(), 'branch_id' => $branches[0]->id],
            ['name' => 'Arjun Menon', 'phone' => '+919811100003', 'email' => 'arjun.staff@irontemple.in', 'role' => 'trainer', 'salary_paise' => 4200000, 'join_date' => now()->subMonths(10)->toDateString(), 'branch_id' => $branches[1]->id ?? $branches[0]->id],
            ['name' => 'Lakshmi Rao', 'phone' => '+919811100004', 'email' => 'lakshmi.staff@irontemple.in', 'role' => 'accountant', 'salary_paise' => 3600000, 'join_date' => now()->subMonths(12)->toDateString(), 'branch_id' => $branches[0]->id],
            ['name' => 'Deepak Raj', 'phone' => '+919811100005', 'email' => 'deepak.staff@irontemple.in', 'role' => 'pos', 'salary_paise' => 2500000, 'join_date' => now()->subMonths(5)->toDateString(), 'branch_id' => $branches[1]->id ?? $branches[0]->id],
        ];

        foreach ($rows as $index => $row) {
            $user = User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'tenant_id' => $tenant->id,
                    'branch_id' => $row['branch_id'],
                    'name' => $row['name'],
                    'preferred_language' => $tenant->default_language,
                    'role' => $row['role'],
                    'password' => 'StaffTemp@123',
                    'must_change_password' => true,
                    'last_login_at' => $index === 4 ? now()->subDays(45) : now()->subDays($index + 1),
                ]
            );

            $staff = Staff::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'email' => $row['email']],
                [
                    'user_id' => $user->id,
                    'branch_id' => $row['branch_id'],
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'role' => $row['role'],
                    'salary_paise' => $row['salary_paise'],
                    'join_date' => $row['join_date'],
                    'status' => $index === 4 ? 'inactive' : 'active',
                    'deactivated_at' => $index === 4 ? now()->subDays(4) : null,
                ]
            );

            for ($d = 0; $d < 10; $d++) {
                $date = Carbon::now()->subDays($d + 1);
                StaffAttendanceLog::query()->updateOrCreate(
                    ['staff_id' => $staff->id, 'attendance_date' => $date->toDateString()],
                    [
                        'tenant_id' => $tenant->id,
                        'branch_id' => $staff->branch_id,
                        'checked_in_at' => $date->copy()->setTime(9, 0),
                        'checked_out_at' => $date->copy()->setTime(18, 0),
                        'hours_worked_minutes' => 540,
                        'source' => 'manual',
                        'reason' => 'Seeded attendance record',
                        'recorded_by' => $owner?->id,
                    ]
                );
            }
        }

        if ($owner) {
            app(\App\Services\Tenant\StaffService::class)->rolePermissions($owner);
        } else {
            foreach (Staff::ROLES as $role) {
                StaffRolePermission::query()->firstOrCreate(
                    ['tenant_id' => $tenant->id, 'role' => $role],
                    ['permissions' => [], 'updated_by' => null, 'updated_at' => now()]
                );
            }
        }
    }
}
