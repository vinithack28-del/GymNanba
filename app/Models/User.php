<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['tenant_id', 'branch_id', 'name', 'email', 'phone', 'preferred_language', 'role', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected string $guard_name = 'web';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'must_change_password' => 'boolean',
            'password' => 'hashed',
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

    public function staffProfile(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isGymOwner(): bool
    {
        return $this->role === 'tenant_owner';
    }

    public function isStaffMember(): bool
    {
        return ! $this->isSuperAdmin() && ! $this->isGymOwner();
    }

    private ?array $cachedPermissions = null;

    private function loadDirectPermissions(): array
    {
        if ($this->cachedPermissions !== null) {
            return $this->cachedPermissions;
        }

        if (! $this->isStaffMember() || ! $this->tenant_id) {
            return $this->cachedPermissions = [];
        }

        // Scope via model_has_roles.tenant_id (team context) only — not roles.tenant_id,
        // because Spatie may assign a global role (tenant_id=NULL) in a tenant's context.
        $this->cachedPermissions = \DB::table('permissions as p')
            ->join('role_has_permissions as rhp', 'rhp.permission_id', '=', 'p.id')
            ->join('roles as r', 'r.id', '=', 'rhp.role_id')
            ->join('model_has_roles as mhr', function ($join) {
                $join->on('mhr.role_id', '=', 'r.id')
                     ->where('mhr.model_type', '=', static::class)
                     ->where('mhr.model_id', '=', $this->id)
                     ->where('mhr.tenant_id', '=', $this->tenant_id);
            })
            ->distinct()
            ->pluck('p.name')
            ->toArray();

        return $this->cachedPermissions;
    }

    /**
     * Check if user has any of the given permissions (pipe-separated OR logic).
     * Owners and super-admins always pass. Staff are checked via direct DB query
     * to avoid relying on Spatie global team state.
     */
    public function canAccess(string $permission): bool
    {
        if ($this->isSuperAdmin() || $this->isGymOwner()) {
            return true;
        }

        $userPerms = $this->loadDirectPermissions();

        foreach (explode('|', $permission) as $p) {
            if (in_array(trim($p), $userPerms, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Effective branch for data scoping.
     * Owners get the session-selected branch; staff are locked to their branch_id.
     */
    public function effectiveBranchId(): ?int
    {
        if ($this->isGymOwner() || $this->isSuperAdmin()) {
            return session('gymos_selected_branch_id');
        }

        return $this->branch_id;
    }
}
