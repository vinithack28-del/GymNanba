<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LockerAssignment extends Model
{
    protected $fillable = [
        'locker_id',
        'member_id',
        'tenant_id',
        'from_date',
        'to_date',
        'notes',
        'assigned_by',
        'released_by',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'released_at' => 'datetime',
        ];
    }

    public function locker(): BelongsTo
    {
        return $this->belongsTo(Locker::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_by');
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'released_by');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('released_at');
    }
}
