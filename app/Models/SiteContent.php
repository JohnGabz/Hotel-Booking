<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[\Illuminate\Database\Eloquent\Attributes\Fillable(['key', 'value'])]
class SiteContent extends Model
{
    use HasFactory;

    public static function landingPageDefaults(): array
    {
        return [
            'about_heading' => 'A refined booking experience for guests and staff.',
            'about_body' => 'Villa Estella brings reservations, room discovery, and guest management together in one premium hospitality workflow.',
            'services_intro' => 'From discovery to checkout, the system keeps each step clean, clear, and easy to use.',
            'faqs_intro' => 'Short, useful answers that help guests move confidently from browsing to booking.',
            'contact_email' => 'hello@villaestella.com',
            'contact_phone' => '+63 912 345 6789',
            'contact_address' => 'Q2M7+452, Sayre Hwy, Maramag, Bukidnon',
            'hero_eyebrow' => 'Villa Estella Fine Inn',
            'hero_title' => 'Experience Luxury Hospitality',
            'hero_subtitle' => 'A welcoming sanctuary combining modern comfort with genuine warmth. Your perfect getaway awaits.',
            'hero_button_text' => 'Explore Rooms',
            'hero_background_image' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1920&q=80',
            'booking_heading' => 'Check Availability',
            'booking_subheading' => 'Find your perfect room and book your stay instantly',
            'services_eyebrow' => 'Why Choose Us',
            'services_title' => 'Why Guests Love Villa Estella',
            'service_1_title' => 'Concierge Booking',
            'service_1_description' => 'Simple reservations, flexible dates, and quick support for every stay.',
            'service_2_title' => 'Fine Hospitality',
            'service_2_description' => 'Thoughtful service, beautiful spaces, and guest-first attention to detail.',
            'service_3_title' => 'Trusted Payments',
            'service_3_description' => 'Secure booking flow with clear totals and convenient payment options.',
            'service_4_title' => 'Guest Reviews',
            'service_4_description' => 'Verified feedback helps future guests choose the right room with confidence.',
            'featured_rooms_eyebrow' => 'Curated Selection',
            'featured_rooms_title' => 'Featured Rooms',
            'featured_rooms_intro' => 'Choose the perfect space for your getaway',
            'about_eyebrow' => 'Our Story',
            'about_secondary' => 'Whether you\'re making a quick stopover or planning a dedicated trip, our doors are always open to offer you an exceptional stay.',
            'about_image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80',
            'facilities_eyebrow' => 'Amenities',
            'facilities_title' => 'Everything You Expect from a Luxury Stay',
            'facilities_intro' => 'Clean, accessible spaces paired with the essentials that make every stay feel complete and comfortable.',
            'facility_1_label' => 'Free Wi-Fi',
            'facility_2_label' => '24/7 Concierge',
            'facility_3_label' => 'Complimentary Breakfast',
            'facility_4_label' => 'Parking',
            'gallery_eyebrow' => 'Experience',
            'gallery_title' => 'A Visual Story',
            'gallery_intro' => 'Spaces, textures, and details that make the experience feel warm, minimal, and intentionally premium.',
            'gallery_card_1_title' => 'Atmosphere',
            'gallery_card_1_text' => 'Calm, warm, and unforgettable.',
            'gallery_card_2_title' => 'Photography',
            'gallery_card_2_text' => 'Resort views and elegant interiors.',
            'gallery_image_1' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
            'gallery_image_2' => 'https://images.unsplash.com/photo-1501117716987-c8e5f10a7f09?auto=format&fit=crop&w=1200&q=80',
            'gallery_image_3' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=80',
            'gallery_image_4' => 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?auto=format&fit=crop&w=1200&q=80',
            'gallery_image_5' => 'https://images.unsplash.com/photo-1505691723518-36a4cdbb1f2a?auto=format&fit=crop&w=1200&q=80',
            'gallery_image_6' => 'https://images.unsplash.com/photo-1506976785307-8732e854ad11?auto=format&fit=crop&w=1200&q=80',
            'testimonial_1_name' => 'A. Mendoza',
            'testimonial_1_role' => 'Traveler',
            'testimonial_1_quote' => 'A delightful stay — highly recommended.',
            'testimonial_2_name' => 'L. Cruz',
            'testimonial_2_role' => 'Couple',
            'testimonial_2_quote' => 'Lovely interiors and attentive staff.',
            'testimonial_3_name' => 'M. Reyes',
            'testimonial_3_role' => 'Business traveler',
            'testimonial_3_quote' => 'Comfortable, quiet, and convenient location.',
            'location_eyebrow' => 'Visit Us',
            'location_title' => 'Find Us in Paradise',
            'location_intro' => 'Located in a serene destination, Villa Estella welcomes you with open doors and warm hospitality.',
            'location_map_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.9147!2d124.3783!3d8.8872!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sVilla%20Estella%20Inn!2sQ2M7%2B452%20Maramag%20Bukidnon!5e0!3m2!1sen!2sph!4v1714867200',
            'location_address_line1' => 'Q2M7+452, Sayre Hwy',
            'location_address_line2' => 'Maramag, Bukidnon, Philippines',
            'location_phone' => '+63 912 345 6789',
            'location_email' => 'hello@villaestella.com',
            'location_hours_1' => 'Check-in: 2:00 PM',
            'location_hours_2' => 'Check-out: 11:00 AM',
            'location_direction_url' => 'https://maps.google.com/?q=Q2M7%2B452+Sayre+Hwy+Maramag+Bukidnon',
            'cta_eyebrow' => 'Ready to Explore',
            'cta_title' => 'Book Your Perfect Stay',
            'cta_body' => 'Browse our curated collection of rooms, compare amenities, and complete your booking with instant confirmation. Experience luxury at Villa Estella today.',
            'cta_button_text' => 'Explore All Rooms',
        ];
    }

    public static function values(array $defaults): array
    {
        $records = static::query()
            ->whereIn('key', array_keys($defaults))
            ->pluck('value', 'key');

        return collect($defaults)->map(function ($default, string $key) use ($records) {
            return $records[$key] ?? $default;
        })->all();
    }

    public static function valueFor(string $key, string $default = ''): string
    {
        $record = static::query()->where('key', $key)->first();

        return $record?->value ?? $default;
    }

    public static function setValue(string $key, string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
