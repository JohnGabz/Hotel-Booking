<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[\Illuminate\Database\Eloquent\Attributes\Fillable(['user_id', 'room_id', 'physical_room_id', 'check_in', 'check_out', 'guests', 'contact_name', 'contact_email', 'contact_phone', 'status', 'payment_method', 'payment_reference', 'payment_proof_path', 'payment_status', 'paid_at', 'total', 'notes', 'source', 'review_token', 'review_token_used_at'])]
class Booking extends Model
{
    use HasFactory;

    public const BLOCKING_STATUSES = ['pending', 'for_verification', 'confirmed'];
    public const SOURCE_ONLINE = 'online';
    public const SOURCE_WALK_IN = 'walk_in';

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'paid_at' => 'datetime',
        'total' => 'decimal:2',
        'source' => 'string',
        'review_token_used_at' => 'datetime',
    ];

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            self::SOURCE_WALK_IN => 'Walk-in',
            default => 'Online',
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function physicalRoom(): BelongsTo
    {
        return $this->belongsTo(PhysicalRoom::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function webhookEvents(): HasMany
    {
        return $this->hasMany(WebhookEvent::class);
    }

    public function scopeOverlapping($query, int $roomId, string $checkIn, string $checkOut, array $statuses = self::BLOCKING_STATUSES)
    {
        return $query
            ->where('room_id', $roomId)
            ->whereIn('status', $statuses)
            ->whereDate('check_in', '<', $checkOut)
            ->whereDate('check_out', '>', $checkIn);
    }

    public static function overlaps(int $roomId, string $checkIn, string $checkOut, array $statuses = self::BLOCKING_STATUSES): bool
    {
        return static::query()->overlapping($roomId, $checkIn, $checkOut, $statuses)->exists();
    }

    public function scopeOverlappingPhysicalRoom($query, int $physicalRoomId, string $checkIn, string $checkOut, array $statuses = self::BLOCKING_STATUSES)
    {
        return $query
            ->where('physical_room_id', $physicalRoomId)
            ->whereIn('status', $statuses)
            ->whereDate('check_in', '<', $checkOut)
            ->whereDate('check_out', '>', $checkIn);
    }

    public static function overlapsPhysicalRoom(int $physicalRoomId, string $checkIn, string $checkOut, array $statuses = self::BLOCKING_STATUSES): bool
    {
        return static::query()->overlappingPhysicalRoom($physicalRoomId, $checkIn, $checkOut, $statuses)->exists();
    }

    public function confirmPayment(?string $reference = null, ?string $method = null, ?string $provider = null, ?array $payload = null): void
    {
        $token = $this->review_token ?: \Illuminate\Support\Str::random(48);

        $this->forceFill([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_reference' => $reference ?: $this->payment_reference,
            'payment_method' => $method ?: $this->payment_method,
            'paid_at' => $this->paid_at ?: now(),
            'review_token' => $token,
        ])->save();

        PaymentTransaction::updateOrCreate(
            [
                'booking_id' => $this->id,
                'transaction_id' => $reference ?: $this->payment_reference ?: 'booking-' . $this->id,
            ],
            [
                'provider' => $provider ?: 'manual',
                'amount' => $this->total,
                'status' => 'confirmed',
                'payment_method' => $method ?: $this->payment_method,
                'payload' => $payload,
                'processed_at' => $this->paid_at ?: now(),
            ]
        );
    }

    public function getTransactionIdAttribute(): string
    {
        return $this->payment_reference ?: 'BOOK-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getReportPaymentStatusAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'confirmed',
            'for_verification' => 'pending',
            default => $this->payment_status ?: 'pending',
        };
    }

    public function getTransactionDateAttribute()
    {
        return $this->paid_at ?: $this->updated_at ?: $this->created_at;
    }

    public function generateReviewToken(): string
    {
        $token = \Illuminate\Support\Str::random(48);
        $this->review_token = $token;
        $this->save();
        return $token;
    }

    public function reviewTokenIsValid(): bool
    {
        return !empty($this->review_token)
            && $this->review_token_used_at === null
            && $this->status === 'confirmed'
            && $this->check_out->isPast();
    }
}
