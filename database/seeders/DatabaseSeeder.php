<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Villa Admin',
            'email' => 'admin@villaestella.test',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Guest User',
            'email' => 'guest@villaestella.test',
            'password' => Hash::make('password'),
        ]);

        Room::create([
            'name' => 'Sunset Suite',
            'slug' => 'sunset-suite',
            'description' => 'Spacious suite with panoramic views, private terrace, and luxurious bedding.',
            'capacity' => 4,
            'price' => 7200.00,
            'status' => 'available',
            'amenities' => ['Wi-Fi', 'King bed', 'Private terrace', 'Mini bar'],
        ]);

        Room::create([
            'name' => 'Garden Villa',
            'slug' => 'garden-villa',
            'description' => 'Private villa surrounded by lush gardens, perfect for families and groups.',
            'capacity' => 5,
            'price' => 8600.00,
            'status' => 'available',
            'amenities' => ['Garden view', 'Jacuzzi', 'Breakfast included', 'Air conditioning'],
        ]);

        Room::create([
            'name' => 'Romantic Hideaway',
            'slug' => 'romantic-hideaway',
            'description' => 'Cozy hideaway ideal for couples, featuring a private pool and sunset balcony.',
            'capacity' => 2,
            'price' => 5400.00,
            'status' => 'available',
            'amenities' => ['Private pool', 'Balcony', 'Couples massage add-on', 'Complimentary wine'],
        ]);
    }
}
