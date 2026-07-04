<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GymClass extends Model
{
    protected $table = 'classes';

    public const TYPES = [
        'yoga', 'hiit', 'zumba', 'strength', 'pilates', 'crossfit', 'aerobics', 'custom',
    ];

    public const STATUSES = ['scheduled', 'cancelled', 'completed'];

    protected $fillable = [
        'tenant_id', 'branch_id', 'name', 'type', 'room', 'trainer_id',
        'start_time', 'end_time', 'class_date', 'max_capacity',
        'allow_waitlist', 'visible', 'description', 'parent_id', 'status', 'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'class_date'    => 'date',
            'allow_waitlist'=> 'boolean',
            'visible'       => 'boolean',
            'max_capacity'  => 'integer',
        ];
    }

    // â”€â”€ Relationships â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'trainer_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(GymClass::class, 'parent_id');
    }

    public function seriesChildren(): HasMany
    {
        return $this->hasMany(GymClass::class, 'parent_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ClassBooking::class, 'class_id');
    }

    // â”€â”€ Computed accessors â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function getBookingCountAttribute(): int
    {
        return $this->bookings->whereIn('status', ['booked', 'attended', 'absent'])->count();
    }

    public function getWaitlistCountAttribute(): int
    {
        return $this->bookings->where('status', 'waitlisted')->count();
    }

    public function getAvailableSpotsAttribute(): int
    {
        return max(0, $this->max_capacity - $this->booking_count);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->available_spots === 0;
    }

    public function getDurationMinutesAttribute(): int
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end   = \Carbon\Carbon::parse($this->end_time);
        return (int) $start->diffInMinutes($end);
    }

    public function getStartMinutesAttribute(): int
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        return $start->hour * 60 + $start->minute;
    }

    // â”€â”€ Scopes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForWeek($query, string $weekStart, string $weekEnd)
    {
        return $query->whereBetween('class_date', [$weekStart, $weekEnd]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('class_date', '>=', now()->toDateString())
                     ->where('status', 'scheduled')
                     ->orderBy('class_date')
                     ->orderBy('start_time');
    }

    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    // â”€â”€ Series helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function seriesRootId(): ?int
    {
        return $this->parent_id ?? ($this->seriesChildren()->exists() ? $this->id : null);
    }
}

