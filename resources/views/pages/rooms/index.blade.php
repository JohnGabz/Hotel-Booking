@extends('layouts.site')

@section('content')
@php
    $roomImages = [
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80',
    ];
    $resolveRoomImage = fn (?string $image) => $image ? (str_starts_with($image, 'http') ? $image : asset('storage/' . $image)) : null;
@endphp

<section class="section-shell pt-8 sm:pt-10">
    <div class="site-shell">
        <div class="surface-strong relative overflow-hidden text-white">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1501117716987-c8e6d4b7f5b8?auto=format&fit=crop&w=1600&q=80');"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-stone-950/90 via-stone-950/72 to-stone-950/40"></div>
            <div class="relative px-6 py-10 sm:px-10 sm:py-12 lg:px-12 lg:py-16">
                <span class="eyebrow border-white/20 bg-white/10 text-white">Our rooms</span>
                <div class="mt-6 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl space-y-4">
                        <h1 class="responsive-title text-white">Choose the perfect room for your stay.</h1>
                        <p class="max-w-2xl text-base leading-7 text-stone-200 sm:text-lg">
                            Explore refined spaces designed for comfort, privacy, and a premium guest experience.
                        </p>
                    </div>
                    <a href="{{ route('home') }}" class="btn-secondary border-white/20 bg-white/10 px-5 py-3 text-white hover:border-white/30 hover:bg-white/15">
                        Back to home
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="card">
                <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Available rooms</p>
                <p class="mt-3 text-3xl font-semibold text-stone-950">{{ $rooms->count() }}</p>
            </div>
            <div class="card">
                <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Average rate</p>
                <p class="mt-3 text-3xl font-semibold text-stone-950">₱{{ number_format($rooms->avg('price') ?? 0, 0) }}</p>
            </div>
            <div class="card">
                <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Guest-ready</p>
                <p class="mt-3 text-3xl font-semibold text-stone-950">24/7</p>
            </div>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-3">
            @forelse ($rooms as $room)
                @php
                    $image = collect($room->images ?? [])->first();
                    $image = $resolveRoomImage($image) ?? $roomImages[$loop->index % count($roomImages)];
                    $amenities = collect($room->amenities ?? [])->take(3);
                @endphp
                <article class="overflow-hidden rounded-[2rem] border border-stone-200 bg-white shadow-[0_20px_60px_rgba(80,61,30,0.08)] transition duration-200 hover:-translate-y-1 hover:shadow-[0_30px_70px_rgba(80,61,30,0.14)]">
                    <div class="relative h-72 overflow-hidden">
                        <img src="{{ $image }}" alt="{{ $room->name }}" class="h-full w-full object-cover transition duration-500 hover:scale-105" loading="lazy" decoding="async" sizes="(min-width: 1024px) 33vw, 100vw">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950/70 via-stone-950/10 to-transparent"></div>
                        <div class="absolute left-5 top-5 rounded-full bg-white/92 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-stone-700 backdrop-blur">
                            {{ ucfirst($room->status) }}
                        </div>
                        <div class="absolute bottom-5 left-5 rounded-full bg-stone-950/80 px-4 py-2 text-sm font-semibold text-white backdrop-blur">
                            ₱{{ number_format($room->price, 0) }} / night
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-semibold text-stone-950">{{ $room->name }}</h2>
                                <p class="mt-2 text-sm uppercase tracking-[0.2em] text-stone-500">{{ $room->capacity }} guests</p>
                            </div>
                            <div class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">4.9</div>
                        </div>

                        <p class="mt-4 text-sm leading-7 text-stone-600">{{ $room->description }}</p>

                        @if ($amenities->isNotEmpty())
                            <div class="mt-5 flex flex-wrap gap-2">
                                @foreach ($amenities as $amenity)
                                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-600">{{ $amenity }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-6 flex items-center justify-between gap-4">
                            <a href="{{ route('rooms.show', $room) }}" class="btn-primary px-4 py-2.5 text-sm">View Room</a>
                            @if ($room->status !== 'available')
                                <span class="text-xs uppercase tracking-[0.2em] text-rose-600">Unavailable</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="card lg:col-span-3 text-center">
                    <p class="text-stone-600">Rooms will appear here once they are published.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
