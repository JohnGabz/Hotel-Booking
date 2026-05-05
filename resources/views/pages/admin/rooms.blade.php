@extends('layouts.admin')

@section('content')
@php
    $roomImages = [
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?auto=format&fit=crop&w=1200&q=80',
    ];
    $viewMode = $viewMode ?? 'grid';
@endphp

<div class="space-y-8">
    <section class="surface p-6 sm:p-8 lg:p-10">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="eyebrow">Rooms</span>
                <h1 class="mt-4 text-4xl sm:text-5xl text-stone-950">Room inventory designed as a visual catalog.</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">Switch between grid and table modes, inspect rates, and update availability from one consistent screen.</p>
            </div>
            <a href="#" data-modal-open="generic-action-modal" data-modal-title="Add room" class="btn-primary">Add room</a>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('admin.rooms', ['view' => 'grid']) }}" class="{{ $viewMode === 'grid' ? 'btn-primary' : 'btn-secondary' }}">Grid view</a>
            <a href="{{ route('admin.rooms', ['view' => 'table']) }}" class="{{ $viewMode === 'table' ? 'btn-primary' : 'btn-secondary' }}">Table view</a>
        </div>
    </section>

    @if ($viewMode === 'table')
        <section class="surface p-6 sm:p-8">
            <h2 class="text-3xl font-semibold text-stone-950">Room table</h2>
            <div class="mt-6 overflow-x-auto rounded-[1.5rem] border border-stone-200 bg-white">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-stone-50 text-xs uppercase tracking-[0.18em] text-stone-500">
                        <tr>
                            <th class="px-5 py-4">Room</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Capacity</th>
                            <th class="px-5 py-4">Rate</th>
                            <th class="px-5 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($rooms as $room)
                            <tr class="transition hover:bg-stone-50/80">
                                <td class="px-5 py-4 font-semibold text-stone-950">{{ $room->name }}</td>
                                <td class="px-5 py-4 text-stone-600">{{ ucfirst($room->status) }}</td>
                                <td class="px-5 py-4 text-stone-600">{{ $room->capacity }}</td>
                                <td class="px-5 py-4 text-stone-600">₱{{ number_format($room->price, 0) }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('rooms.show', $room->slug) }}" class="btn-secondary text-sm">View</a>
                                        <a href="#" data-modal-open="generic-action-modal" data-modal-title="Edit room" class="btn-secondary text-sm btn-edit">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <section class="grid gap-6 lg:grid-cols-3">
            @forelse ($rooms as $room)
                <article class="surface overflow-hidden">
                    <div class="h-56 overflow-hidden bg-stone-200">
                        <img src="{{ $roomImages[$loop->index % count($roomImages)] }}" alt="{{ $room->name }}" class="h-full w-full object-cover transition duration-500 hover:scale-105">
                    </div>
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-semibold text-stone-950">{{ $room->name }}</h2>
                                <p class="mt-1 text-sm text-stone-500">{{ ucfirst($room->status) }} · Capacity {{ $room->capacity }}</p>
                            </div>
                            <span class="badge-primary">₱{{ number_format($room->price, 0) }}</span>
                        </div>
                        <p class="mt-4 text-sm leading-7 text-stone-600">{{ $room->description }}</p>
                        <div class="mt-6 flex flex-wrap gap-2">
                            <a href="{{ route('rooms.show', $room->slug) }}" class="btn-primary">View room</a>
                            <a href="#" data-modal-open="generic-action-modal" data-modal-title="Edit room" class="btn-secondary btn-edit">Edit</a>
                            <a href="#" data-modal-open="generic-action-modal" data-modal-title="Delete room" class="btn-secondary btn-delete">Delete</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="surface p-8 text-center text-stone-500">No rooms found.</div>
            @endforelse
        </section>
    @endif
</div>
@endsection
