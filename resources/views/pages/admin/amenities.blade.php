@extends('layouts.admin')

@section('content')
@php
    $amenities = [
        ['icon' => 'Wi-Fi', 'title' => 'Fast Wi-Fi', 'description' => 'Reliable connectivity in every room and shared space.'],
        ['icon' => 'Breakfast', 'title' => 'Breakfast', 'description' => 'Simple, warm morning service for overnight guests.'],
        ['icon' => 'Parking', 'title' => 'Parking', 'description' => 'Dedicated parking access with staff assistance.'],
        ['icon' => 'Support', 'title' => '24/7 Support', 'description' => 'Assistance for booking, payment, and stay concerns.'],
        ['icon' => 'Laundry', 'title' => 'Laundry', 'description' => 'Practical guest convenience for longer stays.'],
        ['icon' => 'Transport', 'title' => 'Transport', 'description' => 'Optional coordination for pickups and drop-offs.'],
    ];
@endphp

<section class="surface p-6 sm:p-8 lg:p-10">
    <span class="eyebrow">Amenities</span>
    <h1 class="mt-4 responsive-title lg:text-5xl">A service grid with icon-first editing.</h1>
    <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">Each amenity is presented as a card so staff can quickly review, edit, or expand the resort's offering.</p>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($amenities as $amenity)
            <article class="card transition hover:-translate-y-1 hover:shadow-[0_20px_45px_rgba(80,61,30,0.12)]" data-amenity-card>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-primary/10 text-sm font-semibold text-brand-primary">{{ $amenity['icon'] }}</div>
                <h2 class="mt-4 text-2xl font-semibold text-stone-950">{{ $amenity['title'] }}</h2>
                <p class="mt-2 text-sm leading-7 text-stone-600">{{ $amenity['description'] }}</p>
                <div class="mt-5 flex gap-2">
                    <a href="{{ route('admin.settings', ['tab' => 'landing']) }}" class="btn-secondary text-sm">Edit</a>
                    <button type="button" class="btn-secondary text-sm" data-amenity-toggle>Disable</button>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endsection
