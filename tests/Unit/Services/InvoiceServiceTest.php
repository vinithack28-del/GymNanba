<?php

namespace Tests\Unit\Services;

use App\Services\Admin\AuditLogService;
use App\Services\Admin\InvoiceService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class InvoiceServiceTest extends TestCase
{
    private InvoiceService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $auditLog = $this->createMock(AuditLogService::class);
        $this->service = new InvoiceService($auditLog);
    }

    private function callPrivate(string $method, mixed ...$args): mixed
    {
        $ref = new ReflectionMethod($this->service, $method);

        return $ref->invoke($this->service, ...$args);
    }

    #[Test]
    public function billing_cycle_days_returns_30_for_monthly(): void
    {
        $this->assertSame(30, $this->callPrivate('billingCycleDays', 'Monthly'));
    }

    #[Test]
    public function billing_cycle_days_returns_90_for_quarterly(): void
    {
        $this->assertSame(90, $this->callPrivate('billingCycleDays', 'Quarterly'));
    }

    #[Test]
    public function billing_cycle_days_returns_365_for_annual(): void
    {
        $this->assertSame(365, $this->callPrivate('billingCycleDays', 'Annual'));
    }

    #[Test]
    public function billing_cycle_days_defaults_to_30_for_unknown(): void
    {
        $this->assertSame(30, $this->callPrivate('billingCycleDays', 'biweekly'));
    }

    #[Test]
    public function billing_cycle_days_handles_null(): void
    {
        $this->assertSame(30, $this->callPrivate('billingCycleDays', null));
    }

    #[Test]
    public function parse_splits_returns_correct_structure_for_single_split(): void
    {
        $splits = [
            ['method' => 'Cash', 'amount' => '1500.00', 'reference' => null],
        ];

        $result = $this->callPrivate('parseSplits', $splits);

        $this->assertSame(150000, $result['total_paise']);
        $this->assertSame('Cash', $result['primary_method']);
        $this->assertNull($result['primary_ref']);
        $this->assertCount(1, $result['rows']);
    }

    #[Test]
    public function parse_splits_returns_correct_total_for_multiple_splits(): void
    {
        $splits = [
            ['method' => 'Cash', 'amount' => '1000.00'],
            ['method' => 'UPI', 'amount' => '500.00', 'reference' => 'TXN123'],
        ];

        $result = $this->callPrivate('parseSplits', $splits);

        $this->assertSame(150000, $result['total_paise']);
        $this->assertSame('Cash', $result['primary_method']);
        $this->assertNull($result['primary_ref']);
        $this->assertCount(2, $result['rows']);
    }

    #[Test]
    public function parse_splits_skips_zero_amount_rows(): void
    {
        $splits = [
            ['method' => 'Cash', 'amount' => '0'],
            ['method' => 'UPI', 'amount' => '500.00'],
        ];

        $result = $this->callPrivate('parseSplits', $splits);

        $this->assertSame(50000, $result['total_paise']);
        $this->assertSame('UPI', $result['primary_method']);
        $this->assertCount(1, $result['rows']);
    }

    #[Test]
    public function parse_splits_returns_defaults_for_empty_array(): void
    {
        $result = $this->callPrivate('parseSplits', []);

        $this->assertSame(0, $result['total_paise']);
        $this->assertSame('Cash', $result['primary_method']);
        $this->assertNull($result['primary_ref']);
        $this->assertCount(0, $result['rows']);
    }

    #[Test]
    public function parse_splits_skips_negative_amounts(): void
    {
        $splits = [
            ['method' => 'Cash', 'amount' => '-100.00'],
            ['method' => 'UPI', 'amount' => '200.00'],
        ];

        $result = $this->callPrivate('parseSplits', $splits);

        $this->assertSame(20000, $result['total_paise']);
        $this->assertCount(1, $result['rows']);
    }

    #[Test]
    public function parse_splits_preserves_reference_in_rows(): void
    {
        $splits = [
            ['method' => 'Bank', 'amount' => '5000.00', 'reference' => 'NEFT12345'],
        ];

        $result = $this->callPrivate('parseSplits', $splits);

        $this->assertSame('NEFT12345', $result['primary_ref']);
        $this->assertSame('NEFT12345', $result['rows'][0]['reference']);
    }

    #[Test]
    public function parse_splits_handles_fractional_amounts(): void
    {
        $splits = [
            ['method' => 'Cash', 'amount' => '99.99'],
        ];

        $result = $this->callPrivate('parseSplits', $splits);

        $this->assertSame(9999, $result['total_paise']);
    }
}
