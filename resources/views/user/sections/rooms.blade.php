@php
    $rooms = $rooms ?? \App\Models\Room::available()->get();
@endphp

<div class="grid gap-6 lg:grid-cols-3">
    @forelse ($rooms as $room)
        <article class="surface p-5">
            <h3 class="text-lg font-semibold text-stone-950">{{ $room->type_label }}</h3>
            <p class="mt-2 text-sm text-stone-600">{{ Str::limit($room->description, 100) }}</p>
            <p class="mt-3 font-semibold text-brand-primary">₱{{ number_format($room->price, 0) }}</p>
            <a href="{{ route('rooms.show', $room->slug) }}" class="btn-primary mt-4 inline-flex text-sm">View details</a>
        </article>
    @empty
        <p class="text-stone-500">No rooms available.</p>
    @endforelse
</div>
