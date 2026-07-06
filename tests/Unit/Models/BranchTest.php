<?php

namespace Tests\Unit\Models;

use App\Models\Branch;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BranchTest extends TestCase
{
    #[Test]
    public function address_short_accessor_returns_city_and_pin(): void
    {
        $branch = new Branch(['city' => 'Chennai', 'pin' => '600001']);

        $this->assertSame('Chennai, 600001', $branch->address_short);
    }

    #[Test]
    public function amenities_list_returns_empty_array_when_null(): void
    {
        $branch = new Branch;
        $branch->amenities = null;

        $this->assertSame([], $branch->amenities_list);
    }

    #[Test]
    public function amenities_list_returns_amenities_when_set(): void
    {
        $branch = new Branch;
        $branch->amenities = ['pool', 'steam', 'parking'];

        $this->assertSame(['pool', 'steam', 'parking'], $branch->amenities_list);
    }

    #[Test]
    public function indian_states_returns_all_states_and_uts(): void
    {
        $states = Branch::indianStates();

        $this->assertIsArray($states);
        $this->assertGreaterThan(28, count($states));
        $this->assertContains('Tamil Nadu', $states);
        $this->assertContains('Kerala', $states);
        $this->assertContains('Delhi', $states);
        $this->assertContains('Karnataka', $states);
    }

    #[Test]
    public function amenity_options_returns_expected_keys(): void
    {
        $options = Branch::amenityOptions();

        $this->assertArrayHasKey('pool', $options);
        $this->assertArrayHasKey('steam', $options);
        $this->assertArrayHasKey('parking', $options);
        $this->assertArrayHasKey('locker', $options);
        $this->assertArrayHasKey('cafeteria', $options);
        $this->assertArrayHasKey('ac', $options);
        $this->assertArrayHasKey('wifi', $options);

        $this->assertSame('Swimming Pool', $options['pool']);
        $this->assertSame('Air Conditioning', $options['ac']);
    }

    #[Test]
    public function casts_include_expected_types(): void
    {
        $branch = new Branch;
        $casts = $branch->getCasts();

        $this->assertSame('array', $casts['operating_hours']);
        $this->assertSame('array', $casts['amenities']);
        $this->assertSame('boolean', $casts['is_primary']);
    }

    #[Test]
    public function fillable_includes_key_fields(): void
    {
        $branch = new Branch;
        $fillable = $branch->getFillable();

        $expected = ['tenant_id', 'name', 'city', 'state', 'status', 'is_primary'];
        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Missing fillable: {$field}");
        }
    }
}
