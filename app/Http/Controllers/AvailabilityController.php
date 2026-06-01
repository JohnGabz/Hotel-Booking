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

        $rooms = Room::where('status', 'available')->get();
        $availableRoom = $rooms->first(
            fn (Room $room) => $room->isAvailableFor($checkIn->toDateString(), $checkOut->toDateString())
        );

        $result = ['available' => (bool) $availableRoom, 'suggestions' => []];

        if (! $availableRoom) {
            // Suggest next available start date within next 30 days
            $searchStart = $checkOut->copy()->addDay();
            $limit = $checkOut->copy()->addDays(30);

            while ($searchStart->lte($limit)) {
                $searchEnd = $searchStart->copy()->addDays($nights);

                $free = Room::where('status', 'available')->get()->first(
                    fn (Room $room) => $room->isAvailableFor($searchStart->toDateString(), $searchEnd->toDateString())
                );

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
