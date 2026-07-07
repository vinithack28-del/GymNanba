<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GymMembershipPlan extends Model
{
    protected $table = 'gym_membership_plans';

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'duration_type',
        'duration_value',
        'duration_days',
        'session_limit',
        'price_paise',
        'gst_applicable',
        'gst_rate',
        'max_members',
        'grace_days',
        'inclusions',
        'tags',
        'allow_freeze',
        'max_freeze_days',
        'is_transferable',
        'has_transfer_fee',
        'transfer_fee_amount',
        'transfer_fee_gst_applicable',
        'transfer_notes',
        'is_upgradable',
        'has_upgrade_charge',
        'upgrade_charge_type',
        'upgrade_custom_amount',
        'upgrade_notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'gst_applicable'            => 'boolean',
            'allow_freeze'              => 'boolean',
            'is_transferable'           => 'boolean',
            'has_transfer_fee'          => 'boolean',
            'transfer_fee_gst_applicable' => 'boolean',
            'is_upgradable'              => 'boolean',
            'has_upgrade_charge'        => 'boolean',
            'inclusions'                => 'array',
            'tags'                      => 'array',
            'gst_rate'                  => 'float',
            'session_limit'             => 'integer',
            'transfer_fee_amount'       => 'integer',
            'upgrade_custom_amount'     => 'integer',
        ];
    }

    // â”€â”€ Relationships â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'plan_branches', 'plan_id', 'branch_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'plan_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(MembershipPlanTransfer::class, 'membership_plan_id');
    }

    public function upgrades(): HasMany
    {
        return $this->hasMany(MembershipPlanUpgrade::class, 'new_member_plan_id');
    }

    // â”€â”€ Accessors â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function getPriceRupeesAttribute(): float
    {
        return $this->price_paise / 100;
    }

    public function getPriceFormattedAttribute(): string
    {
        return 'Rs. ' . number_format($this->price_paise / 100, 2);
    }

    public function getTotalPricePaiseAttribute(): int
    {
        if ($this->gst_applicable && $this->gst_rate > 0) {
            return (int) round($this->price_paise * (1 + $this->gst_rate / 100));
        }
        return (int) $this->price_paise;
    }

    public function getGstAmountPaiseAttribute(): int
    {
        return max(0, $this->total_price_paise - (int) $this->price_paise);
    }

    public function getTotalPriceFormattedAttribute(): string
    {
        return 'Rs. ' . number_format($this->total_price_paise / 100, 2);
    }

    public function isOneDayPass(): bool
    {
        return $this->duration_type === 'days'
            && (int) ($this->duration_value ?: $this->duration_days) === 1;
    }

    public function getDurationLabelAttribute(): string
    {
        $val  = (int) ($this->duration_value ?: $this->duration_days);
        $type = ($this->duration_type === 'months') ? 'month' : 'day';
        return $val . ' ' . ($val === 1 ? $type : $type . 's');
    }

    public function isSessionBased(): bool
    {
        return (int) ($this->session_limit ?? 0) > 0;
    }

    public function isBothBased(): bool
    {
        return (int) ($this->session_limit ?? 0) > 0 && (int) ($this->duration_value ?? 0) > 0;
    }

    public function getPlanValidityLabelAttribute(): string
    {
        if ($this->isBothBased()) {
            $sessions = (int) $this->session_limit;
            $duration = $this->duration_label;

            return "{$duration} + {$sessions} " . ($sessions === 1 ? 'session' : 'sessions');
        }

        if ($this->isSessionBased()) {
            $sessions = (int) $this->session_limit;

            return $sessions . ' ' . ($sessions === 1 ? 'session' : 'sessions');
        }

        return $this->duration_label;
    }

    public function getActiveMemberCountAttribute(): int
    {
        return $this->members()
            ->where('status', 'active')
            ->where(function ($q): void {
                $today = now()->toDateString();
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $today);
            })
            ->count();
    }

    // â”€â”€ Scopes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVisible($query)
    {
        return $query->whereIn('status', ['active', 'inactive']);
    }

    // â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function computeExpiryDate(string $startDate): ?string
    {
        if ($this->isSessionBased() && !$this->isBothBased()) {
            return null;
        }

        $date = Carbon::parse($startDate);
        return $this->duration_type === 'months'
            ? $date->addMonths((int) $this->duration_value)->toDateString()
            : $date->addDays((int) ($this->duration_value ?: $this->duration_days))->toDateString();
    }

    public function syncDurationDays(): void
    {
        $this->duration_days = $this->duration_type === 'months'
            ? $this->duration_value * 30
            : $this->duration_value;
    }
}

