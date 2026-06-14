<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendBookingLifecycleEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $bookingId, public string $eventName)
    {
    }

    public function handle(): void
    {
        $booking = Booking::with('room')->find($this->bookingId);

        if (! $booking || ! $booking->contact_email) {
            return;
        }

        if ($this->eventName === 'BookingConfirmed') {
            if (!$booking->review_token) {
                $booking->generateReviewToken();
                $booking->refresh();
            }
            $subject = 'Your Villa Estella booking is confirmed';
            $body = "Hi {$booking->contact_name},\n\nYour booking #{$booking->id} for {$booking->room?->name} is confirmed. We look forward to welcoming you.\n\nOnce your stay is complete, you can leave a review here: " . route('reviews.submit-via-token', $booking->review_token);
        } else {
            $subject = 'Villa Estella reservation received';
            $body = "Hi {$booking->contact_name},\n\nReservation #{$booking->id} has been received and is pending payment verification.";
        }

        Mail::raw($body, function ($message) use ($booking, $subject) {
            $message->to($booking->contact_email, $booking->contact_name)
                ->subject($subject);
        });
    }
}
