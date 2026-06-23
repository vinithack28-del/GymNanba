<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WalkIn extends Model
{
    public $timestamps = false;

    public const PURPOSES         = ['day_pass', 'free_trial', 'inquiry', 'guest'];
    public const METHODS          = ['cash', 'upi', 'card'];
    public const ENQUIRY_STATUSES = ['open', 'followed_up', 'converted', 'closed'];

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'phone',
        'purpose',
        'fee_paise',
        'plan_id',
        'member_id',
        'payment_method',
        'payment_meta',
        'reference',
        'notes',
        'guest_of_id',
        'logged_by',
        'enquiry_status',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'fee_paise'  => 'integer',
            'payment_meta'=> 'array',
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

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(GymMembershipPlan::class, 'plan_id');
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'logged_by');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(WalkInFollowup::class)->orderByDesc('created_at');
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeEnquiries($query)
    {
        return $query->where('purpose', 'inquiry');
    }
}
