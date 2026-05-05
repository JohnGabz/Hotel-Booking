@extends('layouts.site')

@section('content')
@php
    $services = [
        ['title' => 'Booking support', 'description' => 'Availability checks, flexible dates, and quick reservation flow.', 'icon' => '01'],
        ['title' => 'Payment options', 'description' => 'Clear payment paths with secure checkout and visible totals.', 'icon' => '02'],
        ['title' => 'Guest reviews', 'description' => 'Verified feedback that builds confidence before and after the stay.', 'icon' => '03'],
    ];
@endphp

<section class="section-shell pt-8 sm:pt-10">
    <div class="site-shell">
        <div class="text-center">
            <span class="eyebrow">Our services</span>
            <h1 class="mt-5 text-5xl leading-[0.95] text-stone-950 sm:text-6xl">Everything your guests need, wrapped in a calm luxury experience.</h1>
            <p class="mx-auto mt-5 max-w-3xl text-base leading-7 text-stone-600 sm:text-lg">
                {{ $intro }}
            </p>
        </div>

        <div class="mt-12 grid gap-6 lg:grid-cols-3">
            @foreach ($services as $service)
                <article class="card text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-lg font-semibold text-amber-700">
                        {{ $service['icon'] }}
                    </div>
                    <h2 class="mt-5 text-2xl font-semibold text-stone-950">{{ $service['title'] }}</h2>
                    <p class="mt-3 text-sm leading-7 text-stone-600">{{ $service['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
