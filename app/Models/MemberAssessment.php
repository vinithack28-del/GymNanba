<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberAssessment extends Model
{
    public const TYPE_PARQ = 'parq';
    public const TYPE_NUTRITION = 'nutrition';
    public const TYPE_BODY_METRICS = 'body_metrics';
    public const TYPE_POSTURE = 'posture';
    public const TYPE_BALANCE = 'balance';
    public const TYPE_VITALS = 'vitals';
    public const TYPE_FITNESS_CARDIO = 'fitness_cardio';
    public const TYPE_FITNESS_STRENGTH = 'fitness_strength';
    public const TYPE_FITNESS_ENDURANCE = 'fitness_endurance';
    public const TYPE_FITNESS_FLEXIBILITY = 'fitness_flexibility';

    public const TYPES = [
        self::TYPE_PARQ,
        self::TYPE_NUTRITION,
        self::TYPE_BODY_METRICS,
        self::TYPE_POSTURE,
        self::TYPE_BALANCE,
        self::TYPE_VITALS,
        self::TYPE_FITNESS_CARDIO,
        self::TYPE_FITNESS_STRENGTH,
        self::TYPE_FITNESS_ENDURANCE,
        self::TYPE_FITNESS_FLEXIBILITY,
    ];

    protected $fillable = [
        'tenant_id',
        'member_id',
        'branch_id',
        'type',
        'title',
        'status',
        'assessment_date',
        'next_assessment_date',
        'payload',
        'ai_insight',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'assessment_date' => 'date',
            'next_assessment_date' => 'date',
            'payload' => 'array',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
