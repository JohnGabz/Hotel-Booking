<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class BookingStatusChanged implements ShouldBroadcastNow
{
    use BroadcastsBookingSummary;

    public function broadcastAs(): string
    {
        return 'booking.status.changed';
    }
}
