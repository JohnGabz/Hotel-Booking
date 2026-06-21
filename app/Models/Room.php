<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable(['name', 'slug', 'description', 'capacity', 'price', 'status', 'amenities', 'images'])]
class Room extends Model
{
    use HasFactory;

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'price' => 'decimal:2',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function physicalRooms(): HasMany
    {
        return $this->hasMany(PhysicalRoom::class);
    }

    public function availablePhysicalRooms()
    {
        return $this->physicalRooms()->available();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('"approved" = true');
    }

    public function scopeAvailable($query)
    {
        return $query
            ->where('status', 'available')
            ->whereHas('physicalRooms', fn ($physicalRooms) => $physicalRooms->available());
    }

    public function availablePhysicalRoomFor(string $checkIn, string $checkOut, bool $lock = false): ?PhysicalRoom
    {
        if ($this->status !== 'available') {
            return null;
        }

        if (Booking::query()->overlapping($this->id, $checkIn, $checkOut)->whereNull('physical_room_id')->exists()) {
            return null;
        }

        $query = $this->availablePhysicalRooms()->orderBy('id');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get()->first(
            fn (PhysicalRoom $physicalRoom) => ! Booking::overlapsPhysicalRoom($physicalRoom->id, $checkIn, $checkOut)
        );
    }

    public function isAvailableFor(string $checkIn, string $checkOut): bool
    {
        return $this->availablePhysicalRoomFor($checkIn, $checkOut) !== null;
    }

    public function isAvailableForDates(string $checkIn, string $checkOut): bool
    {
        if ($this->physicalRooms()->exists()) {
            return $this->isAvailableFor($checkIn, $checkOut);
        }

        return $this->status === 'available' && ! Booking::overlaps($this->id, $checkIn, $checkOut);
    }

    public function getTypeLabelAttribute(): string
    {
        $name = strtolower($this->name);

        if (str_contains($name, 'single')) {
            return 'Single Bed Room';
        }

        if (str_contains($name, 'matrimonial') || str_contains($name, 'double')) {
            return 'Matrimonial Bed Room';
        }

        if (str_contains($name, 'twin')) {
            return 'Twin Bed Room';
        }

        return $this->name;
    }

    public function occupiedPhysicalRoomIdsForDate(string $date): Collection
    {
        return Booking::query()
            ->where('room_id', $this->id)
            ->whereIn('status', Booking::BLOCKING_STATUSES)
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date)
            ->pluck('physical_room_id')
            ->filter()
            ->unique()
            ->values();
    }

    public function availablePhysicalRoomCountForDate(string $date): int
    {
        if ($this->status !== 'available') {
            return 0;
        }

        if (Booking::query()
            ->where('room_id', $this->id)
            ->whereIn('status', Booking::BLOCKING_STATUSES)
            ->whereNull('physical_room_id')
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date)
            ->exists()) {
            return 0;
        }

        $occupiedIds = $this->occupiedPhysicalRoomIdsForDate($date);

        return $this->availablePhysicalRooms()
            ->whereNotIn('id', $occupiedIds)
            ->count();
    }

    public function availablePhysicalRoomCountForRange(string $checkIn, string $checkOut): int
    {
        if ($this->status !== 'available') {
            return 0;
        }

        if (Booking::query()->overlapping($this->id, $checkIn, $checkOut)->whereNull('physical_room_id')->exists()) {
            return 0;
        }

        return $this->availablePhysicalRooms()
            ->orderBy('id')
            ->get()
            ->filter(fn (PhysicalRoom $physicalRoom) => ! Booking::overlapsPhysicalRoom($physicalRoom->id, $checkIn, $checkOut))
            ->count();
    }
}
