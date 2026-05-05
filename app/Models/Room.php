<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[\Illuminate\Database\Eloquent\Attributes\Fillable(['name', 'slug', 'description', 'capacity', 'price', 'status', 'amenities'])]
class Room extends Model
{
    use HasFactory;

    protected $casts = [
        'amenities' => 'array',
        'price' => 'decimal:2',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('approved', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
