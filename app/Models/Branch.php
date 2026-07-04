<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'address1',
        'address2',
        'city',
        'state',
        'pin',
        'phone',
        'email',
        'manager_id',
        'manager_name',
        'operating_hours',
        'amenities',
        'gst_number',
        'status',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'operating_hours' => 'array',
            'amenities'       => 'array',
            'is_primary'      => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'branch_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class, 'branch_id');
    }

    public function posSales(): HasMany
    {
        return $this->hasMany(PosSale::class, 'branch_id');
    }

    public function posStockMovements(): HasMany
    {
        return $this->hasMany(PosStockMovement::class, 'branch_id');
    }

    public function adminUser(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\User::class, 'branch_id')
            ->where('role', 'branch_admin');
    }

    public function getActiveMembersCountAttribute(): int
    {
        $today = now()->toDateString();

        return $this->members()
            ->where('status', 'active')
            ->where(function ($q) use ($today): void {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $today);
            })
            ->count();
    }

    public function getAddressShortAttribute(): string
    {
        return $this->city . ', ' . $this->pin;
    }

    public function getAmenitiesListAttribute(): array
    {
        return $this->amenities ?? [];
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public static function indianStates(): array
    {
        return [
            'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
            'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
            'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
            'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
            'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
            'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
            'Andaman & Nicobar Islands', 'Chandigarh',
            'Dadra & Nagar Haveli and Daman & Diu', 'Delhi',
            'Jammu & Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry',
        ];
    }

    public static function amenityOptions(): array
    {
        return [
            'pool'      => 'Swimming Pool',
            'steam'     => 'Steam Room',
            'parking'   => 'Parking',
            'locker'    => 'Locker Room',
            'cafeteria' => 'Cafeteria',
            'ac'        => 'Air Conditioning',
            'wifi'      => 'WiFi',
        ];
    }
}

