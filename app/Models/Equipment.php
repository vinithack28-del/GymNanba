<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    public const TYPES = [
        'cardio'       => 'Cardio',
        'strength'     => 'Strength',
        'free_weights' => 'Free Weights',
        'functional'   => 'Functional',
        'other'        => 'Other',
    ];

    public const STATUSES = [
        'operational' => 'Operational',
        'maintenance' => 'Maintenance',
        'broken'      => 'Broken',
    ];

    protected $fillable = [
        'tenant_id', 'branch_id', 'name', 'type', 'brand', 'model',
        'purchase_date', 'warranty_expiry', 'purchase_price_paise',
        'status', 'location', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date'    => 'date',
            'warranty_expiry'  => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(EquipmentServiceRecord::class)->orderByDesc('service_date')->orderByDesc('id');
    }

    public function isWarrantyExpired(): bool
    {
        return $this->warranty_expiry && $this->warranty_expiry->isPast();
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
