<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSplit extends Model
{
    public $timestamps = false;

    protected $fillable = ['payment_id', 'method', 'amount_paise', 'reference'];

    protected function casts(): array
    {
        return ['amount_paise' => 'integer'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}

