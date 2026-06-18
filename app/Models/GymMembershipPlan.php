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
        'price_paise',
        'gst_applicable',
        'gst_rate',
        'max_members',
        'grace_days',
        'inclusions',
        'allow_freeze',
        'max_freeze_days',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'gst_applicable' => 'boolean',
            'allow_freeze'   => 'boolean',
            'inclusions'     => 'array',
            'gst_rate'       => 'float',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────────

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

    // ── Accessors ────────────────────────────────────────────────────────────

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

    public function getDurationLabelAttribute(): string
    {
        $val  = (int) ($this->duration_value ?: $this->duration_days);
        $type = ($this->duration_type === 'months') ? 'month' : 'day';
        return $val . ' ' . ($val === 1 ? $type : $type . 's');
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

    // ── Scopes ───────────────────────────────────────────────────────────────

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

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function computeExpiryDate(string $startDate): string
    {
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
