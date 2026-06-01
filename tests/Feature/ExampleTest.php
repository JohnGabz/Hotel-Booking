<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\PhysicalRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $room = Room::create([
            'name' => 'Ocean Suite',
            'slug' => 'ocean-suite',
            'description' => 'Spacious room with ocean view.',
            'capacity' => 2,
            'price' => 4500,
            'status' => 'available',
            'amenities' => ['wifi', 'pool'],
            'images' => ['https://example.com/room.jpg'],
        ]);

        PhysicalRoom::create([
            'room_id' => $room->id,
            'name' => 'Ocean Suite 1',
            'code' => 'ocean-suite-1',
            'status' => 'available',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
