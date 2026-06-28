@extends('layouts.site')

@section('content')
<section class="section-shell pt-8 sm:pt-10">
    <div class="site-shell" data-realtime-fragment="guest-dashboard">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <span class="eyebrow">My dashboard</span>
                <h1 class="mt-4 responsive-title">Your reservations at a glance.</h1>
            </div>
            <a href="{{ route('rooms.index') }}" class="btn-primary">Book a room</a>
        </div>

        <div class="mt-10 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card">
                <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Bookings</p>
                <p class="mt-4 text-4xl font-semibold text-stone-950">{{ $bookings->count() }}</p>
            </div>
            <div class="card">
                <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Upcoming</p>
                <p class="mt-4 text-4xl font-semibold text-stone-950">{{ $bookings->where('status', 'confirmed')->count() }}</p>
            </div>
            <div class="card">
                <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Rooms to review</p>
                <p class="mt-4 text-4xl font-semibold text-stone-950">{{ $recentRooms->count() }}</p>
            </div>
            <div class="card">
                <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Guest access</p>
                <p class="mt-4 text-4xl font-semibold text-stone-950">Active</p>
            </div>
        </div>

        <div class="mt-10 grid gap-8 lg:grid-cols-[1.15fr_0.85fr]">
            <div class="card">
                <span class="eyebrow">Upcoming bookings</span>
                @if ($bookings->isEmpty())
                    <p class="mt-4 text-stone-600">You don’t have any reservations yet. Start by browsing rooms.</p>
                @else
                    <div class="mt-5 space-y-4">
                        @foreach ($bookings as $booking)
                            <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-lg font-semibold text-stone-950">{{ $booking->room->name }}</p>
                                        <p class="mt-1 text-sm text-stone-500">{{ $booking->check_in->format('M j') }} — {{ $booking->check_out->format('M j, Y') }}</p>
                                    </div>
                                    <span class="badge-primary">{{ ucfirst($booking->status) }}</span>
                                </div>
                                <div class="mt-4 flex flex-col gap-2 text-sm text-stone-600 sm:flex-row sm:items-center sm:justify-between">
                                    <p>{{ $booking->guests }} guest(s) · ₱{{ number_format($booking->total, 0) }}</p>
                                    <p class="uppercase tracking-[0.2em]">{{ strtoupper($booking->payment_method) }} · {{ strtoupper($booking->payment_status) }}</p>
                                </div>

                                @if (in_array($booking->payment_status, ['pending', 'failed'], true))
                                    <div class="mt-4 grid gap-3 rounded-[1rem] border border-stone-200 bg-white p-4">
                                        <form action="{{ route('bookings.pay', $booking) }}" method="POST" class="flex gap-2 items-center">
                                            @csrf
                                            <button type="submit" class="btn-primary">Pay now with Xendit test mode</button>
                                        </form>

                                        <form action="{{ route('bookings.payment-proof', $booking) }}" method="POST" enctype="multipart/form-data" class="grid gap-3">
                                            @csrf
                                            <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Or upload payment proof</p>
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <div class="form-group">
                                                    <label class="form-label" for="payment_reference_{{ $booking->id }}">Reference number</label>
                                                    <input id="payment_reference_{{ $booking->id }}" name="payment_reference" class="form-input" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="payment_proof_{{ $booking->id }}">Proof image</label>
                                                    <input id="payment_proof_{{ $booking->id }}" name="payment_proof" type="file" accept="image/*" class="form-input" required>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn-secondary">Submit payment proof</button>
                                        </form>
                                    </div>
                                @elseif ($booking->payment_status === 'for_verification')
                                    <div class="mt-4 rounded-[1rem] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                        Payment proof submitted. Waiting for admin verification.
                                    </div>
                                @elseif ($booking->refund_requested_at)
                                    <div class="mt-4 rounded-[1rem] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                        Refund requested{{ $booking->refund_requested_at ? ' on ' . $booking->refund_requested_at->format('M j, Y') : '' }}. Waiting for admin review.
                                    </div>
                                @elseif ($booking->payment_status === 'paid')
                                    <div class="mt-4 rounded-[1rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                        Payment verified{{ $booking->paid_at ? ' on ' . $booking->paid_at->format('M j, Y g:i A') : '' }}.
                                    </div>
                                @endif

                                @if ($actionLabel = $booking->guestActionLabel())
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            class="btn-secondary text-sm"
                                            data-booking-action-open
                                            data-booking-action="{{ $actionLabel === 'Cancel Booking' ? 'cancel' : 'refund' }}"
                                            data-booking-url="{{ $actionLabel === 'Cancel Booking' ? route('bookings.cancel', $booking) : route('bookings.request-refund', $booking) }}"
                                            data-booking-label="{{ $actionLabel }}"
                                        >
                                            {{ $actionLabel }}
                                        </button>
                                    </div>
                                @elseif ($booking->isFinalState())
                                    <div class="mt-4 rounded-[1rem] border border-stone-200 bg-stone-100 px-4 py-3 text-sm text-stone-600">
                                        This booking is closed and can no longer be changed.
                                    </div>
                                @elseif ($booking->isCheckedInOrPast() && ! $booking->isFinalState())
                                    <div class="mt-4 rounded-[1rem] border border-stone-200 bg-stone-100 px-4 py-3 text-sm text-stone-600">
                                        Check-in has started. Cancellation and refunds are no longer available online.
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="space-y-8">
                <div class="card">
                    <span class="eyebrow">Review eligibility</span>
                    @if ($eligibleBookings->isEmpty())
                        <p class="mt-4 text-stone-600">You don't have any rooms eligible for review at the moment. Reviews are available after a completed, confirmed stay.</p>
                    @else
                        <p class="mt-4 text-stone-600">You have completed stays ready for your feedback! Click a room below to share your experience.</p>
                        <div class="mt-5 space-y-3">
                            @foreach ($eligibleBookings as $booking)
                                <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div>
                                        <p class="font-semibold text-stone-950">{{ $booking->room?->name }}</p>
                                        <p class="text-xs text-stone-500">Stay ended {{ $booking->check_out->format('M j, Y') }}</p>
                                    </div>
                                    <button
                                        type="button"
                                        class="btn-primary text-xs py-2 px-3 shrink-0"
                                        data-modal-open="dashboard-review-modal"
                                        data-room-name="{{ $booking->room?->name }}"
                                        data-room-slug="{{ $booking->room?->slug }}"
                                        data-stay-date="{{ $booking->check_out->format('M j, Y') }}"
                                    >
                                        Write Review
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($recentRooms->isNotEmpty())
                    <div class="card">
                        <span class="eyebrow">Recent rooms</span>
                        <div class="mt-5 grid gap-3">
                            @foreach ($recentRooms as $room)
                                <a href="{{ route('rooms.show', $room) }}" class="rounded-[1.5rem] border border-stone-200 bg-white p-4 transition hover:border-amber-300 hover:shadow-sm">
                                    <p class="font-semibold text-stone-950">{{ $room->type_label }}</p>
                                    <p class="text-sm text-stone-500">See room details and share your experience.</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<x-booking-action-modal />

<x-modal id="dashboard-review-modal" title="Leave a review" size="max-w-xl">
    <form id="dashboard-review-form" method="POST" action="" class="space-y-5" data-no-loader>
        @csrf
        <div id="review-modal-errors" class="hidden rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700"></div>

        <p class="text-sm text-stone-600">Share your experience at <strong id="review-modal-room-name"></strong> for your stay ending <span id="review-modal-stay-date" class="font-semibold"></span>.</p>

        <!-- Interactive Stars Rating -->
        <div class="space-y-2">
            <label class="form-label font-semibold">Your Rating</label>
            <div class="flex items-center gap-2 pt-1" id="modal-star-rating-container">
                @for ($i = 1; $i <= 5; $i++)
                    <button type="button" data-modal-star="{{ $i }}" class="text-stone-300 hover:text-amber-500 hover:scale-110 transition focus:outline-none" aria-label="Rate {{ $i }} star{{ $i > 1 ? 's' : '' }}">
                        <svg class="h-10 w-10 fill-current" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </button>
                @endfor
            </div>
            <input type="hidden" name="rating" id="modal-rating-input" value="" required>
        </div>

        <!-- Comment Textarea -->
        <div class="space-y-2">
            <label for="review-modal-comment" class="form-label font-semibold">Your Review / Comments</label>
            <textarea id="review-modal-comment" name="comment" rows="5" class="form-input w-full rounded-xl" placeholder="Write about your stay (minimum 10 characters)..." required></textarea>
            <p class="text-[11px] text-stone-500">Provide details on what you liked or how we can improve. (10 - 1000 characters)</p>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button type="button" data-modal-close class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary" data-review-submit>Submit Review</button>
        </div>
    </form>
</x-modal>

@endsection

@push('scripts')
<script>
    (() => {
        // Modal population for review modal
        const reviewModal = document.getElementById('dashboard-review-modal');
        const reviewForm = document.getElementById('dashboard-review-form');
        const roomNameEl = document.getElementById('review-modal-room-name');
        const stayDateEl = document.getElementById('review-modal-stay-date');
        const modalRatingInput = document.getElementById('modal-rating-input');
        const modalCommentInput = document.getElementById('review-modal-comment');
        const errorDiv = document.getElementById('review-modal-errors');

        const openButtons = document.querySelectorAll('[data-modal-open="dashboard-review-modal"]');
        openButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const roomName = btn.getAttribute('data-room-name');
                const roomSlug = btn.getAttribute('data-room-slug');
                const stayDate = btn.getAttribute('data-stay-date');

                if (reviewForm) {
                    reviewForm.action = `/rooms/${roomSlug}/review`;
                }
                if (roomNameEl) roomNameEl.textContent = roomName;
                if (stayDateEl) stayDateEl.textContent = stayDate;
                if (modalRatingInput) modalRatingInput.value = '';
                if (modalCommentInput) modalCommentInput.value = '';
                if (errorDiv) {
                    errorDiv.innerHTML = '';
                    errorDiv.classList.add('hidden');
                }
                updateModalStars(0);
            });
        });

        // Modal Interactive Stars
        const modalStars = document.querySelectorAll('[data-modal-star]');
        const updateModalStars = (rating) => {
            modalStars.forEach(star => {
                const starValue = parseInt(star.getAttribute('data-modal-star'), 10);
                if (starValue <= rating) {
                    star.classList.remove('text-stone-300');
                    star.classList.add('text-amber-500');
                } else {
                    star.classList.remove('text-amber-500');
                    star.classList.add('text-stone-300');
                }
            });
        };

        modalStars.forEach(star => {
            star.addEventListener('click', () => {
                const ratingValue = parseInt(star.getAttribute('data-modal-star'), 10);
                if (modalRatingInput) {
                    modalRatingInput.value = ratingValue;
                }
                updateModalStars(ratingValue);
            });

            star.addEventListener('mouseover', () => {
                const hoverValue = parseInt(star.getAttribute('data-modal-star'), 10);
                updateModalStars(hoverValue);
            });

            star.addEventListener('mouseout', () => {
                const currentValue = parseInt(modalRatingInput?.value, 10) || 0;
                updateModalStars(currentValue);
            });
        });
    })();
</script>
@endpush

