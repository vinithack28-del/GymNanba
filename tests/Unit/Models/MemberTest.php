<?php

namespace Tests\Unit\Models;

use App\Models\Member;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MemberTest extends TestCase
{
    #[Test]
    public function effective_status_returns_inactive_when_status_is_inactive(): void
    {
        $member = new Member(['status' => 'inactive']);

        $this->assertSame('inactive', $member->effective_status);
    }

    #[Test]
    public function effective_status_returns_frozen_when_frozen_until_is_future(): void
    {
        $member = new Member(['status' => 'frozen']);
        $member->frozen_until = Carbon::tomorrow();

        $this->assertSame('frozen', $member->effective_status);
    }

    #[Test]
    public function effective_status_returns_active_when_frozen_until_is_past(): void
    {
        $member = new Member(['status' => 'frozen']);
        $member->frozen_until = Carbon::yesterday();

        $this->assertSame('active', $member->effective_status);
    }

    #[Test]
    public function effective_status_returns_expired_when_expiry_date_is_past(): void
    {
        $member = new Member(['status' => 'active']);
        $member->expiry_date = Carbon::yesterday();

        $this->assertSame('expired', $member->effective_status);
    }

    #[Test]
    public function effective_status_returns_active_when_expiry_date_is_future(): void
    {
        $member = new Member(['status' => 'active']);
        $member->expiry_date = Carbon::tomorrow();

        $this->assertSame('active', $member->effective_status);
    }

    #[Test]
    public function effective_status_returns_active_when_no_expiry_date(): void
    {
        $member = new Member(['status' => 'active']);

        $this->assertSame('active', $member->effective_status);
    }

    #[Test]
    public function is_frozen_returns_true_when_effective_status_is_frozen(): void
    {
        $member = new Member(['status' => 'frozen']);
        $member->frozen_until = Carbon::tomorrow();

        $this->assertTrue($member->isFrozen());
    }

    #[Test]
    public function is_frozen_returns_false_when_effective_status_is_not_frozen(): void
    {
        $member = new Member(['status' => 'active']);

        $this->assertFalse($member->isFrozen());
    }

    #[Test]
    public function status_label_returns_correct_labels(): void
    {
        $cases = [
            ['status' => 'active', 'expiry' => Carbon::tomorrow(), 'expected' => 'Active'],
            ['status' => 'inactive', 'expiry' => null, 'expected' => 'Inactive'],
            ['status' => 'frozen', 'expiry' => null, 'expected' => 'Frozen'],
        ];

        foreach ($cases as $case) {
            $member = new Member(['status' => $case['status']]);
            $member->expiry_date = $case['expiry'];
            if ($case['status'] === 'frozen') {
                $member->frozen_until = Carbon::tomorrow();
            }
            $this->assertSame($case['expected'], $member->status_label);
        }
    }

    #[Test]
    public function status_label_returns_expired_for_past_expiry(): void
    {
        $member = new Member(['status' => 'active']);
        $member->expiry_date = Carbon::yesterday();

        $this->assertSame('Expired', $member->status_label);
    }

    #[Test]
    public function balance_rupees_formats_correctly(): void
    {
        $member = new Member(['balance_paise' => 150050]);

        $this->assertSame('Rs. 1,500.50', $member->balance_rupees);
    }

    #[Test]
    public function balance_rupees_uses_absolute_value(): void
    {
        $member = new Member(['balance_paise' => -50000]);

        $this->assertSame('Rs. 500.00', $member->balance_rupees);
    }

    #[Test]
    public function balance_rupees_handles_zero(): void
    {
        $member = new Member(['balance_paise' => 0]);

        $this->assertSame('Rs. 0.00', $member->balance_rupees);
    }

    #[Test]
    public function initials_returns_two_letters_from_two_word_name(): void
    {
        $member = new Member(['name' => 'Arun Kumar']);

        $this->assertSame('AK', $member->initials);
    }

    #[Test]
    public function initials_returns_first_two_letters_from_single_word_name(): void
    {
        $member = new Member(['name' => 'Arun']);

        $this->assertSame('AR', $member->initials);
    }

    #[Test]
    public function initials_handles_three_word_name(): void
    {
        $member = new Member(['name' => 'Arun Kumar Singh']);

        $this->assertSame('AK', $member->initials);
    }

    #[Test]
    public function initials_are_uppercase(): void
    {
        $member = new Member(['name' => 'john doe']);

        $this->assertSame('JD', $member->initials);
    }
}
