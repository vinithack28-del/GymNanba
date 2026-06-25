<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'billing_cycle',
        'price_paise',
        'max_members',
        'max_branches',
        'max_staff_accounts',
        'feature_flags',
        'trial_eligible',
        'is_trial',
        'trial_days',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'feature_flags' => 'array',
            'trial_eligible' => 'boolean',
            'is_trial'       => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
