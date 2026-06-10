<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['room_id', 'name', 'code', 'status'])]
class PhysicalRoom extends Model
{
    use HasFactory;

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function isAvailableForDates(string $checkIn, string $checkOut, ?int $excludeBookingId = null): bool
    {
        if ($this->status !== 'available') {
            return false;
        }

        $query = Booking::query()
            ->overlappingPhysicalRoom($this->id, $checkIn, $checkOut);

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return ! $query->exists();
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available';
    }
}
