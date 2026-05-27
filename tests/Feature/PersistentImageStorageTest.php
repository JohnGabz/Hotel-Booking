<?php

namespace Tests\Feature;

use App\Models\SiteContent;
use App\Models\User;
use App\Support\ImageStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PersistentImageStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_storage_uses_configured_upload_disk(): void
    {
        config(['filesystems.uploads_disk' => 'uploads']);
        Storage::fake('uploads');

        $path = ImageStorage::store($this->image(), 'site-content');

        Storage::disk('uploads')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_image_url_generation_supports_upload_disk_and_legacy_storage_paths(): void
    {
        config([
            'filesystems.uploads_disk' => 'uploads_url_test',
            'filesystems.disks.uploads_url_test' => [
                'driver' => 'local',
                'root' => storage_path('framework/testing/disks/uploads-url-test'),
                'url' => 'https://cdn.example.test/uploads',
                'visibility' => 'public',
                'throw' => false,
            ],
        ]);
        Storage::disk('uploads_url_test')->put('site-content/hero.png', 'image-bytes');

        $this->assertSame(
            'https://cdn.example.test/uploads/site-content/hero.png',
            ImageStorage::url('storage/site-content/hero.png')
        );
    }

    public function test_admin_can_update_landing_section_image_over_ajax(): void
    {
        config(['filesystems.uploads_disk' => 'uploads']);
        Storage::fake('uploads');

        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson(route('admin.site-content.section.update', 'hero'), [
            'hero_eyebrow' => 'Villa Test',
            'hero_title' => 'Persistent Hero',
            'hero_subtitle' => 'A durable upload test.',
            'hero_button_text' => 'Book Now',
            'hero_background_image_upload' => $this->image(),
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('section.title', 'Hero')
            ->assertJsonPath('section.preview', 'Villa Test · Persistent Hero · A durable upload test.');

        $path = SiteContent::valueFor('hero_background_image');

        Storage::disk('uploads')->assertExists($path);
        $this->assertStringStartsWith('site-content/', $path);
    }

    public function test_landing_settings_editor_renders_cards_and_prefilled_modals(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.settings', ['tab' => 'landing']));

        $response
            ->assertOk()
            ->assertSee('Edit the homepage sections from one tab.')
            ->assertSee('data-landing-section-card="hero"', false)
            ->assertSee('landing-section-modal-hero')
            ->assertSee('Experience Luxury Hospitality')
            ->assertSee('data-landing-section-submit disabled', false);
    }

    protected function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'proof.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        );
    }
}
