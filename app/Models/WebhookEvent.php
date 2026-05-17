<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['provider', 'event_id', 'idempotency_key', 'event_type', 'booking_id', 'payload', 'processed_at'])]
class WebhookEvent extends Model
{
    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
