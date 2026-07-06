<?php

namespace Tests\Unit\Models;

use App\Models\Plan;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PlanTest extends TestCase
{
    #[Test]
    public function fillable_includes_all_expected_fields(): void
    {
        $plan = new Plan;
        $fillable = $plan->getFillable();

        $expected = [
            'name', 'billing_cycle', 'price_paise', 'max_members',
            'max_branches', 'max_staff_accounts', 'feature_flags',
            'trial_eligible', 'is_trial', 'trial_days', 'description', 'status',
        ];

        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Missing fillable: {$field}");
        }
    }

    #[Test]
    public function feature_flags_cast_to_array(): void
    {
        $plan = new Plan;
        $casts = $plan->getCasts();

        $this->assertSame('array', $casts['feature_flags']);
    }

    #[Test]
    public function trial_eligible_cast_to_boolean(): void
    {
        $plan = new Plan;
        $casts = $plan->getCasts();

        $this->assertSame('boolean', $casts['trial_eligible']);
    }

    #[Test]
    public function is_trial_cast_to_boolean(): void
    {
        $plan = new Plan;
        $casts = $plan->getCasts();

        $this->assertSame('boolean', $casts['is_trial']);
    }
}
