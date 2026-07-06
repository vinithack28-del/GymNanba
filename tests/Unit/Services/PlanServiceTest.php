<?php

namespace Tests\Unit\Services;

use App\Models\Plan;
use App\Services\Admin\AuditLogService;
use App\Services\Admin\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanServiceTest extends TestCase
{
    use RefreshDatabase;

    private PlanService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $auditLog = $this->createMock(AuditLogService::class);
        $this->service = new PlanService($auditLog);
    }

    public function test_create_regular_plan(): void
    {
        $plan = $this->service->create([
            'name' => 'Gold Monthly',
            'is_trial' => false,
            'billing_cycle' => 'Monthly',
            'price_inr' => '1999.00',
            'max_members' => 150,
            'max_branches' => 2,
            'max_staff_accounts' => 5,
            'features' => ['sms', 'reports'],
            'trial_eligible' => true,
            'description' => 'Gold plan',
            'status' => 'active',
        ]);

        $this->assertInstanceOf(Plan::class, $plan);
        $this->assertSame('Gold Monthly', $plan->name);
        $this->assertFalse($plan->is_trial);
        $this->assertSame('Monthly', $plan->billing_cycle);
        $this->assertSame(199900, $plan->price_paise);
        $this->assertSame(150, $plan->max_members);
        $this->assertSame(2, $plan->max_branches);
        $this->assertSame(5, $plan->max_staff_accounts);
        $this->assertSame(['sms' => true, 'reports' => true], $plan->feature_flags);
        $this->assertTrue($plan->trial_eligible);
        $this->assertSame('active', $plan->status);
    }

    public function test_create_trial_plan_sets_zeroed_fields(): void
    {
        $plan = $this->service->create([
            'name' => 'Free Trial',
            'is_trial' => true,
            'trial_days' => 14,
            'billing_cycle' => 'Monthly',
            'price_inr' => '999.00',
            'max_members' => 100,
            'max_branches' => 3,
            'max_staff_accounts' => 10,
            'features' => ['sms'],
            'trial_eligible' => false,
            'description' => 'Trial plan',
            'status' => 'active',
        ]);

        $this->assertTrue($plan->is_trial);
        $this->assertSame(14, $plan->trial_days);
        $this->assertNull($plan->billing_cycle);
        $this->assertSame(0, $plan->price_paise);
        $this->assertSame(0, $plan->max_members);
        $this->assertSame(0, $plan->max_branches);
        $this->assertSame(0, $plan->max_staff_accounts);
        $this->assertSame([], $plan->feature_flags);
        $this->assertTrue($plan->trial_eligible);
    }

    public function test_update_plan(): void
    {
        $plan = Plan::query()->create([
            'name' => 'Original',
            'billing_cycle' => 'Monthly',
            'price_paise' => 100000,
            'max_members' => 50,
            'max_branches' => 1,
            'max_staff_accounts' => 3,
            'feature_flags' => [],
            'trial_eligible' => false,
            'status' => 'active',
        ]);

        $updated = $this->service->update($plan, [
            'name' => 'Updated Plan',
            'is_trial' => false,
            'billing_cycle' => 'Quarterly',
            'price_inr' => '2999.00',
            'max_members' => 200,
            'max_branches' => 3,
            'max_staff_accounts' => 10,
            'features' => ['sms', 'reports', 'pos'],
            'trial_eligible' => true,
            'description' => 'Updated description',
            'status' => 'active',
        ]);

        $this->assertSame('Updated Plan', $updated->name);
        $this->assertSame('Quarterly', $updated->billing_cycle);
        $this->assertSame(299900, $updated->price_paise);
        $this->assertSame(200, $updated->max_members);
        $this->assertSame(['sms' => true, 'reports' => true, 'pos' => true], $updated->feature_flags);
    }

    public function test_delete_plan(): void
    {
        $plan = Plan::query()->create([
            'name' => 'To Delete',
            'billing_cycle' => 'Monthly',
            'price_paise' => 100000,
            'max_members' => 50,
            'max_branches' => 1,
            'max_staff_accounts' => 3,
            'feature_flags' => [],
            'trial_eligible' => false,
            'status' => 'active',
        ]);

        $planId = $plan->id;
        $this->service->delete($plan);

        $this->assertNull(Plan::query()->find($planId));
    }
}
