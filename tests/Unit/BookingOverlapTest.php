<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_overlap_helper_blocks_pending_verification_and_confirmed_bookings(): void
    {
        $room = $this->room();

        Booking::create([
            'room_id' => $room->id,
            'check_in' => '2026-06-10',
            'check_out' => '2026-06-12',
            'guests' => 1,
            'contact_name' => 'Guest',
            'contact_email' => 'guest@example.com',
            'contact_phone' => '+639000000000',
            'status' => 'pending',
            'payment_method' => 'gcash',
            'payment_status' => 'pending',
            'total' => 1000,
        ]);

        $this->assertTrue(Booking::overlaps($room->id, '2026-06-11', '2026-06-13'));
        $this->assertFalse(Booking::overlaps($room->id, '2026-06-12', '2026-06-14'));
    }

    protected function room(): Room
    {
        return Room::create([
            'name' => 'Garden Villa',
            'slug' => 'garden-villa',
            'description' => 'A test room.',
            'capacity' => 2,
            'price' => 1000,
            'status' => 'available',
            'amenities' => ['Wi-Fi'],
            'images' => ['https://example.com/room.jpg'],
        ]);
    }
}
