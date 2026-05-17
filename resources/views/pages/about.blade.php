@extends('layouts.site')

@section('content')
<section class="section-shell pt-8 sm:pt-10">
    <div class="site-shell grid gap-8 lg:grid-cols-[1fr_0.9fr] lg:items-center">
        <div class="space-y-5">
            <span class="eyebrow">About Villa Estella</span>
            <h1 class="max-w-3xl responsive-title">
                {{ $heading }}
            </h1>
            <p class="max-w-2xl text-base leading-7 text-stone-600 sm:text-lg">
                {{ $body }}
            </p>
        </div>

        <div class="surface p-6 sm:p-8">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-[1.5rem] bg-stone-950 p-5 text-white">
                    <p class="text-xs uppercase tracking-[0.25em] text-stone-300">Guest-first</p>
                    <p class="mt-3 text-2xl font-semibold">Simple booking, elegant rooms, clear next steps.</p>
                </div>
                <div class="rounded-[1.5rem] bg-amber-50 p-5 text-stone-950">
                    <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Staff-ready</p>
                    <p class="mt-3 text-2xl font-semibold">Manage reservations, reviews, and room status in one place.</p>
                </div>
            </div>
            <div class="mt-4 rounded-[1.5rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm leading-7 text-stone-600">
                    The system is built to feel calm, modern, and easy to use on mobile or desktop, while staying focused on conversion and clarity.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
