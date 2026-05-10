<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[\Illuminate\Database\Eloquent\Attributes\Fillable(['user_id', 'room_id', 'check_in', 'check_out', 'guests', 'contact_name', 'contact_email', 'contact_phone', 'status', 'payment_method', 'payment_reference', 'payment_proof_path', 'payment_status', 'paid_at', 'total', 'notes'])]
class Booking extends Model
{
    use HasFactory;

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'paid_at' => 'datetime',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
