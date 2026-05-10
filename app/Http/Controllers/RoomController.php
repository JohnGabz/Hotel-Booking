<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::available()->get();

        return view('pages.rooms.index', compact('rooms'), [
            'seo' => [
                'title' => 'Rooms — ' . config('app.name'),
                'description' => 'Browse available villas and check room details for your stay.',
            ],
        ]);
    }

    public function show(Request $request, Room $room): View
    {
        if ($room->status === 'maintenance') {
            abort(404);
        }

        $room->load(['reviews.user']);

        $canReview = false;

        if (Auth::check()) {
            $canReview = Booking::where('user_id', Auth::id())
                ->where('room_id', $room->id)
                ->where('status', 'confirmed')
                ->whereDate('check_out', '<', now())
                ->exists();
        }

        $calendar = $this->buildBookingCalendar($room, $request->query('month'));

        return view('pages.rooms.show', compact('room', 'canReview', 'calendar'), [
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

    protected function buildBookingCalendar(Room $room, ?string $monthInput): array
    {
        try {
            $monthStart = $monthInput
                ? Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth()
                : now()->startOfMonth();
        } catch (\Throwable) {
            $monthStart = now()->startOfMonth();
        }

        $monthEnd = $monthStart->copy()->endOfMonth();
        $previousMonth = $monthStart->copy()->subMonthNoOverflow()->format('Y-m');
        $nextMonth = $monthStart->copy()->addMonthNoOverflow()->format('Y-m');

        $bookings = Booking::query()
            ->where('room_id', $room->id)
            ->where('status', 'confirmed')
            ->whereDate('check_in', '<=', $monthEnd)
            ->whereDate('check_out', '>=', $monthStart)
            ->orderBy('check_in')
            ->get();

        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $weeks = [];
        $week = [];
        $summary = [
            'open' => 0,
            'occupied' => 0,
            'unavailable' => 0,
            'past' => 0,
            'outside' => 0,
        ];

        for ($date = $calendarStart->copy(); $date->lte($calendarEnd); $date->addDay()) {
            $matchingBooking = $bookings->first(function (Booking $booking) use ($date) {
                return $date->betweenIncluded(
                    Carbon::parse($booking->check_in)->startOfDay(),
                    Carbon::parse($booking->check_out)->endOfDay()
                );
            });

            $isCurrentMonth = $date->month === $monthStart->month && $date->year === $monthStart->year;
            
            
            if (! $isCurrentMonth) {
                $status = 'outside';
            } elseif ($date->isPast() && ! $date->isToday()) {
                $status = 'past';
            } elseif ($room->status !== 'available') {
                $status = 'unavailable';
            } elseif ($matchingBooking) {
                $status = 'occupied';
            } else {
                $status = 'open';
            }

            $summary[$status]++;

            $week[] = [
                'date' => $date->copy(),
                'isCurrentMonth' => $isCurrentMonth,
                'isToday' => $date->isToday(),
                'status' => $status,
                'booking' => $matchingBooking ? [
                    'check_in' => Carbon::parse($matchingBooking->check_in),
                    'check_out' => Carbon::parse($matchingBooking->check_out),
                ] : null,
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }

        return [
            'label' => $monthStart->translatedFormat('F Y'),
            'previousMonth' => $previousMonth,
            'nextMonth' => $nextMonth,
            'weeks' => $weeks,
            'summary' => $summary,
            'bookings' => $bookings->map(fn (Booking $booking) => [
                'check_in' => Carbon::parse($booking->check_in),
                'check_out' => Carbon::parse($booking->check_out),
            ])->values(),
        ];
    }
}
