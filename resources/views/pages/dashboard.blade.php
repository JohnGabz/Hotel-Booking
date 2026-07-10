@extends('layouts.site')

@section('content')
<section class="section-shell pt-8 sm:pt-10">
    <div class="site-shell" data-realtime-fragment="guest-dashboard">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between mb-8">
            <div>
                <span class="eyebrow">My dashboard</span>
                <h1 class="mt-4 responsive-title text-stone-950">Your reservations at a glance.</h1>
            </div>
            <a href="{{ route('rooms.index') }}" class="btn-primary">Book a room</a>
        </div>

        <!-- Cancellation and Down Payment Policy -->
        <div class="mb-8 rounded-xl border border-amber-200 bg-amber-50/70 p-5 text-amber-900 shadow-sm">
            <h3 class="text-sm font-semibold flex items-center gap-1.5 mb-2">
                <svg class="w-5 h-5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Villa Booking & Cancellation Policy
            </h3>
            <ul class="list-disc pl-5 text-xs space-y-1 text-amber-800 leading-relaxed">
                <li><strong>Deposit Obligation:</strong> A 50% down payment is required for all reservations and bookings.</li>
                <li><strong>Remaining Balance:</strong> Outstanding balances must be paid prior to check-in. You can pay the balance online below.</li>
                <li><strong>Cancellation Penalty:</strong> Cancellations made 3 days or more prior to your check-in date are eligible for full refund. Cancellations made less than 3 days before check-in will incur a <strong>50% cancellation penalty</strong> of the booking total.</li>
            </ul>
        </div>

        <!-- Statistics grid -->
        <div class="grid gap-4 sm:grid-cols-3 mb-8">
            <div class="card p-5 bg-white border border-stone-200 shadow-sm rounded-xl">
                <p class="text-xs uppercase tracking-[0.25em] text-stone-500 font-medium">All Bookings</p>
                <p class="mt-2 text-3xl font-semibold text-stone-950">{{ $bookings->count() }}</p>
            </div>
            <div class="card p-5 bg-white border border-stone-200 shadow-sm rounded-xl">
                <p class="text-xs uppercase tracking-[0.25em] text-stone-500 font-medium">Upcoming Bookings</p>
                <p class="mt-2 text-3xl font-semibold text-stone-950">{{ $upcomingBookings->count() }}</p>
            </div>
            <div class="card p-5 bg-white border border-stone-200 shadow-sm rounded-xl">
                <p class="text-xs uppercase tracking-[0.25em] text-stone-500 font-medium">Rooms to Review</p>
                <p class="mt-2 text-3xl font-semibold text-stone-950">{{ $eligibleBookings->count() }}</p>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="border-b border-stone-200 mb-6 flex gap-4">
            <button onclick="switchTab('upcoming')" id="tab-btn-upcoming" class="tab-btn pb-3 px-1 text-sm font-semibold border-b-2 transition-all duration-300 border-brand-primary text-brand-primary">
                Upcoming Bookings ({{ $upcomingBookings->count() }})
            </button>
            <button onclick="switchTab('history')" id="tab-btn-history" class="tab-btn pb-3 px-1 text-sm font-semibold border-b-2 transition-all duration-300 border-transparent text-stone-500 hover:text-stone-900">
                Full Booking History ({{ $bookings->count() }})
            </button>
            <button onclick="switchTab('reviews')" id="tab-btn-reviews" class="tab-btn pb-3 px-1 text-sm font-semibold border-b-2 transition-all duration-300 border-transparent text-stone-500 hover:text-stone-900">
                Rooms to Review ({{ $eligibleBookings->count() }})
            </button>
        </div>

        <!-- Tab Contents -->
        <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden mb-12">
            <!-- Upcoming Bookings Tab -->
            <div id="tab-content-upcoming" class="tab-pane">
                @if ($upcomingBookings->isEmpty())
                    <div class="p-8 text-center text-stone-500">
                        <p>You don't have any upcoming reservations. Start by browsing rooms.</p>
                        <a href="{{ route('rooms.index') }}" class="inline-block mt-4 text-sm font-semibold text-brand-primary hover:underline">Browse Rooms &rarr;</a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-stone-50 border-b border-stone-200 text-xs font-semibold text-stone-500 uppercase tracking-wider">
                                    <th class="p-4">Room Type</th>
                                    <th class="p-4">Stay Dates</th>
                                    <th class="p-4">Guests</th>
                                    <th class="p-4">Total</th>
                                    <th class="p-4">Amount Paid</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 text-stone-800">
                                @foreach ($upcomingBookings as $booking)
                                    <tr class="hover:bg-stone-50/50 transition">
                                        <td class="p-4 font-semibold text-stone-950">{{ $booking->room->name }}</td>
                                        <td class="p-4">
                                            {{ $booking->check_in->format('M j') }} &mdash; {{ $booking->check_out->format('M j, Y') }}
                                        </td>
                                        <td class="p-4 text-stone-600">{{ $booking->guests }}</td>
                                        <td class="p-4 font-medium">₱{{ number_format($booking->total, 2) }}</td>
                                        <td class="p-4 font-medium text-emerald-700">₱{{ number_format($booking->amount_paid, 2) }}</td>
                                        <td class="p-4">
                                            @php
                                                $badgeClass = match (strtolower($booking->status)) {
                                                    'confirmed' => 'bg-emerald-100 text-emerald-800',
                                                    'reserved' => 'bg-blue-100 text-blue-800',
                                                    'pending payment' => 'bg-amber-100 text-amber-800',
                                                    default => 'bg-stone-100 text-stone-800',
                                                };
                                            @endphp
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">{{ ucfirst($booking->status) }}</span>
                                        </td>
                                        <td class="p-4 text-right space-x-2 whitespace-nowrap">
                                            @if ($booking->amount_paid < $booking->total && !in_array(strtolower($booking->status), ['cancelled', 'payment failed'], true))
                                                <form action="{{ route('bookings.pay', $booking) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="btn-primary text-xs py-1.5 px-3">Pay Balance</button>
                                                </form>
                                                
                                                @if ($booking->payment_method !== 'xendit')
                                                    <button type="button" onclick="openUploadProofModal({{ $booking->id }}, '{{ route('bookings.payment-proof', $booking) }}')" class="btn-secondary text-xs py-1.5 px-3">Upload Proof</button>
                                                @endif
                                            @endif

                                            @if ($booking->payment_status === 'for_verification')
                                                <span class="text-xs text-amber-600 font-semibold block sm:inline">Verifying Proof...</span>
                                            @endif

                                            @if ($actionLabel = $booking->guestActionLabel())
                                                <button
                                                    type="button"
                                                    class="btn-secondary text-xs py-1.5 px-3"
                                                    data-booking-action-open
                                                    data-booking-action="{{ $actionLabel === 'Cancel Booking' ? 'cancel' : 'refund' }}"
                                                    data-booking-url="{{ $actionLabel === 'Cancel Booking' ? route('bookings.cancel', $booking) : route('bookings.request-refund', $booking) }}"
                                                    data-booking-label="{{ $actionLabel }}"
                                                >
                                                    {{ $actionLabel }}
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Full History Tab -->
            <div id="tab-content-history" class="tab-pane hidden">
                @if ($bookings->isEmpty())
                    <div class="p-8 text-center text-stone-500">
                        <p>No bookings found in your history.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-stone-50 border-b border-stone-200 text-xs font-semibold text-stone-500 uppercase tracking-wider">
                                    <th class="p-4">Room Type</th>
                                    <th class="p-4">Stay Dates</th>
                                    <th class="p-4">Guests</th>
                                    <th class="p-4">Total</th>
                                    <th class="p-4">Amount Paid</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4">Payment</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 text-stone-800">
                                @foreach ($bookings as $booking)
                                    <tr class="hover:bg-stone-50/50 transition">
                                        <td class="p-4 font-semibold text-stone-950">{{ $booking->room->name }}</td>
                                        <td class="p-4">
                                            {{ $booking->check_in->format('M j, Y') }} &mdash; {{ $booking->check_out->format('M j, Y') }}
                                        </td>
                                        <td class="p-4 text-stone-600">{{ $booking->guests }}</td>
                                        <td class="p-4 font-medium">₱{{ number_format($booking->total, 2) }}</td>
                                        <td class="p-4 font-medium text-emerald-700">₱{{ number_format($booking->amount_paid, 2) }}</td>
                                        <td class="p-4">
                                            @php
                                                $badgeClass = match (strtolower($booking->status)) {
                                                    'confirmed' => 'bg-emerald-100 text-emerald-800',
                                                    'reserved' => 'bg-blue-100 text-blue-800',
                                                    'pending payment' => 'bg-amber-100 text-amber-800',
                                                    'cancelled' => 'bg-rose-100 text-rose-800',
                                                    default => 'bg-stone-100 text-stone-800',
                                                };
                                            @endphp
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">{{ ucfirst($booking->status) }}</span>
                                        </td>
                                        <td class="p-4">
                                            <span class="text-xs uppercase font-semibold text-stone-500">{{ $booking->payment_status ?: 'pending' }}</span>
                                            @if ($booking->cancellation_penalty > 0)
                                                <span class="block text-[10px] text-rose-600 font-bold mt-0.5">Penalty: ₱{{ number_format($booking->cancellation_penalty, 2) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Review Eligibility Tab -->
            <div id="tab-content-reviews" class="tab-pane hidden">
                @if ($eligibleBookings->isEmpty())
                    <div class="p-8 text-center text-stone-500">
                        <p>No completed stays are currently eligible for review.</p>
                    </div>
                @else
                    <div class="p-6">
                        <p class="text-sm text-stone-600 mb-4">Click a room below to share your experience from your completed stays.</p>
                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ($eligibleBookings as $booking)
                                <div class="rounded-xl border border-stone-200 bg-stone-50 p-5 flex items-center justify-between gap-4">
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
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Upload Proof Modal -->
<x-modal id="upload-proof-modal" title="Upload Payment Proof" size="max-w-md">
    <form id="upload-proof-form" method="POST" action="" enctype="multipart/form-data" class="space-y-4" data-no-loader>
        @csrf
        <div class="form-group">
            <label class="form-label" for="modal_payment_reference">Reference Number</label>
            <input id="modal_payment_reference" name="payment_reference" class="form-input" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="modal_payment_proof">Proof Image</label>
            <input id="modal_payment_proof" name="payment_proof" type="file" accept="image/*" class="form-input" required>
        </div>
        <div class="flex justify-end gap-3 pt-2">
            <button type="button" data-modal-close class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Submit Proof</button>
        </div>
    </form>
</x-modal>

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
    function switchTab(tabId) {
        // Toggle tabs
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
        document.getElementById(`tab-content-${tabId}`).classList.remove('hidden');

        // Toggle buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-brand-primary', 'text-brand-primary');
            btn.classList.add('border-transparent', 'text-stone-500');
        });
        const activeBtn = document.getElementById(`tab-btn-${tabId}`);
        activeBtn.classList.remove('border-transparent', 'text-stone-500');
        activeBtn.classList.add('border-brand-primary', 'text-brand-primary');
    }

    function openUploadProofModal(bookingId, uploadUrl) {
        const modal = document.getElementById('upload-proof-modal');
        const form = document.getElementById('upload-proof-form');
        form.action = uploadUrl;
        window.VillaModal?.open ? window.VillaModal.open(modal) : modal.classList.remove('hidden');
    }

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
