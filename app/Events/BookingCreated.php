<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class BookingCreated implements ShouldBroadcastNow
{
    use BroadcastsBookingSummary;

    public function broadcastAs(): string
    {
        return 'booking.created';
    }
}
