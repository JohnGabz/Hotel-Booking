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
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'room_id' => 'nullable|integer|exists:rooms,id',
            'guests' => 'nullable|integer|min:1|max:20',
        ]);

        $checkIn = Carbon::parse($validated['check_in'])->toDateString();
        $checkOut = Carbon::parse($validated['check_out'])->toDateString();

        // Number of nights
        $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));
        $nights = max(1, $nights);

        if ($request->filled('room_id')) {
            $room = Room::find($request->input('room_id'));
            $isAvailable = $room && $room->isAvailableFor($checkIn, $checkOut);

            return response()->json([
                'available' => $isAvailable,
                'room' => $room ? [
                    'id' => $room->id,
                    'name' => $room->name,
                    'slug' => $room->slug,
                    'type_label' => $room->type_label,
                    'price' => $room->price,
                    'capacity' => $room->capacity,
                    'description' => $room->description,
                ] : null,
                'message' => $isAvailable
                    ? 'This room type is available for the selected dates.'
                    : 'This room type is unavailable for the selected dates.'
            ]);
        }

        $rooms = Room::where('status', 'available')->get();
        $availableRooms = $rooms->filter(
            fn (Room $room) => $room->isAvailableFor($checkIn, $checkOut)
        )->map(fn (Room $room) => [
            'id' => $room->id,
            'name' => $room->name,
            'type_label' => $room->type_label,
            'price' => $room->price,
            'capacity' => $room->capacity,
        ])->values();

        $anyAvailable = $availableRooms->isNotEmpty();
        $result = [
            'available' => $anyAvailable,
            'available_rooms' => $availableRooms,
            'suggestions' => [],
        ];

        if (! $anyAvailable) {
            // Suggest next available start date within next 30 days
            $searchStart = Carbon::parse($checkOut)->addDay();
            $limit = Carbon::parse($checkOut)->addDays(30);

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
