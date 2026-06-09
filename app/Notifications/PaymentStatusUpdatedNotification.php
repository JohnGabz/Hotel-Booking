<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(public int $bookingId, public string $roomName, public string $status, public string $paymentStatus)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusLabel = $this->paymentStatus === 'paid' ? 'verified' : $this->paymentStatus;
        return [
            'booking_id' => $this->bookingId,
            'message' => "Your payment status for reservation #{$this->bookingId} ({$this->roomName}) is {$statusLabel}.",
            'action_url' => route('dashboard'),
        ];
    }
}
