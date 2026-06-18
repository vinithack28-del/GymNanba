<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffRolePermission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'role',
        'permissions',
        'updated_by',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'updated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
