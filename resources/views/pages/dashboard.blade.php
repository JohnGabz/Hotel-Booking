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
                                @elseif ($booking->payment_status === 'paid')
                                    <div class="mt-4 rounded-[1rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                        Payment verified{{ $booking->paid_at ? ' on ' . $booking->paid_at->format('M j, Y g:i A') : '' }}.
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
                    <p class="mt-4 text-stone-600">Visit any room page to leave feedback after a confirmed stay. Your recent room selections are ready below.</p>
                </div>

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
            </div>
        </div>
    </div>
</section>
@endsection

