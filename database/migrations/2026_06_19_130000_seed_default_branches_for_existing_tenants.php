<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tenants = DB::table('tenants')
            ->whereNotIn('id', DB::table('branches')->select('tenant_id')->distinct())
            ->get(['id', 'gym_name', 'address', 'city', 'state', 'pin', 'phone', 'owner_email', 'gst_number']);

        $now = now();

        foreach ($tenants as $tenant) {
            $name = $tenant->gym_name . ' (Main Branch)';
            $exists = DB::table('branches')
                ->where('tenant_id', $tenant->id)
                ->where('name', $name)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('branches')->insert([
                'tenant_id'  => $tenant->id,
                'name'       => $name,
                'address1'   => $tenant->address ?? '',
                'city'       => $tenant->city,
                'state'      => $tenant->state,
                'pin'        => $tenant->pin ?? '',
                'phone'      => $tenant->phone,
                'email'      => $tenant->owner_email,
                'gst_number' => $tenant->gst_number,
                'status'     => 'active',
                'is_primary' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Removes only auto-generated main branches for tenants that have exactly one branch
        DB::table('branches')
            ->where('name', 'like', '% (Main Branch)')
            ->where('is_primary', true)
            ->whereIn('tenant_id', function ($q): void {
                $q->select('tenant_id')
                    ->from('branches')
                    ->groupBy('tenant_id')
                    ->havingRaw('COUNT(*) = 1');
            })
            ->delete();
    }
};
