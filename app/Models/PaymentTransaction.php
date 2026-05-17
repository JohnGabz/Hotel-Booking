<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['booking_id', 'transaction_id', 'provider', 'amount', 'status', 'payment_method', 'payload', 'processed_at'])]
class PaymentTransaction extends Model
{
    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
