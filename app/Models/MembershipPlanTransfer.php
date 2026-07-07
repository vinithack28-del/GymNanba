<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPlanTransfer extends Model
{
    protected $fillable = [
        'tenant_id',
        'source_member_id',
        'target_member_id',
        'membership_plan_id',
        'transfer_date',
        'old_start_date',
        'old_expiry_date',
        'new_start_date',
        'new_expiry_date',
        'remaining_days',
        'transfer_fee_amount',
        'invoice_id',
        'payment_id',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transfer_date' => 'date',
            'old_start_date' => 'date',
            'old_expiry_date' => 'date',
            'new_start_date' => 'date',
            'new_expiry_date' => 'date',
            'transfer_fee_amount' => 'integer',
            'remaining_days' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sourceMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'source_member_id');
    }

    public function targetMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'target_member_id');
    }

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(GymMembershipPlan::class, 'membership_plan_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePendingPayment($query)
    {
        return $query->where('status', 'pending_payment');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function getTransferFeeRupeesAttribute(): float
    {
        return $this->transfer_fee_amount / 100;
    }

    public function getTransferFeeFormattedAttribute(): string
    {
        return 'Rs. ' . number_format($this->transfer_fee_amount / 100, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_payment' => 'Pending Payment',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
