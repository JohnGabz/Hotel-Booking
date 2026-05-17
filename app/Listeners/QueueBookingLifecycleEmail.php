<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Jobs\SendBookingLifecycleEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueBookingLifecycleEmail implements ShouldQueue
{
    public function handle(BookingCreated|BookingConfirmed $event): void
    {
        SendBookingLifecycleEmail::dispatch($event->bookingId, class_basename($event));
    }
}
