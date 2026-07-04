<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Locker extends Model
{
    public const AVAILABILITIES = [
        'available' => 'Available',
        'occupied' => 'Occupied',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'locker_number',
        'location',
        'availability',
        'status',
        'notes',
        'created_by',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LockerAssignment::class)->orderByDesc('from_date')->orderByDesc('id');
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(LockerAssignment::class)->whereNull('released_at');
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function isOccupied(): bool
    {
        return $this->availability === 'occupied';
    }
}

