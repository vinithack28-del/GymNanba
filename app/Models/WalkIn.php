<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalkIn extends Model
{
    public $timestamps = false;

    public const PURPOSES = ['day_pass', 'free_trial', 'inquiry', 'guest'];
    public const METHODS  = ['cash', 'upi', 'card'];

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'phone',
        'purpose',
        'fee_paise',
        'payment_method',
        'reference',
        'notes',
        'guest_of_id',
        'logged_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'fee_paise'  => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function guestOf(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'guest_of_id');
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'logged_by');
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
