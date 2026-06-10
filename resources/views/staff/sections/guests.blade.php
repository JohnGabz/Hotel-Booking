<div class="space-y-6">
    <section class="surface p-6 sm:p-8">
        <span class="eyebrow">Guests</span>
        <h2 class="mt-3 text-2xl font-bold text-stone-950">Guest directory</h2>
        <p class="mt-2 text-sm text-stone-600">Contact numbers and booking history for registered guests.</p>
    </section>

    <div class="grid gap-4">
        @forelse ($users as $user)
            <article class="surface p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-stone-950">{{ $user->name }}</h3>
                        <p class="text-sm text-stone-500">{{ $user->email }}</p>
                        <p class="mt-1 text-sm font-medium text-stone-700">
                            Contact: {{ $user->contact_number ?? 'Not provided' }}
                        </p>
                    </div>
                    <span class="badge-primary">{{ $user->bookings->count() }} bookings</span>
                </div>
                @if ($user->bookings->isNotEmpty())
                    <ul class="mt-4 space-y-2 text-sm text-stone-600">
                        @foreach ($user->bookings->take(3) as $booking)
                            <li>{{ $booking->room?->type_label ?? 'Room' }} · {{ $booking->check_in->format('M j, Y') }} – {{ $booking->check_out->format('M j, Y') }}</li>
                        @endforeach
                    </ul>
                @endif
            </article>
        @empty
            <p class="text-stone-500">No guest records found.</p>
        @endforelse
    </div>
</div>
