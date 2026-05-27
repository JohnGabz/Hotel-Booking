<?php

namespace Tests\Feature;

use App\Events\BookingCreated;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_walkin_booking_with_payment_proof(): void
    {
        config(['filesystems.uploads_disk' => 'uploads']);
        Storage::fake('uploads');
        Event::fake([BookingCreated::class]);

        $admin = User::factory()->create(['is_admin' => true]);
        $room = $this->room();

        $response = $this->actingAs($admin)->postJson(route('admin.bookings.walkin'), [
            'room_id' => $room->id,
            'check_in' => now()->addDays(10)->toDateString(),
            'check_out' => now()->addDays(12)->toDateString(),
            'contact_name' => 'Walk In Guest',
            'contact_email' => 'walkin@example.com',
            'contact_phone' => '+639123456789',
            'guests' => 2,
            'payment_method' => 'gcash',
            'payment_proof' => $this->proofImage(),
            'notes' => 'Front desk booking.',
        ]);

        $response
            ->assertOk()
            ->assertJson(['success' => true]);

        $booking = Booking::firstOrFail();

        $this->assertNull($booking->user_id);
        $this->assertSame($room->id, $booking->room_id);
        $this->assertSame('pending', $booking->status);
        $this->assertSame('for_verification', $booking->payment_status);
        $this->assertSame(Booking::SOURCE_WALK_IN, $booking->source);
        $this->assertSame('Walk In Guest', $booking->contact_name);
        $this->assertSame('Front desk booking.', $booking->notes);
        Storage::disk('uploads')->assertExists($booking->payment_proof_path);
        Event::assertDispatched(BookingCreated::class, fn (BookingCreated $event) => $event->bookingId === $booking->id);
    }

    public function test_admin_walkin_confirmed_booking_is_marked_paid(): void
    {
        Event::fake([BookingCreated::class]);

        $admin = User::factory()->create(['is_admin' => true]);
        $room = $this->room();

        $this->actingAs($admin)->postJson(route('admin.bookings.walkin'), [
            'room_id' => $room->id,
            'check_in' => now()->addDays(15)->toDateString(),
            'check_out' => now()->addDays(16)->toDateString(),
            'contact_name' => 'Paid Guest',
            'contact_phone' => '+639123456789',
            'guests' => 1,
            'payment_method' => 'cash',
            'status' => 'confirmed',
        ])->assertOk();

        $booking = Booking::firstOrFail();

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame(Booking::SOURCE_WALK_IN, $booking->source);
        $this->assertNotNull($booking->paid_at);
        $this->assertDatabaseHas('payment_transactions', [
            'booking_id' => $booking->id,
            'transaction_id' => 'walkin-' . $booking->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_admin_walkin_booking_blocks_overlapping_dates(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $room = $this->room();

        Booking::create([
            'user_id' => null,
            'room_id' => $room->id,
            'check_in' => now()->addDays(20)->toDateString(),
            'check_out' => now()->addDays(22)->toDateString(),
            'guests' => 1,
            'contact_name' => 'Existing Guest',
            'contact_phone' => '+639123456789',
            'status' => 'pending',
            'payment_method' => 'gcash',
            'payment_status' => 'for_verification',
            'total' => 5000,
        ]);

        $this->actingAs($admin)->postJson(route('admin.bookings.walkin'), [
            'room_id' => $room->id,
            'check_in' => now()->addDays(21)->toDateString(),
            'check_out' => now()->addDays(23)->toDateString(),
            'contact_name' => 'Overlap Guest',
            'contact_phone' => '+639123456789',
            'guests' => 1,
            'payment_method' => 'cash',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('check_in');

        $this->assertSame(1, Booking::count());
    }

    protected function room(): Room
    {
        return Room::create([
            'name' => 'Garden Villa ' . fake()->unique()->numberBetween(100, 999),
            'slug' => 'garden-villa-' . fake()->unique()->numberBetween(100, 999),
            'description' => 'A test room.',
            'capacity' => 4,
            'price' => 2500,
            'status' => 'available',
            'amenities' => ['Wi-Fi'],
            'images' => ['https://example.com/room.jpg'],
        ]);
    }

    protected function proofImage(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'proof.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        );
    }
}
