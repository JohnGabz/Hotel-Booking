<?php

namespace Tests\Feature;

use App\Events\BookingCreated;
use App\Models\Booking;
use App\Models\PaymentTransaction;
use App\Models\Room;
use App\Models\User;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_creation_sets_pending_online_source_and_blocks_overlapping_dates(): void
    {
        $room = $this->room();
        $guest = User::factory()->create();

        $payload = [
            'check_in' => now()->addDays(10)->toDateString(),
            'check_out' => now()->addDays(12)->toDateString(),
            'contact_name' => 'Maria Santos',
            'contact_email' => 'maria@example.com',
            'contact_phone' => '+639123456789',
            'payment_method' => 'gcash',
        ];

        $this->actingAs($guest)
            ->post(route('bookings.store', $room), $payload)
            ->assertRedirect(route('rooms.show', $room))
            ->assertSessionHas('success', 'Reservation received - pending payment verification.');

        $booking = Booking::firstOrFail();

        $this->assertSame('pending', $booking->status);
        $this->assertSame('pending', $booking->payment_status);
        $this->assertSame(Booking::SOURCE_ONLINE, $booking->source);
        $this->assertTrue(Booking::overlaps($room->id, $payload['check_in'], $payload['check_out']));

        $this->actingAs($guest)
            ->post(route('bookings.store', $room), $payload)
            ->assertSessionHasErrors('check_in');
    }

    public function test_public_booking_redirects_even_when_post_create_side_effect_fails(): void
    {
        Event::listen(BookingCreated::class, function (): void {
            throw new \RuntimeException('Simulated side effect failure.');
        });

        $room = $this->room();
        $guest = User::factory()->create();

        $this->actingAs($guest)
            ->post(route('bookings.store', $room), [
                'check_in' => now()->addDays(30)->toDateString(),
                'check_out' => now()->addDays(32)->toDateString(),
                'contact_name' => 'Maria Santos',
                'contact_email' => 'maria@example.com',
                'contact_phone' => '+639123456789',
                'payment_method' => 'gcash',
            ])
            ->assertRedirect(route('rooms.show', $room))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'room_id' => $room->id,
            'contact_email' => 'maria@example.com',
            'source' => Booking::SOURCE_ONLINE,
        ]);
    }

    public function test_payment_webhook_confirms_booking_and_is_idempotent(): void
    {
        Config::set('services.payment.webhook_secret', 'test-secret');

        $booking = $this->booking([
            'payment_reference' => 'xnd_invoice_123',
        ]);

        $payload = [
            'id' => 'evt_123',
            'external_id' => 'villa-estela-booking-' . $booking->id,
            'status' => 'PAID',
            'payment_method' => 'gcash',
        ];
        $this->postJson(route('webhooks.payments'), $payload, ['X-Callback-Token' => 'test-secret'])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->postJson(route('webhooks.payments'), $payload, ['X-Callback-Token' => 'test-secret'])
            ->assertOk()
            ->assertJson(['status' => 'duplicate']);

        $booking->refresh();

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertNotNull($booking->paid_at);
        $this->assertSame(1, WebhookEvent::count());
        $this->assertSame(1, PaymentTransaction::where('transaction_id', 'evt_123')->count());
    }

    public function test_manual_payment_proof_and_admin_verification_confirm_booking(): void
    {
        Storage::fake('public');

        $guest = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $booking = $this->booking(['user_id' => $guest->id]);

        $this->actingAs($guest)
            ->post(route('bookings.payment-proof', $booking), [
                'payment_reference' => 'manual_ref_123',
                'payment_proof' => UploadedFile::fake()->createWithContent(
                    'proof.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
                ),
            ])
            ->assertRedirect(route('dashboard'));

        $booking->refresh();

        $this->assertSame('for_verification', $booking->payment_status);
        Storage::disk('public')->assertExists($booking->payment_proof_path);

        $this->actingAs($admin)
            ->post(route('admin.bookings.payment-status', $booking), [
                'payment_status' => 'paid',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $booking->refresh();

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertNotNull($booking->paid_at);
        $this->assertDatabaseHas('payment_transactions', [
            'booking_id' => $booking->id,
            'transaction_id' => 'manual_ref_123',
            'status' => 'confirmed',
        ]);
    }

    protected function booking(array $overrides = []): Booking
    {
        $room = $overrides['room'] ?? $this->room();

        return Booking::create(array_merge([
            'user_id' => User::factory()->create()->id,
            'room_id' => $room->id,
            'check_in' => now()->addDays(5)->toDateString(),
            'check_out' => now()->addDays(7)->toDateString(),
            'guests' => 1,
            'contact_name' => 'Maria Santos',
            'contact_email' => 'maria@example.com',
            'contact_phone' => '+639123456789',
            'status' => 'pending',
            'payment_method' => 'gcash',
            'payment_status' => 'pending',
            'total' => 2500,
            'source' => Booking::SOURCE_ONLINE,
        ], $overrides));
    }

    protected function room(): Room
    {
        return Room::create([
            'name' => 'Garden Villa ' . fake()->unique()->numberBetween(100, 999),
            'slug' => 'garden-villa-' . fake()->unique()->numberBetween(100, 999),
            'description' => 'A test room.',
            'capacity' => 2,
            'price' => 2500,
            'status' => 'available',
            'amenities' => ['Wi-Fi'],
            'images' => ['https://example.com/room.jpg'],
        ]);
    }
}
