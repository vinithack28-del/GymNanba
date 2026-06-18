<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSale extends Model
{
    public const METHODS = [
        'cash',
        'upi',
        'card',
    ];

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'bill_number',
        'member_id',
        'subtotal_paise',
        'gst_paise',
        'discount_paise',
        'total_paise',
        'method',
        'reference',
        'notes',
        'sold_by',
        'refunded_at',
        'refunded_by',
        'refund_reason',
    ];

    protected function casts(): array
    {
        return [
            'refunded_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'sold_by');
    }

    public function refundActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosSaleItem::class, 'sale_id');
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function getTotalRupeesAttribute(): string
    {
        return number_format($this->total_paise / 100, 2);
    }

    public function getSubtotalRupeesAttribute(): string
    {
        return number_format($this->subtotal_paise / 100, 2);
    }

    public function getGstRupeesAttribute(): string
    {
        return number_format($this->gst_paise / 100, 2);
    }

    public function getDiscountRupeesAttribute(): string
    {
        return number_format($this->discount_paise / 100, 2);
    }

    public function getMethodLabelAttribute(): string
    {
        return strtoupper($this->method);
    }

    public function getItemsSummaryAttribute(): string
    {
        return $this->items
            ->take(2)
            ->map(fn (PosSaleItem $item) => "{$item->product_name} x{$item->qty}")
            ->implode(', ');
    }
}
