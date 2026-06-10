<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\PhysicalRoom;
use App\Models\User;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class HotelEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup test route for PostTooLargeException
        Route::any('/test-post-too-large', function () {
            throw new \Illuminate\Http\Exceptions\PostTooLargeException();
        });
    }

    /**
     * 1. User Registration Enhancement Tests
     */
    public function test_user_registration_requires_valid_contact_number(): void
    {
        // Missing contact_number should fail
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors('contact_number');

        // Invalid format should fail
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'contact_number' => 'abc-invalid',
        ]);
        $response->assertSessionHasErrors('contact_number');

        // Valid contact_number should succeed
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'contact_number' => '+639123456789',
        ]);

        $response->assertRedirect('/email/verify');
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'contact_number' => '+639123456789',
        ]);
    }

    /**
     * 2. Multiple Physical Rooms & Availability Tests
     */
    public function test_booking_availability_respects_physical_rooms(): void
    {
        $room = Room::create([
            'name' => 'Ocean View Suite',
            'slug' => 'ocean-view-suite',
            'description' => 'A beautiful ocean view room.',
            'capacity' => 2,
            'price' => 5000,
            'status' => 'available',
            'amenities' => ['wifi'],
            'images' => ['https://example.com/image.jpg'],
        ]);

        // Create 2 physical rooms
        $pRoom1 = PhysicalRoom::create([
            'room_id' => $room->id,
            'name' => 'Room 101',
            'code' => 'room-101',
            'status' => 'available',
        ]);

        $pRoom2 = PhysicalRoom::create([
            'room_id' => $room->id,
            'name' => 'Room 102',
            'code' => 'room-102',
            'status' => 'available',
        ]);

        $checkIn = now()->addDays(2)->toDateString();
        $checkOut = now()->addDays(4)->toDateString();

        // Check availability initially (should be available since we have 2 rooms)
        $this->assertTrue(Booking::isAvailableFor($room->id, $checkIn, $checkOut));

        // Create 1 booking (occupying pRoom1)
        Booking::create([
            'room_id' => $room->id,
            'physical_room_id' => $pRoom1->id,
            'user_id' => null,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => 1,
            'total' => 10000,
            'status' => 'confirmed',
            'payment_status' => 'confirmed',
            'contact_name' => 'Maria',
            'contact_phone' => '1234567',
        ]);

        // Should still be available (pRoom2 is free)
        $this->assertTrue(Booking::isAvailableFor($room->id, $checkIn, $checkOut));

        // Create 2nd booking (occupying pRoom2)
        Booking::create([
            'room_id' => $room->id,
            'physical_room_id' => $pRoom2->id,
            'user_id' => null,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => 1,
            'total' => 10000,
            'status' => 'confirmed',
            'payment_status' => 'confirmed',
            'contact_name' => 'Jose',
            'contact_phone' => '7654321',
        ]);

        // Both physical rooms booked, should not be available
        $this->assertFalse(Booking::isAvailableFor($room->id, $checkIn, $checkOut));
    }

    /**
     * 3. Sync Physical Rooms Inline Table format
     */
    public function test_sync_physical_rooms_from_raw_string(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $room = Room::create([
            'name' => 'Garden Villa',
            'slug' => 'garden-villa',
            'description' => 'Beautiful villa.',
            'capacity' => 4,
            'price' => 7000,
            'status' => 'available',
            'amenities' => ['wifi'],
            'images' => ['https://example.com/image.jpg'],
        ]);

        // Sync new physical rooms via store/update format
        $this->actingAs($admin)
            ->put("/admin/rooms/{$room->id}", [
                'name' => 'Garden Villa Updated',
                'description' => 'Beautiful villa.',
                'capacity' => 4,
                'price' => 7000,
                'status' => 'available',
                'physical_rooms' => "Room A1 | available\nRoom A2 | maintenance",
                'retained_images' => ['https://example.com/image.jpg'],
            ]);

        $this->assertDatabaseHas('physical_rooms', [
            'room_id' => $room->id,
            'name' => 'Room A1',
            'status' => 'available',
        ]);

        $this->assertDatabaseHas('physical_rooms', [
            'room_id' => $room->id,
            'name' => 'Room A2',
            'status' => 'maintenance',
        ]);
    }

    /**
     * 4. Dynamic Database-Backed Notifications tests
     */
    public function test_notifications_synced_across_lifecycle_events(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $guest = User::factory()->create();
        $room = Room::create([
            'name' => 'Studio Room',
            'slug' => 'studio-room',
            'description' => 'Compact studio room.',
            'capacity' => 2,
            'price' => 3000,
            'status' => 'available',
            'amenities' => ['wifi'],
            'images' => ['https://example.com/image.jpg'],
        ]);

        // Clear existing notifications
        \Illuminate\Support\Facades\DB::table('notifications')->truncate();

        // 1. Create a booking -> notify admin
        $this->actingAs($guest)
            ->post("/rooms/{$room->slug}/book", [
                'check_in' => now()->addDays(5)->toDateString(),
                'check_out' => now()->addDays(7)->toDateString(),
                'contact_name' => 'Maria',
                'contact_email' => 'maria@example.com',
                'contact_phone' => '0912345678',
                'payment_method' => 'gcash',
            ]);

        $booking = Booking::firstOrFail();

        // Assert database notification exists for admin
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->id,
        ]);

        $notif = \Illuminate\Support\Facades\DB::table('notifications')->first();
        $data = json_decode($notif->data, true);
        $this->assertStringContainsString('New booking created', $data['message'] ?? '');

        // 2. Upload payment proof -> notify admin
        $this->actingAs($guest)
            ->post("/bookings/{$booking->id}/payment-proof", [
                'payment_proof_mode' => 'link',
                'payment_proof_link' => 'https://example.com/proof.jpg',
            ]);

        $this->assertDatabaseCount('notifications', 2);

        // 3. Admin updates payment status -> notify guest
        $this->actingAs($admin)
            ->post("/admin/bookings/{$booking->id}/payment-status", [
                'payment_status' => 'confirmed',
            ]);

        // Guest should be notified (3rd notification in table)
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $guest->id,
        ]);

        // 4. Test Notification API endpoints
        $this->actingAs($admin);
        
        // Unread endpoint
        $response = $this->get('/notifications/unread');
        $response->assertOk();
        $response->assertJsonStructure(['unread_count', 'notifications']);
        
        $unreadCount = $response->json('unread_count');
        $this->assertGreaterThan(0, $unreadCount);

        // Mark individual read
        $adminUnreadNotif = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('notifiable_id', $admin->id)
            ->whereNull('read_at')
            ->first();

        $this->post("/notifications/{$adminUnreadNotif->id}/read")->assertOk();

        $this->assertDatabaseHas('notifications', [
            'id' => $adminUnreadNotif->id,
        ]);
        $this->assertNotNull(\Illuminate\Support\Facades\DB::table('notifications')->find($adminUnreadNotif->id)->read_at);

        // Mark all read
        $this->post('/notifications/read-all')->assertOk();
        $this->assertEquals(0, \Illuminate\Support\Facades\DB::table('notifications')
            ->where('notifiable_id', $admin->id)
            ->whereNull('read_at')
            ->count()
        );
    }

    /**
     * 5. Paste URL Image Upload Toggles tests
     */
    public function test_image_input_modes_and_url_toggles(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Creating a room type using Paste URL mode
        $response = $this->actingAs($admin)
            ->post('/admin/rooms', [
                'name' => 'Royal Suite',
                'description' => 'Luxurious royal suite.',
                'capacity' => 2,
                'price' => 12000,
                'status' => 'available',
                'physical_room_count' => 1,
                'amenities' => 'wifi, pool',
                'image_links' => "https://images.unsplash.com/photo-1566073771259-6a8506099945\nhttps://images.unsplash.com/photo-1505693416388",
            ]);

        $response->assertRedirect(route('admin.rooms'));
        $response->assertSessionHas('success');

        $room = Room::where('name', 'Royal Suite')->firstOrFail();
        $this->assertCount(2, $room->images);
        $this->assertEquals('https://images.unsplash.com/photo-1566073771259-6a8506099945', $room->images[0]);
    }

    /**
     * 6. PostTooLargeException Graceful Handling
     */
    public function test_post_too_large_exception_handled_gracefully(): void
    {
        $response = $this->get('/test-post-too-large');
        
        $response->assertRedirect();
        $response->assertSessionHasErrors('images');
    }
}
