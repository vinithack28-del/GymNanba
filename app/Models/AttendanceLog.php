<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    public $timestamps = false;

    public const METHODS = ['manual', 'qr', 'biometric'];

    protected $fillable = [
        'tenant_id',
        'member_id',
        'branch_id',
        'method',
        'checked_in_at',
        'checked_out_at',
        'is_auto_checkout',
        'reason',
        'checked_in_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at'    => 'datetime',
            'checked_out_at'   => 'datetime',
            'is_auto_checkout' => 'boolean',
            'created_at'       => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'checked_in_by');
    }

    public function getDurationAttribute(): ?string
    {
        if (! $this->checked_out_at) {
            return null;
        }

        $minutes = (int) $this->checked_in_at->diffInMinutes($this->checked_out_at);

        if ($minutes < 60) {
            return "{$minutes}m";
        }

        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return $m > 0 ? "{$h}h {$m}m" : "{$h}h";
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('checked_in_at', $date);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
