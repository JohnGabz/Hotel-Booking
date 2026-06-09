<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public int $bookingId, public string $roomName, public string $guestName, public bool $isAdminAction = false)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $type = $this->isAdminAction ? 'Walk-in' : 'Online';
        return [
            'booking_id' => $this->bookingId,
            'message' => "New {$type} booking #{$this->bookingId} created by {$this->guestName} for {$this->roomName}.",
            'action_url' => route('admin.bookings') . '?room=all',
        ];
    }
}
