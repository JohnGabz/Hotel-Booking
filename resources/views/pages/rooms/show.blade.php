@extends('layouts.site')

@section('content')
@php
    $roomImages = [
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1400&q=80',
    ];
    $resolveRoomImage = fn (?string $image) => \App\Support\ImageStorage::url($image, '');
    $roomCarouselImages = collect($room->images ?? [])
        ->map(fn ($image) => $resolveRoomImage($image))
        ->filter()
        ->values();

    if ($roomCarouselImages->isEmpty()) {
        $roomCarouselImages = collect([$roomImages[$room->id % count($roomImages)]]);
    }

    $roomImage = $roomCarouselImages->first();
    $amenities = collect($room->amenities ?? []);
@endphp

<section class="section-shell pt-8 sm:pt-10">
    <div class="site-shell">
        <div
            id="room-availability-meta"
            data-availability-url="{{ route('rooms.availability', $room) }}"
            data-form-enabled="{{ $room->status === 'available' ? '1' : '0' }}"
            data-room-price="{{ $room->price }}"
            data-room-capacity="{{ $room->capacity }}"
        >
        </div>

        <div class="surface-strong overflow-hidden text-white shadow-[0_35px_90px_rgba(41,24,4,0.28)]">
            <div class="grid gap-0 lg:grid-cols-[1.15fr_0.85fr]">
                <div class="relative min-h-[28rem] overflow-hidden bg-stone-950" data-room-carousel data-carousel-count="{{ $roomCarouselImages->count() }}">
                    <div class="absolute inset-0">
                        @foreach ($roomCarouselImages as $index => $carouselImage)
                            <div class="absolute inset-0 transition duration-700 ease-out {{ $index === 0 ? 'opacity-100' : 'opacity-0 pointer-events-none' }}" data-carousel-slide data-carousel-index="{{ $index }}">
                                <img src="{{ $carouselImage }}" alt="{{ $room->name }} image {{ $index + 1 }}" class="h-full w-full object-cover" loading="{{ $index === 0 ? 'eager' : 'lazy' }}" decoding="async" sizes="(min-width: 1024px) 60vw, 100vw">
                            </div>
                        @endforeach
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-stone-950/88 via-stone-950/35 to-stone-950/15"></div>
                    <div class="relative flex h-full flex-col justify-between p-6 sm:p-8 lg:p-10">
                        <div class="flex flex-wrap gap-3">
                            <span class="eyebrow border-white/20 bg-white/10 text-white">{{ ucfirst($room->status) }}</span>
                            <span class="eyebrow border-white/20 bg-white/10 text-white">{{ $room->capacity }} guests</span>
                        </div>

                        <div class="max-w-2xl space-y-5">
                            <p class="text-sm uppercase tracking-[0.35em] text-stone-200">Room experience</p>
                            <h1 class="max-w-xl responsive-title text-white">{{ $room->type_label }}</h1>
                            <p class="max-w-2xl text-base leading-7 text-stone-200 sm:text-lg">{{ $room->description }}</p>
                        </div>
                    </div>

                    @if ($roomCarouselImages->count() > 1)
                        <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-3 bg-stone-950/35 px-4 py-4 backdrop-blur-sm sm:px-6" data-carousel-controls>
                            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/25 bg-white/10 text-white transition hover:bg-white/20" data-carousel-prev aria-label="Previous image">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </button>

                            <div class="flex items-center gap-2 overflow-x-auto px-1 py-1" data-carousel-thumbs>
                                @foreach ($roomCarouselImages as $index => $carouselImage)
                                    <button type="button" class="group relative h-16 w-24 overflow-hidden rounded-xl border border-white/20 bg-white/10 transition {{ $index === 0 ? 'ring-2 ring-white' : 'opacity-70 hover:opacity-100' }}" data-carousel-thumb data-carousel-index="{{ $index }}" aria-label="Show image {{ $index + 1 }}">
                                        <img src="{{ $carouselImage }}" alt="Thumbnail {{ $index + 1 }}" class="h-full w-full object-cover">
                                        <span class="absolute inset-0 bg-stone-950/0 group-hover:bg-stone-950/10"></span>
                                    </button>
                                @endforeach
                            </div>

                            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/25 bg-white/10 text-white transition hover:bg-white/20" data-carousel-next aria-label="Next image">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                    @endif
                </div>

                <div class="bg-white p-6 text-stone-900 sm:p-8 lg:p-10">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="eyebrow">Start your stay</p>
                            <h2 class="mt-4 text-4xl text-stone-950">Reserve this room</h2>
                        </div>
                        <div class="rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-800">
                            ₱{{ number_format($room->price, 0) }}/night
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-[1.5rem] bg-stone-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Capacity</p>
                            <p class="mt-2 text-lg font-semibold text-stone-950">{{ $room->capacity }} guests</p>
                        </div>
                        <div class="rounded-[1.5rem] bg-stone-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Status</p>
                            <p class="mt-2 text-lg font-semibold text-stone-950">{{ ucfirst($room->status) }}</p>
                        </div>
                        <div class="rounded-[1.5rem] bg-stone-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Rating</p>
                            <p class="mt-2 text-lg font-semibold text-stone-950">4.9 / 5</p>
                        </div>
                    </div>

                    <div class="rounded-[1.5rem] border-2 border-dashed border-stone-300 bg-stone-50 px-6 py-8 text-center">
                        <p class="text-lg font-semibold text-stone-700">Select dates on the calendar</p>
                        <p class="mt-2 text-sm text-stone-600">Click on an open date to start your booking, then add your contact details in the modal that appears.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10 grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <section class="surface p-6 sm:p-8">
                <div
                    id="room-calendar"
                    class="room-calendar-shell"
                    data-calendar-swipe
                    data-calendar-prev-url="{{ route('rooms.show', ['room' => $room, 'month' => $calendar['previousMonth']]) }}"
                    data-calendar-next-url="{{ route('rooms.show', ['room' => $room, 'month' => $calendar['nextMonth']]) }}"
                >
                    <div class="room-calendar-header">
                        <div>
                            <span class="eyebrow">Booking calendar</span>
                            <h2 class="mt-4 text-3xl font-semibold text-stone-950">Open and occupied dates</h2>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-600">Scan the month to see when at least one physical room under this room type is open.</p>
                        </div>
                        <div class="room-calendar-nav">
                            <a href="{{ route('rooms.show', ['room' => $room, 'month' => $calendar['previousMonth']]) }}" class="btn-secondary ajax-calendar-nav px-3 py-2 text-sm" data-no-loader aria-label="Previous month">&larr;</a>
                            <span class="room-calendar-month">{{ $calendar['label'] }}</span>
                            <a href="{{ route('rooms.show', ['room' => $room, 'month' => $calendar['nextMonth']]) }}" class="btn-secondary ajax-calendar-nav px-3 py-2 text-sm" data-no-loader aria-label="Next month">&rarr;</a>
                        </div>
                    </div>

                    <div class="room-calendar-scroll">
                        <div class="room-calendar-track">
                            <div class="room-calendar-weekdays" role="presentation" aria-hidden="true">
                                <span>Mon</span>
                                <span>Tue</span>
                                <span>Wed</span>
                                <span>Thu</span>
                                <span>Fri</span>
                                <span>Sat</span>
                                <span>Sun</span>
                            </div>

                            <div class="room-calendar-grid" role="grid" aria-label="Room booking calendar">
                                @foreach ($calendar['weeks'] as $week)
                                    @foreach ($week as $day)
                                        @php
                                            $cellClasses = match ($day['status']) {
                                                'open' => 'border-emerald-300 bg-emerald-50 text-emerald-700',
                                                'occupied' => 'border-[#B6424F]/45 bg-[#B6424F]/12 text-[#8F3340]',
                                                'unavailable' => 'border-[#9FAAAC]/45 bg-[#9FAAAC]/18 text-[#56656A]',
                                                'past' => 'border-[#989B88]/45 bg-[#989B88]/18 text-[#6D715F]',
                                                default => 'border-stone-300 bg-stone-100 text-stone-500',
                                            };

                                            $statusDotClasses = match ($day['status']) {
                                                'open' => 'bg-emerald-500',
                                                'occupied' => 'bg-[#B6424F]',
                                                'unavailable' => 'bg-[#9FAAAC]',
                                                'past' => 'bg-[#989B88]',
                                                default => 'bg-stone-400',
                                            };

                                            $statusLabel = match ($day['status']) {
                                                'open' => 'Open',
                                                'occupied' => 'Booked',
                                                'unavailable' => 'Closed',
                                                'past' => 'Past',
                                                default => 'Other',
                                            };

                                            $ariaStatusLabel = match ($day['status']) {
                                                'open' => 'Open',
                                                'occupied' => 'Occupied',
                                                'unavailable' => 'Unavailable',
                                                'past' => 'Past',
                                                default => 'Other month',
                                            };
                                        @endphp
                                        <button
                                            type="button"
                                            class="room-calendar-cell {{ $cellClasses }} {{ $day['isToday'] ? 'ring-2 ring-[#B6424F] ring-offset-1 ring-offset-white' : '' }} {{ $day['isCurrentMonth'] ? '' : 'opacity-50' }}"
                                            data-calendar-day
                                            data-date="{{ $day['date']->format('Y-m-d') }}"
                                            data-status="{{ $day['status'] }}"
                                            data-current-month="{{ $day['isCurrentMonth'] ? '1' : '0' }}"
                                            data-selectable="{{ $day['status'] === 'open' && $day['isCurrentMonth'] ? '1' : '0' }}"
                                            role="gridcell"
                                            aria-label="{{ $day['date']->format('F j, Y') }} - {{ $ariaStatusLabel }}"
                                            aria-disabled="{{ $day['status'] === 'open' && $day['isCurrentMonth'] ? 'false' : 'true' }}"
                                            title="{{ $day['date']->format('M j, Y') }} - {{ $ariaStatusLabel }}"
                                        >
                                            <div class="room-calendar-cell-top">
                                                <span class="room-calendar-day-number">{{ $day['date']->format('j') }}</span>
                                            </div>
                                            <div class="room-calendar-cell-status">
                                                <span class="room-calendar-status-dot {{ $statusDotClasses }}" aria-hidden="true"></span>
                                                <span class="room-calendar-status-label">
                                                    @if ($day['status'] === 'open')
                                                        @if (auth()->check() && auth()->user()->is_admin)
                                                            {{ $day['availableCount'] }} open
                                                        @else
                                                            Open
                                                        @endif
                                                    @else
                                                        {{ $statusLabel }}
                                                    @endif
                                                </span>
                                            </div>
                                        </button>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div id="calendar-selected-bar" class="calendar-selected-bar hidden" aria-live="polite">
                    <p class="text-sm font-semibold text-stone-900">Selected date: <span id="calendar-selected-date">None</span></p>
                    <p class="text-xs text-stone-600">Choose an open date to start booking.</p>
                </div>
            </section>

            <aside class="space-y-6">
                <div class="surface p-6 sm:p-8">
                    <span class="eyebrow">Legend</span>
                    <div class="mt-5 space-y-3 text-sm text-stone-600">
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                            <span>At least one physical room is open for booking</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full bg-rose-500"></span>
                            <span>Occupied by a confirmed stay</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full bg-slate-400"></span>
                            <span>Room unavailable (maintenance or closed)</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full bg-gray-400"></span>
                            <span>Past dates</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full bg-stone-400"></span>
                            <span>Outside the selected month</span>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-4">
                        <div class="rounded-[1.25rem] bg-stone-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Open days</p>
                            <p class="mt-2 text-2xl font-semibold text-stone-950">{{ $calendar['summary']['open'] }}</p>
                        </div>
                        <div class="rounded-[1.25rem] bg-stone-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Occupied</p>
                            <p class="mt-2 text-2xl font-semibold text-stone-950">{{ $calendar['summary']['occupied'] }}</p>
                        </div>
                        <div class="rounded-[1.25rem] bg-stone-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Unavailable</p>
                            <p class="mt-2 text-2xl font-semibold text-stone-950">{{ $calendar['summary']['unavailable'] }}</p>
                        </div>
                        <div class="rounded-[1.25rem] bg-stone-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Past</p>
                            <p class="mt-2 text-2xl font-semibold text-stone-950">{{ $calendar['summary']['past'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="surface p-6 sm:p-8">
                    <span class="eyebrow">Booked ranges</span>
                    @if ($calendar['bookings']->isEmpty())
                        <p class="mt-4 text-sm text-stone-600">No confirmed bookings overlap with this month yet.</p>
                    @else
                        <div class="mt-5 space-y-3">
                            @foreach ($calendar['bookings']->take(4) as $booking)
                                <div class="rounded-[1.25rem] border border-stone-200 bg-stone-50 p-4">
                                    <p class="text-sm font-semibold text-stone-950">{{ $booking['check_in']->format('M j') }} - {{ $booking['check_out']->format('M j, Y') }}</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.18em] text-stone-500">Confirmed stay</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </aside>
        </div>

        <div class="mt-10 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="space-y-8">
                <div class="card">
                    <span class="eyebrow">Room details</span>
                    <p class="mt-4 text-base leading-7 text-stone-600">{{ $room->description }}</p>
                    @if ($amenities->isNotEmpty())
                        <div class="mt-5 flex flex-wrap gap-2">
                            @foreach ($amenities as $amenity)
                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-600">{{ $amenity }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="card">
                    <span class="eyebrow">Guest reviews</span>
                    @if ($room->reviews->isEmpty())
                        <p class="mt-4 text-stone-600">No reviews yet. Be the first guest to leave feedback.</p>
                    @else
                        <div class="mt-5 space-y-4">
                            @foreach ($room->reviews as $review)
                                <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-stone-950">{{ $review->user->name }}</p>
                                            <p class="text-sm text-stone-500">{{ $review->created_at->format('F j, Y') }}</p>
                                        </div>
                                        <span class="badge-primary">{{ $review->rating }} / 5</span>
                                    </div>
                                    <p class="mt-3 text-sm leading-7 text-stone-600">{{ $review->comment }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @auth
                @if ($canReview)
                    <div class="card h-fit">
                        <span class="eyebrow">Leave a review</span>
                        <form action="{{ route('reviews.store', $room) }}" method="POST" class="mt-5 space-y-4">
                            @csrf
                            <div class="form-group">
                                <label class="form-label" for="rating">Rating</label>
                                <select id="rating" name="rating" class="form-input" required>
                                    @for ($star = 5; $star >= 1; $star--)
                                        <option value="{{ $star }}" {{ old('rating') == $star ? 'selected' : '' }}>{{ $star }} stars</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="comment">Comment</label>
                                <textarea id="comment" name="comment" rows="5" class="form-input" required>{{ old('comment') }}</textarea>
                                @error('comment') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" class="btn-secondary w-full">Submit review</button>
                        </form>
                    </div>
                @else
                    <div class="card h-fit">
                        <span class="eyebrow">Reviews</span>
                        <p class="mt-4 text-stone-600">Submit a review after your confirmed stay at this room.</p>
                    </div>
                @endif
            @endauth
        </div>
    </div>
</section>

<!-- Booking Modal -->
<div id="booking-modal" class="hidden fixed inset-0 z-50 overflow-y-auto items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="booking-modal-title" tabindex="-1">
    <div class="modal-panel max-w-2xl safe-scroll relative z-10 mt-auto sm:my-auto">
        <!-- Processing Overlay -->
        <div id="booking-loading-overlay" class="hidden absolute inset-0 z-50 flex flex-col items-center justify-center bg-white/95 p-6 text-center">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-stone-200 border-t-[#B6424F]"></div>
            <h3 id="loading-overlay-title" class="mt-6 text-lg font-semibold text-stone-900">Processing your booking...</h3>
            <p id="loading-overlay-desc" class="mt-2 text-sm text-stone-500 max-w-sm">We are creating your reservation and redirecting you to Xendit's secure payment checkout. Please do not close or refresh this page.</p>
        </div>
        <div class="sticky top-0 flex items-center justify-between border-b border-stone-200 bg-white px-6 py-5 sm:px-8">
            <h2 id="booking-modal-title" class="text-2xl font-semibold text-stone-950">Confirm your booking</h2>
            <button type="button" id="close-booking-modal" class="btn-icon text-stone-500" aria-label="Close booking form">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="{{ route('bookings.store', $room) }}" method="POST" class="space-y-4 p-6 sm:p-8" id="booking-form">
            @csrf
            <div id="modal-availability-banner" class="hidden rounded-lg border px-4 py-3 text-sm" role="status" aria-live="polite"></div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="check_in">Check-in</label>
                    <input type="date" id="check_in" name="check_in" value="{{ old('check_in') }}" class="form-input" required min="{{ now()->toDateString() }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="check_out">Check-out</label>
                    <input type="date" id="check_out" name="check_out" value="{{ old('check_out') }}" class="form-input" required min="{{ now()->addDay()->toDateString() }}">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-1">
                <div class="form-group">
                    <label class="form-label" for="contact_name">Contact name</label>
                    <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name', auth()->user()?->name ?? '') }}" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="contact_email">Contact email</label>
                    <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', auth()->user()?->email ?? '') }}" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="contact_phone">Contact phone</label>
                    <input type="tel" id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}" class="form-input" required placeholder="e.g. +63 912 345 6789">
                </div>
            </div>

            <input type="hidden" id="payment_method" name="payment_method" value="xendit">

            <div class="form-group pt-2">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" id="with_breakfast" name="with_breakfast" value="1" {{ old('with_breakfast') ? 'checked' : '' }} class="h-5 w-5 rounded border-stone-300 text-brand-primary focus:ring-brand-primary">
                    <span class="text-sm font-medium text-stone-905 text-stone-900">
                        Include Breakfast <span class="text-xs text-stone-500 font-normal">(₱50 x room capacity per night)</span>
                    </span>
                </label>
            </div>

            <!-- Payment & Amount Breakdown -->
            <div class="rounded-xl border border-stone-200 bg-stone-50 p-4 space-y-3.5">
                <h4 class="text-xs uppercase tracking-[0.18em] text-stone-500 font-bold">Billing Summary</h4>
                <div class="space-y-1.5 text-sm text-stone-600">
                    <div class="flex justify-between">
                        <span>Room rate (<span id="summary-nights-label">1 night</span>)</span>
                        <span>₱<span id="summary-room-rate">0</span></span>
                    </div>
                    <div id="summary-breakfast-row" class="flex justify-between hidden">
                        <span>Breakfast charge</span>
                        <span>₱<span id="summary-breakfast-charge">0</span></span>
                    </div>
                    <div class="flex justify-between border-t border-stone-200 pt-2 font-bold text-stone-900">
                        <span>Total amount</span>
                        <span>₱<span id="summary-total-price">0</span></span>
                    </div>
                </div>

                <div class="border-t border-stone-200 pt-3 mt-1 space-y-2">
                    <div class="flex items-center gap-2 text-xs font-semibold text-stone-700">
                        <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <span>Secure Checkout via Xendit</span>
                    </div>
                    <p class="text-[11px] leading-relaxed text-stone-500">
                        Confirming your booking will redirect you to Xendit's secure payment portal. You can pay via GCash, credit/debit card, or bank transfer. The reservation will be confirmed automatically once payment is verified.
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-3 pt-4 sm:flex-row">
                <button type="button" id="cancel-booking-btn" class="btn-secondary flex-1">Cancel</button>
                <button type="submit" id="confirm-booking-btn" class="btn-primary flex-1 py-3" @if ($room->status !== 'available') disabled @endif>
                    Confirm booking
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (() => {
        // Modal management
        const modal = document.getElementById('booking-modal');
        const closeBtn = document.getElementById('close-booking-modal');
        const cancelBtn = document.getElementById('cancel-booking-btn');

        const openModal = () => {
            window.VillaModal?.open ? window.VillaModal.open(modal) : modal.classList.remove('hidden');
        };

        const closeModal = () => {
            window.VillaModal?.close ? window.VillaModal.close(modal) : modal.classList.add('hidden');
        };

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        // Close on backdrop click
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
        }

        // Availability check
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        const banner = document.getElementById('modal-availability-banner');
        const button = document.getElementById('confirm-booking-btn');
        const meta = document.getElementById('room-availability-meta');

        if (!meta) return;

        const availabilityUrl = meta.dataset.availabilityUrl || '';
        const formEnabledByServer = meta.dataset.formEnabled === '1';

        if (!checkInInput || !checkOutInput || !banner || !button) return;

        const renderBanner = (available, message) => {
            banner.classList.remove('hidden', 'border-rose-200', 'bg-rose-50', 'text-rose-700', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-700');

            if (available) {
                banner.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
            } else {
                banner.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-700');
            }

            banner.textContent = message;
            button.disabled = !available || !formEnabledByServer;
        };

        const checkAvailability = async () => {
            const checkIn = checkInInput.value;
            const checkOut = checkOutInput.value;

            if (!checkIn || !checkOut) {
                banner.classList.add('hidden');
                button.disabled = !formEnabledByServer;
                return;
            }

            const query = new URLSearchParams({ check_in: checkIn, check_out: checkOut }).toString();

            try {
                const transport = window.VillaLoader?.fetch || window.fetch;
                const response = await transport(`${availabilityUrl}?${query}`);
                if (!response.ok) return;

                const data = await response.json();
                renderBanner(Boolean(data.available), data.message || 'Availability status updated.');
            } catch (error) {
                console.error(error);
            }
        };

        const withBreakfastInput = document.getElementById('with_breakfast');
        const summaryNightsLabel = document.getElementById('summary-nights-label');
        const summaryRoomRate = document.getElementById('summary-room-rate');
        const summaryBreakfastRow = document.getElementById('summary-breakfast-row');
        const summaryBreakfastCharge = document.getElementById('summary-breakfast-charge');
        const summaryTotalPrice = document.getElementById('summary-total-price');

        const roomPrice = parseFloat(meta.dataset.roomPrice || '0');

        const calculateBreakdown = () => {
            const checkIn = checkInInput.value;
            const checkOut = checkOutInput.value;

            if (!checkIn || !checkOut) {
                if (summaryRoomRate) summaryRoomRate.textContent = '0';
                if (summaryTotalPrice) summaryTotalPrice.textContent = '0';
                return;
            }

            const inDate = new Date(checkIn);
            const outDate = new Date(checkOut);
            const timeDiff = outDate.getTime() - inDate.getTime();
            const nights = Math.max(1, Math.ceil(timeDiff / (1000 * 3600 * 24)));

            if (summaryNightsLabel) {
                summaryNightsLabel.textContent = `${nights} night${nights > 1 ? 's' : ''}`;
            }

            const roomTotal = roomPrice * nights;
            if (summaryRoomRate) {
                summaryRoomRate.textContent = roomTotal.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }

            const withBreakfast = withBreakfastInput && withBreakfastInput.checked;
            const roomCapacity = parseFloat(meta.dataset.roomCapacity || '1');
            const breakfastTotal = withBreakfast ? (50 * roomCapacity * nights) : 0;

            if (summaryBreakfastRow) {
                summaryBreakfastRow.classList.toggle('hidden', !withBreakfast);
            }
            if (summaryBreakfastCharge) {
                summaryBreakfastCharge.textContent = breakfastTotal.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }

            const overallTotal = roomTotal + breakfastTotal;
            if (summaryTotalPrice) {
                summaryTotalPrice.textContent = overallTotal.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }
        };

        checkInInput.addEventListener('change', () => { checkAvailability(); calculateBreakdown(); });
        checkOutInput.addEventListener('change', () => { checkAvailability(); calculateBreakdown(); });
        if (withBreakfastInput) {
            withBreakfastInput.addEventListener('change', calculateBreakdown);
        }
        setInterval(checkAvailability, 30000);

        // Expose modal open/populate for calendar
        window.bookingModal = {
            open: openModal,
            setDates: (checkIn, checkOut) => {
                checkInInput.value = checkIn;
                checkOutInput.value = checkOut;
                checkAvailability();
                calculateBreakdown();
            }
        };

        // AJAX form submit with loading overlay
        const bookingForm = document.getElementById('booking-form');
        const loadingOverlay = document.getElementById('booking-loading-overlay');
        const loadingTitle = document.getElementById('loading-overlay-title');
        const loadingDesc = document.getElementById('loading-overlay-desc');

        if (bookingForm && loadingOverlay) {
            bookingForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                // Clear previous errors
                banner.classList.add('hidden');

                // Show loading overlay
                loadingOverlay.classList.remove('hidden');
                if (loadingTitle) loadingTitle.textContent = "Processing your booking...";
                if (loadingDesc) loadingDesc.textContent = "We are creating your reservation and setting up your secure checkout session. Please do not close this window.";

                const formData = new FormData(bookingForm);

                try {
                    const response = await fetch(bookingForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        loadingOverlay.classList.add('hidden');
                        const errors = data.errors ? Object.values(data.errors).flat() : [data.message || 'An error occurred. Please try again.'];
                        renderBanner(false, errors.join(' '));
                        return;
                    }

                    if (data.checkout_url) {
                        if (loadingTitle) loadingTitle.textContent = "Redirecting to payment...";
                        if (loadingDesc) loadingDesc.textContent = "We are transferring you to Xendit's secure payment gateway. Please wait...";
                        window.location.href = data.checkout_url;
                    } else {
                        window.location.reload();
                    }
                } catch (err) {
                    loadingOverlay.classList.add('hidden');
                    renderBanner(false, 'Unable to connect. Please check your internet connection and try again.');
                }
            });
        }

        // Run initially
        calculateBreakdown();
    })();
</script>
@endpush
