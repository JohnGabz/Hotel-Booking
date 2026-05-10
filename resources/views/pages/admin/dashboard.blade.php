@extends('layouts.admin')

@section('content')
@php
    $confirmedPercent = $bookingsCount > 0 ? round(($confirmedCount / $bookingsCount) * 100) : 0;
    $paymentPercent = $bookingsCount > 0 ? round(($pendingPayments->count() / $bookingsCount) * 100) : 0;
    $reviewPercent = $bookingsCount > 0 ? round(($reviews->count() / $bookingsCount) * 100) : 0;
    $availablePercent = $rooms->count() > 0 ? round(($availableRooms / $rooms->count()) * 100) : 0;
    $chartLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $chartValues = [18, 24, 20, 32, 29, 35, 28];
    $maxValue = max($chartValues);
@endphp

<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <span class="eyebrow">Dashboard</span>
            <h1 class="mt-3 text-3xl font-bold text-stone-950 sm:text-4xl">Welcome back</h1>
            <p class="mt-2 text-sm text-stone-600">Monitor performance and manage operations at a glance</p>
        </div>
        <a href="{{ route('admin.settings', ['tab' => 'landing']) }}" class="btn-secondary text-sm">Settings</a>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="card overflow-hidden">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <p class="text-xs font-medium uppercase tracking-[0.25em] text-stone-500">Total Bookings</p>
                    <p class="mt-4 text-4xl font-bold text-stone-950">{{ number_format($bookingsCount ?? 0) }}</p>
                    <p class="mt-3 text-xs font-semibold text-emerald-600">{{ number_format($confirmedCount ?? 0) }} confirmed</p>
                </div>
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-primary/10 to-brand-primary/5">
                    <svg class="h-8 w-8 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3M5 11h14M6 21h12a1 1 0 001-1V6a1 1 0 00-1-1H6a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                    </svg>
                </div>
            </div>
        </article>

        <article class="card overflow-hidden">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <p class="text-xs font-medium uppercase tracking-[0.25em] text-stone-500">Revenue</p>
                    <p class="mt-4 text-4xl font-bold text-stone-950">₱{{ number_format($bookings->sum('total') ?? 0, 0) }}</p>
                    <p class="mt-3 text-xs text-stone-500">From all bookings</p>
                </div>
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-secondary/10 to-brand-secondary/5">
                    <svg class="h-8 w-8 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </article>

        <article class="card overflow-hidden">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <p class="text-xs font-medium uppercase tracking-[0.25em] text-stone-500">Available Rooms</p>
                    <p class="mt-4 text-4xl font-bold text-stone-950">{{ number_format($availableRooms ?? 0) }}</p>
                    <p class="mt-3 text-xs text-stone-600">Ready for guests</p>
                </div>
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-300/20 to-slate-300/10">
                    <svg class="h-8 w-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 4l4 2m-2-2l4-2"/>
                    </svg>
                </div>
            </div>
        </article>

        <article class="card overflow-hidden">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <p class="text-xs font-medium uppercase tracking-[0.25em] text-stone-500">Occupancy Rate</p>
                    <p class="mt-4 text-4xl font-bold text-stone-950">{{ $confirmedPercent }}<span class="text-2xl">%</span></p>
                    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-stone-200">
                        <div class="h-full bg-gradient-to-r from-brand-primary to-brand-secondary" style="width: {{ $confirmedPercent }}%"></div>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-3">
        <div class="surface p-6 sm:p-8 lg:col-span-2">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-stone-950">Booking Trends</h2>
                    <p class="mt-1 text-sm text-stone-600">Last 7 days</p>
                </div>
            </div>

            <div class="flex h-48 items-end justify-between gap-2">
                @foreach ($chartValues as $index => $value)
                    @php $barHeight = ($value / $maxValue) * 100; @endphp
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <div class="relative flex w-full items-end justify-center" style="height: 120px;">
                            <div class="w-3/4 rounded-t-lg bg-gradient-to-t from-brand-primary to-brand-secondary shadow-lg transition hover:shadow-xl" style="height: {{ $barHeight }}%; opacity: 0.9;" title="{{ $value }} bookings"></div>
                        </div>
                        <p class="text-xs font-medium text-stone-600">{{ $chartLabels[$index] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="surface p-6 sm:p-8">
            <h2 class="mb-6 text-xl font-bold text-stone-950">Status Overview</h2>

            <div class="space-y-4">
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-medium text-stone-700">Confirmed</p>
                        <p class="text-sm font-bold text-stone-900">{{ number_format($confirmedCount ?? 0) }}</p>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-stone-200">
                        <div class="h-full bg-emerald-500" style="width: {{ $confirmedPercent }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-medium text-stone-700">Pending Payment</p>
                        <p class="text-sm font-bold text-stone-900">{{ $pendingPayments->count() ?? 0 }}</p>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-stone-200">
                        <div class="h-full bg-amber-500" style="width: {{ $paymentPercent }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-medium text-stone-700">Pending Reviews</p>
                        <p class="text-sm font-bold text-stone-900">{{ $reviews->count() ?? 0 }}</p>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-stone-200">
                        <div class="h-full bg-blue-500" style="width: {{ $reviewPercent }}%"></div>
                    </div>
                </div>

                <div class="border-t border-stone-200 pt-4">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-medium text-stone-700">Available Rooms</p>
                        <p class="text-sm font-bold text-stone-900">{{ $availableRooms ?? 0 }}</p>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-stone-200">
                        <div class="h-full bg-slate-400" style="width: {{ $availablePercent }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        @if ($pendingPayments->count() > 0)
            <div class="surface p-6 sm:p-8">
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-stone-950">Payment Verification</h2>
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">{{ $pendingPayments->count() }}</span>
                </div>

                <div class="max-h-96 space-y-3 overflow-y-auto">
                    @forelse ($pendingPayments->take(5) as $booking)
                        <article class="rounded-lg border border-stone-200 bg-stone-50 p-4">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-stone-950">{{ $booking->contact_name ?? $booking->user?->name ?? 'Guest' }}</p>
                                    <p class="text-xs text-stone-600">{{ $booking->room?->name ?? 'Room' }} • {{ strtoupper($booking->payment_method) }}</p>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">{{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}</span>
                            </div>
                            <form action="{{ route('admin.bookings.payment-status', $booking) }}" method="POST" class="flex gap-2">
                                @csrf
                                <select name="payment_status" class="form-input flex-1 text-xs">
                                    <option value="pending" @selected($booking->payment_status === 'pending')>Pending</option>
                                    <option value="for_verification" @selected($booking->payment_status === 'for_verification')>For Verification</option>
                                    <option value="paid" @selected($booking->payment_status === 'paid')>Paid</option>
                                    <option value="failed" @selected($booking->payment_status === 'failed')>Failed</option>
                                </select>
                                <button type="submit" class="btn-primary px-3 py-2 text-xs">Update</button>
                            </form>
                        </article>
                    @empty
                        <p class="text-sm text-stone-500">No pending payment records at the moment.</p>
                    @endforelse
                </div>
            </div>
        @endif

        @if ($reviews->count() > 0)
            <div class="surface p-6 sm:p-8">
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-stone-950">Reviews to Approve</h2>
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">{{ $reviews->count() }}</span>
                </div>

                <div class="max-h-96 space-y-3 overflow-y-auto">
                    @forelse ($reviews->take(5) as $review)
                        <article class="rounded-lg border border-stone-200 bg-stone-50 p-4">
                            <div class="mb-2 flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-stone-950">{{ $review->user?->name ?? 'Guest' }}</p>
                                    <p class="text-xs text-stone-600">{{ $review->room?->name ?? 'Room' }}</p>
                                </div>
                                <span class="text-sm font-bold text-yellow-600">{{ $review->rating }}/5</span>
                            </div>
                            <p class="mb-3 text-xs text-stone-600">{{ $review->comment }}</p>
                            <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-primary w-full py-2 text-xs">Approve Review</button>
                            </form>
                        </article>
                    @empty
                        <p class="text-sm text-stone-500">No pending reviews at the moment.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </section>

</div>
@endsection
