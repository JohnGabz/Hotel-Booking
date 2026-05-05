<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::all();

        return view('pages.rooms.index', compact('rooms'), [
            'seo' => [
                'title' => 'Rooms — ' . config('app.name'),
                'description' => 'Browse available villas and check room details for your stay.',
            ],
        ]);
    }

    public function show(Room $room): View
    {
        $room->load(['reviews.user']);

        $canReview = false;

        if (Auth::check()) {
            $canReview = Booking::where('user_id', Auth::id())
                ->where('room_id', $room->id)
                ->where('status', 'confirmed')
                ->whereDate('check_out', '<', now())
                ->exists();
        }

        return view('pages.rooms.show', compact('room', 'canReview'), [
            'seo' => [
                'title' => $room->name . ' — ' . config('app.name'),
                'description' => 'Reserve ' . $room->name . ' with GCash or Landbank payment options.',
            ],
        ]);
    }

    public function availability(Request $request, Room $room): JsonResponse
    {
        $checkIn = $request->query('check_in');
        $checkOut = $request->query('check_out');

        $hasOverlap = false;

        if ($checkIn && $checkOut) {
            $hasOverlap = Booking::where('room_id', $room->id)
                ->where('status', 'confirmed')
                ->where(function ($query) use ($checkIn, $checkOut) {
                    $query->whereBetween('check_in', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out', [$checkIn, $checkOut])
                        ->orWhere(function ($query) use ($checkIn, $checkOut) {
                            $query->where('check_in', '<=', $checkIn)
                                ->where('check_out', '>=', $checkOut);
                        });
                })
                ->exists();
        }

        $isAvailable = $room->status === 'available' && ! $hasOverlap;

        return response()->json([
            'room_status' => $room->status,
            'available' => $isAvailable,
            'message' => $isAvailable
                ? 'Room is available for the selected dates.'
                : 'Room is unavailable for the selected dates.',
            'checked_at' => now()->toIso8601String(),
        ]);
    }
}
