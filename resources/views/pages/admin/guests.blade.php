@extends('layouts.admin')

@section('content')
<div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
    <section class="surface p-6 sm:p-8">
        <span class="eyebrow">Guests</span>
        <h1 class="mt-4 responsive-title lg:text-5xl">Profile-based guest directory.</h1>
        <p class="mt-4 text-sm leading-7 text-stone-600">Scan guest identity, review booking count, and open a detailed history panel without leaving the list.</p>

        <!-- Search & Filter Controls -->
        <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-stone-100 pb-5">
            <!-- Tabs -->
            <div class="inline-flex rounded-lg p-1 bg-stone-100 shrink-0">
                <a href="{{ route('admin.guests', ['type' => 'registered', 'search' => $search]) }}" class="px-4 py-2 text-xs font-semibold rounded-md transition-all {{ $type === 'registered' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-900' }}">
                    Registered Guests
                </a>
                <a href="{{ route('admin.guests', ['type' => 'unregistered', 'search' => $search]) }}" class="px-4 py-2 text-xs font-semibold rounded-md transition-all {{ $type === 'unregistered' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-900' }}">
                    Unregistered Guests
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.guests') }}" method="GET" class="w-full sm:max-w-xs flex gap-2">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name/email/phone..." class="form-input py-1.5 px-3 text-xs w-full">
                <button type="submit" class="btn-primary py-1.5 px-3 text-xs font-bold">Search</button>
            </form>
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($users as $user)
                <a href="{{ route('admin.guests', ['guest' => $user->id, 'type' => $type, 'search' => $search, 'page' => $users->currentPage()]) }}" class="block rounded-[1.5rem] border {{ ($selectedGuest?->id ?? null) === $user->id ? 'border-brand-primary bg-brand-primary/5' : 'border-stone-200 bg-white' }} p-4 transition hover:border-brand-primary/30 hover:shadow-[0_14px_35px_rgba(80,61,30,0.08)]">
                    <div class="flex items-center gap-4">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=B6424F&color=fff" class="h-12 w-12 rounded-full" alt="{{ $user->name }}">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-stone-950">{{ $user->name }}</p>
                            <p class="truncate text-sm text-stone-500">{{ $user->email }}</p>
                        </div>
                        <span class="badge-primary">{{ $user->bookings->count() }} stays</span>
                    </div>
                </a>
            @empty
                <p class="text-stone-500">No guest records found.</p>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </section>

    <aside class="space-y-6">
        <section class="surface p-6 sm:p-8">
            <span class="eyebrow">Guest profile</span>
            @if ($selectedGuest)
                <div class="mt-4 flex items-center gap-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($selectedGuest->name) }}&background=B57D59&color=fff" class="h-16 w-16 rounded-full" alt="{{ $selectedGuest->name }}">
                    <div>
                        <h2 class="text-2xl font-semibold text-stone-950">{{ $selectedGuest->name }}</h2>
                        <p class="text-sm text-stone-500">{{ $selectedGuest->email }}</p>
                        @if ($selectedGuest->contact_number)
                            <p class="text-sm text-stone-500 font-medium mt-0.5">Contact: {{ $selectedGuest->contact_number }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-stone-50 p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-stone-400">Bookings</p>
                        <p class="mt-2 text-2xl font-semibold text-stone-950">{{ $selectedGuest->bookings->count() }}</p>
                    </div>
                    <div class="rounded-2xl bg-stone-50 p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-stone-400">Role</p>
                        <p class="mt-2 text-2xl font-semibold text-stone-950">Guest</p>
                    </div>
                    <div class="rounded-2xl bg-stone-50 p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-stone-400">Verified</p>
                        <p class="mt-2 text-2xl font-semibold text-stone-950">{{ $selectedGuest->email_verified_at ? 'Yes' : 'No' }}</p>
                    </div>
                </div>
            @endif
        </section>

        <section class="surface p-6 sm:p-8">
            <span class="eyebrow">Stay history</span>
            @if ($selectedGuest && $selectedGuest->bookings->isNotEmpty())
                <div class="mt-4 space-y-3">
                    @foreach ($selectedGuest->bookings as $booking)
                        <div class="rounded-2xl bg-stone-50 p-4">
                            <p class="font-semibold text-stone-950">{{ $booking->room?->name ?? 'Room' }}</p>
                            <p class="text-sm text-stone-500">{{ $booking->check_in->format('M j') }} - {{ $booking->check_out->format('M j') }} · {{ ucfirst($booking->status) }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-4 text-sm text-stone-500">No stay history yet.</p>
            @endif
        </section>
    </aside>
</div>
@endsection
