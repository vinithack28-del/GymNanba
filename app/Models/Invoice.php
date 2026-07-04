<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    public const STATUSES = ['paid', 'unpaid', 'partial', 'void'];

    public const GST_RATES = [0, 5, 12, 18, 28];

    public const VOID_REASONS = [
        'data_entry_error',
        'duplicate_invoice',
        'cancelled_service',
        'other',
    ];

    protected $fillable = [
        'tenant_id', 'member_id', 'payment_id', 'branch_id',
        'invoice_number', 'invoice_date', 'due_date',
        'line_items', 'subtotal_paise', 'gst_paise', 'total_paise',
        'status', 'notes', 'voided_at', 'void_reason', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date'  => 'date',
            'due_date'      => 'date',
            'voided_at'     => 'datetime',
            'line_items'    => 'array',
            'subtotal_paise' => 'integer',
            'gst_paise'     => 'integer',
            'total_paise'   => 'integer',
        ];
    }

    // â”€â”€ Relationships â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    // â”€â”€ Scopes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}

