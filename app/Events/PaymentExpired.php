<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PaymentExpired implements ShouldBroadcastNow
{
    use BroadcastsBookingSummary;

    public function broadcastAs(): string
    {
        return 'payment.expired';
    }
}
