<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class BookingCancelled implements ShouldBroadcastNow
{
    use BroadcastsBookingSummary;

    public function broadcastAs(): string
    {
        return 'booking.cancelled';
    }
}
