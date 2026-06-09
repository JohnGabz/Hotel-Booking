<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentProofUploadedNotification extends Notification
{
    use Queueable;

    public function __construct(public int $bookingId, public string $guestName, public string $referenceNumber)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->bookingId,
            'message' => "Payment proof submitted for booking #{$this->bookingId} by {$this->guestName} (Ref: {$this->referenceNumber}).",
            'action_url' => route('admin.bookings') . '?status=pending',
        ];
    }
}
