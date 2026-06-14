<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use App\Models\Booking;
use App\Models\Review;
use App\Notifications\ReviewSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReviewTokenTest extends TestCase
{
    use RefreshDatabase;

    protected Room $room;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->room = Room::create([
            'name' => 'Garden Room',
            'slug' => 'garden-room',
            'description' => 'A cozy room next to the gardens.',
            'capacity' => 2,
            'price' => 2500,
            'status' => 'available',
            'amenities' => ['wifi'],
            'images' => ['https://example.com/garden.jpg'],
        ]);
    }

    public function test_cannot_access_review_form_with_invalid_token(): void
    {
        $response = $this->get('/reviews/submit-via-token/invalid-token-123');
        $response->assertStatus(403);
        $response->assertSee('This review link is invalid');
    }

    public function test_cannot_access_review_form_before_stay_is_completed(): void
    {
        $booking = Booking::create([
            'room_id' => $this->room->id,
            'user_id' => null,
            'check_in' => now()->addDays(2)->toDateString(),
            'check_out' => now()->addDays(5)->toDateString(),
            'guests' => 1,
            'total' => 7500,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'contact_name' => 'John Doe',
            'contact_phone' => '1234567890',
        ]);

        $token = $booking->generateReviewToken();

        $response = $this->get("/reviews/submit-via-token/{$token}");
        $response->assertStatus(403);
        $response->assertSee('This review link is invalid');
    }

    public function test_can_access_review_form_when_stay_completed(): void
    {
        $booking = Booking::create([
            'room_id' => $this->room->id,
            'user_id' => null,
            'check_in' => now()->subDays(5)->toDateString(),
            'check_out' => now()->subDays(2)->toDateString(),
            'guests' => 1,
            'total' => 7500,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'contact_name' => 'John Doe',
            'contact_phone' => '1234567890',
        ]);

        $token = $booking->generateReviewToken();

        $response = $this->get("/reviews/submit-via-token/{$token}");
        $response->assertOk();
        $response->assertViewIs('pages.reviews.submit-via-token');
        $response->assertSee('Garden Room');
    }

    public function test_can_submit_review_with_valid_token_and_consumes_it(): void
    {
        Notification::fake();

        $booking = Booking::create([
            'room_id' => $this->room->id,
            'user_id' => null,
            'check_in' => now()->subDays(5)->toDateString(),
            'check_out' => now()->subDays(2)->toDateString(),
            'guests' => 1,
            'total' => 7500,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'contact_name' => 'John Doe',
            'contact_phone' => '1234567890',
        ]);

        $token = $booking->generateReviewToken();

        $response = $this->post("/reviews/submit-via-token/{$token}", [
            'rating' => 5,
            'comment' => 'This is a fantastic place. Highly recommended stay!',
        ]);

        $response->assertOk();
        $response->assertSee('Review Submitted');

        // Verify Review created
        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'rating' => 5,
            'comment' => 'This is a fantastic place. Highly recommended stay!',
            'approved' => false,
            'user_id' => null, // walk-in guest has no user_id
        ]);

        // Verify Token is consumed
        $booking->refresh();
        $this->assertNotNull($booking->review_token_used_at);

        // Verify Notification is sent to admin
        $admin = User::factory()->create(['is_admin' => true]);
        Notification::assertSentTo($admin, ReviewSubmittedNotification::class);

        // Try submitting again, should fail
        $response2 = $this->post("/reviews/submit-via-token/{$token}", [
            'rating' => 4,
            'comment' => 'Duplicate attempt should be blocked.',
        ]);
        $response2->assertStatus(403);
    }

    public function test_cannot_review_twice_even_if_token_not_marked_used(): void
    {
        $booking = Booking::create([
            'room_id' => $this->room->id,
            'user_id' => null,
            'check_in' => now()->subDays(5)->toDateString(),
            'check_out' => now()->subDays(2)->toDateString(),
            'guests' => 1,
            'total' => 7500,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'contact_name' => 'John Doe',
            'contact_phone' => '1234567890',
        ]);

        $token = $booking->generateReviewToken();

        // Manually insert a review for this booking
        Review::create([
            'booking_id' => $booking->id,
            'room_id' => $booking->room_id,
            'user_id' => null,
            'rating' => 5,
            'comment' => 'Pre-existing review.',
            'approved' => false,
        ]);

        $response = $this->get("/reviews/submit-via-token/{$token}");
        $response->assertStatus(400);
        $response->assertSee('You&#039;ve already reviewed this stay');

        $response2 = $this->post("/reviews/submit-via-token/{$token}", [
            'rating' => 4,
            'comment' => 'Trying to submit duplicate.',
        ]);
        $response2->assertStatus(400);
    }
}
