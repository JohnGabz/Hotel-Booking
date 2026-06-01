<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\PhysicalRoom;
use App\Models\SiteContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditModalFocusTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_edit_modal_shows_key_fields_and_preserves_hidden_content(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        SiteContent::setValue('hero_eyebrow', 'Original eyebrow');
        SiteContent::setValue('hero_title', 'Original title');
        SiteContent::setValue('hero_subtitle', 'Original subtitle');

        $this->actingAs($admin)
            ->get(route('admin.settings', ['tab' => 'landing']))
            ->assertOk()
            ->assertSee('name="hero_title"', false)
            ->assertSee('name="hero_subtitle"', false)
            ->assertDontSee('name="hero_eyebrow"', false);

        $this->actingAs($admin)
            ->postJson(route('admin.site-content.section.update', 'hero'), [
                'hero_title' => 'Sharper landing headline',
                'hero_subtitle' => 'A focused modal only saves the fields it renders.',
                'hero_button_text' => 'Book now',
            ])
            ->assertOk()
            ->assertJsonPath('section.values.hero_title', 'Sharper landing headline')
            ->assertJsonPath('section.values.hero_eyebrow', 'Original eyebrow');

        $this->assertSame('Original eyebrow', SiteContent::valueFor('hero_eyebrow'));
        $this->assertSame('Sharper landing headline', SiteContent::valueFor('hero_title'));
    }

    public function test_landing_modal_validation_returns_inline_json_errors(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->postJson(route('admin.site-content.section.update', 'location'), [
                'location_title' => 'Find us',
                'location_intro' => 'Arrival details for guests.',
                'location_address_line1' => 'Tagaytay',
                'location_phone' => '+63 912 345 6789',
                'location_email' => 'not-an-email',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('location_email');
    }

    public function test_room_edit_modal_hides_gallery_controls_and_preserves_existing_images(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $room = $this->room();
        $physicalRoom = $room->physicalRooms()->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.rooms'))
            ->assertOk()
            ->assertDontSee('id="edit_room_images"', false)
            ->assertDontSee('name="retained_images[]"', false);

        $this->actingAs($admin)
            ->put(route('admin.rooms.update', $room), [
                'name' => 'Garden Villa Deluxe',
                'description' => 'Updated public description.',
                'capacity' => 3,
                'price' => 3200,
                'status' => 'available',
                'physical_rooms' => $physicalRoom->id . ' | Garden Villa 1 | available',
                'amenities' => 'Wi-Fi, Breakfast',
            ])
            ->assertRedirect(route('admin.rooms'));

        $room->refresh();

        $this->assertSame(['https://example.com/room.jpg'], $room->images);
        $this->assertSame('Garden Villa Deluxe', $room->name);
    }

    protected function room(): Room
    {
        $room = Room::create([
            'name' => 'Garden Villa',
            'slug' => 'garden-villa',
            'description' => 'A test room.',
            'capacity' => 2,
            'price' => 2500,
            'status' => 'available',
            'amenities' => ['Wi-Fi'],
            'images' => ['https://example.com/room.jpg'],
        ]);

        PhysicalRoom::create([
            'room_id' => $room->id,
            'name' => 'Garden Villa 1',
            'code' => 'garden-villa-1',
            'status' => 'available',
        ]);

        return $room->fresh();
    }
}
