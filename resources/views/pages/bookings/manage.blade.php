@extends('layouts.site')

@section('content')
<section class="section-shell pt-8 sm:pt-10">
    <div class="site-shell max-w-2xl mx-auto">
        <div class="card p-6 sm:p-10 space-y-8 bg-white border border-stone-200 rounded-[2rem] shadow-xl">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="rounded-[1rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-[1rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Header --}}
            <div class="text-center space-y-4">
                @if ($booking->payment_status === 'refunded')
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-sky-50 text-sky-600">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-stone-900 tracking-tight">Booking Refunded</h1>
                    <p class="text-stone-500 max-w-md mx-auto text-sm leading-relaxed">
                        This booking has been refunded. The amount will be returned to your original payment method.
                    </p>
                @elseif ($booking->isFinalState())
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-stone-100 text-stone-500">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-stone-900 tracking-tight">Booking Closed</h1>
                    <p class="text-stone-500 max-w-md mx-auto text-sm leading-relaxed">
                        This booking is closed and can no longer be changed.
                    </p>
                @elseif ($booking->refund_requested_at)
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-amber-600 animate-pulse">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-stone-900 tracking-tight">Refund Under Review</h1>
                    <p class="text-stone-500 max-w-md mx-auto text-sm leading-relaxed">
                        Your refund request was submitted on {{ $booking->refund_requested_at->format('M j, Y') }}. Our team will review it shortly.
                    </p>
                @else
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-stone-900 tracking-tight">Manage Your Booking</h1>
                    <p class="text-stone-500 max-w-md mx-auto text-sm leading-relaxed">
                        View your booking details below. You can request a refund if your plans change.
                    </p>
                @endif
            </div>

            {{-- Booking Summary --}}
            <div class="divide-y divide-stone-200/60 rounded-2xl bg-stone-50 p-6 space-y-4 text-sm border border-stone-200/50">
                <div class="flex justify-between items-center pb-3 border-b border-stone-200/60">
                    <span class="text-stone-500 font-medium">Booking Reference</span>
                    <span class="font-mono font-bold text-stone-950">#{{ $booking->id }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-stone-200/60">
                    <span class="text-stone-500 font-medium">Guest Name</span>
                    <span class="font-semibold text-stone-900">{{ $booking->contact_name }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-stone-200/60">
                    <span class="text-stone-500 font-medium">Room Reserved</span>
                    <span class="font-semibold text-stone-900">{{ $booking->room?->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-stone-200/60">
                    <span class="text-stone-500 font-medium">Check-in Date</span>
                    <span class="font-semibold text-stone-900">{{ $booking->check_in->format('F j, Y') }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-stone-200/60">
                    <span class="text-stone-500 font-medium">Check-out Date</span>
                    <span class="font-semibold text-stone-900">{{ $booking->check_out->format('F j, Y') }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-stone-200/60">
                    <span class="text-stone-500 font-medium">Total Amount</span>
                    <span class="font-bold text-stone-950 text-base">₱{{ number_format($booking->total, 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-stone-200/60">
                    <span class="text-stone-500 font-medium">Payment Status</span>
                    @php
                        $badgeClasses = match($booking->payment_status) {
                            'paid' => 'bg-emerald-100 text-emerald-700',
                            'refunded' => 'bg-sky-100 text-sky-700',
                            'failed' => 'bg-rose-100 text-rose-700',
                            default => 'bg-stone-200 text-stone-700',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $badgeClasses }}">
                        {{ $booking->payment_status }}
                    </span>
                </div>
                <div class="flex justify-between items-center pt-3">
                    <span class="text-stone-500 font-medium">Status</span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-stone-200 text-stone-700">
                        {{ $booking->status }}
                    </span>
                </div>
            </div>

            {{-- Refund Request Action --}}
            @if ($booking->canGuestRequestRefund())
                <div class="space-y-4">
                    <div class="rounded-[1rem] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        <strong>Need to cancel?</strong> You can request a refund below. Our team will review your request and process the refund to your original payment method.
                    </div>

                    <form action="{{ route('bookings.manage.refund', $token) }}" method="POST" class="space-y-4"
                          onsubmit="return confirm('Are you sure you want to request a refund for this booking? This action cannot be undone.');">
                        @csrf
                        <div class="form-group">
                            <label class="form-label" for="cancellation_reason">Reason for refund <span class="text-stone-400">(optional)</span></label>
                            <textarea
                                id="cancellation_reason"
                                name="cancellation_reason"
                                rows="3"
                                maxlength="500"
                                class="form-input w-full"
                                placeholder="Please let us know why you'd like a refund..."
                            >{{ old('cancellation_reason') }}</textarea>
                            @error('cancellation_reason')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="btn-secondary w-full py-3 text-center">
                            Request Refund
                        </button>
                    </form>
                </div>
            @elseif ($booking->refund_requested_at)
                <div class="rounded-[1rem] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <strong>Refund pending.</strong> Your refund request is being reviewed by our team. We'll process it as soon as possible.
                    @if ($booking->cancellation_reason)
                        <p class="mt-2 text-amber-700"><strong>Your reason:</strong> {{ $booking->cancellation_reason }}</p>
                    @endif
                </div>
            @elseif ($booking->isCheckedInOrPast() && ! $booking->isFinalState())
                <div class="rounded-[1rem] border border-stone-200 bg-stone-100 px-4 py-3 text-sm text-stone-600">
                    Check-in has started. Cancellation and refunds are no longer available online. Please contact us directly if you need assistance.
                </div>
            @endif

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="rounded-[1rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Footer actions --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('home') }}" class="btn-secondary w-full py-3 text-center flex items-center justify-center">
                    Return to Home
                </a>
                <a href="{{ route('rooms.index') }}" class="btn-primary w-full py-3 text-center flex items-center justify-center">
                    Browse Rooms
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
