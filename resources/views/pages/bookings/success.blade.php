@extends('layouts.site')

@section('content')
<section class="section-shell pt-8 sm:pt-10">
    <div class="site-shell max-w-2xl mx-auto">
        <div class="card p-6 sm:p-10 space-y-8 bg-white border border-stone-200 rounded-[2rem] shadow-xl">
            <!-- Icon and title based on status -->
            <div class="text-center space-y-4">
                @if ($booking->status === 'Confirmed' || $booking->status === 'confirmed')
                    <!-- Success State -->
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-stone-900 tracking-tight">Booking Confirmed!</h1>
                    <p class="text-stone-500 max-w-md mx-auto text-sm leading-relaxed">
                        Thank you for choosing Villa Estella. Your payment has been processed successfully and your stay is confirmed.
                    </p>
                @elseif ($booking->status === 'Pending Payment' || $booking->status === 'pending')
                    <!-- Pending State -->
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-amber-600 animate-pulse">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-stone-900 tracking-tight">Awaiting Payment Confirmation</h1>
                    <p class="text-stone-500 max-w-md mx-auto text-sm leading-relaxed">
                        We are waiting for Xendit to notify us of your payment success. This page will update automatically, or you can refresh.
                    </p>
                @else
                    <!-- Other Statuses (Failed / Expired) -->
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-stone-900 tracking-tight">Booking Status: {{ $booking->status }}</h1>
                    <p class="text-stone-500 max-w-md mx-auto text-sm leading-relaxed">
                        There was an issue processing your payment. Your booking status is currently {{ $booking->status }}.
                    </p>
                @endif
            </div>

            <!-- Booking Summary Details -->
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
                    <span class="font-semibold text-stone-900">{{ $booking->room->name }}</span>
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
                <div class="flex justify-between items-center pt-3">
                    <span class="text-stone-500 font-medium">Status</span>
                    <span class="badge-primary bg-stone-200 text-stone-700 uppercase tracking-wider text-[10px] font-bold">
                        {{ $booking->status }}
                    </span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('home') }}" class="btn-secondary w-full py-3 text-center flex items-center justify-center">
                    Return to Home
                </a>
                @if (Auth::check())
                    <a href="{{ route('dashboard') }}" class="btn-primary w-full py-3 text-center flex items-center justify-center">
                        Go to My Dashboard
                    </a>
                @else
                    <a href="{{ route('rooms.index') }}" class="btn-primary w-full py-3 text-center flex items-center justify-center">
                        Browse More Rooms
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
@if ($booking->status === 'Pending Payment' || $booking->status === 'pending')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const checkStatusUrl = "{{ route('bookings.status-api', $booking) }}";
        const interval = setInterval(async () => {
            try {
                const response = await fetch(checkStatusUrl);
                if (response.ok) {
                    const data = await response.json();
                    if (data.status === 'Confirmed' || data.status === 'confirmed') {
                        clearInterval(interval);
                        window.location.reload();
                    }
                }
            } catch (error) {
                console.error('Error checking payment status:', error);
            }
        }, 3000);

        // Stop polling after 5 minutes
        setTimeout(() => clearInterval(interval), 300000);
    });
</script>
@endif
@endpush
