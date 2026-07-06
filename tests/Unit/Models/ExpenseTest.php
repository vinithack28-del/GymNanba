<?php

namespace Tests\Unit\Models;

use App\Models\Expense;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExpenseTest extends TestCase
{
    #[Test]
    public function categories_constant_has_expected_keys(): void
    {
        $keys = array_keys(Expense::CATEGORIES);

        $expected = ['rent', 'utilities', 'salaries', 'equipment', 'marketing', 'supplies', 'insurance', 'software', 'miscellaneous'];

        $this->assertSame($expected, $keys);
    }

    #[Test]
    public function rent_subcategories_are_correct(): void
    {
        $this->assertSame(['main_hall', 'studio', 'storage', 'office'], Expense::CATEGORIES['rent']);
    }

    #[Test]
    public function utilities_subcategories_are_correct(): void
    {
        $this->assertSame(['electricity', 'water', 'internet', 'phone'], Expense::CATEGORIES['utilities']);
    }

    #[Test]
    public function miscellaneous_has_empty_subcategories(): void
    {
        $this->assertSame([], Expense::CATEGORIES['miscellaneous']);
    }

    #[Test]
    public function methods_constant_contains_expected_values(): void
    {
        $this->assertSame(['cash', 'upi', 'bank', 'cheque', 'card'], Expense::METHODS);
    }

    #[Test]
    public function statuses_constant_contains_expected_values(): void
    {
        $this->assertSame(['pending', 'approved', 'rejected'], Expense::STATUSES);
    }

    #[Test]
    public function recurrence_constant_contains_expected_values(): void
    {
        $this->assertSame(['daily', 'weekly', 'monthly', 'annual'], Expense::RECURRENCE);
    }

    #[Test]
    public function casts_include_expected_types(): void
    {
        $expense = new Expense;
        $casts = $expense->getCasts();

        $this->assertSame('date', $casts['date']);
        $this->assertSame('date', $casts['recurrence_end']);
        $this->assertSame('boolean', $casts['is_recurring']);
        $this->assertSame('integer', $casts['amount_paise']);
        $this->assertSame('integer', $casts['gst_paise']);
    }

    #[Test]
    public function fillable_includes_key_fields(): void
    {
        $expense = new Expense;
        $fillable = $expense->getFillable();

        $expected = [
            'tenant_id', 'branch_id', 'date', 'category', 'description',
            'amount_paise', 'method', 'status', 'is_recurring',
        ];

        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Missing fillable: {$field}");
        }
    }
}
