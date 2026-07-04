<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    public const CATEGORIES = [
        'rent'          => ['main_hall', 'studio', 'storage', 'office'],
        'utilities'     => ['electricity', 'water', 'internet', 'phone'],
        'salaries'      => ['full_time', 'part_time', 'contract', 'bonus'],
        'equipment'     => ['purchase', 'repair', 'maintenance', 'replacement'],
        'marketing'     => ['social_media', 'flyers', 'events', 'promotions'],
        'supplies'      => ['cleaning', 'consumables', 'stationery', 'toiletries'],
        'insurance'     => ['liability', 'equipment', 'health'],
        'software'      => ['gymos_subscription', 'other'],
        'miscellaneous' => [],
    ];

    public const METHODS = ['cash', 'upi', 'bank', 'cheque', 'card'];

    public const STATUSES = ['pending', 'approved', 'rejected'];

    public const RECURRENCE = ['daily', 'weekly', 'monthly', 'annual'];

    protected $fillable = [
        'tenant_id', 'branch_id', 'date', 'category', 'sub_category',
        'description', 'amount_paise', 'gst_paise', 'method', 'vendor',
        'reference', 'receipt_url', 'notes', 'status', 'rejection_reason',
        'is_recurring', 'recurrence_freq', 'recurrence_end',
        'staff_id', 'salary_month', 'created_by', 'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'date'           => 'date',
            'recurrence_end' => 'date',
            'is_recurring'   => 'boolean',
            'amount_paise'   => 'integer',
            'gst_paise'      => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}

