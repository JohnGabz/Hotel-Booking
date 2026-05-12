@extends('layouts.site')

@section('content')
@php
    $content = $landingContent ?? [];

    $heroEyebrow = $content['hero_eyebrow'] ?? 'Villa Estella Fine Inn';
    $heroTitle = $content['hero_title'] ?? 'Experience Luxury Hospitality';
    $heroSubtitle = $content['hero_subtitle'] ?? 'A welcoming sanctuary combining modern comfort with genuine warmth. Your perfect getaway awaits.';
    $heroButtonText = $content['hero_button_text'] ?? 'Explore Rooms';

    $bookingHeading = $content['booking_heading'] ?? 'Check Availability';
    $bookingSubheading = $content['booking_subheading'] ?? 'Find your perfect room and book your stay instantly';

    $services = [
        [
            'title' => $content['service_1_title'] ?? 'Concierge Booking',
            'description' => $content['service_1_description'] ?? 'Simple reservations, flexible dates, and quick support for every stay.',
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-3.314 0-6 2.239-6 5v4h12v-4c0-2.761-2.686-5-6-5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 8V7a4 4 0 1 1 8 0v1"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v1a3 3 0 0 0 6 0v-1"/></svg>',
        ],
        [
            'title' => $content['service_2_title'] ?? 'Fine Hospitality',
            'description' => $content['service_2_description'] ?? 'Thoughtful service, beautiful spaces, and guest-first attention to detail.',
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 8l8 5 8-5-8-5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 12l8 5 8-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l8 5 8-5"/></svg>',
        ],
        [
            'title' => $content['service_3_title'] ?? 'Trusted Payments',
            'description' => $content['service_3_description'] ?? 'Secure booking flow with clear totals and convenient payment options.',
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7.5h15v9h-15z"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 11h3"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11h1.5"/></svg>',
        ],
        [
            'title' => $content['service_4_title'] ?? 'Guest Reviews',
            'description' => $content['service_4_description'] ?? 'Verified feedback helps future guests choose the right room with confidence.',
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 17.3 6.5 20l1-6.1-4.4-4.3 6.2-1L12 3l2.7 5.6 6.2 1-4.4 4.3 1 6.1-5.5-2.7Z"/></svg>',
        ],
    ];

    $featuredRoomsEyebrow = $content['featured_rooms_eyebrow'] ?? 'Curated Selection';
    $featuredRoomsTitle = $content['featured_rooms_title'] ?? 'Featured Rooms';
    $featuredRoomsIntro = $content['featured_rooms_intro'] ?? 'Choose the perfect space for your getaway';

    $aboutEyebrow = $content['about_eyebrow'] ?? 'Our Story';
    $aboutHeading = $content['about_heading'] ?? 'A refined booking experience for guests and staff.';
    $aboutBody = $content['about_body'] ?? 'Villa Estella brings reservations, room discovery, and guest management together in one premium hospitality workflow.';
    $aboutSecondary = $content['about_secondary'] ?? 'Whether you\'re making a quick stopover or planning a dedicated trip, our doors are always open to offer you an exceptional stay.';

    $facilities = [
        ['label' => $content['facility_1_label'] ?? 'Free Wi-Fi', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M3 8h18M7 16h10"/></svg>'],
        ['label' => $content['facility_2_label'] ?? '24/7 Concierge', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v4M6 7v4M18 7v4M4 21h16"/></svg>'],
        ['label' => $content['facility_3_label'] ?? 'Complimentary Breakfast', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M6 8v8a4 4 0 0 0 8 0V8"/></svg>'],
        ['label' => $content['facility_4_label'] ?? 'Parking', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13h14v6H5zM7 13V7h6v6"/></svg>'],
    ];

    $gallery = [
        $content['gallery_image_1'] ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
        $content['gallery_image_2'] ?? 'https://images.unsplash.com/photo-1501117716987-c8e5f10a7f09?auto=format&fit=crop&w=1200&q=80',
        $content['gallery_image_3'] ?? 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=80',
        $content['gallery_image_4'] ?? 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?auto=format&fit=crop&w=1200&q=80',
        $content['gallery_image_5'] ?? 'https://images.unsplash.com/photo-1505691723518-36a4cdbb1f2a?auto=format&fit=crop&w=1200&q=80',
        $content['gallery_image_6'] ?? 'https://images.unsplash.com/photo-1506976785307-8732e854ad11?auto=format&fit=crop&w=1200&q=80',
    ];

    $galleryEyebrow = $content['gallery_eyebrow'] ?? 'Experience';
    $galleryTitle = $content['gallery_title'] ?? 'A Visual Story';
    $galleryIntro = $content['gallery_intro'] ?? 'Spaces, textures, and details that make the experience feel warm, minimal, and intentionally premium.';

    $testimonials = [
        ['name' => $content['testimonial_1_name'] ?? 'A. Mendoza', 'role' => $content['testimonial_1_role'] ?? 'Traveler', 'rating' => 5, 'quote' => $content['testimonial_1_quote'] ?? 'A delightful stay — highly recommended.'],
        ['name' => $content['testimonial_2_name'] ?? 'L. Cruz', 'role' => $content['testimonial_2_role'] ?? 'Couple', 'rating' => 5, 'quote' => $content['testimonial_2_quote'] ?? 'Lovely interiors and attentive staff.'],
        ['name' => $content['testimonial_3_name'] ?? 'M. Reyes', 'role' => $content['testimonial_3_role'] ?? 'Business traveler', 'rating' => 5, 'quote' => $content['testimonial_3_quote'] ?? 'Comfortable, quiet, and convenient location.'],
    ];

    $testimonialsEyebrow = $content['testimonials_eyebrow'] ?? 'Testimonials';
    $testimonialsTitle = $content['testimonials_title'] ?? 'Trusted by guests who return for the experience.';

    $locationEyebrow = $content['location_eyebrow'] ?? 'Visit Us';
    $locationTitle = $content['location_title'] ?? 'Find Us in Paradise';
    $locationIntro = $content['location_intro'] ?? 'Located in a serene destination, Villa Estella welcomes you with open doors and warm hospitality.';
    $locationMapUrl = $content['location_map_url'] ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.9147!2d124.3783!3d8.8872!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sVilla%20Estella%20Inn!2sQ2M7%2B452%20Maramag%20Bukidnon!5e0!3m2!1sen!2sph!4v1714867200';
    $locationAddressLine1 = $content['location_address_line1'] ?? 'Q2M7+452, Sayre Hwy';
    $locationAddressLine2 = $content['location_address_line2'] ?? 'Maramag, Bukidnon, Philippines';
    $locationPhone = $content['location_phone'] ?? '+63 912 345 6789';
    $locationEmail = $content['location_email'] ?? 'hello@villaestella.com';
    $locationHours1 = $content['location_hours_1'] ?? 'Check-in: 2:00 PM';
    $locationHours2 = $content['location_hours_2'] ?? 'Check-out: 11:00 AM';
    $locationDirectionUrl = $content['location_direction_url'] ?? 'https://maps.google.com/?q=Q2M7%2B452+Sayre+Hwy+Maramag+Bukidnon';

    $ctaEyebrow = $content['cta_eyebrow'] ?? 'Ready to Explore';
    $ctaTitle = $content['cta_title'] ?? 'Book Your Perfect Stay';
    $ctaBody = $content['cta_body'] ?? 'Browse our curated collection of rooms, compare amenities, and complete your booking with instant confirmation. Experience luxury at Villa Estella today.';
    $ctaButtonText = $content['cta_button_text'] ?? 'Explore All Rooms';

    $rawHero = $heroBackground ?? ($content['hero_background_image'] ?? '');

    if (filter_var($rawHero, FILTER_VALIDATE_URL)) {
        $heroBackground = $rawHero;
    } elseif (filled($rawHero)) {
        $candidate = ltrim($rawHero, '/');
        $heroBackground = str_starts_with($candidate, 'storage/')
            ? asset($candidate)
            : asset('storage/' . $candidate);
    } else {
        $heroBackground = 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1920&q=80';
    }
@endphp

<!-- Hero Section -->
<section class="relative bg-stone-900 min-h-[75vh] flex items-center overflow-hidden">
    <div class="absolute inset-0 overflow-hidden">
        <img src="{{ $heroBackground }}" alt="Hero background" class="w-full h-full object-cover opacity-35">
        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/60 to-stone-950/20"></div>
    </div>
    
    <div class="relative z-10 site-shell pt-12 pb-24 sm:pt-20 sm:pb-32">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-white/80 mb-4">{{ $heroEyebrow }}</p>
            <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl text-white font-bold mb-4 sm:mb-6 leading-tight">{{ $heroTitle }}</h1>
            <p class="text-base sm:text-lg text-stone-100 mb-8 leading-relaxed max-w-xl">{{ $heroSubtitle }}</p>
            <a href="{{ route('rooms.index') }}" class="inline-flex items-center bg-brand-primary hover:bg-brand-secondary text-white px-6 sm:px-8 py-3 sm:py-3.5 rounded-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-0.5 group">
                {{ $heroButtonText }}
                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>
    </div>
</section>

<!-- Booking Quick Form -->
<section class="relative -mt-16 sm:-mt-20 site-shell z-20">
    <div class="bg-white rounded-2xl shadow-2xl border border-stone-200 overflow-hidden">
        <div class="p-6 sm:p-10 lg:p-12">
            <div class="mb-8">
                <h2 class="text-2xl sm:text-3xl font-display font-bold text-stone-900 mb-2">{{ $bookingHeading }}</h2>
                <p class="text-sm text-stone-600">{{ $bookingSubheading }}</p>
            </div>
            <form action="{{ url('/bookings/search') }}" method="GET" class="grid gap-4 sm:gap-5 md:grid-cols-5 md:items-end">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-[0.15em] mb-3">Check-in</label>
                    <input type="date" name="check_in" class="w-full border border-stone-300 rounded-lg px-4 py-3 text-sm text-stone-900 bg-white focus:ring-2 focus:ring-brand-primary focus:border-transparent outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-[0.15em] mb-3">Check-out</label>
                    <input type="date" name="check_out" class="w-full border border-stone-300 rounded-lg px-4 py-3 text-sm text-stone-900 bg-white focus:ring-2 focus:ring-brand-primary focus:border-transparent outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-[0.15em] mb-3">Guests</label>
                    <select name="guests" class="w-full border border-stone-300 rounded-lg px-4 py-3 text-sm text-stone-900 bg-white focus:ring-2 focus:ring-brand-primary focus:border-transparent outline-none transition appearance-none cursor-pointer">
                        <option>1 Guest</option>
                        <option>2 Guests</option>
                        <option>3 Guests</option>
                        <option>4+ Guests</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-[0.15em] mb-3">Rooms</label>
                    <select name="rooms" class="w-full border border-stone-300 rounded-lg px-4 py-3 text-sm text-stone-900 bg-white focus:ring-2 focus:ring-brand-primary focus:border-transparent outline-none transition appearance-none cursor-pointer">
                        <option>1 Room</option>
                        <option>2 Rooms</option>
                        <option>3+ Rooms</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-brand-primary hover:bg-brand-secondary text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                    Search
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Services Overview -->
<section class="section-shell bg-white">
    <div class="site-shell">
        <div class="text-center mb-12 sm:mb-16">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-primary mb-3">{{ $content['services_eyebrow'] ?? 'Why Choose Us' }}</p>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-display font-bold text-stone-900 mb-4">{{ $content['services_title'] ?? 'Why Guests Love Villa Estella' }}</h2>
            <p class="text-base text-stone-600 max-w-2xl mx-auto leading-relaxed">{{ $content['services_intro'] ?? 'We combine thoughtful service, beautiful spaces, and guest-first attention to detail' }}</p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8 scroll-animate-stagger">
            @foreach ($services as $service)
                <div class="group bg-white border border-stone-200 rounded-2xl p-8 shadow-lg hover:border-brand-primary/40 hover:shadow-2xl hover:bg-stone-50/50 transition-all duration-300 hover:-translate-y-1">
                    <div class="w-16 h-16 bg-brand-primary/10 rounded-xl flex items-center justify-center mb-6 text-brand-primary group-hover:bg-brand-primary/15 transition-colors duration-300">
                        {!! $service['icon'] !!}
                    </div>
                    <h3 class="text-xl font-semibold text-stone-900 mb-3">{{ $service['title'] }}</h3>
                    <p class="text-sm leading-relaxed text-stone-600">{{ $service['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Rooms -->
<section class="section-shell bg-stone-50">
    <div class="site-shell">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-12 sm:mb-16">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-primary mb-3">{{ $featuredRoomsEyebrow }}</p>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-display font-bold text-stone-900">{{ $featuredRoomsTitle }}</h2>
                <p class="mt-3 text-base text-stone-600">{{ $featuredRoomsIntro }}</p>
            </div>
            <a href="{{ route('rooms.index') }}" class="inline-flex items-center text-brand-primary font-semibold hover:text-brand-secondary transition-colors group">
                View All
                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 scroll-animate-stagger">
            @forelse ($featuredRooms as $room)
                <div class="group bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-lg hover:border-brand-primary/40 hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="relative h-72 overflow-hidden bg-stone-200">
                        @if ($room->image)
                            <img src="{{ asset('storage/' . $room->image) }}" alt="{{ $room->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        @else
                            <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80" alt="{{ $room->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="absolute top-4 right-4 bg-white/95 backdrop-blur px-4 py-2 rounded-lg text-sm font-semibold text-brand-primary shadow-lg">
                            ₱{{ number_format($room->price_per_night, 0) }}/night
                        </div>
                    </div>
                    <div class="p-6 sm:p-7">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <h3 class="text-xl font-semibold text-stone-900">{{ $room->name }}</h3>
                                <p class="mt-1 text-xs uppercase tracking-[0.2em] text-stone-500">{{ ucfirst($room->status) }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-stone-600 mb-4 line-clamp-2 leading-relaxed">{{ $room->description }}</p>
                        
                        <div class="flex items-center gap-4 mb-6 text-xs text-stone-500 font-medium">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20a3 3 0 00-3-3H10a3 3 0 00-3 3M12 12a4 4 0 100-8 4 4 0 000 8z"/></svg>
                                {{ $room->capacity }} Guest{{ $room->capacity > 1 ? 's' : '' }}
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke-width="2"/></svg>
                                {{ $room->size_sqm }}m²
                            </span>
                        </div>
                        
                        <a href="{{ route('rooms.show', $room->slug) }}" class="inline-flex w-full justify-center items-center gap-2 border border-brand-primary text-brand-primary hover:bg-brand-primary hover:text-white font-semibold py-3 rounded-lg transition-all duration-300 group/btn">
                            View Details
                            <svg class="w-4 h-4 group-hover/btn:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white border border-stone-200 rounded-2xl p-12 text-center">
                    <p class="text-stone-600">No rooms available at the moment.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- About Section -->
<section class="section-shell bg-white">
    <div class="site-shell">
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden grid md:grid-cols-2 shadow-lg">
            <div class="p-8 md:p-12 flex flex-col justify-center scroll-animate-in-left bg-stone-50">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-primary mb-4">{{ $aboutEyebrow }}</p>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-display font-bold text-stone-900 mb-6">{{ $aboutHeading }}</h2>
                <p class="text-stone-600 mb-6 leading-relaxed text-base">{{ $aboutBody }}</p>
                <p class="text-stone-500 mb-8 leading-relaxed text-sm">{{ $aboutSecondary }}</p>
                <a href="{{ route('about') }}" class="inline-flex items-center text-brand-primary font-semibold hover:text-brand-secondary transition-colors group w-fit">
                    Learn More
                    <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </a>
            </div>
            <div class="h-80 md:h-auto bg-stone-300 scroll-animate-in-right">
                <img src="{{ $content['about_image'] ?? 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80' }}" alt="Villa Estella interior" class="w-full h-full object-cover">
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section-shell">
    <div class="site-shell">
        <div class="bg-gradient-to-r from-brand-primary to-brand-secondary text-white rounded-2xl shadow-lg p-12 sm:p-16 text-center">
        <h2 class="text-3xl sm:text-4xl font-display font-bold mb-4">Ready to Book Your Stay?</h2>
        <p class="text-lg text-white/90 mb-8 max-w-2xl mx-auto">Experience luxury hospitality with seamless booking and world-class service.</p>
        <a href="{{ route('rooms.index') }}" class="inline-flex items-center bg-white text-brand-primary hover:bg-stone-100 px-8 py-3 rounded-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
            Browse All Rooms
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
        </a>
        </div>
    </div>
</section>

<!-- Facilities Section -->
<section class="section-shell bg-stone-50">
    <div class="site-shell">
        <div class="bg-white rounded-2xl border border-stone-200 shadow-lg p-8 sm:p-10 lg:p-12">
            <div class="mb-12">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-primary mb-3">{{ $content['facilities_eyebrow'] ?? 'Amenities' }}</p>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-display font-bold text-stone-900 mb-4">{{ $content['facilities_title'] ?? 'Everything You Expect from a Luxury Stay' }}</h2>
                <p class="max-w-2xl text-base text-stone-600 leading-relaxed">{{ $content['facilities_intro'] ?? 'Clean, accessible spaces paired with the essentials that make every stay feel complete and comfortable.' }}</p>
            </div>

            <div class="icon-grid scroll-animate-stagger">
                @foreach ($facilities as $facility)
                    <div class="group rounded-xl border border-stone-200 bg-stone-50 shadow-sm hover:bg-white hover:border-brand-primary/40 hover:shadow-lg p-6 sm:p-8 text-center transition-all duration-300 hover:-translate-y-1">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-xl bg-brand-primary/10 text-brand-primary group-hover:bg-brand-primary/15 transition-colors duration-300">
                            {!! $facility['icon'] !!}
                        </div>
                        <p class="mt-4 text-sm font-semibold uppercase tracking-[0.2em] text-stone-700 group-hover:text-brand-primary transition-colors duration-300">{{ $facility['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="section-shell">
    <div class="site-shell grid gap-8 lg:grid-cols-[0.92fr_1.08fr] lg:items-start">
        <div class="bg-white border border-stone-200 rounded-2xl p-8 sm:p-10 shadow-lg scroll-animate-in-left">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-primary mb-3">{{ $galleryEyebrow }}</p>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-display font-bold text-stone-900 mb-6">{{ $galleryTitle }}</h2>
            <p class="text-base leading-relaxed text-stone-600 mb-8">{{ $galleryIntro }}</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-brand-primary/5 border border-brand-primary/20 p-6">
                    <p class="text-xs uppercase tracking-[0.25em] text-brand-primary font-semibold mb-2">Atmosphere</p>
                    <p class="text-base font-semibold text-stone-900">Calm, warm, and unforgettable.</p>
                </div>
                <div class="rounded-xl bg-brand-primary/5 border border-brand-primary/20 p-6">
                    <p class="text-xs uppercase tracking-[0.25em] text-brand-primary font-semibold mb-2">Photography</p>
                    <p class="text-base font-semibold text-stone-900">Resort views and elegant interiors.</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 scroll-animate-in-right">
            <div class="overflow-hidden rounded-2xl sm:row-span-2 border border-stone-200 shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <img src="{{ $gallery[0] }}" alt="Resort suite" class="h-full w-full object-cover hover:scale-110 transition-transform duration-500">
            </div>
            <div class="overflow-hidden rounded-2xl border border-stone-200 shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <img src="{{ $gallery[1] }}" alt="Bedroom view" class="h-full w-full object-cover hover:scale-110 transition-transform duration-500">
            </div>
            <div class="overflow-hidden rounded-2xl border border-stone-200 shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <img src="{{ $gallery[2] }}" alt="Hotel lounge" class="h-full w-full object-cover hover:scale-110 transition-transform duration-500">
            </div>
            <div class="overflow-hidden rounded-2xl sm:col-span-2 border border-stone-200 shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <img src="{{ $gallery[5] }}" alt="Sunset by the water" class="h-64 w-full object-cover hover:scale-110 transition-transform duration-500">
            </div>
        </div>
    </div>
</section>

<section class="section-shell pt-0">
    <div class="site-shell">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <span class="eyebrow">{{ $testimonialsEyebrow }}</span>
                <h2 class="mt-4 text-4xl sm:text-5xl text-stone-950">{{ $testimonialsTitle }}</h2>
            </div>
            <a href="{{ route('contact') }}" class="btn-secondary">Talk to us</a>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-3 scroll-animate-stagger">
            @foreach ($testimonials as $testimonial)
                <article class="card bg-white border border-stone-200 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center gap-1 text-amber-500">
                        @for ($star = 1; $star <= $testimonial['rating']; $star++)
                            <span aria-hidden="true">★</span>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm leading-7 text-stone-600">“{{ $testimonial['quote'] }}”</p>
                    <div class="mt-6 border-t border-stone-200 pt-4">
                        <p class="font-semibold text-stone-950">{{ $testimonial['name'] }}</p>
                        <p class="text-sm text-stone-500">{{ $testimonial['role'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<!-- Location Section -->
<section class="section-shell bg-white">
    <div class="site-shell">
        <div class="mb-12 sm:mb-16">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-primary mb-3">{{ $locationEyebrow }}</p>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-display font-bold text-stone-900">{{ $locationTitle }}</h2>
            <p class="mt-3 max-w-2xl text-base text-stone-600 leading-relaxed">{{ $locationIntro }}</p>
        </div>

        <div class="grid md:grid-cols-2 gap-8 lg:gap-12 items-stretch">
            <div class="col-span-full mb-6">
                <div class="inline-flex rounded-lg bg-stone-100 p-1">
                    <button type="button" class="px-4 py-2 rounded-lg font-medium text-stone-800" data-location-tab data-location-target="map" aria-pressed="true">Map</button>
                    <button type="button" class="px-4 py-2 rounded-lg font-medium text-stone-600" data-location-tab data-location-target="details" aria-pressed="false">Details</button>
                </div>
            </div>

            <div class="scroll-animate-in-left" data-location-panel="map">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-stone-200 h-96 md:h-full min-h-96 hover:shadow-2xl transition-shadow duration-300">
                    <iframe
                        width="100%"
                        height="100%"
                        style="border:0; border-radius: 1rem;"
                        loading="lazy"
                        allowfullscreen=""
                        referrerpolicy="no-referrer-when-downgrade"
                        src="{{ $locationMapUrl }}">
                    </iframe>
                </div>
            </div>

            <div class="scroll-animate-in-right flex flex-col justify-center bg-white rounded-2xl border border-stone-200 shadow-lg p-8 md:p-10" data-location-panel="details">
                <div class="space-y-6">
                    <div class="group">
                        <div class="flex items-start gap-4 mb-3">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-brand-primary/10 flex items-center justify-center text-brand-primary group-hover:bg-brand-primary group-hover:text-white transition-colors duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.15em] font-semibold text-stone-500 mb-1">Address</p>
                                <p class="text-base font-medium text-stone-900">{{ $locationAddressLine1 }}</p>
                                <p class="text-sm text-stone-600">{{ $locationAddressLine2 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="group">
                        <div class="flex items-start gap-4 mb-3">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-brand-primary/10 flex items-center justify-center text-brand-primary group-hover:bg-brand-primary group-hover:text-white transition-colors duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.15em] font-semibold text-stone-500 mb-1">Phone</p>
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $locationPhone) }}" class="text-base font-medium text-stone-900 hover:text-brand-primary transition-colors">{{ $locationPhone }}</a>
                                <p class="text-sm text-stone-600">{{ $locationHours1 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="group">
                        <div class="flex items-start gap-4 mb-3">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-brand-primary/10 flex items-center justify-center text-brand-primary group-hover:bg-brand-primary group-hover:text-white transition-colors duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.15em] font-semibold text-stone-500 mb-1">Email</p>
                                <a href="mailto:{{ $locationEmail }}" class="text-base font-medium text-stone-900 hover:text-brand-primary transition-colors">{{ $locationEmail }}</a>
                                <p class="text-sm text-stone-600">{{ $locationHours2 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="group">
                        <div class="flex items-start gap-4 mb-3">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-brand-primary/10 flex items-center justify-center text-brand-primary group-hover:bg-brand-primary group-hover:text-white transition-colors duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.15em] font-semibold text-stone-500 mb-1">Hours</p>
                                <p class="text-base font-medium text-stone-900">{{ $locationHours1 }}</p>
                                <p class="text-sm text-stone-600">{{ $locationHours2 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <a href="{{ $locationDirectionUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 bg-brand-primary hover:bg-brand-secondary text-white px-8 py-3.5 rounded-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-0.5 group">
                            Get Directions
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-shell">
    <div class="site-shell">
        <div class="bg-gradient-to-r from-brand-primary to-brand-secondary text-white rounded-2xl shadow-lg p-12 sm:p-16 text-center">
            <div class="grid gap-0 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="py-4 sm:py-6 lg:py-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-white/80 mb-3">{{ $ctaEyebrow }}</p>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-display font-bold text-white mb-4">{{ $ctaTitle }}</h2>
                    <p class="text-base leading-relaxed text-white/90 max-w-lg mx-auto lg:mx-0">{{ $ctaBody }}</p>
                    <a href="{{ route('rooms.index') }}" class="inline-flex items-center mt-8 bg-white text-brand-primary hover:bg-stone-100 px-8 py-3.5 rounded-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl group">
                        {{ $ctaButtonText }}
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>
                <div class="hidden lg:flex items-center justify-end">
                    <div class="w-32 h-32 rounded-full bg-white/10 blur-3xl"></div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
