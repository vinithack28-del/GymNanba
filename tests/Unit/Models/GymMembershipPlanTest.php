<?php

namespace Tests\Unit\Models;

use App\Models\GymMembershipPlan;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GymMembershipPlanTest extends TestCase
{
    #[Test]
    public function price_rupees_accessor_converts_paise_to_rupees(): void
    {
        $plan = new GymMembershipPlan(['price_paise' => 150000]);

        $this->assertSame(1500.0, $plan->price_rupees);
    }

    #[Test]
    public function price_formatted_accessor_returns_formatted_string(): void
    {
        $plan = new GymMembershipPlan(['price_paise' => 150000]);

        $this->assertSame('Rs. 1,500.00', $plan->price_formatted);
    }

    #[Test]
    public function total_price_paise_without_gst(): void
    {
        $plan = new GymMembershipPlan([
            'price_paise' => 100000,
            'gst_applicable' => false,
            'gst_rate' => 0,
        ]);

        $this->assertSame(100000, $plan->total_price_paise);
    }

    #[Test]
    public function total_price_paise_with_gst(): void
    {
        $plan = new GymMembershipPlan([
            'price_paise' => 100000,
            'gst_applicable' => true,
            'gst_rate' => 18.0,
        ]);

        $this->assertSame(118000, $plan->total_price_paise);
    }

    #[Test]
    public function gst_amount_paise_returns_zero_without_gst(): void
    {
        $plan = new GymMembershipPlan([
            'price_paise' => 100000,
            'gst_applicable' => false,
            'gst_rate' => 0,
        ]);

        $this->assertSame(0, $plan->gst_amount_paise);
    }

    #[Test]
    public function gst_amount_paise_returns_correct_amount(): void
    {
        $plan = new GymMembershipPlan([
            'price_paise' => 100000,
            'gst_applicable' => true,
            'gst_rate' => 18.0,
        ]);

        $this->assertSame(18000, $plan->gst_amount_paise);
    }

    #[Test]
    public function total_price_formatted_accessor(): void
    {
        $plan = new GymMembershipPlan([
            'price_paise' => 100000,
            'gst_applicable' => true,
            'gst_rate' => 18.0,
        ]);

        $this->assertSame('Rs. 1,180.00', $plan->total_price_formatted);
    }

    #[Test]
    public function is_one_day_pass_returns_true_for_single_day(): void
    {
        $plan = new GymMembershipPlan([
            'duration_type' => 'days',
            'duration_value' => 1,
        ]);

        $this->assertTrue($plan->isOneDayPass());
    }

    #[Test]
    public function is_one_day_pass_returns_false_for_multiple_days(): void
    {
        $plan = new GymMembershipPlan([
            'duration_type' => 'days',
            'duration_value' => 7,
        ]);

        $this->assertFalse($plan->isOneDayPass());
    }

    #[Test]
    public function is_one_day_pass_returns_false_for_months(): void
    {
        $plan = new GymMembershipPlan([
            'duration_type' => 'months',
            'duration_value' => 1,
        ]);

        $this->assertFalse($plan->isOneDayPass());
    }

    #[Test]
    public function duration_label_for_single_month(): void
    {
        $plan = new GymMembershipPlan([
            'duration_type' => 'months',
            'duration_value' => 1,
        ]);

        $this->assertSame('1 month', $plan->duration_label);
    }

    #[Test]
    public function duration_label_for_multiple_months(): void
    {
        $plan = new GymMembershipPlan([
            'duration_type' => 'months',
            'duration_value' => 3,
        ]);

        $this->assertSame('3 months', $plan->duration_label);
    }

    #[Test]
    public function duration_label_for_single_day(): void
    {
        $plan = new GymMembershipPlan([
            'duration_type' => 'days',
            'duration_value' => 1,
        ]);

        $this->assertSame('1 day', $plan->duration_label);
    }

    #[Test]
    public function duration_label_for_multiple_days(): void
    {
        $plan = new GymMembershipPlan([
            'duration_type' => 'days',
            'duration_value' => 30,
        ]);

        $this->assertSame('30 days', $plan->duration_label);
    }

    #[Test]
    public function is_session_based_returns_true_when_session_limit_set(): void
    {
        $plan = new GymMembershipPlan(['session_limit' => 10]);

        $this->assertTrue($plan->isSessionBased());
    }

    #[Test]
    public function is_session_based_returns_false_when_no_session_limit(): void
    {
        $plan = new GymMembershipPlan(['session_limit' => 0]);

        $this->assertFalse($plan->isSessionBased());
    }

    #[Test]
    public function is_session_based_returns_false_when_session_limit_null(): void
    {
        $plan = new GymMembershipPlan(['session_limit' => null]);

        $this->assertFalse($plan->isSessionBased());
    }

    #[Test]
    public function plan_validity_label_for_session_based_plan(): void
    {
        $plan = new GymMembershipPlan(['session_limit' => 10]);

        $this->assertSame('10 sessions', $plan->plan_validity_label);
    }

    #[Test]
    public function plan_validity_label_for_single_session(): void
    {
        $plan = new GymMembershipPlan(['session_limit' => 1]);

        $this->assertSame('1 session', $plan->plan_validity_label);
    }

    #[Test]
    public function plan_validity_label_for_duration_based_plan(): void
    {
        $plan = new GymMembershipPlan([
            'session_limit' => 0,
            'duration_type' => 'months',
            'duration_value' => 3,
        ]);

        $this->assertSame('3 months', $plan->plan_validity_label);
    }

    #[Test]
    public function compute_expiry_date_for_months(): void
    {
        $plan = new GymMembershipPlan([
            'duration_type' => 'months',
            'duration_value' => 3,
            'session_limit' => 0,
        ]);

        $expiry = $plan->computeExpiryDate('2026-01-01');

        $this->assertSame('2026-04-01', $expiry);
    }

    #[Test]
    public function compute_expiry_date_for_days(): void
    {
        $plan = new GymMembershipPlan([
            'duration_type' => 'days',
            'duration_value' => 30,
            'session_limit' => 0,
        ]);

        $expiry = $plan->computeExpiryDate('2026-01-01');

        $this->assertSame('2026-01-31', $expiry);
    }

    #[Test]
    public function compute_expiry_date_returns_null_for_session_based(): void
    {
        $plan = new GymMembershipPlan([
            'duration_type' => 'days',
            'duration_value' => 30,
            'session_limit' => 10,
        ]);

        $this->assertNull($plan->computeExpiryDate('2026-01-01'));
    }

    #[Test]
    public function sync_duration_days_for_months(): void
    {
        $plan = new GymMembershipPlan([
            'duration_type' => 'months',
            'duration_value' => 3,
        ]);

        $plan->syncDurationDays();

        $this->assertSame(90, $plan->duration_days);
    }

    #[Test]
    public function sync_duration_days_for_days(): void
    {
        $plan = new GymMembershipPlan([
            'duration_type' => 'days',
            'duration_value' => 15,
        ]);

        $plan->syncDurationDays();

        $this->assertSame(15, $plan->duration_days);
    }
}
