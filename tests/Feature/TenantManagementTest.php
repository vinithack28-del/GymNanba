<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlatformLanguage;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Tenancy\TenantDatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_tenant_owner_with_shared_database_defaults(): void
    {
        $admin = User::factory()->create([
            'email' => 'superadmin@gymnanba.com',
            'role' => 'super_admin',
        ]);

        PlatformLanguage::query()->create([
            'locale_code' => 'en-IN',
            'display_name' => 'English (India)',
            'is_active' => true,
            'completeness_pct' => 100,
            'is_rtl' => false,
        ]);

        $plan = Plan::query()->create([
            'name' => 'Starter',
            'billing_cycle' => 'Monthly',
            'price_paise' => 199900,
            'max_members' => 150,
            'max_branches' => 1,
            'max_staff_accounts' => 5,
            'feature_flags' => [],
            'trial_eligible' => true,
            'description' => 'Starter plan',
            'status' => 'active',
        ]);

        $fakeManager = new class extends TenantDatabaseManager
        {
            public bool $provisioned = false;

            public function provision(Tenant $tenant): void
            {
                $this->provisioned = true;
            }
        };

        $this->app->instance(TenantDatabaseManager::class, $fakeManager);

        $response = $this->actingAs($admin)->post(route('admin.tenants.store'), [
            'gym_name' => 'Lift Lab',
            'business_type' => 'Gym',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'address' => '42 Anna Salai, Teynampet, Chennai',
            'gst_number' => '33ABCDE1234F1Z5',
            'phone' => '+919999999999',
            'owner_name' => 'Arun Kumar',
            'owner_email' => 'owner@liftlab.in',
            'owner_password' => 'OwnerPass@123',
            'owner_password_confirmation' => 'OwnerPass@123',
            'subdomain' => 'liftlab',
            'domain_mode' => 'shared',
            'database_mode' => 'shared',
            'plan_id' => $plan->id,
            'default_language' => 'en-IN',
            'trial_enabled' => '1',
        ]);

        $tenant = Tenant::query()->where('subdomain', 'liftlab')->firstOrFail();
        $owner = User::query()->where('email', 'owner@liftlab.in')->firstOrFail();

        $response->assertRedirect(route('admin.tenants.show', $tenant, false));
        $this->assertSame('shared', $tenant->domain_mode);
        $this->assertSame('shared', $tenant->database_mode);
        $this->assertNull($tenant->database_name);
        $this->assertSame('tenant_owner', $owner->role);
        $this->assertSame($tenant->id, $owner->tenant_id);
        $this->assertSame($owner->id, $tenant->owner_user_id);
        $this->assertFalse($fakeManager->provisioned);
    }

    public function test_tenant_owner_can_log_in_with_created_credentials(): void
    {
        $tenant = Tenant::query()->create([
            'gym_name' => 'Lift Lab',
            'business_type' => 'Gym',
            'owner_name' => 'Arun Kumar',
            'owner_email' => 'owner@liftlab.in',
            'phone' => '+919999999999',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'address' => '42 Anna Salai, Teynampet, Chennai',
            'gst_number' => null,
            'subdomain' => 'liftlab',
            'domain_mode' => 'shared',
            'custom_domain' => null,
            'database_mode' => 'shared',
            'database_name' => null,
            'status' => 'active',
            'default_language' => 'en-IN',
        ]);

        $owner = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Arun Kumar',
            'email' => 'owner@liftlab.in',
            'role' => 'tenant_owner',
            'password' => 'OwnerPass@123',
        ]);

        $tenant->update(['owner_user_id' => $owner->id]);

        $response = $this->post('/login', [
            'email' => 'owner@liftlab.in',
            'password' => 'OwnerPass@123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($owner);

        $dashboard = $this->actingAs($owner)->get('/dashboard');
        $dashboard->assertOk();
        $dashboard->assertSee('Lift Lab');
    }
}
