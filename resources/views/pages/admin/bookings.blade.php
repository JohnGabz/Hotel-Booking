@extends('layouts.admin')

@section('content')
@php
    $statusOptions = ['all', 'confirmed', 'pending', 'cancelled'];
@endphp

<div class="space-y-8">
    <section class="surface p-6 sm:p-8 lg:p-10">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="eyebrow">Bookings</span>
                <h1 class="mt-4 text-4xl sm:text-5xl text-stone-950">Filter and manage reservations with a table-first workflow.</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">Use the filters to narrow by stay date, status, or room, then expand rows for quick action.</p>
            </div>
            <a href="#" data-modal-open="generic-action-modal" data-modal-title="Add booking" class="btn-primary">Add booking</a>
        </div>

        <form class="mt-8 grid gap-4 rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4 md:grid-cols-4">
            <div class="form-group">
                <label class="form-label">Date from</label>
                <input type="date" class="form-input" value="{{ request('date_from') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Date to</label>
                <input type="date" class="form-input" value="{{ request('date_to') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select class="form-input">
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option }}" @selected(($filters['status'] ?? 'all') === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Room</label>
                <select class="form-input">
                    <option value="all">All rooms</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}" @selected(($filters['room'] ?? 'all') == $room->id)>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="surface p-6 sm:p-8">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-3xl font-semibold text-stone-950">Booking table</h2>
                    <p class="mt-2 text-sm text-stone-600">Expandable rows help staff inspect stay details without leaving the list.</p>
                </div>
                <span class="text-sm text-stone-500">{{ $bookings->count() }} records</span>
            </div>

            <div class="mt-6 overflow-x-auto rounded-[1.5rem] border border-stone-200 bg-white">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-stone-50 text-xs uppercase tracking-[0.18em] text-stone-500">
                        <tr>
                            <th class="px-5 py-4">Guest</th>
                            <th class="px-5 py-4">Room</th>
                            <th class="px-5 py-4">Dates</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($bookings as $booking)
                            <tr class="align-top transition hover:bg-stone-50/80">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-stone-950">{{ $booking->user?->name ?? 'Guest' }}</p>
                                    <p class="text-xs uppercase tracking-[0.2em] text-stone-400">#{{ $booking->id }}</p>
                                </td>
                                <td class="px-5 py-4 text-stone-700">{{ $booking->room?->name ?? 'Room' }}</td>
                                <td class="px-5 py-4 text-stone-600">{{ $booking->check_in->format('M j') }} - {{ $booking->check_out->format('M j') }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $booking->status === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : ($booking->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-stone-100 text-stone-700') }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-medium text-stone-950">₱{{ number_format($booking->total, 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-stone-500">No bookings found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="surface p-6 sm:p-8">
                <span class="eyebrow">Booking detail</span>
                @php($primaryBooking = $bookings->first())
                @if ($primaryBooking)
                    <h3 class="mt-4 text-2xl font-semibold text-stone-950">{{ $primaryBooking->room?->name ?? 'Room' }}</h3>
                    <p class="mt-2 text-sm text-stone-600">{{ $primaryBooking->user?->name ?? 'Guest' }} · {{ $primaryBooking->check_in->format('F j') }} - {{ $primaryBooking->check_out->format('F j') }}</p>
                    <div class="mt-4 grid gap-3 text-sm text-stone-600">
                        <div class="rounded-2xl bg-stone-50 p-4">Payment method: <strong>{{ strtoupper($primaryBooking->payment_method) }}</strong></div>
                        <div class="rounded-2xl bg-stone-50 p-4">Payment status: <strong>{{ ucfirst(str_replace('_', ' ', $primaryBooking->payment_status)) }}</strong></div>
                        <div class="rounded-2xl bg-stone-50 p-4">Guests: <strong>{{ $primaryBooking->guests }}</strong></div>
                    </div>
                @else
                    <p class="mt-4 text-sm text-stone-500">No booking details available yet.</p>
                @endif
            </div>

            <div class="surface p-6 sm:p-8">
                <span class="eyebrow">Actions</span>
                <div class="mt-4 space-y-3">
                    <a href="{{ route('admin.reports') }}" class="btn-secondary w-full justify-start">Open reports</a>
                    <a href="{{ route('admin.rooms') }}" class="btn-secondary w-full justify-start">Review room availability</a>
                </div>
            </div>
        </aside>
    </section>
</div>
@endsection
