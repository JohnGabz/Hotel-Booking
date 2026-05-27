<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\SiteContent;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $featuredRooms = Room::available()->take(3)->get();
        $landingContent = SiteContent::values(SiteContent::landingPageDefaults());

        $heroBackground = $this->resolveImageUrl(
            $landingContent['hero_background_image'],
            'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1920&q=80'
        );

        $services = [
            [
                'title' => $landingContent['service_1_title'],
                'description' => $landingContent['service_1_description'],
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-3.314 0-6 2.239-6 5v4h12v-4c0-2.761-2.686-5-6-5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 8V7a4 4 0 1 1 8 0v1"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v1a3 3 0 0 0 6 0v-1"/></svg>',
            ],
            [
                'title' => $landingContent['service_2_title'],
                'description' => $landingContent['service_2_description'],
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 8l8 5 8-5-8-5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 12l8 5 8-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l8 5 8-5"/></svg>',
            ],
            [
                'title' => $landingContent['service_3_title'],
                'description' => $landingContent['service_3_description'],
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7.5h15v9h-15z"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 11h3"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11h1.5"/></svg>',
            ],
            [
                'title' => $landingContent['service_4_title'],
                'description' => $landingContent['service_4_description'],
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 17.3 6.5 20l1-6.1-4.4-4.3 6.2-1L12 3l2.7 5.6 6.2 1-4.4 4.3 1 6.1-5.5-2.7Z"/></svg>',
            ],
        ];

        $facilities = [
            ['label' => $landingContent['facility_1_label'], 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M3 8h18M7 16h10"/></svg>'],
            ['label' => $landingContent['facility_2_label'], 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v4M6 7v4M18 7v4M4 21h16"/></svg>'],
            ['label' => $landingContent['facility_3_label'], 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M6 8v8a4 4 0 0 0 8 0V8"/></svg>'],
            ['label' => $landingContent['facility_4_label'], 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13h14v6H5zM7 13V7h6v6"/></svg>'],
        ];

        $gallery = [
            $this->resolveImageUrl($landingContent['gallery_image_1']),
            $this->resolveImageUrl($landingContent['gallery_image_2']),
            $this->resolveImageUrl($landingContent['gallery_image_3']),
            $this->resolveImageUrl($landingContent['gallery_image_4']),
            $this->resolveImageUrl($landingContent['gallery_image_5']),
            $this->resolveImageUrl($landingContent['gallery_image_6']),
        ];

        $testimonials = [
            ['name' => $landingContent['testimonial_1_name'], 'role' => $landingContent['testimonial_1_role'], 'rating' => 5, 'quote' => $landingContent['testimonial_1_quote']],
            ['name' => $landingContent['testimonial_2_name'], 'role' => $landingContent['testimonial_2_role'], 'rating' => 5, 'quote' => $landingContent['testimonial_2_quote']],
            ['name' => $landingContent['testimonial_3_name'], 'role' => $landingContent['testimonial_3_role'], 'rating' => 5, 'quote' => $landingContent['testimonial_3_quote']],
        ];

        return view('pages.home', compact('featuredRooms', 'services', 'facilities', 'gallery', 'testimonials', 'landingContent', 'heroBackground'), [
            'seo' => [
                'title' => config('app.name') . ' — ' . config('seo.tagline'),
                'description' => config('seo.default_description'),
            ],
        ]);
    }

    public function about(): View
    {
        $heading = SiteContent::valueFor('about_heading', 'A refined booking experience for guests and staff.');
        $body = SiteContent::valueFor('about_body', 'Villa Estella brings reservations, room discovery, and guest management together in one premium hospitality workflow.');

        return view('pages.about', [
            'heading' => $heading,
            'body' => $body,
            'seo' => [
                'title' => 'About — ' . config('app.name'),
                'description' => 'Learn more about Villa Estella and our guest-first villa experience.',
            ],
        ]);
    }

    public function services(): View
    {
        $intro = SiteContent::valueFor('services_intro', 'From discovery to checkout, the system keeps each step clean, clear, and easy to use.');

        return view('pages.services', [
            'intro' => $intro,
            'seo' => [
                'title' => 'Services — ' . config('app.name'),
                'description' => 'Guest-focused rooms, payment options, and review tools for every stay.',
            ],
        ]);
    }

    public function faqs(): View
    {
        $intro = SiteContent::valueFor('faqs_intro', 'Short, useful answers that help guests move confidently from browsing to booking.');

        return view('pages.faqs', [
            'intro' => $intro,
            'seo' => [
                'title' => 'FAQs — ' . config('app.name'),
                'description' => 'Frequently asked questions about reservations, payments, and reviews.',
            ],
        ]);
    }

    public function contact(): View
    {
        $contact = [
            'email' => SiteContent::valueFor('contact_email', 'hello@villaestella.com'),
            'phone' => SiteContent::valueFor('contact_phone', '+63 912 345 6789'),
            'address' => SiteContent::valueFor('contact_address', 'Sunset Boulevard, Tagaytay, Philippines'),
        ];

        return view('pages.contact', [
            'contactInfo' => $contact,
            'seo' => [
                'title' => 'Contact — ' . config('app.name'),
                'description' => 'Get in touch with Villa Estella to book your next stay.',
            ],
        ]);
    }

    protected function resolveImageUrl(string $value, ?string $fallback = null): string
    {
        return ImageStorage::url($value, $fallback);
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:150',
            'message' => 'required|string|max:1200',
        ]);

        return redirect()->route('contact')->with('success', 'Thanks for reaching out! We will respond within 24 hours.');
    }
}
