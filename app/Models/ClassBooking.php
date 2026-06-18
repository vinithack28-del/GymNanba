<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassBooking extends Model
{
    public const STATUSES = ['booked', 'waitlisted', 'cancelled', 'attended', 'absent', 'late_cancel'];

    protected $fillable = [
        'class_id', 'member_id', 'tenant_id', 'status', 'waitlist_pos', 'booked_by',
    ];

    protected function casts(): array
    {
        return [
            'waitlist_pos' => 'integer',
        ];
    }

    public function gymClass(): BelongsTo
    {
        return $this->belongsTo(GymClass::class, 'class_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function bookedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'booked_by');
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['booked', 'waitlisted']);
    }
}
