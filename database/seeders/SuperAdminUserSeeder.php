<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SuperAdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's default superadmin user.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@gymnanba.com'],
            [
                'tenant_id' => null,
                'name' => 'Super Admin',
                'preferred_language' => 'en-IN',
                'role' => 'super_admin',
                'email_verified_at' => Carbon::now(),
                'password' => 'SuperAdmin@123',
            ],
        );
    }
}
