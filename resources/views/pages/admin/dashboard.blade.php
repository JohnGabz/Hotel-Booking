@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <section id="overview" class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="surface p-6 sm:p-8 lg:p-10">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <span class="eyebrow">Admin overview</span>
                    <h1 class="mt-4 text-4xl sm:text-5xl text-stone-950">Manage bookings with a calm, structured workflow.</h1>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-stone-600">
                        Monitor reservations, verify payments, and keep room operations clear and fast from one place.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('rooms.index') }}" class="btn-primary">New booking</a>
                    <a href="{{ route('admin.settings', ['tab' => 'landing']) }}" class="btn-secondary">Edit landing page</a>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="card">
                    <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Total bookings</p>
                    <p class="mt-3 text-3xl font-semibold text-stone-950">{{ number_format($bookingsCount ?? 0) }}</p>
                    <p class="mt-2 text-sm text-emerald-600">Confirmed: {{ number_format($confirmedCount ?? 0) }}</p>
                </article>
                <article class="card">
                    <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Revenue</p>
                    <p class="mt-3 text-3xl font-semibold text-stone-950">₱{{ number_format($bookings->sum('total') ?? 0, 2) }}</p>
                    <p class="mt-2 text-sm text-stone-500">All collected reservations</p>
                </article>
                <article class="card">
                    <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Available rooms</p>
                    <p class="mt-3 text-3xl font-semibold text-stone-950">{{ number_format($availableRooms ?? 0) }}</p>
                    <p class="mt-2 text-sm text-stone-500">Ready for new guests</p>
                </article>
                <article class="card">
                    <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Occupancy</p>
                    <p class="mt-3 text-3xl font-semibold text-stone-950">{{ ($confirmedCount ?? 0) && ($bookingsCount ?? 0) ? round(($confirmedCount / max(1, $bookingsCount)) * 100, 0) : 0 }}%</p>
                    <p class="mt-2 text-sm text-stone-500">Based on booked stays</p>
                </article>
            </div>
        </div>

        <aside class="surface p-6 sm:p-8 lg:p-10">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="eyebrow">Quick actions</p>
                    <h2 class="mt-4 text-2xl font-semibold text-stone-950">Stay on top of admin tasks</h2>
                </div>
                <a href="#bookings" class="text-sm font-semibold text-brand-primary hover:text-brand-secondary">Jump to bookings</a>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                <a href="#bookings" class="btn-secondary justify-start">Recent bookings</a>
                <a href="#rooms" class="btn-secondary justify-start">Room inventory</a>
                <a href="#payments" class="btn-secondary justify-start">Payment verification</a>
                <a href="#reviews" class="btn-secondary justify-start">Pending reviews</a>
            </div>

            <div class="mt-6 rounded-[1.5rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Today</p>
                <p class="mt-2 text-sm leading-6 text-stone-700">
                    Keep the booking flow moving: confirm room availability, resolve payment proofs, then approve reviews.
                </p>
            </div>
        </aside>
    </section>

    <section id="bookings" class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <div class="surface p-6 sm:p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <span class="eyebrow">Booking management</span>
                    <h2 class="mt-4 text-3xl font-semibold text-stone-950">Recent bookings</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-600">
                        Track the latest reservations, identify the guest, and confirm the stay details without switching screens.
                    </p>
                </div>
                <a href="{{ route('rooms.index') }}" class="text-sm font-semibold text-brand-primary hover:text-brand-secondary">Create booking</a>
            </div>

            <div class="mt-6 overflow-hidden rounded-[1.5rem] border border-stone-200 bg-white">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-stone-50 text-xs uppercase tracking-[0.18em] text-stone-500">
                            <tr>
                                <th class="px-5 py-4">Booking</th>
                                <th class="px-5 py-4">Guest</th>
                                <th class="px-5 py-4">Property</th>
                                <th class="px-5 py-4">Dates</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($bookings as $booking)
                                <tr class="transition hover:bg-stone-50/80">
                                    <td class="px-5 py-4 font-medium text-stone-900">#{{ $booking->id }}</td>
                                    <td class="px-5 py-4 text-stone-700">{{ $booking->user?->name ?? 'Guest' }}</td>
                                    <td class="px-5 py-4 text-stone-700">{{ $booking->room?->name ?? 'Room' }}</td>
                                    <td class="px-5 py-4 text-stone-600">{{ $booking->check_in->format('M j') }} - {{ $booking->check_out->format('M j') }}</td>
                                    <td class="px-5 py-4">
                                        <span class="{{ $booking->status === 'confirmed' ? 'badge-primary' : 'inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-700' }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 font-medium text-stone-900">₱{{ number_format($booking->total, 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-stone-500">No bookings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="surface p-6 sm:p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <span class="eyebrow">Room management</span>
                    <h2 class="mt-4 text-3xl font-semibold text-stone-950">Room inventory</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-600">
                        Keep room status aligned with reality, update availability in seconds, and maintain a clear view of nightly rates.
                    </p>
                </div>
                <a href="{{ route('rooms.index') }}" class="text-sm font-semibold text-brand-primary hover:text-brand-secondary">Browse rooms</a>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($rooms as $room)
                    <div class="rounded-[1.25rem] border border-stone-200 bg-white p-4 shadow-[0_12px_30px_rgba(80,61,30,0.05)]">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-stone-950">{{ $room->name }}</p>
                                <p class="text-sm text-stone-500">{{ ucfirst($room->status) }} · ₱{{ number_format($room->price, 0) }} / night</p>
                                <p class="mt-2 text-xs uppercase tracking-[0.22em] text-stone-400">Capacity {{ $room->capacity }} guests</p>
                            </div>

                            <form action="{{ route('admin.rooms.status', $room) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <select name="status" class="form-input text-sm w-40" aria-label="Room status">
                                    @foreach (['available', 'occupied', 'maintenance'] as $status)
                                        <option value="{{ $status }}" @selected($room->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn-secondary text-sm">Update</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-stone-500">No room records found.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section id="payments" class="surface p-6 sm:p-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <span class="eyebrow">Payment verification</span>
                <h2 class="mt-4 text-3xl font-semibold text-stone-950">Review submitted proofs</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-600">
                    Verify incoming payment proofs, change the status when cleared, and keep guests moving through the booking flow.
                </p>
            </div>
            <span class="text-sm text-stone-500">{{ $pendingPayments->count() }} items</span>
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($pendingPayments as $booking)
                <article class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="font-semibold text-stone-950">{{ $booking->user?->name ?? 'Guest' }} · {{ $booking->room?->name ?? 'Room' }}</p>
                            <p class="mt-1 text-sm text-stone-600">{{ strtoupper($booking->payment_method) }} · Ref: {{ $booking->payment_reference ?: 'Not yet submitted' }}</p>
                            <p class="mt-1 text-sm text-stone-600">Status: {{ strtoupper($booking->payment_status) }}</p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @if ($booking->payment_proof_path)
                                <a href="{{ asset('storage/' . $booking->payment_proof_path) }}" target="_blank" rel="noreferrer" class="btn-secondary text-sm">View proof</a>
                            @endif
                            <a href="#reviews" class="btn-secondary text-sm">Next task</a>
                        </div>
                    </div>

                    <form action="{{ route('admin.bookings.payment-status', $booking) }}" method="POST" class="mt-4 flex flex-wrap items-center gap-2">
                        @csrf
                        <select name="payment_status" class="form-input text-sm w-full sm:w-auto sm:min-w-56" aria-label="Payment status">
                            @foreach (['pending', 'for_verification', 'paid', 'failed'] as $status)
                                <option value="{{ $status }}" @selected($booking->payment_status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary text-sm">Update payment</button>
                    </form>
                </article>
            @empty
                <p class="text-stone-500">No pending payment records at the moment.</p>
            @endforelse
        </div>
    </section>

    <section id="reviews" class="surface p-6 sm:p-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <span class="eyebrow">Guest reviews</span>
                <h2 class="mt-4 text-3xl font-semibold text-stone-950">Approve feedback</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-600">
                    Moderate reviews with a quick read of sentiment, then approve the ones that help future guests book with confidence.
                </p>
            </div>
            <span class="text-sm text-stone-500">{{ $reviews->count() }} pending</span>
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($reviews as $review)
                <article class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-semibold text-stone-950">{{ $review->user?->name ?? 'Guest' }}</p>
                            <p class="text-sm text-stone-600">{{ $review->room?->name ?? 'Room' }}</p>
                        </div>
                        <span class="badge-primary">{{ $review->rating }}/5</span>
                    </div>
                    <p class="mt-3 text-sm leading-7 text-stone-600">{{ $review->comment }}</p>
                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="btn-primary text-sm">Approve review</button>
                    </form>
                </article>
            @empty
                <p class="text-stone-500">No pending reviews at the moment.</p>
            @endforelse
        </div>
    </section>

    <section id="content" class="surface p-6 sm:p-8">
        <div>
            <span class="eyebrow">Content editor</span>
            <h2 class="mt-4 text-3xl font-semibold text-stone-950">Update site copy</h2>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-600">
                Edit the public-facing copy for the villa's story, service highlights, and contact details without leaving the dashboard.
            </p>
        </div>

        <form action="{{ route('admin.site-content.update') }}" method="POST" class="mt-6 grid gap-4">
            @csrf
            <div class="grid gap-4 xl:grid-cols-2">
                <div class="form-group xl:col-span-2">
                    <label class="form-label" for="about_heading">About heading</label>
                    <input id="about_heading" name="about_heading" class="form-input" value="{{ old('about_heading', $siteContent['about_heading'] ?? '') }}">
                </div>
                <div class="form-group xl:col-span-2">
                    <label class="form-label" for="about_body">About body</label>
                    <textarea id="about_body" name="about_body" rows="4" class="form-input">{{ old('about_body', $siteContent['about_body'] ?? '') }}</textarea>
                </div>
                <div class="form-group xl:col-span-2">
                    <label class="form-label" for="services_intro">Services intro</label>
                    <textarea id="services_intro" name="services_intro" rows="3" class="form-input">{{ old('services_intro', $siteContent['services_intro'] ?? '') }}</textarea>
                </div>
                <div class="form-group xl:col-span-2">
                    <label class="form-label" for="faqs_intro">FAQs intro</label>
                    <textarea id="faqs_intro" name="faqs_intro" rows="3" class="form-input">{{ old('faqs_intro', $siteContent['faqs_intro'] ?? '') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="contact_email">Contact email</label>
                    <input id="contact_email" name="contact_email" type="email" class="form-input" value="{{ old('contact_email', $siteContent['contact_email'] ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="contact_phone">Contact phone</label>
                    <input id="contact_phone" name="contact_phone" class="form-input" value="{{ old('contact_phone', $siteContent['contact_phone'] ?? '') }}">
                </div>
                <div class="form-group xl:col-span-2">
                    <label class="form-label" for="contact_address">Contact address</label>
                    <input id="contact_address" name="contact_address" class="form-input" value="{{ old('contact_address', $siteContent['contact_address'] ?? '') }}">
                </div>
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="btn-primary">Save content</button>
                <a href="#overview" class="btn-secondary">Back to top</a>
            </div>
        </form>
    </section>
</div>
@endsection
