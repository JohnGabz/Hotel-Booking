@extends('layouts.admin')

@section('content')
@php
    $conversations = [
        ['name' => 'Alyssa Cruz', 'message' => 'Can we move the check-in by one hour?', 'time' => '2m ago', 'unread' => true],
        ['name' => 'Mark Dela Cruz', 'message' => 'Need the invoice for room 204.', 'time' => '12m ago', 'unread' => false],
        ['name' => 'Sofia Lim', 'message' => 'Do you offer airport pickup?', 'time' => '45m ago', 'unread' => true],
    ];
    $thread = [
        ['from' => 'guest', 'text' => 'Hello, I have a question about late check-in.'],
        ['from' => 'staff', 'text' => 'Absolutely. We can arrange that and note it in your booking.'],
        ['from' => 'guest', 'text' => 'Great, thank you!'],
    ];
@endphp

<section class="surface p-6 sm:p-8 lg:p-10">
    <span class="eyebrow">Messages</span>
    <h1 class="mt-4 text-4xl sm:text-5xl text-stone-950">A split conversation workspace for inquiries.</h1>
    <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">Unread indicators, quick replies, and an inbox list keep support focused and easy to scan.</p>
</section>

<section class="mt-8 grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
    <aside class="surface p-4 sm:p-6">
        <div class="flex items-center justify-between px-2 pb-4">
            <h2 class="text-2xl font-semibold text-stone-950">Inbox</h2>
            <span class="text-sm text-stone-500">{{ count($conversations) }} threads</span>
        </div>
        <div class="space-y-3">
            @foreach ($conversations as $conversation)
                <button class="w-full rounded-[1.5rem] border border-stone-200 bg-white p-4 text-left transition hover:border-brand-primary/30 hover:shadow-[0_12px_30px_rgba(80,61,30,0.08)]">
                    <div class="flex items-start gap-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($conversation['name']) }}&background=B57D59&color=fff" class="h-10 w-10 rounded-full" alt="{{ $conversation['name'] }}">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-semibold text-stone-950">{{ $conversation['name'] }}</p>
                                @if ($conversation['unread'])
                                    <span class="h-2.5 w-2.5 rounded-full bg-brand-primary"></span>
                                @endif
                            </div>
                            <p class="truncate text-sm text-stone-500">{{ $conversation['message'] }}</p>
                            <p class="mt-2 text-xs uppercase tracking-[0.2em] text-stone-400">{{ $conversation['time'] }}</p>
                        </div>
                    </div>
                </button>
            @endforeach
        </div>
    </aside>

    <section class="surface p-6 sm:p-8">
        <div class="flex items-center justify-between gap-4 border-b border-stone-200 pb-4">
            <div>
                <h2 class="text-3xl font-semibold text-stone-950">Alyssa Cruz</h2>
                <p class="text-sm text-stone-500">Guest inquiry thread</p>
            </div>
            <a href="{{ route('admin.bookings') }}" class="btn-secondary text-sm">Open booking</a>
        </div>

        <div class="mt-6 space-y-4">
            @foreach ($thread as $message)
                <div class="flex {{ $message['from'] === 'staff' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-xl rounded-[1.5rem] px-4 py-3 text-sm leading-7 {{ $message['from'] === 'staff' ? 'bg-brand-primary text-white' : 'bg-stone-100 text-stone-700' }}">
                        {{ $message['text'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
            <label class="form-label">Quick reply</label>
            <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                <input class="form-input flex-1" placeholder="Type a response...">
                <button class="btn-primary">Send</button>
            </div>
        </div>
    </section>
</section>
@endsection
