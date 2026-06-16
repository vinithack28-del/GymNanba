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
        'gym_name',
        'business_type',
        'owner_name',
        'owner_email',
        'phone',
        'city',
        'state',
        'address',
        'gst_number',
        'subdomain',
        'domain_mode',
        'custom_domain',
        'database_mode',
        'database_name',
        'owner_user_id',
        'status',
        'default_language',
        'members_count',
        'last_owner_login_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'last_owner_login_at' => 'datetime',
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

    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
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
