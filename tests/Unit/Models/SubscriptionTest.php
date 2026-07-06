<?php

namespace Tests\Unit\Models;

use App\Models\Subscription;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    #[Test]
    public function fillable_includes_key_fields(): void
    {
        $subscription = new Subscription;
        $fillable = $subscription->getFillable();

        $expected = [
            'tenant_id', 'plan_id', 'status', 'start_date', 'end_date',
            'trial_end_date', 'price_paise', 'created_by',
            'cancelled_at', 'cancellation_reason',
        ];

        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Missing fillable: {$field}");
        }
    }

    #[Test]
    public function casts_include_expected_types(): void
    {
        $subscription = new Subscription;
        $casts = $subscription->getCasts();

        $this->assertSame('date', $casts['start_date']);
        $this->assertSame('date', $casts['end_date']);
        $this->assertSame('date', $casts['trial_end_date']);
        $this->assertSame('datetime', $casts['cancelled_at']);
    }
}
