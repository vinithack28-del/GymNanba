<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionModule extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'slug',
        'name',
        'icon',
        'sort_order',
    ];
}
