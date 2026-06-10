<div class="space-y-6">
    <section class="surface p-6 sm:p-8">
        <span class="eyebrow">Bookings</span>
        <h2 class="mt-3 text-2xl font-bold text-stone-950">Staff booking overview</h2>
    </section>

    <div class="surface overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                <tr>
                    <th class="px-4 py-3 text-left">Guest</th>
                    <th class="px-4 py-3 text-left">Room type</th>
                    <th class="px-4 py-3 text-left">Physical room</th>
                    <th class="px-4 py-3 text-left">Dates</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($bookings as $booking)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $booking->contact_name ?? $booking->user?->name ?? 'Guest' }}</td>
                        <td class="px-4 py-3">{{ $booking->room?->type_label ?? 'Room' }}</td>
                        <td class="px-4 py-3">
                            @if ($booking->physicalRoom)
                                {{ $booking->physicalRoom->name }} ({{ $booking->physicalRoom->code }})
                            @else
                                <span class="text-stone-400">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $booking->check_in->format('M j') }} – {{ $booking->check_out->format('M j') }}</td>
                        <td class="px-4 py-3">{{ ucfirst($booking->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-stone-500">No bookings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
