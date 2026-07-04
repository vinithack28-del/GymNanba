<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalkInFollowup extends Model
{
    public $timestamps = false;

    public const OUTCOMES = [
        'called',
        'visited',
        'messaged',
        'no_answer',
        'not_interested',
        'converted',
    ];

    protected $fillable = [
        'walk_in_id',
        'tenant_id',
        'outcome',
        'notes',
        'next_followup_date',
        'logged_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'next_followup_date' => 'date',
            'created_at'         => 'datetime',
        ];
    }

    public function walkIn(): BelongsTo
    {
        return $this->belongsTo(WalkIn::class);
    }

    public function loggedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}

