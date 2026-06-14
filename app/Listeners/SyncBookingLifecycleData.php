<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Events\PaymentVerified;
use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncBookingLifecycleData implements ShouldQueue
{
    public function handle(BookingCreated|PaymentVerified|BookingConfirmed $event): void
    {
        $booking = Booking::with(['room', 'user'])->find($event->bookingId);

        if (! $booking) {
            return;
        }

        if ($event instanceof BookingConfirmed && $booking->review_token === null) {
            $booking->generateReviewToken();
        }

        Cache::forget('rooms.available');
        Cache::forget('room.' . $booking->room_id . '.availability');

        Log::info('Booking lifecycle synced', [
            'event' => class_basename($event),
            'booking_id' => $booking->id,
            'room_id' => $booking->room_id,
            'status' => $booking->status,
            'payment_status' => $booking->payment_status,
        ]);
    }
}
