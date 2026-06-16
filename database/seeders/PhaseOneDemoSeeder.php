<?php

namespace Database\Seeders;

use App\Models\AdminAuditLog;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantPayment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PhaseOneDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::first();

        $plans = [
            [
                'name' => 'Starter',
                'billing_cycle' => 'Monthly',
                'price_paise' => 199900,
                'max_members' => 150,
                'max_branches' => 1,
                'max_staff_accounts' => 5,
                'feature_flags' => ['pos' => false, 'analytics' => false, 'whatsapp' => true, 'api_access' => false],
                'trial_eligible' => true,
                'description' => 'Built for single-branch gyms starting their digital operations.',
                'status' => 'active',
            ],
            [
                'name' => 'Growth',
                'billing_cycle' => 'Monthly',
                'price_paise' => 399900,
                'max_members' => 500,
                'max_branches' => 3,
                'max_staff_accounts' => 15,
                'feature_flags' => ['pos' => true, 'analytics' => true, 'whatsapp' => true, 'api_access' => false],
                'trial_eligible' => true,
                'description' => 'For growing gyms that need multiple branches and stronger analytics.',
                'status' => 'active',
            ],
            [
                'name' => 'Enterprise',
                'billing_cycle' => 'Annual',
                'price_paise' => 3599900,
                'max_members' => 0,
                'max_branches' => 0,
                'max_staff_accounts' => 0,
                'feature_flags' => ['pos' => true, 'analytics' => true, 'whatsapp' => true, 'api_access' => true],
                'trial_eligible' => false,
                'description' => 'Unlimited scale for premium multi-location gym brands.',
                'status' => 'active',
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(['name' => $planData['name']], $planData);
        }

        $tenantRows = [
            ['gym_name' => 'Iron Temple Fitness', 'business_type' => 'Gym', 'owner_name' => 'Raghav Iyer', 'owner_email' => 'owner@irontemple.in', 'phone' => '+919840000001', 'city' => 'Chennai', 'state' => 'Tamil Nadu', 'address' => '21 Cathedral Road, Thousand Lights', 'gst_number' => '33ABCDE1234F1Z5', 'subdomain' => 'irontemple', 'status' => 'active', 'default_language' => 'en-IN', 'members_count' => 186, 'last_owner_login_at' => Carbon::now()->subHours(5), 'notes' => 'High-conversion launch tenant.'],
            ['gym_name' => 'Pulse Yoga Studio', 'business_type' => 'Yoga', 'owner_name' => 'Meera Joshi', 'owner_email' => 'owner@pulseyoga.in', 'phone' => '+919840000002', 'city' => 'Bengaluru', 'state' => 'Karnataka', 'address' => '12 Indiranagar 100 Ft Road', 'gst_number' => null, 'subdomain' => 'pulseyoga', 'status' => 'trial', 'default_language' => 'hi-IN', 'members_count' => 74, 'last_owner_login_at' => Carbon::now()->subDay(), 'notes' => 'Trial ends soon.'],
            ['gym_name' => 'Forge Arena', 'business_type' => 'Gym', 'owner_name' => 'Nikhil Verma', 'owner_email' => 'owner@forgearena.in', 'phone' => '+919840000003', 'city' => 'Hyderabad', 'state' => 'Telangana', 'address' => '9 Jubilee Hills Main Road', 'gst_number' => '36ABCDE1234F1Z8', 'subdomain' => 'forgearena', 'status' => 'active', 'default_language' => 'te-IN', 'members_count' => 241, 'last_owner_login_at' => Carbon::now()->subHours(11), 'notes' => 'Requested white-label roadmap.'],
            ['gym_name' => 'Astra Turf Club', 'business_type' => 'Turf', 'owner_name' => 'Sana Khan', 'owner_email' => 'owner@astraturf.in', 'phone' => '+919840000004', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'address' => '44 Andheri Sports Lane', 'gst_number' => '27ABCDE1234F1Z9', 'subdomain' => 'astraturf', 'status' => 'suspended', 'default_language' => 'en-IN', 'members_count' => 112, 'last_owner_login_at' => Carbon::now()->subDays(4), 'notes' => 'Suspended for overdue payment.'],
            ['gym_name' => 'Core Republic', 'business_type' => 'Gym', 'owner_name' => 'Ananya Rao', 'owner_email' => 'owner@corerepublic.in', 'phone' => '+919840000005', 'city' => 'Pune', 'state' => 'Maharashtra', 'address' => '85 Baner High Street', 'gst_number' => '27ABCDE2234F1Z7', 'subdomain' => 'corerepublic', 'status' => 'active', 'default_language' => 'ta-IN', 'members_count' => 319, 'last_owner_login_at' => Carbon::now()->subHours(2), 'notes' => 'Multi-branch upgrade candidate.'],
            ['gym_name' => 'Zenfit Collective', 'business_type' => 'Yoga', 'owner_name' => 'Harish Menon', 'owner_email' => 'owner@zenfit.in', 'phone' => '+919840000006', 'city' => 'Kochi', 'state' => 'Kerala', 'address' => '7 Marine Drive Annex', 'gst_number' => null, 'subdomain' => 'zenfit', 'status' => 'archived', 'default_language' => 'en-IN', 'members_count' => 0, 'last_owner_login_at' => Carbon::now()->subMonths(2), 'notes' => 'Archived after closure request.'],
        ];

        $planMap = Plan::query()->get()->keyBy('name');

        foreach ($tenantRows as $index => $tenantData) {
            $ownerUser = User::updateOrCreate(
                ['email' => $tenantData['owner_email']],
                [
                    'tenant_id' => null,
                    'name' => $tenantData['owner_name'],
                    'preferred_language' => $tenantData['default_language'],
                    'role' => 'tenant_owner',
                    'email_verified_at' => Carbon::now(),
                    'password' => 'TenantOwner@123',
                ],
            );

            $tenant = Tenant::updateOrCreate(
                ['subdomain' => $tenantData['subdomain']],
                [
                    ...$tenantData,
                    'domain_mode' => 'shared',
                    'custom_domain' => null,
                    'database_mode' => 'shared',
                    'database_name' => null,
                    'owner_user_id' => $ownerUser->id,
                ],
            );

            $ownerUser->update(['tenant_id' => $tenant->id]);

            $planName = match ($tenantData['status']) {
                'trial' => 'Starter',
                'suspended' => 'Growth',
                'archived' => 'Starter',
                default => $index % 2 === 0 ? 'Growth' : 'Starter',
            };

            $plan = $planMap[$planName];
            $startDate = Carbon::now()->subDays(30 - ($index * 2));
            $trialEndDate = $tenantData['status'] === 'trial' ? Carbon::now()->addDays(5) : null;
            $endDate = $tenantData['status'] === 'trial'
                ? null
                : $startDate->copy()->addDays($plan->billing_cycle === 'Annual' ? 365 : 30);

            Subscription::updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'plan_id' => $plan->id,
                    'status' => $tenantData['status'] === 'trial' ? 'trial' : ($tenantData['status'] === 'archived' ? 'cancelled' : 'active'),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'trial_end_date' => $trialEndDate,
                    'price_paise' => $plan->price_paise,
                    'created_by' => $admin?->id,
                    'cancelled_at' => $tenantData['status'] === 'archived' ? Carbon::now()->subDays(20) : null,
                    'cancellation_reason' => $tenantData['status'] === 'archived' ? 'Owner requested closure' : null,
                ],
            );

            if ($tenantData['status'] !== 'trial' && $tenantData['status'] !== 'archived') {
                TenantPayment::updateOrCreate(
                    ['tenant_id' => $tenant->id],
                    [
                        'admin_id' => $admin?->id,
                        'amount_paise' => $plan->price_paise,
                        'payment_method' => $index % 2 === 0 ? 'UPI' : 'Bank transfer',
                        'transaction_ref' => 'GYMOS-PAY-10'.($index + 1),
                        'paid_at' => Carbon::now()->subDays(8 - $index),
                    ],
                );
            }
        }

        $auditRows = [
            ['action_type' => 'LOGIN', 'target_type' => 'ADMIN_ACCOUNT', 'target_id' => $admin?->id, 'target_name' => $admin?->email, 'difference' => ['device' => 'Chrome on macOS'], 'created_at' => Carbon::now()->subMinutes(35)],
            ['action_type' => 'TENANT_CREATE', 'target_type' => 'TENANT', 'target_id' => (string) Tenant::where('subdomain', 'pulseyoga')->value('id'), 'target_name' => 'Pulse Yoga Studio', 'difference' => ['status' => ['old' => null, 'new' => 'trial']], 'created_at' => Carbon::now()->subHours(3)],
            ['action_type' => 'PLAN_ASSIGN', 'target_type' => 'SUBSCRIPTION', 'target_id' => (string) Subscription::whereHas('tenant', fn ($query) => $query->where('subdomain', 'pulseyoga'))->value('id'), 'target_name' => 'Pulse Yoga Studio / Starter', 'difference' => ['plan' => 'Starter'], 'created_at' => Carbon::now()->subHours(2)],
            ['action_type' => 'PAYMENT_RECORD', 'target_type' => 'INVOICE', 'target_id' => (string) TenantPayment::whereHas('tenant', fn ($query) => $query->where('subdomain', 'corerepublic'))->value('id'), 'target_name' => 'Core Republic', 'difference' => ['amount_paise' => 399900], 'created_at' => Carbon::now()->subHours(7)],
            ['action_type' => 'SUSPEND', 'target_type' => 'TENANT', 'target_id' => (string) Tenant::where('subdomain', 'astraturf')->value('id'), 'target_name' => 'Astra Turf Club', 'difference' => ['status' => ['old' => 'active', 'new' => 'suspended']], 'created_at' => Carbon::now()->subDays(1)],
            ['action_type' => 'SETTINGS_CHANGE', 'target_type' => 'SETTINGS', 'target_id' => null, 'target_name' => 'Platform languages', 'difference' => ['enabled_locale' => 'ta-IN'], 'created_at' => Carbon::now()->subDays(2)],
        ];

        foreach ($auditRows as $auditRow) {
            AdminAuditLog::updateOrCreate(
                [
                    'action_type' => $auditRow['action_type'],
                    'target_name' => $auditRow['target_name'],
                ],
                [
                    'actor_admin_id' => $admin?->id,
                    'actor_name' => $admin?->name ?? 'System',
                    'actor_ip' => '203.0.113.45',
                    'target_type' => $auditRow['target_type'],
                    'target_id' => $auditRow['target_id'],
                    'old_value' => null,
                    'new_value' => null,
                    'difference' => $auditRow['difference'],
                    'user_agent' => 'Mozilla/5.0',
                    'created_at' => $auditRow['created_at'],
                ],
            );
        }
    }
}
