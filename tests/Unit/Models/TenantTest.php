<?php

namespace Tests\Unit\Models;

use App\Models\Tenant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TenantTest extends TestCase
{
    #[Test]
    public function default_operating_hours_returns_seven_days(): void
    {
        $hours = Tenant::defaultOperatingHours();

        $this->assertCount(7, $hours);
        $this->assertArrayHasKey('mon', $hours);
        $this->assertArrayHasKey('tue', $hours);
        $this->assertArrayHasKey('wed', $hours);
        $this->assertArrayHasKey('thu', $hours);
        $this->assertArrayHasKey('fri', $hours);
        $this->assertArrayHasKey('sat', $hours);
        $this->assertArrayHasKey('sun', $hours);
    }

    #[Test]
    public function default_operating_hours_weekdays_are_open(): void
    {
        $hours = Tenant::defaultOperatingHours();

        foreach (['mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day) {
            $this->assertFalse($hours[$day]['closed'], "{$day} should not be closed");
            $this->assertSame('06:00', $hours[$day]['open']);
            $this->assertSame('22:00', $hours[$day]['close']);
        }
    }

    #[Test]
    public function default_operating_hours_sunday_is_closed(): void
    {
        $hours = Tenant::defaultOperatingHours();

        $this->assertTrue($hours['sun']['closed']);
        $this->assertSame('06:00', $hours['sun']['open']);
        $this->assertSame('22:00', $hours['sun']['close']);
    }

    #[Test]
    public function primary_domain_returns_subdomain_with_shared_domain_for_shared_mode(): void
    {
        $tenant = new Tenant([
            'subdomain' => 'liftlab',
            'domain_mode' => 'shared',
            'custom_domain' => null,
        ]);

        $domain = $tenant->primary_domain;

        $this->assertStringStartsWith('liftlab.', $domain);
    }

    #[Test]
    public function primary_domain_returns_custom_domain_for_separate_mode(): void
    {
        $tenant = new Tenant([
            'subdomain' => 'liftlab',
            'domain_mode' => 'separate',
            'custom_domain' => 'gym.liftlab.in',
        ]);

        $this->assertSame('gym.liftlab.in', $tenant->primary_domain);
    }

    #[Test]
    public function primary_domain_falls_back_to_subdomain_when_separate_but_no_custom_domain(): void
    {
        $tenant = new Tenant([
            'subdomain' => 'liftlab',
            'domain_mode' => 'separate',
            'custom_domain' => null,
        ]);

        $domain = $tenant->primary_domain;

        $this->assertStringStartsWith('liftlab.', $domain);
    }

    #[Test]
    public function fillable_includes_key_fields(): void
    {
        $tenant = new Tenant;
        $fillable = $tenant->getFillable();

        $expected = [
            'gym_name', 'business_type', 'owner_name', 'owner_email', 'phone',
            'subdomain', 'domain_mode', 'database_mode', 'status', 'default_language',
        ];

        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Missing fillable: {$field}");
        }
    }

    #[Test]
    public function social_links_cast_to_array(): void
    {
        $tenant = new Tenant;
        $casts = $tenant->getCasts();

        $this->assertSame('array', $casts['social_links']);
    }

    #[Test]
    public function operating_hours_cast_to_array(): void
    {
        $tenant = new Tenant;
        $casts = $tenant->getCasts();

        $this->assertSame('array', $casts['operating_hours']);
    }

    #[Test]
    public function last_owner_login_at_cast_to_datetime(): void
    {
        $tenant = new Tenant;
        $casts = $tenant->getCasts();

        $this->assertSame('datetime', $casts['last_owner_login_at']);
    }
}
