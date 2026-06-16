<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'admin_id',
        'amount_paise',
        'payment_method',
        'transaction_ref',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
