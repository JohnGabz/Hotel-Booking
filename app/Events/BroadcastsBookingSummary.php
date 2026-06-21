<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

trait BroadcastsBookingSummary
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $bookingId) {}

    public function broadcastWhen(): bool
    {
        if (config('broadcasting.default') !== 'pusher') {
            return true;
        }

        return filled(config('broadcasting.connections.pusher.key'))
            && filled(config('broadcasting.connections.pusher.secret'))
            && filled(config('broadcasting.connections.pusher.app_id'));
    }

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('admins')];
        $booking = Booking::query()->select('id', 'user_id')->find($this->bookingId);

        if ($booking?->user_id) {
            $channels[] = new PrivateChannel('users.'.$booking->user_id);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        $booking = Booking::with(['room:id,name', 'physicalRoom:id,name,code', 'user:id,name'])->find($this->bookingId);

        if (! $booking) {
            return ['booking_id' => $this->bookingId];
        }

        return [
            'booking_id' => $booking->id,
            'room_id' => $booking->room_id,
            'room_name' => $booking->room?->name,
            'guest_name' => $booking->contact_name ?: $booking->user?->name ?: 'Guest',
            'check_in' => $booking->check_in?->toDateString(),
            'check_out' => $booking->check_out?->toDateString(),
            'status' => $booking->status,
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment_method,
            'total' => (float) $booking->total,
            'physical_room' => $booking->physicalRoom
                ? trim($booking->physicalRoom->name.' ('.$booking->physicalRoom->code.')')
                : null,
            'source' => $booking->source,
            'updated_at' => optional($booking->updated_at)->toIso8601String(),
        ];
    }
}
