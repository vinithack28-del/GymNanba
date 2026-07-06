<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SuperAdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's default superadmin user.
     */
    public function run(): void
    {
        $password = env('SUPER_ADMIN_PASSWORD', Str::random(16));

        $user = User::updateOrCreate(
            ['email' => env('SUPER_ADMIN_EMAIL', 'superadmin@gymnanba.com')],
            [
                'tenant_id' => null,
                'name' => 'Super Admin',
                'preferred_language' => 'en-IN',
                'role' => 'super_admin',
                'email_verified_at' => Carbon::now(),
                'password' => $password,
            ],
        );

        if ($user->wasRecentlyCreated && ! env('SUPER_ADMIN_PASSWORD')) {
            $this->command?->warn("Generated super-admin password: {$password}");
            $this->command?->warn('Set SUPER_ADMIN_PASSWORD in .env to use a fixed password.');
        }
    }
}
