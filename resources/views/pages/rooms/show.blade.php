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
                            <p class="eyebrow font-medium text-stone-500">Start your stay</p>
                            <h2 class="mt-4 text-4xl font-semibold text-stone-950">Reserve this room</h2>
                        </div>
                        <div class="rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-800">
                            ₱{{ number_format($room->price, 0) }}/night
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-4">
                        <div class="rounded-[1.5rem] bg-stone-50 p-4 border border-stone-100">
                            <p class="text-xs uppercase tracking-[0.2em] text-stone-500 font-medium">Capacity</p>
                            <p class="mt-2 text-lg font-semibold text-stone-950">{{ $room->capacity }} guests</p>
                        </div>
                        <div class="rounded-[1.5rem] bg-stone-50 p-4 border border-stone-100">
                            <p class="text-xs uppercase tracking-[0.2em] text-stone-500 font-medium">Status</p>
                            <p class="mt-2 text-lg font-semibold text-stone-950">{{ ucfirst($room->status) }}</p>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button type="button" id="open-room-availability-btn" class="w-full bg-brand-primary hover:bg-brand-secondary text-white px-6 py-4 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-0.5 text-center">
                            Reserve / Book Room
                        </button>
                    </div>

                    <div class="mt-6 border-t border-stone-200 pt-6 text-xs text-stone-500 space-y-2 leading-relaxed">
                        <div class="flex items-center gap-2 font-semibold text-stone-700">
                            <svg class="w-4.5 h-4.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Down Payment & Cancellation Policy
                        </div>
                        <p>
                            A 50% deposit down payment is required to confirm bookings and reservations. If cancelled within 3 days prior to your check-in date, a 50% cancellation penalty applies.
                        </p>
                    </div>
                </div>
            </div>
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
                            <div class="space-y-2">
                                <label class="form-label font-semibold">Rating</label>
                                <div class="flex items-center gap-2 pt-1" id="star-rating-container">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <button type="button" data-star="{{ $i }}" class="text-stone-300 hover:text-amber-500 hover:scale-110 transition focus:outline-none" aria-label="Rate {{ $i }} star{{ $i > 1 ? 's' : '' }}">
                                            <svg class="h-8 w-8 fill-current" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </button>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" id="rating-input" value="{{ old('rating') }}" required>
                                @error('rating')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
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

        <form action="{{ route('bookings.store', $room) }}" method="POST" class="space-y-4 p-6 sm:p-8" id="booking-form" data-no-loader>
            @csrf
            <div id="modal-availability-banner" class="hidden rounded-lg border px-4 py-3 text-sm" role="status" aria-live="polite"></div>

            <div class="grid gap-4 sm:grid-cols-4">
                <div class="form-group">
                    <label class="form-label" for="check_in">Check-in</label>
                    <input type="date" id="check_in" name="check_in" value="{{ old('check_in') }}" class="form-input" required min="{{ now()->toDateString() }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="check_out">Check-out</label>
                    <input type="date" id="check_out" name="check_out" value="{{ old('check_out') }}" class="form-input" required min="{{ now()->addDay()->toDateString() }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="guests">Guests</label>
                    <input type="number" id="guests" name="guests" value="{{ old('guests', 1) }}" class="form-input" required min="1" max="{{ $room->capacity }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="booking_type">Type</label>
                    <select id="booking_type" name="booking_type" class="form-input">
                        <option value="booking" {{ old('booking_type') === 'booking' ? 'selected' : '' }}>Booking</option>
                        <option value="reservation" {{ old('booking_type') === 'reservation' ? 'selected' : '' }}>Reservation</option>
                    </select>
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
                    <div class="flex justify-between font-bold text-brand-primary pt-1">
                        <span>50% Deposit Required</span>
                        <span>₱<span id="summary-deposit-price">0</span></span>
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

<!-- Availability Check Modal -->
<div id="room-availability-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-sm hidden transition-all duration-300" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl border border-stone-200 overflow-hidden transform transition-all scale-95 duration-300">
        <div class="absolute top-4 right-4 z-10">
            <button type="button" id="close-room-availability-modal" class="btn-icon bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-full p-2" aria-label="Close modal">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-6 sm:p-8">
            <div id="modal-form-state">
                <h3 id="modal-title" class="text-2xl font-display font-bold text-stone-900 mb-2">Check Availability</h3>
                <p class="text-sm text-stone-600 mb-6">Select your dates to verify availability for this room.</p>
                
                <form id="room-availability-form" class="space-y-4">
                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-[0.15em] mb-2">Check-in</label>
                        <input type="date" name="check_in" class="w-full border border-stone-300 rounded-lg px-4 py-3 text-sm text-stone-900 bg-white focus:ring-2 focus:ring-brand-primary focus:border-transparent outline-none transition" required min="{{ now()->toDateString() }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-[0.15em] mb-2">Check-out</label>
                        <input type="date" name="check_out" class="w-full border border-stone-300 rounded-lg px-4 py-3 text-sm text-stone-900 bg-white focus:ring-2 focus:ring-brand-primary focus:border-transparent outline-none transition" required min="{{ now()->addDay()->toDateString() }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-[0.15em] mb-2">Guests</label>
                        <input type="number" name="guests" value="1" min="1" max="{{ $room->capacity }}" class="w-full border border-stone-300 rounded-lg px-4 py-3 text-sm text-stone-900 bg-white focus:ring-2 focus:ring-brand-primary focus:border-transparent outline-none transition" required>
                    </div>
                    <button type="submit" class="w-full bg-brand-primary hover:bg-brand-secondary text-white px-6 py-3.5 rounded-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl mt-4">
                        Check Availability
                      </button>
                </form>
            </div>

            <!-- Available State -->
            <div id="modal-available-state" class="hidden">
                <div class="flex items-center gap-3 mb-6">
                    <span class="p-2 bg-emerald-100 text-emerald-700 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </span>
                    <h3 class="text-2xl font-display font-bold text-stone-900">Room is Available!</h3>
                </div>
                
                <div class="bg-stone-50 rounded-xl p-5 border border-stone-200/60 space-y-3 mb-6">
                    <p class="text-lg font-bold text-stone-900" id="modal-room-name">{{ $room->name }}</p>
                    <div class="grid grid-cols-2 gap-4 text-sm text-stone-600">
                        <div>Dates: <span class="font-semibold text-stone-950" id="modal-dates"></span></div>
                        <div>Nights: <span class="font-semibold text-stone-950" id="modal-nights"></span></div>
                        <div>Total: <span class="font-semibold text-stone-950" id="modal-total"></span></div>
                        <div>50% Deposit: <span class="font-semibold text-brand-primary animate-pulse" id="modal-deposit"></span></div>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-xs text-amber-800 space-y-2 mb-6">
                    <p class="font-semibold flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Important Policies & Rules
                    </p>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>A 50% down payment is required to secure the reservation or booking.</li>
                        <li>Cancellation penalty: Cancellations within 3 days of check-in will apply a 50% penalty of the total amount.</li>
                    </ul>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <button id="btn-reserve-action" class="btn-secondary w-full justify-center py-3 text-sm font-semibold">Reserve Room</button>
                    <button id="btn-book-action" class="btn-primary w-full justify-center py-3 text-sm font-semibold">Book Room</button>
                </div>
            </div>

            <!-- Unavailable State -->
            <div id="modal-unavailable-state" class="hidden">
                <div class="flex items-center gap-3 mb-6">
                    <span class="p-2 bg-rose-100 text-rose-700 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </span>
                    <h3 class="text-2xl font-display font-bold text-stone-900">Room is Unavailable</h3>
                </div>

                <p class="text-sm text-stone-600 mb-6" id="modal-unavailable-message">
                    The selected room is unfortunately fully booked for your dates.
                </p>

                <!-- Next Available Suggestion -->
                <div id="modal-suggestion-box" class="hidden bg-stone-50 rounded-xl p-5 border border-stone-200/60 mb-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-2">Suggested Dates</p>
                    <p class="text-sm font-medium text-stone-900 mb-3" id="suggestion-dates-text"></p>
                    <button type="button" id="btn-apply-suggestion" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-primary hover:text-brand-secondary">
                        Use suggested dates
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (() => {
        // Elements for Availability Check Modal
        const availModal = document.getElementById('room-availability-modal');
        const openAvailBtn = document.getElementById('open-room-availability-btn');
        const closeAvailBtn = document.getElementById('close-room-availability-modal');
        const availForm = document.getElementById('room-availability-form');
        const availFormState = document.getElementById('modal-form-state');
        const availAvailableState = document.getElementById('modal-available-state');
        const availUnavailableState = document.getElementById('modal-unavailable-state');

        const openAvailModal = () => {
            availFormState.classList.remove('hidden');
            availAvailableState.classList.add('hidden');
            availUnavailableState.classList.add('hidden');
            availModal.classList.remove('hidden');
            setTimeout(() => availModal.querySelector('.relative').classList.remove('scale-95'), 50);
        };

        const closeAvailModal = () => {
            availModal.querySelector('.relative').classList.add('scale-95');
            setTimeout(() => availModal.classList.add('hidden'), 200);
        };

        if (openAvailBtn) openAvailBtn.addEventListener('click', openAvailModal);
        if (closeAvailBtn) closeAvailBtn.addEventListener('click', closeAvailModal);

        if (availModal) {
            availModal.addEventListener('click', (e) => {
                if (e.target === availModal) closeAvailModal();
            });
        }

        // Handle Availability Form Submit
        if (availForm) {
            availForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const checkInInput = availForm.querySelector('input[name="check_in"]');
                const checkOutInput = availForm.querySelector('input[name="check_out"]');
                const guestsInput = availForm.querySelector('input[name="guests"]');

                const checkIn = checkInInput.value;
                const checkOut = checkOutInput.value;
                const guests = guestsInput.value;
                const roomId = "{{ $room->id }}";

                if (!checkIn || !checkOut) return;

                try {
                    const response = await fetch(`/availability/check?check_in=${checkIn}&check_out=${checkOut}&room_id=${roomId}&guests=${guests}`);
                    if (!response.ok) return;
                    const data = await response.json();

                    availFormState.classList.add('hidden');
                    if (data.available) {
                        availAvailableState.classList.remove('hidden');
                        availUnavailableState.classList.add('hidden');

                        const price = parseFloat("{{ $room->price }}");
                        const nights = Math.max(1, Math.round((new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24)));
                        const total = price * nights;
                        const deposit = total * 0.5;

                        document.getElementById('modal-dates').textContent = `${checkIn} to ${checkOut}`;
                        document.getElementById('modal-nights').textContent = `${nights} night(s)`;
                        document.getElementById('modal-total').textContent = `₱${total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                        document.getElementById('modal-deposit').textContent = `₱${deposit.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

                        const handleRedirect = (bookingType) => {
                            if (window.VillaRealtime && window.VillaRealtime.userId) {
                                // Close availability check modal
                                closeAvailModal();

                                // Populate checkout forms
                                const checkoutCheckIn = document.getElementById('check_in');
                                const checkoutCheckOut = document.getElementById('check_out');
                                const checkoutGuests = document.getElementById('guests');
                                const checkoutBookingType = document.getElementById('booking_type');

                                if (checkoutCheckIn) checkoutCheckIn.value = checkIn;
                                if (checkoutCheckOut) checkoutCheckOut.value = checkOut;
                                if (checkoutGuests) checkoutGuests.value = guests;
                                if (checkoutBookingType) checkoutBookingType.value = bookingType;

                                // Recalculate breakdown and open
                                calculateBreakdown();
                                checkAvailability();
                                openModal();
                            } else {
                                window.location.href = `/register?room_id=${roomId}&check_in=${checkIn}&check_out=${checkOut}&guests=${guests}&booking_type=${bookingType}`;
                            }
                        };

                        document.getElementById('btn-reserve-action').onclick = () => handleRedirect('reservation');
                        document.getElementById('btn-book-action').onclick = () => handleRedirect('booking');
                    } else {
                        availAvailableState.classList.add('hidden');
                        availUnavailableState.classList.remove('hidden');

                        // Suggestions
                        const suggestionBox = document.getElementById('modal-suggestion-box');
                        if (data.suggestions && data.suggestions.length > 0) {
                            suggestionBox.classList.remove('hidden');
                            const sug = data.suggestions[0];
                            document.getElementById('suggestion-dates-text').textContent = `${sug.start} to ${sug.end}`;
                            document.getElementById('btn-apply-suggestion').onclick = () => {
                                checkInInput.value = sug.start;
                                checkOutInput.value = sug.end;

                                availUnavailableState.classList.add('hidden');
                                availFormState.classList.remove('hidden');
                                availForm.dispatchEvent(new Event('submit'));
                            };
                        } else {
                            suggestionBox.classList.add('hidden');
                        }
                    }
                } catch (err) {
                    console.error(err);
                }
            });
        }

        // Checkout Modal management
        const modal = document.getElementById('booking-modal');
        const closeBtn = document.getElementById('close-booking-modal');
        const cancelBtn = document.getElementById('cancel-booking-btn');
        const bookingForm = document.getElementById('booking-form');
        const bookingTypeSelect = document.getElementById('booking_type');
        const confirmBtn = document.getElementById('confirm-booking-btn');

        const updateButtonText = () => {
            if (bookingTypeSelect && confirmBtn) {
                confirmBtn.textContent = bookingTypeSelect.value === 'reservation' ? 'Confirm Reservation' : 'Confirm Booking';
            }
        };

        if (bookingTypeSelect) {
            bookingTypeSelect.addEventListener('change', updateButtonText);
        }

        const openModal = () => {
            updateButtonText();
            window.VillaModal?.open ? window.VillaModal.open(modal) : modal.classList.remove('hidden');
        };

        const closeModal = () => {
            window.VillaModal?.close ? window.VillaModal.close(modal) : modal.classList.add('hidden');
        };

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
        }

        // Availability check for checkout modal
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
        const summaryDepositPrice = document.getElementById('summary-deposit-price');

        const roomPrice = parseFloat(meta.dataset.roomPrice || '0');

        const calculateBreakdown = () => {
            const checkIn = checkInInput.value;
            const checkOut = checkOutInput.value;

            if (!checkIn || !checkOut) {
                if (summaryRoomRate) summaryRoomRate.textContent = '0';
                if (summaryTotalPrice) summaryTotalPrice.textContent = '0';
                if (summaryDepositPrice) summaryDepositPrice.textContent = '0';
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
            if (summaryDepositPrice) {
                const depositTotal = overallTotal * 0.5;
                summaryDepositPrice.textContent = depositTotal.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }
        };

        checkInInput.addEventListener('change', () => { checkAvailability(); calculateBreakdown(); });
        checkOutInput.addEventListener('change', () => { checkAvailability(); calculateBreakdown(); });
        if (withBreakfastInput) {
            withBreakfastInput.addEventListener('change', calculateBreakdown);
        }

        // Expose modal open/populate
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
        const loadingOverlay = document.getElementById('booking-loading-overlay');
        const loadingTitle = document.getElementById('loading-overlay-title');
        const loadingDesc = document.getElementById('loading-overlay-desc');

        if (bookingForm && loadingOverlay) {
            bookingForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                banner.classList.add('hidden');
                loadingOverlay.classList.remove('hidden');

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
                        window.location.href = data.checkout_url;
                    } else {
                        window.location.reload();
                    }
                } catch (err) {
                    loadingOverlay.classList.add('hidden');
                    renderBanner(false, 'Unable to connect. Please check your connection.');
                }
            });
        }

        // Star rating logic
        const stars = document.querySelectorAll('[data-star]');
        const ratingInput = document.getElementById('rating-input');

        if (stars.length > 0 && ratingInput) {
            const updateStars = (rating) => {
                stars.forEach(star => {
                    const starValue = parseInt(star.getAttribute('data-star'), 10);
                    if (starValue <= rating) {
                        star.classList.remove('text-stone-300');
                        star.classList.add('text-amber-500');
                    } else {
                        star.classList.remove('text-amber-500');
                        star.classList.add('text-stone-300');
                    }
                });
            };

            stars.forEach(star => {
                star.addEventListener('click', () => {
                    const ratingValue = parseInt(star.getAttribute('data-star'), 10);
                    ratingInput.value = ratingValue;
                    updateStars(ratingValue);
                });

                star.addEventListener('mouseover', () => {
                    const hoverValue = parseInt(star.getAttribute('data-star'), 10);
                    updateStars(hoverValue);
                });

                star.addEventListener('mouseout', () => {
                    const currentValue = parseInt(ratingInput.value, 10) || 0;
                    updateStars(currentValue);
                });
            });

            const initialRating = parseInt(ratingInput.value, 10) || 0;
            if (initialRating > 0) {
                updateStars(initialRating);
            }
        }

        // Query parameters check for auto-opening modal
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('booking_modal') === '1') {
            const checkIn = urlParams.get('check_in');
            const checkOut = urlParams.get('check_out');
            const guests = urlParams.get('guests') || 1;
            const bookingType = urlParams.get('booking_type') || 'booking';

            if (checkIn && checkOut) {
                checkInInput.value = checkIn;
                checkOutInput.value = checkOut;
                document.getElementById('guests').value = guests;
                if (bookingTypeSelect) {
                    bookingTypeSelect.value = bookingType;
                }
                checkAvailability();
                calculateBreakdown();
                openModal();
            }
        } else {
            calculateBreakdown();
        }
    })();
</script>
@endpush
