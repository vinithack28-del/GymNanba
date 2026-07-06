<?php

namespace Tests\Unit\Models;

use App\Models\Payment;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    #[Test]
    public function methods_constant_contains_expected_values(): void
    {
        $expected = ['cash', 'upi', 'card', 'bank', 'cheque'];

        $this->assertSame($expected, Payment::METHODS);
    }

    #[Test]
    public function ref_required_constant_contains_expected_values(): void
    {
        $expected = ['card', 'bank', 'cheque'];

        $this->assertSame($expected, Payment::REF_REQUIRED);
    }

    #[Test]
    public function void_reasons_constant_contains_expected_values(): void
    {
        $expected = ['data_entry_error', 'duplicate_payment', 'refund', 'other'];

        $this->assertSame($expected, Payment::VOID_REASONS);
    }

    #[Test]
    public function fillable_includes_key_fields(): void
    {
        $payment = new Payment;
        $fillable = $payment->getFillable();

        $expectedFields = [
            'tenant_id', 'member_id', 'branch_id', 'amount_paise',
            'method', 'payment_date', 'status',
        ];

        foreach ($expectedFields as $field) {
            $this->assertContains($field, $fillable, "Missing fillable: {$field}");
        }
    }

    #[Test]
    public function casts_include_expected_types(): void
    {
        $payment = new Payment;
        $casts = $payment->getCasts();

        $this->assertSame('date', $casts['payment_date']);
        $this->assertSame('date', $casts['due_date']);
        $this->assertSame('datetime', $casts['voided_at']);
        $this->assertSame('integer', $casts['amount_paise']);
        $this->assertSame('integer', $casts['total_paise']);
        $this->assertSame('boolean', $casts['is_partial']);
        $this->assertSame('boolean', $casts['reminder_sent']);
    }

    #[Test]
    public function cash_does_not_require_reference(): void
    {
        $this->assertNotContains('cash', Payment::REF_REQUIRED);
    }

    #[Test]
    public function upi_does_not_require_reference(): void
    {
        $this->assertNotContains('upi', Payment::REF_REQUIRED);
    }
}
