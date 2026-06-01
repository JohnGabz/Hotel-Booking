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
                <h1 class="mt-4 responsive-title lg:text-5xl">Filter and manage reservations with a table-first workflow.</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">Use the filters to narrow by stay date, status, or room type, then inspect the physical room assigned to each booking.</p>
            </div>
            <button type="button" class="btn-primary" data-modal-open="walkin-booking-modal" data-walkin-room="{{ $selectedRoom?->id }}">Add walk-in booking</button>
        </div>

        <form method="GET" action="{{ route('admin.bookings') }}" class="mt-8 grid gap-4 rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4 md:grid-cols-5">
            <div class="form-group">
                <label class="form-label" for="booking_date_from">Date from</label>
                <input id="booking_date_from" name="date_from" type="date" class="form-input" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="booking_date_to">Date to</label>
                <input id="booking_date_to" name="date_to" type="date" class="form-input" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="booking_status">Status</label>
                <select id="booking_status" name="status" class="form-input">
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option }}" @selected(($filters['status'] ?? 'all') === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="booking_room">Room type</label>
                <select id="booking_room" name="room" class="form-input">
                    <option value="all">All room types</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}" @selected(($filters['room'] ?? 'all') == $room->id)>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary w-full">Apply</button>
                <a href="{{ route('admin.bookings') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </section>

    <section class="space-y-6">
        <div class="surface p-6 sm:p-8">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="eyebrow">Room calendar</span>
                        <p class="mt-3 text-sm text-stone-600">Inspect occupancy directly inside the bookings tab.</p>
                    </div>
                    <form method="GET" action="{{ route('admin.bookings') }}">
                        <select name="room" class="form-input min-w-40" onchange="this.form.submit()">
                            @foreach ($rooms as $r)
                                <option value="{{ $r->id }}" @selected(($selectedRoom?->id ?? null) === $r->id)>{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                @if ($selectedRoom && $calendar)
                    <div class="mt-5 flex items-center justify-between gap-2">
                        <a href="{{ route('admin.bookings', ['room' => $selectedRoom->id, 'month' => $calendar['previousMonth']]) }}" class="btn-secondary px-4 py-2 text-sm" aria-label="Previous month">&larr;</a>
                        <span class="rounded-full bg-stone-100 px-4 py-2 text-sm font-semibold text-stone-700">{{ $calendar['label'] }}</span>
                        <a href="{{ route('admin.bookings', ['room' => $selectedRoom->id, 'month' => $calendar['nextMonth']]) }}" class="btn-secondary px-4 py-2 text-sm" aria-label="Next month">&rarr;</a>
                    </div>

                    <div class="admin-calendar-weekdays" role="presentation" aria-hidden="true">
                        <span>Mon</span>
                        <span>Tue</span>
                        <span>Wed</span>
                        <span>Thu</span>
                        <span>Fri</span>
                        <span>Sat</span>
                        <span>Sun</span>
                    </div>

                    <div id="room-calendar" class="admin-calendar-grid" role="grid" aria-label="Admin room occupancy calendar">
                        @foreach ($calendar['weeks'] as $week)
                            @foreach ($week as $day)
                                @php
                                    $cellClasses = match ($day['status']) {
                                        'open' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                        'occupied' => 'border-rose-200 bg-rose-50 text-rose-700',
                                        'unavailable' => 'border-slate-300 bg-slate-100 text-slate-500',
                                        'past' => 'border-gray-300 bg-gray-100 text-gray-400',
                                        default => 'border-stone-200 bg-stone-100 text-stone-400',
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
                                <div
                                    class="admin-calendar-cell {{ $cellClasses }} {{ $day['isToday'] ? 'ring-2 ring-brand-primary ring-offset-1 ring-offset-white' : '' }} {{ $day['isCurrentMonth'] ? '' : 'opacity-45' }}"
                                    role="gridcell"
                                    aria-label="{{ $day['date']->format('F j, Y') }} - {{ $ariaStatusLabel }}"
                                    title="{{ $day['date']->format('M j, Y') }} - {{ $ariaStatusLabel }}"
                                >
                                    <div class="admin-calendar-cell-top">
                                        <span class="admin-calendar-day-number">{{ $day['date']->format('j') }}</span>
                                        @if ($day['isToday'])
                                            <span class="admin-calendar-today">Today</span>
                                        @endif
                                    </div>
                                    @if ($day['isCurrentMonth'])
                                        <div class="admin-calendar-status">
                                            {{ $day['status'] === 'open' ? $day['availableCount'] . ' open' : $statusLabel }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-stone-500">No room is available to display.</p>
                @endif
        </div>

        <div class="surface p-6 sm:p-8">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-3xl font-semibold text-stone-950">Booking table</h2>
                    <p class="mt-2 text-sm text-stone-600">Expandable rows help staff inspect stay details without leaving the list.</p>
                </div>
                <span class="text-sm text-stone-500">{{ $bookings->count() }} records</span>
            </div>

            <div class="mt-6 rounded-lg border border-stone-200 bg-white p-3 md:overflow-x-auto md:p-0">
                <table class="mobile-card-table md:min-w-full">
                    <thead class="bg-stone-50 text-xs uppercase tracking-[0.18em] text-stone-500">
                        <tr>
                            <th class="px-5 py-4">Guest</th>
                            <th class="px-5 py-4">Room assignment</th>
                            <th class="px-5 py-4">Dates</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($bookings as $booking)
                            <tr class="align-top transition hover:bg-stone-50/80">
                                <td class="px-5 py-4" data-label="Guest">
                                    <p class="font-semibold text-stone-950">{{ $booking->contact_name ?? $booking->user?->name ?? 'Guest' }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <p class="text-xs uppercase tracking-[0.2em] text-stone-400">#{{ $booking->id }}</p>
                                        @if ($booking->source === \App\Models\Booking::SOURCE_WALK_IN)
                                            <span class="inline-flex rounded-full bg-sky-100 px-2 py-0.5 text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-sky-700">{{ $booking->source_label }}</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-emerald-700">{{ $booking->source_label }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-stone-700" data-label="Room assignment">
                                    <p class="font-semibold text-stone-950">{{ $booking->room?->name ?? 'Room type' }}</p>
                                    <p class="mt-1 text-xs text-stone-500">{{ $booking->physicalRoom?->name ? 'Assigned: ' . $booking->physicalRoom->name : 'No physical room assigned' }}</p>
                                </td>
                                <td class="px-5 py-4 text-stone-600" data-label="Dates">{{ $booking->check_in->format('M j') }} - {{ $booking->check_out->format('M j') }}</td>
                                <td class="px-5 py-4" data-label="Status">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $booking->status === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : ($booking->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-stone-100 text-stone-700') }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-medium text-stone-950" data-label="Amount">₱{{ number_format($booking->total, 0) }}</td>
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

        <div class="surface p-6 sm:p-8">
                <span class="eyebrow">Booking detail</span>
                @php($primaryBooking = $bookings->first())
                @if ($primaryBooking)
                    <h3 class="mt-4 text-2xl font-semibold text-stone-950">{{ $primaryBooking->room?->name ?? 'Room' }}</h3>
                    <p class="mt-2 text-sm text-stone-600">{{ $primaryBooking->contact_name ?? $primaryBooking->user?->name ?? 'Guest' }} · {{ $primaryBooking->check_in->format('F j') }} - {{ $primaryBooking->check_out->format('F j') }}</p>
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
    </section>
</div>

<x-modal id="walkin-booking-modal" title="Add walk-in booking" size="max-w-3xl">
    <form id="walkin-booking-form" method="POST" action="{{ route('admin.bookings.walkin') }}" enctype="multipart/form-data" class="space-y-5" data-no-loader>
        @csrf
        <div id="walkin-booking-errors" class="hidden rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700"></div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="form-group">
                <label class="form-label" for="walkin_check_in">Check in</label>
                <input id="walkin_check_in" name="check_in" type="date" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="walkin_check_out">Check out</label>
                <input id="walkin_check_out" name="check_out" type="date" class="form-input" required>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="form-group">
                <label class="form-label" for="walkin_room_id">Room type</label>
                <select id="walkin_room_id" name="room_id" class="form-input" required>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}" @selected(($selectedRoom?->id ?? null) === $room->id)>{{ $room->name }} - {{ $room->capacity }} guests</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="walkin_guests">Guests</label>
                <input id="walkin_guests" name="guests" type="number" min="1" max="20" class="form-input" value="1" required>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="form-group">
                <label class="form-label" for="walkin_contact_name">Contact name</label>
                <input id="walkin_contact_name" name="contact_name" type="text" class="form-input" maxlength="150" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="walkin_contact_phone">Contact phone</label>
                <input id="walkin_contact_phone" name="contact_phone" type="text" class="form-input" maxlength="80" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="walkin_contact_email">Contact email</label>
            <input id="walkin_contact_email" name="contact_email" type="email" class="form-input" maxlength="150">
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="form-group">
                <label class="form-label" for="walkin_payment_method">Payment method</label>
                <select id="walkin_payment_method" name="payment_method" class="form-input" required>
                    <option value="gcash">GCash</option>
                    <option value="landbank">Landbank</option>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank transfer</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="walkin_status">Status</label>
                <select id="walkin_status" name="status" class="form-input">
                    <option value="pending">Pending</option>
                    <option value="for_verification">For verification</option>
                    <option value="confirmed">Confirmed</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="walkin_payment_proof">Payment proof</label>
            <input id="walkin_payment_proof" name="payment_proof" type="file" accept="image/*" class="form-input">
        </div>

        <div class="form-group">
            <label class="form-label" for="walkin_notes">Notes</label>
            <textarea id="walkin_notes" name="notes" rows="3" class="form-input" maxlength="2000"></textarea>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button type="button" data-modal-close class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary" data-walkin-submit>Save booking</button>
        </div>
    </form>
</x-modal>

<script>
    (() => {
        const form = document.getElementById('walkin-booking-form');
        const errors = document.getElementById('walkin-booking-errors');
        const submit = form?.querySelector('[data-walkin-submit]');
        const checkIn = document.getElementById('walkin_check_in');
        const checkOut = document.getElementById('walkin_check_out');
        const roomSelect = document.getElementById('walkin_room_id');
        const openButtons = document.querySelectorAll('[data-modal-open="walkin-booking-modal"]');

        if (!form || !errors || !checkIn || !checkOut) return;

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayString = today.toISOString().slice(0, 10);
        checkIn.min = todayString;
        checkOut.min = todayString;

        const showErrors = (messages) => {
            const list = Array.isArray(messages) ? messages : [messages];
            errors.innerHTML = '';
            list.forEach((message) => {
                const item = document.createElement('p');
                item.textContent = message;
                errors.appendChild(item);
            });
            errors.classList.remove('hidden');
        };

        const clearErrors = () => {
            errors.innerHTML = '';
            errors.classList.add('hidden');
        };

        const syncCheckOutMin = () => {
            if (!checkIn.value) return;

            const date = new Date(`${checkIn.value}T00:00:00`);
            date.setDate(date.getDate() + 1);
            const minCheckout = date.toISOString().slice(0, 10);
            checkOut.min = minCheckout;

            if (checkOut.value && checkOut.value <= checkIn.value) {
                checkOut.value = minCheckout;
            }
        };

        openButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const roomId = button.getAttribute('data-walkin-room');
                if (roomId && roomSelect) {
                    roomSelect.value = roomId;
                }
            });
        });

        checkIn.addEventListener('change', syncCheckOutMin);

        form.addEventListener('submit', async (event) => {
            if (!window.fetchWithoutLoader) return;

            event.preventDefault();
            clearErrors();
            syncCheckOutMin();

            if (!checkIn.value || !checkOut.value || checkOut.value <= checkIn.value) {
                showErrors('Check out must be after check in.');
                return;
            }

            submit?.setAttribute('disabled', 'disabled');
            submit?.classList.add('opacity-70');

            try {
                const response = await window.fetchWithoutLoader(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const flattened = payload.errors ? Object.values(payload.errors).flat() : [payload.message || 'Unable to save this booking.'];
                    showErrors(flattened);
                    return;
                }

                window.location.reload();
            } catch (error) {
                showErrors('Unable to save this booking. Please try again.');
            } finally {
                submit?.removeAttribute('disabled');
                submit?.classList.remove('opacity-70');
            }
        });
    })();
</script>
@endsection
