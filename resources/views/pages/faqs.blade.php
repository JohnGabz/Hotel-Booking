@extends('layouts.site')

@section('content')
<section class="section-shell pt-8 sm:pt-10">
    <div class="site-shell">
        <div class="text-center">
            <span class="eyebrow">FAQs</span>
            <h1 class="mt-5 responsive-title">Everything guests usually ask, answered clearly.</h1>
            <p class="mx-auto mt-5 max-w-3xl text-base leading-7 text-stone-600 sm:text-lg">
                {{ $intro }}
            </p>
        </div>

        <div class="mt-12 space-y-5">
            <div class="card">
                <h2 class="text-2xl font-semibold text-stone-950">How do I check if a room is available?</h2>
                <p class="mt-3 text-sm leading-7 text-stone-600">Choose a room, pick your dates, and submit the booking form. The system handles availability checks automatically.</p>
            </div>
            <div class="card">
                <h2 class="text-2xl font-semibold text-stone-950">What payment methods are accepted?</h2>
                <p class="mt-3 text-sm leading-7 text-stone-600">Guests can book using GCash or Landbank, with clear payment details shown during the reservation flow.</p>
            </div>
            <div class="card">
                <h2 class="text-2xl font-semibold text-stone-950">Can I update a booking?</h2>
                <p class="mt-3 text-sm leading-7 text-stone-600">Bookings can be reviewed by staff, and guests can contact the hotel directly for changes when needed.</p>
            </div>
            <div class="card">
                <h2 class="text-2xl font-semibold text-stone-950">How are reviews handled?</h2>
                <p class="mt-3 text-sm leading-7 text-stone-600">Guests can submit feedback after a confirmed stay, and approved reviews appear on the room page.</p>
            </div>
        </div>
    </div>
</section>
@endsection
