<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'gym_name', 'business_type', 'owner_name', 'owner_email', 'phone',
        'city', 'state', 'address', 'address2', 'pin', 'email', 'website',
        'gst_number', 'gstin', 'pan', 'reg_number',
        'logo_url', 'cover_photo_url', 'social_links', 'about', 'operating_hours',
        'subdomain', 'domain_mode', 'custom_domain', 'database_mode', 'database_name',
        'owner_user_id', 'status', 'default_language', 'members_count',
        'last_owner_login_at', 'notes', 'registration_token',
    ];

    protected function casts(): array
    {
        return [
            'last_owner_login_at' => 'datetime',
            'social_links'        => 'array',
            'operating_hours'     => 'array',
        ];
    }

    public static function defaultOperatingHours(): array
    {
        $standard = ['open' => '06:00', 'close' => '22:00', 'closed' => false];
        return [
            'mon' => $standard, 'tue' => $standard, 'wed' => $standard,
            'thu' => $standard, 'fri' => $standard, 'sat' => $standard,
            'sun' => ['open' => '06:00', 'close' => '22:00', 'closed' => true],
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TenantPayment::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    public function memberRegistrations(): HasMany
    {
        return $this->hasMany(MemberRegistration::class);
    }

    public function ensureRegistrationToken(): string
    {
        if (! $this->registration_token) {
            $this->update(['registration_token' => \Illuminate\Support\Str::random(40)]);
        }

        return $this->registration_token;
    }

    public function getRegistrationUrlAttribute(): string
    {
        return route('register.show', $this->ensureRegistrationToken());
    }

    public function getPrimaryDomainAttribute(): string
    {
        if ($this->domain_mode === 'separate' && $this->custom_domain) {
            return $this->custom_domain;
        }

        $sharedDomain = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'gymos.in';

        return "{$this->subdomain}.{$sharedDomain}";
    }
}
