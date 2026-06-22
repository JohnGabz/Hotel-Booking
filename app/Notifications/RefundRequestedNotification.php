<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RefundRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(public int $bookingId, public string $roomName, public string $guestName)
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
            'message' => "Refund requested for {$this->roomName} (#{$this->bookingId}).",
            'action_url' => route('admin.bookings').'?status=confirmed',
        ];
    }
}
