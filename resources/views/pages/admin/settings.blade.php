@extends('layouts.admin')

@section('content')
@php
    $tab = $activeSettingsTab ?? 'general';

    $landingSections = [
        [
            'title' => 'Hero',
            'description' => 'Opening message, call-to-action, and hero background image.',
            'fields' => [
                ['key' => 'hero_eyebrow', 'label' => 'Eyebrow'],
                ['key' => 'hero_title', 'label' => 'Title', 'span' => 'xl:col-span-2'],
                ['key' => 'hero_subtitle', 'label' => 'Subtitle', 'type' => 'textarea', 'rows' => 4, 'span' => 'xl:col-span-2'],
                ['key' => 'hero_button_text', 'label' => 'Button text'],
            ],
        ],
        [
            'title' => 'Booking',
            'description' => 'Section title and supporting copy for the booking quick form.',
            'fields' => [
                ['key' => 'booking_heading', 'label' => 'Heading'],
                ['key' => 'booking_subheading', 'label' => 'Subheading', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
            ],
        ],
        [
            'title' => 'Services',
            'description' => 'Intro copy and the four service cards shown on the landing page.',
            'fields' => [
                ['key' => 'services_eyebrow', 'label' => 'Eyebrow'],
                ['key' => 'services_title', 'label' => 'Title', 'span' => 'xl:col-span-2'],
                ['key' => 'services_intro', 'label' => 'Intro text', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
                ['key' => 'service_1_title', 'label' => 'Card 1 title'],
                ['key' => 'service_1_description', 'label' => 'Card 1 description', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
                ['key' => 'service_2_title', 'label' => 'Card 2 title'],
                ['key' => 'service_2_description', 'label' => 'Card 2 description', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
                ['key' => 'service_3_title', 'label' => 'Card 3 title'],
                ['key' => 'service_3_description', 'label' => 'Card 3 description', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
                ['key' => 'service_4_title', 'label' => 'Card 4 title'],
                ['key' => 'service_4_description', 'label' => 'Card 4 description', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
            ],
        ],
        [
            'title' => 'Featured rooms',
            'description' => 'Section heading and intro above the featured room cards.',
            'fields' => [
                ['key' => 'featured_rooms_eyebrow', 'label' => 'Eyebrow'],
                ['key' => 'featured_rooms_title', 'label' => 'Title', 'span' => 'xl:col-span-2'],
                ['key' => 'featured_rooms_intro', 'label' => 'Intro text', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
            ],
        ],
        [
            'title' => 'About',
            'description' => 'Copy and image used in the about section on the landing page.',
            'fields' => [
                ['key' => 'about_heading', 'label' => 'Heading', 'span' => 'xl:col-span-2'],
                ['key' => 'about_body', 'label' => 'Body', 'type' => 'textarea', 'rows' => 4, 'span' => 'xl:col-span-2'],
                ['key' => 'about_secondary', 'label' => 'Secondary body', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
                ['key' => 'about_image', 'label' => 'Image', 'span' => 'xl:col-span-2'],
            ],
        ],
        [
            'title' => 'Facilities',
            'description' => 'Amenities heading and the four feature labels below it.',
            'fields' => [
                ['key' => 'facilities_eyebrow', 'label' => 'Eyebrow'],
                ['key' => 'facilities_title', 'label' => 'Title', 'span' => 'xl:col-span-2'],
                ['key' => 'facilities_intro', 'label' => 'Intro text', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
                ['key' => 'facility_1_label', 'label' => 'Facility 1 label'],
                ['key' => 'facility_2_label', 'label' => 'Facility 2 label'],
                ['key' => 'facility_3_label', 'label' => 'Facility 3 label'],
                ['key' => 'facility_4_label', 'label' => 'Facility 4 label'],
            ],
        ],
        [
            'title' => 'Gallery',
            'description' => 'The gallery heading and six images used in the masonry grid.',
            'fields' => [
                ['key' => 'gallery_eyebrow', 'label' => 'Eyebrow'],
                ['key' => 'gallery_title', 'label' => 'Title', 'span' => 'xl:col-span-2'],
                ['key' => 'gallery_intro', 'label' => 'Intro text', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
                ['key' => 'gallery_image_1', 'label' => 'Image 1', 'span' => 'xl:col-span-2'],
                ['key' => 'gallery_image_2', 'label' => 'Image 2', 'span' => 'xl:col-span-2'],
                ['key' => 'gallery_image_3', 'label' => 'Image 3', 'span' => 'xl:col-span-2'],
                ['key' => 'gallery_image_4', 'label' => 'Image 4', 'span' => 'xl:col-span-2'],
                ['key' => 'gallery_image_5', 'label' => 'Image 5', 'span' => 'xl:col-span-2'],
                ['key' => 'gallery_image_6', 'label' => 'Image 6', 'span' => 'xl:col-span-2'],
            ],
        ],
        [
            'title' => 'Testimonials',
            'description' => 'Guest quotes and names shown in the testimonial cards.',
            'fields' => [
                ['key' => 'testimonial_1_name', 'label' => 'Testimonial 1 name'],
                ['key' => 'testimonial_1_role', 'label' => 'Testimonial 1 role'],
                ['key' => 'testimonial_1_quote', 'label' => 'Testimonial 1 quote', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
                ['key' => 'testimonial_2_name', 'label' => 'Testimonial 2 name'],
                ['key' => 'testimonial_2_role', 'label' => 'Testimonial 2 role'],
                ['key' => 'testimonial_2_quote', 'label' => 'Testimonial 2 quote', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
                ['key' => 'testimonial_3_name', 'label' => 'Testimonial 3 name'],
                ['key' => 'testimonial_3_role', 'label' => 'Testimonial 3 role'],
                ['key' => 'testimonial_3_quote', 'label' => 'Testimonial 3 quote', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
            ],
        ],
        [
            'title' => 'Location',
            'description' => 'Map embed, contact details, and arrival information.',
            'fields' => [
                ['key' => 'location_eyebrow', 'label' => 'Eyebrow'],
                ['key' => 'location_title', 'label' => 'Title', 'span' => 'xl:col-span-2'],
                ['key' => 'location_intro', 'label' => 'Intro text', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
                ['key' => 'location_map_url', 'label' => 'Map embed URL', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
                ['key' => 'location_address_line1', 'label' => 'Address line 1'],
                ['key' => 'location_address_line2', 'label' => 'Address line 2', 'span' => 'xl:col-span-2'],
                ['key' => 'location_phone', 'label' => 'Phone'],
                ['key' => 'location_email', 'label' => 'Email'],
                ['key' => 'location_hours_1', 'label' => 'Hours line 1'],
                ['key' => 'location_hours_2', 'label' => 'Hours line 2'],
                ['key' => 'location_direction_url', 'label' => 'Directions URL', 'span' => 'xl:col-span-2'],
                ['key' => 'contact_address', 'label' => 'Public contact address', 'span' => 'xl:col-span-2'],
            ],
        ],
        [
            'title' => 'Call to action',
            'description' => 'Final booking prompt shown at the bottom of the homepage.',
            'fields' => [
                ['key' => 'cta_eyebrow', 'label' => 'Eyebrow'],
                ['key' => 'cta_title', 'label' => 'Title', 'span' => 'xl:col-span-2'],
                ['key' => 'cta_body', 'label' => 'Body', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
                ['key' => 'cta_button_text', 'label' => 'Button text'],
            ],
        ],
        [
            'title' => 'Shared copy',
            'description' => 'Support copy used on the about, services, FAQs, and contact pages.',
            'fields' => [
                ['key' => 'faqs_intro', 'label' => 'FAQs intro', 'type' => 'textarea', 'rows' => 3, 'span' => 'xl:col-span-2'],
            ],
        ],
    ];

    $heroImage = $siteContent['hero_background_image'] ?? '';
    $heroImagePreview = str_starts_with($heroImage, 'http://') || str_starts_with($heroImage, 'https://')
        ? $heroImage
        : (filled($heroImage)
            ? (str_starts_with(ltrim($heroImage, '/'), 'storage/')
                ? asset(ltrim($heroImage, '/'))
                : asset('storage/' . ltrim($heroImage, '/')))
            : '');
@endphp

<div id="top" class="space-y-8">
    <section class="surface p-6 sm:p-8 lg:p-10">
        <span class="eyebrow">Settings</span>
        <h1 class="mt-4 text-4xl sm:text-5xl text-stone-950">Tabbed controls for general, account, preferences, and the landing page.</h1>
        <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">Keep admin configuration grouped and easy to scan without overwhelming the page.</p>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('admin.settings', ['tab' => 'general']) }}" class="{{ $tab === 'general' ? 'btn-primary' : 'btn-secondary' }}">General</a>
            <a href="{{ route('admin.settings', ['tab' => 'account']) }}" class="{{ $tab === 'account' ? 'btn-primary' : 'btn-secondary' }}">Account</a>
            <a href="{{ route('admin.settings', ['tab' => 'preferences']) }}" class="{{ $tab === 'preferences' ? 'btn-primary' : 'btn-secondary' }}">Preferences</a>
            <a href="{{ route('admin.settings', ['tab' => 'landing']) }}" class="{{ $tab === 'landing' ? 'btn-primary' : 'btn-secondary' }}">Landing page</a>
        </div>
    </section>

    @if ($tab === 'general')
        <section class="surface p-6 sm:p-8">
            <h2 class="text-3xl font-semibold text-stone-950">General settings</h2>
            <div class="mt-6 grid gap-4 xl:grid-cols-2">
                <div class="form-group xl:col-span-2"><label class="form-label">Hotel name</label><input class="form-input" value="Villa Estella Fine Inn"></div>
                <div class="form-group"><label class="form-label">Primary color</label><input class="form-input" value="#B6424F"></div>
                <div class="form-group"><label class="form-label">Secondary color</label><input class="form-input" value="#B57D59"></div>
            </div>
        </section>
    @elseif ($tab === 'account')
        <section class="surface p-6 sm:p-8">
            <h2 class="text-3xl font-semibold text-stone-950">Account settings</h2>
            <div class="mt-6 grid gap-4 xl:grid-cols-2">
                <div class="form-group"><label class="form-label">Name</label><input class="form-input" value="{{ Auth::user()?->name ?? 'Admin' }}"></div>
                <div class="form-group"><label class="form-label">Email</label><input class="form-input" value="{{ Auth::user()?->email ?? '' }}"></div>
                <div class="form-group xl:col-span-2"><label class="form-label">Password</label><input class="form-input" type="password" value="********"></div>
            </div>
        </section>
    @elseif ($tab === 'landing')
        <section class="surface p-6 sm:p-8 space-y-8">
            <div>
                <span class="eyebrow">Landing page</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-semibold text-stone-950">Edit the homepage sections from one tab.</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-600">Update the words, background images, and contact details that appear on the public landing page.</p>
            </div>

            <form action="{{ route('admin.site-content.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                @foreach ($landingSections as $section)
                    <section class="rounded-[1.75rem] border border-stone-200 bg-white p-5 sm:p-6 shadow-[0_14px_36px_rgba(80,61,30,0.05)]">
                        <div>
                            <span class="eyebrow">{{ $section['title'] }}</span>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-600">{{ $section['description'] }}</p>
                        </div>

                        <div class="mt-6 grid gap-4 xl:grid-cols-2">
                            @foreach ($section['fields'] as $field)
                                <div class="form-group {{ $field['span'] ?? '' }}">
                                    <label class="form-label" for="{{ $field['key'] }}">{{ $field['label'] }}</label>
                                    @if (($field['type'] ?? 'text') === 'textarea')
                                        <textarea id="{{ $field['key'] }}" name="{{ $field['key'] }}" rows="{{ $field['rows'] ?? 3 }}" class="form-input @error($field['key']) error @enderror">{{ old($field['key'], $siteContent[$field['key']] ?? '') }}</textarea>
                                        @error($field['key']) <p class="form-error">{{ $message }}</p> @enderror
                                    @elseif(str_contains($field['key'], 'image'))
                                        @php
                                            $current = $siteContent[$field['key']] ?? '';
                                            $preview = str_starts_with($current, 'http://') || str_starts_with($current, 'https://')
                                                ? $current
                                                : (filled($current) ? (str_starts_with(ltrim($current, '/'), 'storage/') ? asset(ltrim($current, '/')) : asset('storage/' . ltrim($current, '/'))) : '');
                                        @endphp
                                        <input id="{{ $field['key'] }}_upload" name="{{ $field['key'] }}_upload" type="file" accept="image/*" class="form-input pt-2 @error($field['key'] . '_upload') error @enderror">
                                        @error($field['key'] . '_upload') <p class="form-error">{{ $message }}</p> @enderror
                                        @if($preview)
                                            <div class="mt-3">
                                                <img src="{{ $preview }}" alt="{{ $field['label'] }}" class="h-40 w-full object-cover rounded-2xl border border-stone-200">
                                            </div>
                                        @else
                                            <p class="mt-2 text-sm text-stone-500">No image uploaded yet.</p>
                                        @endif
                                    @else
                                        <input id="{{ $field['key'] }}" name="{{ $field['key'] }}" class="form-input @error($field['key']) error @enderror" value="{{ old($field['key'], $siteContent[$field['key']] ?? '') }}">
                                        @error($field['key']) <p class="form-error">{{ $message }}</p> @enderror
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <section class="rounded-[1.75rem] border border-stone-200 bg-white p-5 sm:p-6 shadow-[0_14px_36px_rgba(80,61,30,0.05)]">
                    <div>
                        <span class="eyebrow">Hero image</span>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-600">Upload your own background image for the landing page hero. This replaces the current image immediately after saving.</p>
                    </div>

                    <div class="mt-6 grid gap-4 xl:grid-cols-2">
                        <div class="form-group xl:col-span-2">
                            <label class="form-label" for="hero_background_upload">Upload new image</label>
                            <input id="hero_background_upload" name="hero_background_upload" type="file" accept="image/*" class="form-input pt-2 @error('hero_background_upload') error @enderror">
                            @error('hero_background_upload') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="xl:col-span-2">
                            <p class="text-xs uppercase tracking-[0.18em] text-stone-500 mb-2">Preview</p>
                            <img id="hero-background-preview" src="{{ $heroImagePreview }}" alt="Current hero image" class="h-56 w-full rounded-2xl object-cover border border-stone-200 {{ empty($heroImagePreview) ? 'hidden' : '' }}">
                            <div id="hero-background-empty" class="{{ empty($heroImagePreview) ? '' : 'hidden' }} rounded-2xl border border-dashed border-stone-300 bg-stone-50 px-4 py-8 text-sm text-stone-500">
                                No hero image uploaded yet.
                            </div>
                        </div>
                    </div>
                </section>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="btn-primary">Save landing page</button>
                    <a href="{{ route('home') }}" target="_blank" rel="noreferrer" class="btn-secondary">Preview site</a>
                    <a href="#top" class="btn-secondary">Back to top</a>
                </div>
            </form>
        </section>
    @else
        <section class="surface p-6 sm:p-8">
            <h2 class="text-3xl font-semibold text-stone-950">Preferences</h2>
            <div class="mt-6 grid gap-4 xl:grid-cols-2">
                <label class="flex items-center justify-between rounded-2xl border border-stone-200 bg-white p-4"><span>Dark mode</span><input type="checkbox"></label>
                <label class="flex items-center justify-between rounded-2xl border border-stone-200 bg-white p-4"><span>Email alerts</span><input type="checkbox" checked></label>
                <label class="flex items-center justify-between rounded-2xl border border-stone-200 bg-white p-4"><span>Mobile summary</span><input type="checkbox" checked></label>
                <label class="flex items-center justify-between rounded-2xl border border-stone-200 bg-white p-4"><span>Auto-approve reviews</span><input type="checkbox"></label>
            </div>
        </section>
    @endif

    @if ($tab !== 'landing')
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Back to dashboard</a>
        </div>
    @endif
</div>
@endsection
