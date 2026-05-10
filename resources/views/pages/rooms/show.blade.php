@extends('layouts.site')

@section('content')
@php
    $roomImages = [
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1400&q=80',
    ];
    $resolveRoomImage = fn (?string $image) => $image ? (str_starts_with($image, 'http') ? $image : asset('storage/' . $image)) : null;
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
        >
        </div>

        <div class="surface-strong overflow-hidden text-white shadow-[0_35px_90px_rgba(41,24,4,0.28)]">
            <div class="grid gap-0 lg:grid-cols-[1.15fr_0.85fr]">
                <div class="relative min-h-[28rem] overflow-hidden bg-stone-950" data-room-carousel data-carousel-count="{{ $roomCarouselImages->count() }}">
                    <div class="absolute inset-0">
                        @foreach ($roomCarouselImages as $index => $carouselImage)
                            <div class="absolute inset-0 transition duration-700 ease-out {{ $index === 0 ? 'opacity-100' : 'opacity-0 pointer-events-none' }}" data-carousel-slide data-carousel-index="{{ $index }}">
                                <img src="{{ $carouselImage }}" alt="{{ $room->name }} image {{ $index + 1 }}" class="h-full w-full object-cover">
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
                            <h1 class="max-w-xl text-5xl leading-[0.95] text-white sm:text-6xl">{{ $room->name }}</h1>
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
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <span class="eyebrow">Booking calendar</span>
                        <h2 class="mt-4 text-3xl font-semibold text-stone-950">Open and occupied dates</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-600">Scan the month at a glance to see when this room is open, occupied, or outside the selected month.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('rooms.show', ['room' => $room, 'month' => $calendar['previousMonth']]) }}" class="btn-secondary ajax-calendar-nav px-4 py-2 text-sm" data-no-loader aria-label="Previous month">&larr;</a>
                        <span class="rounded-full bg-stone-100 px-4 py-2 text-sm font-semibold text-stone-700">{{ $calendar['label'] }}</span>
                        <a href="{{ route('rooms.show', ['room' => $room, 'month' => $calendar['nextMonth']]) }}" class="btn-secondary ajax-calendar-nav px-4 py-2 text-sm" data-no-loader aria-label="Next month">&rarr;</a>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-7 gap-2 text-center text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-stone-400 sm:text-xs">
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Wed</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span>Sat</span>
                    <span>Sun</span>
                </div>

                <div id="room-calendar" class="mt-3 grid grid-cols-7 gap-2">
                    @foreach ($calendar['weeks'] as $week)
                        @foreach ($week as $day)
                            @php
                                $cellClasses = match ($day['status']) {
                                    'open' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    'occupied' => 'border-rose-200 bg-rose-50 text-rose-700',
                                    'unavailable' => 'border-slate-300 bg-slate-100 text-slate-500 cursor-not-allowed',
                                    'past' => 'border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed',
                                    default => 'border-stone-200 bg-stone-100 text-stone-400',
                                };
                            @endphp
                            <div class="min-h-24 rounded-2xl border p-3 transition calendar-day {{ $cellClasses }} {{ $day['isToday'] ? 'ring-2 ring-brand-primary ring-offset-2 ring-offset-white' : '' }} {{ $day['isCurrentMonth'] ? '' : 'opacity-45' }}" data-date="{{ $day['date']->format('Y-m-d') }}" data-status="{{ $day['status'] }}" data-current-month="{{ $day['isCurrentMonth'] ? '1' : '0' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-sm font-semibold">{{ $day['date']->format('j') }}</span>
                                    @if ($day['isToday'])
                                        <span class="text-xs font-semibold uppercase">Today</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </section>

            <aside class="space-y-6">
                <div class="surface p-6 sm:p-8">
                    <span class="eyebrow">Legend</span>
                    <div class="mt-5 space-y-3 text-sm text-stone-600">
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                            <span>Open for booking</span>
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
            @else
                <div class="card h-fit">
                    <span class="eyebrow">Guest access</span>
                    <p class="mt-4 text-stone-600">Please <a href="{{ route('login') }}" class="font-semibold text-amber-700">login</a> to reserve and review rooms.</p>
                </div>
            @endauth
        </div>
    </div>
</section>

<!-- Booking Modal -->
<div id="booking-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="relative max-h-[90vh] max-w-2xl w-full overflow-y-auto rounded-3xl bg-white shadow-2xl">
        <div class="sticky top-0 flex items-center justify-between border-b border-stone-200 bg-white px-6 py-5 sm:px-8">
            <h2 class="text-2xl font-semibold text-stone-950">Confirm your booking</h2>
            <button type="button" id="close-booking-modal" class="rounded-lg p-1 text-stone-500 hover:bg-stone-100">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="{{ route('bookings.store', $room) }}" method="POST" class="space-y-4 p-6 sm:p-8" id="booking-form">
            @csrf
            <div id="modal-availability-banner" class="hidden rounded-[1.5rem] border px-4 py-3 text-sm"></div>

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

            <div class="grid gap-4 sm:grid-cols-1">
                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment method</label>
                    <select id="payment_method" name="payment_method" class="form-input" required>
                        <option value="gcash" {{ old('payment_method') === 'gcash' ? 'selected' : '' }}>GCash</option>
                        <option value="landbank" {{ old('payment_method') === 'landbank' ? 'selected' : '' }}>Landbank</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3 pt-4">
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
        const carousel = document.querySelector('[data-room-carousel]');
        if (carousel) {
            const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
            const thumbs = Array.from(carousel.querySelectorAll('[data-carousel-thumb]'));
            const prevBtn = carousel.querySelector('[data-carousel-prev]');
            const nextBtn = carousel.querySelector('[data-carousel-next]');
            const total = slides.length;
            let currentIndex = 0;

            const showSlide = (index) => {
                if (!total) return;

                currentIndex = (index + total) % total;

                slides.forEach((slide, slideIndex) => {
                    const active = slideIndex === currentIndex;
                    slide.classList.toggle('opacity-100', active);
                    slide.classList.toggle('opacity-0', !active);
                    slide.classList.toggle('pointer-events-none', !active);
                });

                thumbs.forEach((thumb, thumbIndex) => {
                    thumb.classList.toggle('ring-2', thumbIndex === currentIndex);
                    thumb.classList.toggle('ring-white', thumbIndex === currentIndex);
                    thumb.classList.toggle('opacity-70', thumbIndex !== currentIndex);
                    thumb.classList.toggle('opacity-100', thumbIndex === currentIndex);
                });
            };

            if (prevBtn) {
                prevBtn.addEventListener('click', () => showSlide(currentIndex - 1));
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => showSlide(currentIndex + 1));
            }

            thumbs.forEach((thumb) => {
                thumb.addEventListener('click', () => {
                    const index = Number(thumb.dataset.carouselIndex || 0);
                    showSlide(index);
                });
            });

            showSlide(0);
        }

        // Modal management
        const modal = document.getElementById('booking-modal');
        const closeBtn = document.getElementById('close-booking-modal');
        const cancelBtn = document.getElementById('cancel-booking-btn');

        const openModal = () => {
            modal.classList.remove('hidden');
        };

        const closeModal = () => {
            modal.classList.add('hidden');
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

        checkInInput.addEventListener('change', checkAvailability);
        checkOutInput.addEventListener('change', checkAvailability);
        setInterval(checkAvailability, 30000);

        // Expose modal open/populate for calendar
        window.bookingModal = {
            open: openModal,
            setDates: (checkIn, checkOut) => {
                checkInInput.value = checkIn;
                checkOutInput.value = checkOut;
                checkAvailability();
            }
        };
    })();
</script>
<script>
    (() => {
        // Calendar interactivity: click to select check-in/check-out range and open modal
        const dayNodes = Array.from(document.querySelectorAll('.calendar-day'));
        const checkIn = document.getElementById('check_in');
        const checkOut = document.getElementById('check_out');

        if (!dayNodes.length || !checkIn || !checkOut) return;

        let rangeStart = null;
        let rangeEnd = null;

        const parseDate = (d) => new Date(d + 'T00:00:00');
        const formatDate = (dt) => dt.toISOString().slice(0,10);

        const clearSelectionVisual = () => {
            dayNodes.forEach(n => {
                n.classList.remove('selected-day');
                n.classList.remove('in-range');
                n.style.boxShadow = '';
                n.style.background = '';
            });
        };

        const applySelectionVisual = () => {
            clearSelectionVisual();
            if (!rangeStart) return;
            const start = parseDate(rangeStart);
            const end = rangeEnd ? parseDate(rangeEnd) : start;

            dayNodes.forEach(n => {
                const d = parseDate(n.dataset.date);
                if (d < start || d > end) return;
                if (d.getTime() === start.getTime()) {
                    n.classList.add('selected-day');
                    n.style.boxShadow = '0 0 0 3px rgba(59,130,246,0.12)';
                } else if (d.getTime() === end.getTime()) {
                    n.classList.add('selected-day');
                    n.style.boxShadow = '0 0 0 3px rgba(59,130,246,0.12)';
                } else {
                    n.classList.add('in-range');
                    n.style.background = 'rgba(59,130,246,0.06)';
                }
            });
        };

        const isSelectable = (node) => {
            return node.dataset.status === 'open' && node.dataset.currentMonth === '1';
        };

        dayNodes.forEach(node => {
            node.style.cursor = isSelectable(node) ? 'pointer' : 'default';
            node.addEventListener('click', (e) => {
                if (!isSelectable(node)) return;

                const clicked = node.dataset.date;

                if (!rangeStart || (rangeStart && rangeEnd)) {
                    // start new selection
                    rangeStart = clicked;
                    rangeEnd = null;
                } else {
                    // set end
                    const a = parseDate(rangeStart);
                    const b = parseDate(clicked);
                    if (b < a) {
                        rangeEnd = rangeStart;
                        rangeStart = clicked;
                    } else {
                        rangeEnd = clicked;
                    }
                }

                // If end remains null, set check-out to next day
                if (!rangeEnd) {
                    const next = new Date(parseDate(rangeStart));
                    next.setDate(next.getDate() + 1);
                    rangeEnd = formatDate(next);
                }

                // Populate modal form and open modal
                if (window.bookingModal) {
                    window.bookingModal.setDates(rangeStart, rangeEnd);
                    window.bookingModal.open();
                }

                applySelectionVisual();
            });
        });

        // If inputs already have values, reflect on calendar
        if (checkIn.value) {
            rangeStart = checkIn.value;
        }
        if (checkOut.value) {
            rangeEnd = checkOut.value;
        }
        if (rangeStart) applySelectionVisual();
    })();
</script>
@endpush
