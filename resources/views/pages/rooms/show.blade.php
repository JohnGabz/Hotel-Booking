@extends('layouts.site')

@section('content')
@php
    $roomImages = [
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1400&q=80',
    ];
    $roomImage = $roomImages[$room->id % count($roomImages)];
    $amenities = collect($room->amenities ?? []);
@endphp

<section class="section-shell pt-8 sm:pt-10">
    <div class="site-shell">
        <div
            id="room-availability-meta"
            data-availability-url="{{ route('rooms.availability', $room) }}"
            data-form-enabled="{{ ($room->status === 'available' && (!auth()->check() || auth()->user()->hasVerifiedEmail())) ? '1' : '0' }}"
        ></div>

        <div class="surface-strong overflow-hidden text-white shadow-[0_35px_90px_rgba(41,24,4,0.28)]">
            <div class="grid gap-0 lg:grid-cols-[1.15fr_0.85fr]">
                <div class="relative min-h-[28rem] overflow-hidden">
                    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $roomImage }}');"></div>
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

                    <div id="availability-banner" class="hidden rounded-[1.5rem] border px-4 py-3 text-sm"></div>

                    <form action="{{ route('bookings.store', $room) }}" method="POST" class="mt-8 space-y-4" id="booking-form">
                        @csrf
                        @if ($room->status !== 'available')
                            <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                This room is currently unavailable. You can still review the details or explore other rooms.
                            </div>
                        @endif

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="form-group">
                                <label class="form-label" for="check_in">Check-in</label>
                                <input type="date" id="check_in" name="check_in" value="{{ old('check_in') }}" class="form-input" required>
                                @error('check_in') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="check_out">Check-out</label>
                                <input type="date" id="check_out" name="check_out" value="{{ old('check_out') }}" class="form-input" required>
                                @error('check_out') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="form-group">
                                <label class="form-label" for="guests">Guests</label>
                                <input type="number" id="guests" name="guests" min="1" max="{{ $room->capacity }}" value="{{ old('guests', 1) }}" class="form-input" required>
                                @error('guests') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="payment_method">Payment method</label>
                                <select id="payment_method" name="payment_method" class="form-input" required>
                                    <option value="gcash" {{ old('payment_method') === 'gcash' ? 'selected' : '' }}>GCash</option>
                                    <option value="landbank" {{ old('payment_method') === 'landbank' ? 'selected' : '' }}>Landbank</option>
                                </select>
                                @error('payment_method') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        @auth
                            @if (! auth()->user()->hasVerifiedEmail())
                                <div class="rounded-[1.5rem] border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                    Please verify your email before confirming a booking.
                                    <a href="{{ route('verification.notice') }}" class="font-semibold underline">Verify now</a>
                                </div>
                            @endif
                        @endauth

                        <button type="submit" id="confirm-booking-btn" class="btn-primary w-full py-3.5 text-base" @if ($room->status !== 'available' || (auth()->check() && !auth()->user()->hasVerifiedEmail())) disabled @endif>
                            Confirm booking
                        </button>
                    </form>
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
@endsection

@push('scripts')
<script>
    (() => {
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        const banner = document.getElementById('availability-banner');
        const button = document.getElementById('confirm-booking-btn');
        const meta = document.getElementById('room-availability-meta');

        if (!meta) {
            return;
        }

        const availabilityUrl = meta.dataset.availabilityUrl || '';
        const formEnabledByServer = meta.dataset.formEnabled === '1';

        if (!checkInInput || !checkOutInput || !banner || !button) {
            return;
        }

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
                const response = await fetch(`${availabilityUrl}?${query}`);
                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                renderBanner(Boolean(data.available), data.message || 'Availability status updated.');
            } catch (error) {
                console.error(error);
            }
        };

        checkInInput.addEventListener('change', checkAvailability);
        checkOutInput.addEventListener('change', checkAvailability);
        setInterval(checkAvailability, 30000);
        checkAvailability();
    })();
</script>
@endpush
