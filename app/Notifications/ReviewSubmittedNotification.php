<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReviewSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public int $reviewId, public string $roomName, public string $guestName, public int $rating)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'review_id' => $this->reviewId,
            'message' => "New {$this->rating}-star review submitted by {$this->guestName} for {$this->roomName} is pending approval.",
            'action_url' => route('admin.dashboard'),
        ];
    }
}
