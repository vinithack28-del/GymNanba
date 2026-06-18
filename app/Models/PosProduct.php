<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosProduct extends Model
{
    public const CATEGORIES = [
        'supplement',
        'apparel',
        'equipment',
        'food_drink',
        'other',
    ];

    public const UNITS = [
        'piece',
        'kg',
        'litre',
        'pack',
    ];

    public const GST_RATES = [0, 5, 12, 18, 28];

    public const STATUSES = [
        'active',
        'inactive',
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'category',
        'sku',
        'unit',
        'cost_paise',
        'price_paise',
        'gst_rate',
        'stock_quantity',
        'low_stock_threshold',
        'photo_url',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'gst_rate' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(PosSaleItem::class, 'product_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(PosStockMovement::class, 'product_id');
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term): void {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function getCategoryLabelAttribute(): string
    {
        return str($this->category)->replace('_', ' ')->title()->toString();
    }

    public function getUnitLabelAttribute(): string
    {
        return strtoupper($this->unit === 'kg' ? 'kg' : ($this->unit === 'litre' ? 'litre' : $this->unit));
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    public function getPriceRupeesAttribute(): string
    {
        return number_format($this->price_paise / 100, 2);
    }

    public function getCostRupeesAttribute(): string
    {
        return number_format($this->cost_paise / 100, 2);
    }

    public function getStockValuePaisaAttribute(): int
    {
        return $this->stock_quantity * $this->cost_paise;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }
}
