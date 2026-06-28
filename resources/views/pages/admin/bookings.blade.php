@extends('layouts.admin')

@section('content')
@php
    $statusOptions = ['all', 'confirmed', 'pending', 'cancelled'];
@endphp

<div class="space-y-8" data-realtime-fragment="admin-bookings">
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

    <div class="grid gap-6 lg:grid-cols-4 items-start">
        <!-- Left Column: Calendar & Table -->
        <div class="lg:col-span-3 space-y-6">
        <div class="surface p-6 sm:p-8" id="admin-calendar-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="eyebrow">Room calendar</span>
                        <p class="mt-3 text-sm text-stone-600">Inspect occupancy directly inside the bookings tab.</p>
                    </div>
                    <div>
                        <select name="room" id="calendar-room-select" class="form-input min-w-40">
                            @foreach ($rooms as $r)
                                <option value="{{ $r->id }}" @selected(($selectedRoom?->id ?? null) === $r->id)>{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if ($selectedRoom && $calendar)
                    <div class="mt-5 flex items-center justify-between gap-2">
                        <a href="{{ route('admin.bookings', ['room' => $selectedRoom->id, 'month' => $calendar['previousMonth']]) }}" class="btn-secondary calendar-nav-btn px-4 py-2 text-sm" aria-label="Previous month">&larr;</a>
                        <span class="rounded-full bg-stone-100 px-4 py-2 text-sm font-semibold text-stone-700">{{ $calendar['label'] }}</span>
                        <a href="{{ route('admin.bookings', ['room' => $selectedRoom->id, 'month' => $calendar['nextMonth']]) }}" class="btn-secondary calendar-nav-btn px-4 py-2 text-sm" aria-label="Next month">&rarr;</a>
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
                            <tr class="align-top transition hover:bg-stone-50/80 cursor-pointer"
                                data-booking-id="{{ $booking->id }}"
                                data-status="{{ strtolower($booking->status) }}"
                                data-room-name="{{ $booking->room?->name ?? 'Room type' }}"
                                data-guest-name="{{ $booking->contact_name ?? $booking->user?->name ?? 'Guest' }}"
                                data-check-in="{{ $booking->check_in->format('F j') }}"
                                data-check-out="{{ $booking->check_out->format('F j') }}"
                                data-physical-room="{{ $booking->physicalRoom ? $booking->physicalRoom->name . ' (' . $booking->physicalRoom->code . ')' : 'None' }}"
                                data-payment-method="{{ strtoupper($booking->payment_method) }}"
                                data-payment-status="{{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}"
                                data-guests="{{ $booking->guests }}"
                            >
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
                                    <p class="mt-1 text-xs text-stone-500">
                                        @if ($booking->physicalRoom)
                                            Assigned: {{ $booking->physicalRoom->name }} ({{ $booking->physicalRoom->code }})
                                        @else
                                            No physical room assigned
                                        @endif
                                    </p>
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
        </div>

        <!-- Right Column: Details & Actions (Sticky) -->
        <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-6">

        <div class="surface p-6 sm:p-8">
                <span class="eyebrow">Booking detail</span>
                @php($primaryBooking = $bookings->first())
                @if ($primaryBooking)
                    <h3 class="mt-4 text-2xl font-semibold text-stone-950" id="detail-room-name">{{ $primaryBooking->room?->name ?? 'Room' }}</h3>
                    <p class="mt-2 text-sm text-stone-600" id="detail-guest-dates">{{ $primaryBooking->contact_name ?? $primaryBooking->user?->name ?? 'Guest' }} · {{ $primaryBooking->check_in->format('F j') }} - {{ $primaryBooking->check_out->format('F j') }}</p>
                    <div class="mt-4 grid gap-3 text-sm text-stone-600">
                        <div class="rounded-2xl bg-stone-50 p-4">Assigned Room: <strong id="detail-physical-room">{{ $primaryBooking->physicalRoom ? $primaryBooking->physicalRoom->name . ' (' . $primaryBooking->physicalRoom->code . ')' : 'None' }}</strong></div>
                        <div class="rounded-2xl bg-stone-50 p-4">Payment method: <strong id="detail-payment-method">{{ strtoupper($primaryBooking->payment_method) }}</strong></div>
                        <div class="rounded-2xl bg-stone-50 p-4">Payment status: <strong id="detail-payment-status">{{ ucfirst(str_replace('_', ' ', $primaryBooking->payment_status)) }}</strong></div>
                        <div class="rounded-2xl bg-stone-50 p-4">Guests: <strong id="detail-guests">{{ $primaryBooking->guests }}</strong></div>
                    </div>

                    <form id="detail-confirm-form" action="{{ $primaryBooking ? route('admin.bookings.payment-status', $primaryBooking) : '' }}" method="POST" class="mt-4 {{ $primaryBooking && strtolower($primaryBooking->status) !== 'confirmed' ? '' : 'hidden' }}">
                        @csrf
                        <input type="hidden" name="payment_status" value="paid">
                        <button type="submit" class="btn-primary w-full py-3">Confirm Reservation</button>
                    </form>
                @else
                    <p class="mt-4 text-sm text-stone-500">No booking details available yet.</p>
                @endif
        </div>


        </div>
    </div>
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

        <div class="form-group">
            <label class="form-label" for="walkin_physical_room_id">Physical room <span class="text-stone-400">(optional, auto-assigned if empty)</span></label>
            <select id="walkin_physical_room_id" name="physical_room_id" class="form-input">
                <option value="">Auto-assign</option>
            </select>
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

        <div class="form-group pt-2">
            <label class="flex items-center gap-3 cursor-pointer select-none">
                <input type="checkbox" id="walkin_with_breakfast" name="with_breakfast" value="1" class="h-5 w-5 rounded border-stone-300 text-brand-primary focus:ring-brand-primary">
                <span class="text-sm font-medium text-stone-900">
                    Include Breakfast <span class="text-xs text-stone-500 font-normal">(₱50 x room capacity per night)</span>
                </span>
            </label>
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
        const physicalRoomSelect = document.getElementById('walkin_physical_room_id');
        const openButtons = document.querySelectorAll('[data-modal-open="walkin-booking-modal"]');

        if (!form || !errors || !checkIn || !checkOut) return;

        const updatePhysicalRooms = async () => {
            if (!physicalRoomSelect || !roomSelect) return;
            const roomId = roomSelect.value;
            const checkInVal = checkIn.value;
            const checkOutVal = checkOut.value;

            if (!roomId || !checkInVal || !checkOutVal || checkOutVal <= checkInVal) {
                physicalRoomSelect.innerHTML = '<option value="">Auto-assign</option>';
                return;
            }

            try {
                const response = await fetch(`{{ route('admin.bookings.available-physical-rooms') }}?room_id=${roomId}&check_in=${checkInVal}&check_out=${checkOutVal}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                if (response.ok) {
                    const rooms = await response.json();
                    let html = '<option value="">Auto-assign</option>';
                    rooms.forEach(r => {
                        html += `<option value="${r.id}">${r.name} (${r.code})</option>`;
                    });
                    physicalRoomSelect.innerHTML = html;
                }
            } catch (err) {
                console.error('Failed to fetch available physical rooms:', err);
            }
        };

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
                updatePhysicalRooms();
            });
        });

        checkIn.addEventListener('change', () => {
            syncCheckOutMin();
            updatePhysicalRooms();
        });
        checkOut.addEventListener('change', updatePhysicalRooms);
        roomSelect.addEventListener('change', updatePhysicalRooms);

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

        // Interactive row clicking for booking details
        document.querySelectorAll('tr[data-booking-id]').forEach(row => {
            row.addEventListener('click', () => {
                const bookingId = row.dataset.bookingId;
                const status = row.dataset.status;
                const roomName = row.dataset.roomName;
                const guestName = row.dataset.guestName;
                const checkIn = row.dataset.checkIn;
                const checkOut = row.dataset.checkOut;
                const physicalRoom = row.dataset.physicalRoom;
                const paymentMethod = row.dataset.paymentMethod;
                const paymentStatus = row.dataset.paymentStatus;
                const guests = row.dataset.guests;

                const detailRoomName = document.getElementById('detail-room-name');
                const detailGuestDates = document.getElementById('detail-guest-dates');
                const detailPhysicalRoom = document.getElementById('detail-physical-room');
                const detailPaymentMethod = document.getElementById('detail-payment-method');
                const detailPaymentStatus = document.getElementById('detail-payment-status');
                const detailGuests = document.getElementById('detail-guests');
                const detailConfirmForm = document.getElementById('detail-confirm-form');

                if (detailRoomName) detailRoomName.textContent = roomName;
                if (detailGuestDates) detailGuestDates.textContent = `${guestName} · ${checkIn} - ${checkOut}`;
                if (detailPhysicalRoom) detailPhysicalRoom.textContent = physicalRoom;
                if (detailPaymentMethod) detailPaymentMethod.textContent = paymentMethod;
                if (detailPaymentStatus) detailPaymentStatus.textContent = paymentStatus;
                if (detailGuests) detailGuests.textContent = guests;

                if (detailConfirmForm) {
                    detailConfirmForm.action = `/admin/bookings/${bookingId}/payment-status`;
                    if (status !== 'confirmed') {
                        detailConfirmForm.classList.remove('hidden');
                    } else {
                        detailConfirmForm.classList.add('hidden');
                    }
                }

                // Highlight active row
                document.querySelectorAll('tr[data-booking-id]').forEach(r => r.classList.remove('bg-brand-primary/5'));
                row.classList.add('bg-brand-primary/5');
            });
        });

        // AJAX calendar navigation and room change
        const calendarCard = document.getElementById('admin-calendar-card');

        async function loadCalendar(url) {
            if (!calendarCard) return;
            calendarCard.classList.add('opacity-50');
            calendarCard.style.pointerEvents = 'none';

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Failed to load calendar');

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newCalendar = doc.getElementById('admin-calendar-card');
                if (newCalendar) {
                    calendarCard.innerHTML = newCalendar.innerHTML;
                    // Update URL bar
                    window.history.pushState({}, '', url);
                    // Re-bind listeners
                    attachCalendarListeners();
                }
            } catch (err) {
                console.error(err);
            } finally {
                calendarCard.classList.remove('opacity-50');
                calendarCard.style.pointerEvents = '';
            }
        }

        function attachCalendarListeners() {
            const select = document.getElementById('calendar-room-select');
            if (select) {
                select.addEventListener('change', () => {
                    const roomId = select.value;
                    const url = new URL(window.location.href);
                    url.searchParams.set('room', roomId);
                    loadCalendar(url.toString());
                });
            }

            const navLinks = document.querySelectorAll('.calendar-nav-btn');
            navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    loadCalendar(link.href);
                });
            });
        }

        // Initialize listeners
        attachCalendarListeners();
    })();
</script>
@endsection


