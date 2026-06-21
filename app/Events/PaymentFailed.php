<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PaymentFailed implements ShouldBroadcastNow
{
    use BroadcastsBookingSummary;

    public function broadcastAs(): string
    {
        return 'payment.failed';
    }
}
