<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'member_code',
        'name',
        'phone',
        'email',
        'gender',
        'dob',
        'address',
        'id_proof_type',
        'id_proof_number',
        'id_proof_url',
        'photo_url',
        'plan_id',
        'plan_name',
        'start_date',
        'expiry_date',
        'status',
        'frozen_until',
        'balance_paise',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'dob'          => 'date',
            'start_date'   => 'date',
            'expiry_date'  => 'date',
            'frozen_until' => 'date',
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

    public function plan(): BelongsTo
    {
        return $this->belongsTo(GymMembershipPlan::class, 'plan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function posSales(): HasMany
    {
        return $this->hasMany(PosSale::class, 'member_id');
    }

    public function lockerAssignments(): HasMany
    {
        return $this->hasMany(LockerAssignment::class);
    }

    public function transfersAsSource(): HasMany
    {
        return $this->hasMany(MembershipPlanTransfer::class, 'source_member_id');
    }

    public function transfersAsTarget(): HasMany
    {
        return $this->hasMany(MembershipPlanTransfer::class, 'target_member_id');
    }

    public function upgrades(): HasMany
    {
        return $this->hasMany(MembershipPlanUpgrade::class, 'member_id');
    }

    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === 'inactive') {
            return 'inactive';
        }
        if ($this->status === 'frozen') {
            // Auto-unfreeze if the freeze period has ended
            if ($this->frozen_until && $this->frozen_until->isPast()) {
                return 'active';
            }
            return 'frozen';
        }
        if ($this->expiry_date && $this->expiry_date->isPast()) {
            return 'expired';
        }
        if ($this->hasCompletedSessionLimit()) {
            return 'expired';
        }

        return 'active';
    }

    public function hasCompletedSessionLimit(): bool
    {
        $sessionLimit = (int) ($this->plan?->session_limit ?? 0);

        if ($sessionLimit <= 0) {
            return false;
        }

        return $this->usedSessions() >= $sessionLimit;
    }

    public function usedSessions(): int
    {
        return AttendanceLog::query()
            ->where('tenant_id', $this->tenant_id)
            ->where('member_id', $this->id)
            ->when($this->start_date, fn ($q) => $q->whereDate('checked_in_at', '>=', $this->start_date->toDateString()))
            ->count();
    }

    public function isFrozen(): bool
    {
        return $this->effective_status === 'frozen';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->effective_status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            'expired' => 'Expired',
            'frozen' => 'Frozen',
            default => ucfirst($this->status),
        };
    }

    public function getBalanceRupeesAttribute(): string
    {
        return 'Rs. ' . number_format(abs($this->balance_paise) / 100, 2);
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));

        return strtoupper(
            count($words) >= 2
                ? substr($words[0], 0, 1) . substr($words[1], 0, 1)
                : substr($words[0], 0, 2)
        );
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term): void {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('member_code', 'like', "%{$term}%");
        });
    }

    public function scopeWithStatus($query, string $status)
    {
        $today = now()->toDateString();

        if ($status === 'expired') {
            return $query->where(function ($q) use ($today): void {
                $q->where('status', 'expired')
                    ->orWhere(function ($q2) use ($today): void {
                        $q2->where('status', 'active')->where('expiry_date', '<', $today);
                    })
                    ->orWhere(fn ($q2) => static::scopeSessionLimitReached($q2));
            });
        }

        if ($status === 'active') {
            return $query->where('status', 'active')->where(function ($q) use ($today): void {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $today);
            })->where(fn ($q) => static::scopeSessionLimitNotReached($q));
        }

        return $query->where('status', $status);
    }

    public static function generateCode(int $tenantId): string
    {
        $max = static::where('tenant_id', $tenantId)
            ->withTrashed()
            ->count();

        return 'MEM-' . str_pad($max + 1, 5, '0', STR_PAD_LEFT);
    }

    private static function scopeSessionLimitReached($query)
    {
        return $query->whereHas('plan', fn ($planQuery) => $planQuery
            ->whereNotNull('session_limit')
            ->where('session_limit', '>', 0)
            ->whereRaw('(
                select count(*)
                from attendance_logs
                where attendance_logs.member_id = members.id
                  and attendance_logs.tenant_id = members.tenant_id
                  and (members.start_date is null or DATE(attendance_logs.checked_in_at) >= members.start_date)
            ) >= gym_membership_plans.session_limit')
        );
    }

    private static function scopeSessionLimitNotReached($query)
    {
        return $query->where(function ($q): void {
            $q->whereDoesntHave('plan', fn ($planQuery) => $planQuery
                ->whereNotNull('session_limit')
                ->where('session_limit', '>', 0)
            )->orWhereHas('plan', fn ($planQuery) => $planQuery
                ->whereNotNull('session_limit')
                ->where('session_limit', '>', 0)
                ->whereRaw('(
                    select count(*)
                    from attendance_logs
                    where attendance_logs.member_id = members.id
                      and attendance_logs.tenant_id = members.tenant_id
                      and (members.start_date is null or DATE(attendance_logs.checked_in_at) >= members.start_date)
                ) < gym_membership_plans.session_limit')
            );
        });
    }
}

