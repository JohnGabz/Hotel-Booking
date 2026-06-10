<div class="space-y-6">
    <section class="surface p-6 sm:p-8">
        <span class="eyebrow">Bookings</span>
        <h2 class="mt-3 text-2xl font-bold text-stone-950">Manage reservations and room assignments</h2>
    </section>

    <div class="surface overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                <tr>
                    <th class="px-4 py-3 text-left">Guest</th>
                    <th class="px-4 py-3 text-left">Room type</th>
                    <th class="px-4 py-3 text-left">Dates</th>
                    <th class="px-4 py-3 text-left">Physical room</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($bookings as $booking)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $booking->contact_name ?? $booking->user?->name ?? 'Guest' }}</td>
                        <td class="px-4 py-3">{{ $booking->room?->type_label ?? 'Room' }}</td>
                        <td class="px-4 py-3">{{ $booking->check_in->format('M j') }} – {{ $booking->check_out->format('M j') }}</td>
                        <td class="px-4 py-3">
                            @if ($booking->physicalRoom)
                                <span class="font-medium">{{ $booking->physicalRoom->name }}</span>
                                <span class="text-stone-400">({{ $booking->physicalRoom->code }})</span>
                            @else
                                <form method="POST" action="{{ route('bookings.assign-room', $booking) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    <select name="physical_room_id" required class="border border-gray-300 rounded-lg px-2 py-1 text-xs">
                                        <option value="">Assign unit...</option>
                                        @foreach (($physicalRoomsByRoom[$booking->room_id] ?? collect()) as $physicalRoom)
                                            <option value="{{ $physicalRoom->id }}">{{ $physicalRoom->name }} ({{ $physicalRoom->code }})</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn-secondary text-xs py-1 px-2">Assign</button>
                                </form>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ ucfirst($booking->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-stone-500">No bookings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
