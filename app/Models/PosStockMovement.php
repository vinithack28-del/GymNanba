<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosStockMovement extends Model
{
    public const TYPES = [
        'restock',
        'adjustment',
        'sale',
        'return',
    ];

    public const ADJUSTMENT_REASONS = [
        'damaged',
        'expired',
        'theft',
        'count_correction',
        'sample_gift',
    ];

    protected $fillable = [
        'product_id',
        'tenant_id',
        'branch_id',
        'sale_id',
        'type',
        'quantity',
        'cost_paise',
        'reason',
        'reference',
        'movement_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'movement_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PosProduct::class, 'product_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class, 'sale_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}

