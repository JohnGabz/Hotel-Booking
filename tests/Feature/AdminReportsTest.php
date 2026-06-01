<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\PhysicalRoom;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_reports_show_payment_transactions_and_exports(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
        $guest = User::factory()->create(['name' => 'Maria Santos']);
        $room = Room::create([
            'name' => 'Sunset Suite',
            'slug' => 'sunset-suite',
            'description' => 'A test room.',
            'capacity' => 2,
            'price' => 5000,
            'status' => 'available',
            'amenities' => ['Wi-Fi'],
            'images' => ['https://example.com/room.jpg'],
        ]);

        PhysicalRoom::create([
            'room_id' => $room->id,
            'name' => 'Sunset Suite 1',
            'code' => 'sunset-suite-1',
            'status' => 'available',
        ]);

        $room = $room->fresh();

        $booking = Booking::create([
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'check_in' => now()->addDay()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
            'guests' => 2,
            'contact_name' => 'Maria Santos',
            'contact_email' => 'maria@example.com',
            'contact_phone' => '+639123456789',
            'status' => 'confirmed',
            'payment_method' => 'gcash',
            'payment_reference' => 'xnd_test_invoice_123',
            'payment_status' => 'paid',
            'paid_at' => now(),
            'total' => 5000,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports', ['range' => 'all_time', 'search' => 'xnd_test_invoice_123']))
            ->assertOk()
            ->assertSee('Payment transactions')
            ->assertSee('xnd_test_invoice_123')
            ->assertSee('#' . $booking->id);

        $this->actingAs($admin)
            ->get(route('admin.reports.export', ['range' => 'all_time', 'format' => 'csv']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->actingAs($admin)
            ->get(route('admin.reports.export', ['range' => 'all_time', 'format' => 'pdf']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_amenities_route_is_removed(): void
    {
        $this->assertNull(app('router')->getRoutes()->getByName('admin.amenities'));
    }
}
