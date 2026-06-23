<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentServiceRecord extends Model
{
    public const TYPES = [
        'maintenance'  => 'Maintenance',
        'repair'       => 'Repair',
        'inspection'   => 'Inspection',
        'calibration'  => 'Calibration',
        'cleaning'     => 'Cleaning',
        'replacement'  => 'Replacement',
    ];

    public $timestamps = false;

    protected $fillable = [
        'equipment_id', 'tenant_id', 'service_date', 'service_type',
        'cost_paise', 'service_provider', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'created_at'   => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($m) => $m->created_at = now());
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
