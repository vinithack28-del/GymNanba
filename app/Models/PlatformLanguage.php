<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformLanguage extends Model
{
    use HasFactory;

    protected $primaryKey = 'locale_code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'locale_code',
        'display_name',
        'is_active',
        'completeness_pct',
        'is_rtl',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_rtl' => 'boolean',
        ];
    }
}

