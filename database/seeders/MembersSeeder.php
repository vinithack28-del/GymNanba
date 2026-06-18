<?php

namespace Database\Seeders;

use App\Models\GymMembershipPlan;
use App\Models\Member;
use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MembersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'irontemple')->first()
            ?? Tenant::first();

        if (!$tenant) {
            return;
        }

        $plans = [
            ['name' => 'Monthly Basic',     'duration_days' => 30,  'price_paise' => 80000,  'description' => 'Access to all gym equipment, 6 AM – 9 PM.'],
            ['name' => 'Monthly Premium',   'duration_days' => 30,  'price_paise' => 120000, 'description' => 'Equipment + group classes + locker.'],
            ['name' => 'Quarterly Basic',   'duration_days' => 90,  'price_paise' => 220000, 'description' => '3-month access, equipment only.'],
            ['name' => 'Quarterly Premium', 'duration_days' => 90,  'price_paise' => 320000, 'description' => '3-month all-access with personal training session.'],
            ['name' => 'Annual Basic',      'duration_days' => 365, 'price_paise' => 700000, 'description' => 'Full-year access, best value for committed members.'],
        ];

        $planModels = [];
        foreach ($plans as $planData) {
            $planModels[] = GymMembershipPlan::updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $planData['name']],
                [...$planData, 'tenant_id' => $tenant->id, 'status' => 'active']
            );
        }

        $demoMembers = [
            ['name' => 'Aarav Sharma',     'phone' => '+919000100001', 'email' => 'aarav.sharma@gmail.com',     'gender' => 'male',   'plan_idx' => 0, 'start_offset' => -15, 'status' => 'active'],
            ['name' => 'Priya Nair',       'phone' => '+919000100002', 'email' => 'priya.nair@gmail.com',       'gender' => 'female', 'plan_idx' => 1, 'start_offset' => -5,  'status' => 'active'],
            ['name' => 'Karthik Rajan',    'phone' => '+919000100003', 'email' => null,                          'gender' => 'male',   'plan_idx' => 2, 'start_offset' => -45, 'status' => 'active'],
            ['name' => 'Sneha Iyer',       'phone' => '+919000100004', 'email' => 'sneha.iyer@outlook.com',     'gender' => 'female', 'plan_idx' => 3, 'start_offset' => -60, 'status' => 'active'],
            ['name' => 'Rohit Kumar',      'phone' => '+919000100005', 'email' => 'rohit.kumar@yahoo.com',      'gender' => 'male',   'plan_idx' => 4, 'start_offset' => -10, 'status' => 'active'],
            ['name' => 'Divya Menon',      'phone' => '+919000100006', 'email' => 'divya.menon@gmail.com',      'gender' => 'female', 'plan_idx' => 0, 'start_offset' => -40, 'status' => 'inactive'],
            ['name' => 'Suresh Babu',      'phone' => '+919000100007', 'email' => null,                          'gender' => 'male',   'plan_idx' => 1, 'start_offset' => -65, 'status' => 'active'],
            ['name' => 'Ananya Pillai',    'phone' => '+919000100008', 'email' => 'ananya.pillai@gmail.com',    'gender' => 'female', 'plan_idx' => 2, 'start_offset' => -20, 'status' => 'active'],
            ['name' => 'Vijay Krishnan',   'phone' => '+919000100009', 'email' => 'vijay.k@hotmail.com',        'gender' => 'male',   'plan_idx' => 0, 'start_offset' => -35, 'status' => 'active'],
            ['name' => 'Meena Chandran',   'phone' => '+919000100010', 'email' => 'meena.c@gmail.com',          'gender' => 'female', 'plan_idx' => 3, 'start_offset' => -90, 'status' => 'active'],
            ['name' => 'Arun Selvam',      'phone' => '+919000100011', 'email' => null,                          'gender' => 'male',   'plan_idx' => 1, 'start_offset' => -50, 'status' => 'active'],
            ['name' => 'Kavitha Raj',      'phone' => '+919000100012', 'email' => 'kavitha.raj@gmail.com',      'gender' => 'female', 'plan_idx' => 4, 'start_offset' => -8,  'status' => 'active'],
            ['name' => 'Manoj Dubey',      'phone' => '+919000100013', 'email' => 'manoj.d@gmail.com',          'gender' => 'male',   'plan_idx' => 0, 'start_offset' => -33, 'status' => 'active'],
            ['name' => 'Sunita Verma',     'phone' => '+919000100014', 'email' => null,                          'gender' => 'female', 'plan_idx' => 2, 'start_offset' => -70, 'status' => 'inactive'],
            ['name' => 'Deepak Shetty',    'phone' => '+919000100015', 'email' => 'deepak.s@gmail.com',         'gender' => 'male',   'plan_idx' => 1, 'start_offset' => -25, 'status' => 'active'],
            ['name' => 'Lakshmi Patel',    'phone' => '+919000100016', 'email' => 'lakshmi.p@gmail.com',        'gender' => 'female', 'plan_idx' => 3, 'start_offset' => -12, 'status' => 'active'],
            ['name' => 'Nikhil Reddy',     'phone' => '+919000100017', 'email' => null,                          'gender' => 'male',   'plan_idx' => 0, 'start_offset' => -55, 'status' => 'active'],
            ['name' => 'Pooja Agarwal',    'phone' => '+919000100018', 'email' => 'pooja.a@outlook.com',        'gender' => 'female', 'plan_idx' => 1, 'start_offset' => -3,  'status' => 'active'],
            ['name' => 'Sanjay Rao',       'phone' => '+919000100019', 'email' => 'sanjay.rao@gmail.com',       'gender' => 'male',   'plan_idx' => 2, 'start_offset' => -80, 'status' => 'active'],
            ['name' => 'Geeta Nambiar',    'phone' => '+919000100020', 'email' => null,                          'gender' => 'female', 'plan_idx' => 4, 'start_offset' => -18, 'status' => 'active'],
            ['name' => 'Harish Pillai',    'phone' => '+919000100021', 'email' => 'harish.p@gmail.com',         'gender' => 'male',   'plan_idx' => 0, 'start_offset' => -95, 'status' => 'active'],
            ['name' => 'Rekha Devi',       'phone' => '+919000100022', 'email' => 'rekha.d@gmail.com',          'gender' => 'female', 'plan_idx' => 1, 'start_offset' => -28, 'status' => 'active'],
            ['name' => 'Sathish Kumar',    'phone' => '+919000100023', 'email' => null,                          'gender' => 'male',   'plan_idx' => 3, 'start_offset' => -42, 'status' => 'active'],
            ['name' => 'Bharathi Devi',    'phone' => '+919000100024', 'email' => 'bharathi.d@gmail.com',       'gender' => 'female', 'plan_idx' => 2, 'start_offset' => -7,  'status' => 'active'],
            ['name' => 'Ravi Shankar',     'phone' => '+919000100025', 'email' => 'ravi.shankar@gmail.com',     'gender' => 'male',   'plan_idx' => 4, 'start_offset' => -120, 'status' => 'active'],
            ['name' => 'Uma Maheshwari',   'phone' => '+919000100026', 'email' => null,                          'gender' => 'female', 'plan_idx' => 0, 'start_offset' => -32, 'status' => 'inactive'],
            ['name' => 'Gopal Krishnan',   'phone' => '+919000100027', 'email' => 'gopal.k@gmail.com',          'gender' => 'male',   'plan_idx' => 1, 'start_offset' => -52, 'status' => 'active'],
            ['name' => 'Sindhu Ravi',      'phone' => '+919000100028', 'email' => 'sindhu.r@yahoo.com',         'gender' => 'female', 'plan_idx' => 2, 'start_offset' => -14, 'status' => 'active'],
            ['name' => 'Bala Murugan',     'phone' => '+919000100029', 'email' => null,                          'gender' => 'male',   'plan_idx' => 3, 'start_offset' => -22, 'status' => 'active'],
            ['name' => 'Chitra Suresh',    'phone' => '+919000100030', 'email' => 'chitra.s@gmail.com',         'gender' => 'female', 'plan_idx' => 0, 'start_offset' => -37, 'status' => 'active'],
        ];

        // Seed one more tenant (pulseyoga) with plans too
        $tenant2 = Tenant::where('subdomain', 'pulseyoga')->first();
        if ($tenant2) {
            foreach ($plans as $planData) {
                GymMembershipPlan::updateOrCreate(
                    ['tenant_id' => $tenant2->id, 'name' => $planData['name']],
                    [...$planData, 'tenant_id' => $tenant2->id, 'status' => 'active']
                );
            }
        }

        Member::where('tenant_id', $tenant->id)->delete();

        foreach ($demoMembers as $idx => $data) {
            $plan = $planModels[$data['plan_idx']];
            $startDate = Carbon::now()->addDays($data['start_offset']);
            $expiryDate = $startDate->copy()->addDays($plan->duration_days);

            $effectiveStatus = $data['status'];
            if ($effectiveStatus === 'active' && $expiryDate->isPast()) {
                $effectiveStatus = 'active'; // keep as active but expiry is past (shown as expired in UI)
            }

            Member::create([
                'tenant_id'   => $tenant->id,
                'member_code' => 'MEM-' . str_pad($idx + 1, 5, '0', STR_PAD_LEFT),
                'name'        => $data['name'],
                'phone'       => $data['phone'],
                'email'       => $data['email'],
                'gender'      => $data['gender'],
                'plan_id'     => $plan->id,
                'plan_name'   => $plan->name,
                'start_date'  => $startDate->toDateString(),
                'expiry_date' => $expiryDate->toDateString(),
                'status'      => $effectiveStatus,
                'balance_paise' => $idx % 7 === 0 ? 50000 : 0,
                'created_by'  => null,
            ]);
        }
    }
}
