<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipPlanUpgrade extends Model
{
    protected $fillable = [
        'tenant_id',
        'member_id',
        'old_member_plan_id',
        'new_member_plan_id',
        'upgrade_date',
        'old_plan_price_paise',
        'new_plan_price_paise',
        'upgrade_amount_paise',
        'invoice_id',
        'payment_id',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'upgrade_date' => 'date',
            'old_plan_price_paise' => 'integer',
            'new_plan_price_paise' => 'integer',
            'upgrade_amount_paise' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function oldPlan(): BelongsTo
    {
        return $this->belongsTo(GymMembershipPlan::class, 'old_member_plan_id');
    }

    public function newPlan(): BelongsTo
    {
        return $this->belongsTo(GymMembershipPlan::class, 'new_member_plan_id');
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

    public function getOldPlanPriceRupeesAttribute(): float
    {
        return $this->old_plan_price_paise / 100;
    }

    public function getNewPlanPriceRupeesAttribute(): float
    {
        return $this->new_plan_price_paise / 100;
    }

    public function getUpgradeAmountRupeesAttribute(): float
    {
        return $this->upgrade_amount_paise / 100;
    }

    public function getOldPlanPriceFormattedAttribute(): string
    {
        return 'Rs. ' . number_format($this->old_plan_price_paise / 100, 2);
    }

    public function getNewPlanPriceFormattedAttribute(): string
    {
        return 'Rs. ' . number_format($this->new_plan_price_paise / 100, 2);
    }

    public function getUpgradeAmountFormattedAttribute(): string
    {
        return 'Rs. ' . number_format($this->upgrade_amount_paise / 100, 2);
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
