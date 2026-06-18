<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHODS = ['cash', 'upi', 'card', 'bank', 'cheque'];

    public const REF_REQUIRED = ['upi', 'card', 'bank', 'cheque'];

    public const VOID_REASONS = [
        'data_entry_error',
        'duplicate_payment',
        'refund',
        'other',
    ];

    protected $fillable = [
        'tenant_id', 'member_id', 'branch_id', 'plan_id',
        'receipt_number', 'amount_paise', 'gst_paise', 'total_paise',
        'method', 'reference', 'payment_date', 'notes', 'status',
        'voided_at', 'void_reason', 'voided_by', 'collected_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date'  => 'date',
            'voided_at'     => 'datetime',
            'amount_paise'  => 'integer',
            'gst_paise'     => 'integer',
            'total_paise'   => 'integer',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(GymMembershipPlan::class, 'plan_id');
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'collected_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'voided_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
