<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AvailabilityController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'nullable|integer|min:1|max:20',
        ]);

        $checkIn = Carbon::parse($validated['check_in'])->startOfDay();
        $checkOut = Carbon::parse($validated['check_out'])->startOfDay();

        // Number of nights
        $nights = $checkIn->diffInDays($checkOut);

        // Find any room that is available and has no overlapping bookings in blocking statuses
        $rooms = Room::where('status', 'available')->get();

        $availableRoom = null;

        foreach ($rooms as $room) {
            $conflict = Booking::query()
                ->where('room_id', $room->id)
                ->whereIn('status', Booking::BLOCKING_STATUSES)
                ->whereDate('check_in', '<', $checkOut->toDateString())
                ->whereDate('check_out', '>', $checkIn->toDateString())
                ->exists();

            if (! $conflict) {
                $availableRoom = $room;
                break;
            }
        }

        $result = ['available' => (bool) $availableRoom, 'suggestions' => []];

        if (! $availableRoom) {
            // Suggest next available start date within next 30 days
            $searchStart = $checkOut->copy()->addDay();
            $limit = $checkOut->copy()->addDays(30);

            while ($searchStart->lte($limit)) {
                $searchEnd = $searchStart->copy()->addDays($nights);

                $free = Room::where('status', 'available')->get()->first(function ($room) use ($searchStart, $searchEnd) {
                    return ! Booking::query()
                        ->where('room_id', $room->id)
                        ->whereIn('status', Booking::BLOCKING_STATUSES)
                        ->whereDate('check_in', '<', $searchEnd->toDateString())
                        ->whereDate('check_out', '>', $searchStart->toDateString())
                        ->exists();
                });

                if ($free) {
                    $result['suggestions'][] = [
                        'start' => $searchStart->toDateString(),
                        'end' => $searchEnd->toDateString(),
                    ];
                    break;
                }

                $searchStart->addDay();
            }
        }

        return response()->json($result);
    }
}
