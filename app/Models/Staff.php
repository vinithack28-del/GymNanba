<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'active',
        'inactive',
    ];

    public const ID_PROOF_TYPES = [
        'aadhaar',
        'pan',
        'passport',
    ];

    protected $fillable = [
        'tenant_id',
        'user_id',
        'branch_id',
        'name',
        'phone',
        'email',
        'role',
        'salary_paise',
        'join_date',
        'id_proof_type',
        'id_proof_url',
        'photo_url',
        'status',
        'deactivated_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'deactivated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function loginActivities(): HasMany
    {
        return $this->hasMany(StaffLoginActivity::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(StaffAttendanceLog::class);
    }

    public function posSales(): HasMany
    {
        return $this->hasMany(PosSale::class, 'sold_by');
    }

    public function posStockMovements(): HasMany
    {
        return $this->hasMany(PosStockMovement::class, 'created_by');
    }

    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->name)) ?: [];

        return strtoupper(
            count($words) >= 2
                ? substr($words[0], 0, 1).substr($words[1], 0, 1)
                : substr($words[0] ?? 'S', 0, 2)
        );
    }

    public function getRoleLabelAttribute(): string
    {
        return str($this->role)->replace('_', ' ')->title()->toString();
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
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->when(in_array($user->role, ['branch_manager', 'branch_admin'], true), function ($q) use ($user): void {
            $q->where('branch_id', $user->branch_id);
        });
    }
}

