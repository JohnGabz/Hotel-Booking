<?php

namespace Tests\Feature;

use App\Models\Room;
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
        Room::create([
            'name' => 'Ocean Suite',
            'slug' => 'ocean-suite',
            'description' => 'Spacious room with ocean view.',
            'capacity' => 2,
            'price' => 4500,
            'status' => 'available',
            'amenities' => ['wifi', 'pool'],
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
