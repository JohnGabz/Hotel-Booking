<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Room;
use App\Models\User;
use App\Models\SiteContent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@villaestella.test'],
            [
                'name' => 'Villa Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $guests = collect([
            ['name' => 'Guest User', 'email' => 'guest@villaestella.test'],
            ['name' => 'Alyssa Cruz', 'email' => 'alyssa.cruz@villaestella.test'],
            ['name' => 'Marco Reyes', 'email' => 'marco.reyes@villaestella.test'],
            ['name' => 'Noel Dizon', 'email' => 'noel.dizon@villaestella.test'],
        ])->map(function (array $guest) {
            return User::query()->updateOrCreate(
                ['email' => $guest['email']],
                [
                    'name' => $guest['name'],
                    'password' => Hash::make('password'),
                    'is_admin' => false,
                    'email_verified_at' => now(),
                ]
            );
        });

        $rooms = collect([
            [
                'name' => 'Sunset Suite',
                'slug' => 'sunset-suite',
                'description' => 'Spacious suite with panoramic views, private terrace, and luxurious bedding.',
                'capacity' => 4,
                'price' => 7200.00,
                'status' => 'available',
                'amenities' => ['Wi-Fi', 'King bed', 'Private terrace', 'Mini bar'],
                'images' => [
                    'https://images.unsplash.com/photo-1631049307038-da0ec05d9339?w=800',
                    'https://images.unsplash.com/photo-1611432579715-cc6de042e470?w=800',
                    'https://images.unsplash.com/photo-1584622614875-2f8151013e50?w=800',
                    'https://images.unsplash.com/photo-1582719471384-894fbb16e074?w=800',
                ],
            ],
            [
                'name' => 'Garden Villa',
                'slug' => 'garden-villa',
                'description' => 'Private villa surrounded by lush gardens, perfect for families and groups.',
                'capacity' => 5,
                'price' => 8600.00,
                'status' => 'available',
                'amenities' => ['Garden view', 'Jacuzzi', 'Breakfast included', 'Air conditioning'],
                'images' => [
                    'https://images.unsplash.com/photo-1615886287537-b60b8e238616?w=800',
                    'https://images.unsplash.com/photo-1600121848334-fa11d2e6ecff?w=800',
                    'https://images.unsplash.com/photo-1564069114553-7db3f35b335d?w=800',
                    'https://images.unsplash.com/photo-1616394584738-fc6e612ce4d0?w=800',
                    'https://images.unsplash.com/photo-1613395877344-13d4a8e0d49e?w=800',
                ],
            ],
            [
                'name' => 'Romantic Hideaway',
                'slug' => 'romantic-hideaway',
                'description' => 'Cozy hideaway ideal for couples, featuring a private pool and sunset balcony.',
                'capacity' => 2,
                'price' => 5400.00,
                'status' => 'available',
                'amenities' => ['Private pool', 'Balcony', 'Couples massage add-on', 'Complimentary wine'],
                'images' => [
                    'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?w=800',
                    'https://images.unsplash.com/photo-1571508601166-72f95cb76be1?w=800',
                    'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800',
                    'https://images.unsplash.com/photo-1566995671694-98e992b83b42?w=800',
                ],
            ],
            [
                'name' => 'Executive Loft',
                'slug' => 'executive-loft',
                'description' => 'Minimal loft-style room tailored for business stays and quiet evenings.',
                'capacity' => 3,
                'price' => 6500.00,
                'status' => 'maintenance',
                'amenities' => ['Work desk', 'Smart TV', 'Coffee station', 'Blackout curtains'],
                'images' => [
                    'https://images.unsplash.com/photo-1576676081293-1a1ee719f46d?w=800',
                    'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800',
                    'https://images.unsplash.com/photo-1586288949529-03b39b1b253e?w=800',
                ],
            ],
            [
                'name' => 'Courtyard Family Room',
                'slug' => 'courtyard-family-room',
                'description' => 'Comfortable family room with direct courtyard access and extra bedding.',
                'capacity' => 6,
                'price' => 7800.00,
                'status' => 'available',
                'amenities' => ['Courtyard access', 'Two queen beds', 'Dining nook', 'Crib on request'],
                'images' => [
                    'https://images.unsplash.com/photo-1591474400929-caea1b339d51?w=800',
                    'https://images.unsplash.com/photo-1590381014100-e100d440a323?w=800',
                    'https://images.unsplash.com/photo-1562546285-c87d19399e7a?w=800',
                    'https://images.unsplash.com/photo-1609892612177-5f85cdcd30df?w=800',
                    'https://images.unsplash.com/photo-1617393866418-b2c8bcaecf4e?w=800',
                ],
            ],
        ])->map(function (array $room) {
            return Room::query()->updateOrCreate(
                ['slug' => $room['slug']],
                $room
            );
        });

        $bookings = [
            [
                'user' => $guests[0],
                'room' => $rooms[0],
                'check_in' => now()->addDays(2)->toDateString(),
                'check_out' => now()->addDays(5)->toDateString(),
                'guests' => 2,
                'status' => 'confirmed',
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'BT-2026-1001',
                'payment_status' => 'paid',
                'paid_at' => now()->subDay(),
                'total' => 21600.00,
                'notes' => 'Requesting early check-in if available.',
            ],
            [
                'user' => $guests[1],
                'room' => $rooms[1],
                'check_in' => now()->addDays(7)->toDateString(),
                'check_out' => now()->addDays(10)->toDateString(),
                'guests' => 4,
                'status' => 'confirmed',
                'payment_method' => 'gcash',
                'payment_reference' => 'GC-2026-1002',
                'payment_status' => 'for_verification',
                'paid_at' => null,
                'total' => 25800.00,
                'notes' => 'Family holiday booking.',
            ],
            [
                'user' => $guests[2],
                'room' => $rooms[2],
                'check_in' => now()->subDays(12)->toDateString(),
                'check_out' => now()->subDays(9)->toDateString(),
                'guests' => 2,
                'status' => 'completed',
                'payment_method' => 'credit_card',
                'payment_reference' => 'CC-2026-1003',
                'payment_status' => 'paid',
                'paid_at' => now()->subDays(13),
                'total' => 16200.00,
                'notes' => 'Anniversary stay.',
            ],
            [
                'user' => $guests[3],
                'room' => $rooms[3],
                'check_in' => now()->addDays(14)->toDateString(),
                'check_out' => now()->addDays(17)->toDateString(),
                'guests' => 3,
                'status' => 'pending',
                'payment_method' => 'bank_transfer',
                'payment_reference' => null,
                'payment_status' => 'pending',
                'paid_at' => null,
                'total' => 19500.00,
                'notes' => 'Awaiting payment proof.',
            ],
            [
                'user' => $guests[1],
                'room' => $rooms[4],
                'check_in' => now()->subDays(4)->toDateString(),
                'check_out' => now()->subDays(1)->toDateString(),
                'guests' => 5,
                'status' => 'cancelled',
                'payment_method' => 'cash',
                'payment_reference' => 'CS-2026-1004',
                'payment_status' => 'failed',
                'paid_at' => null,
                'total' => 23400.00,
                'notes' => 'Cancelled due to schedule change.',
            ],
        ];

        $createdBookings = [];

        foreach ($bookings as $index => $bookingData) {
            $booking = Booking::query()->updateOrCreate(
                [
                    'user_id' => $bookingData['user']->id,
                    'room_id' => $bookingData['room']->id,
                    'check_in' => $bookingData['check_in'],
                    'check_out' => $bookingData['check_out'],
                ],
                [
                    'guests' => $bookingData['guests'],
                    'status' => $bookingData['status'],
                    'payment_method' => $bookingData['payment_method'],
                    'payment_reference' => $bookingData['payment_reference'],
                    'payment_proof_path' => null,
                    'payment_status' => $bookingData['payment_status'],
                    'paid_at' => $bookingData['paid_at'],
                    'total' => $bookingData['total'],
                    'notes' => $bookingData['notes'],
                ]
            );

            $createdBookings[$index] = $booking;
        }

        Review::query()->updateOrCreate(
            [
                'user_id' => $guests[0]->id,
                'room_id' => $rooms[0]->id,
                'booking_id' => $createdBookings[0]->id,
            ],
            [
                'rating' => 5,
                'comment' => 'A calm, polished stay with excellent service and a beautiful room.',
                'approved' => true,
            ]
        );

        Review::query()->updateOrCreate(
            [
                'user_id' => $guests[1]->id,
                'room_id' => $rooms[1]->id,
                'booking_id' => $createdBookings[1]->id,
            ],
            [
                'rating' => 4,
                'comment' => 'Comfortable and warm with a very easy booking process.',
                'approved' => false,
            ]
        );

        Review::query()->updateOrCreate(
            [
                'user_id' => $guests[2]->id,
                'room_id' => $rooms[2]->id,
                'booking_id' => $createdBookings[2]->id,
            ],
            [
                'rating' => 5,
                'comment' => 'The room was clean, quiet, and perfect for a weekend escape.',
                'approved' => true,
            ]
        );

        SiteContent::query()->upsert(
            collect(SiteContent::landingPageDefaults())->map(function (string $value, string $key) {
                return [
                    'key' => $key,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all(),
            ['key'],
            ['value', 'updated_at']
        );
    }
}
