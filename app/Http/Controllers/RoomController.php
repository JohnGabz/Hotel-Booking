<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Support\ImageInput;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
                ->whereIn('status', ['confirmed', 'Confirmed'])
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

    public function store(Request $request): RedirectResponse
    {
        if (! Auth::check() || ! Auth::user()?->is_admin) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:3000'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,occupied,maintenance'],
            'amenities' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'max:10240'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:10240'],
            'image_links' => ['nullable', 'string', 'max:5000'],
            'image_input_mode' => ['nullable', 'in:upload,url'],
            'physical_rooms' => ['nullable', 'array'],
            'physical_rooms.*.name' => ['required_with:physical_rooms', 'string', 'max:255'],
            'physical_rooms.*.code' => ['required_with:physical_rooms', 'string', 'max:50', 'distinct', Rule::unique('physical_rooms', 'code')],
        ]);

        $images = ImageInput::resolveMany($request);
        if (empty($images)) {
            return redirect()->route('admin.rooms')
                ->withInput()
                ->withErrors(['images' => 'Add at least one room image.']);
        }

        $room = Room::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
            'description' => $validated['description'],
            'capacity' => $validated['capacity'],
            'price' => $validated['price'],
            'status' => $validated['status'],
            'amenities' => collect(explode(',', $validated['amenities'] ?? ''))->map(fn ($a) => trim($a))->filter()->values()->all(),
            'images' => $images,
        ]);

        foreach ($request->input('physical_rooms', []) as $physicalRoom) {
            if (empty($physicalRoom['name']) || empty($physicalRoom['code'])) {
                continue;
            }

            $room->physicalRooms()->create([
                'name' => $physicalRoom['name'],
                'code' => $physicalRoom['code'],
                'status' => ! empty($physicalRoom['is_available']) ? 'available' : 'maintenance',
            ]);
        }

        return redirect()->route('admin.rooms')->with('success', 'Room created successfully.');
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        if (! Auth::check() || ! Auth::user()?->is_admin) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:3000'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,occupied,maintenance'],
            'amenities' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'max:10240'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:10240'],
            'image_links' => ['nullable', 'string', 'max:5000'],
            'image_input_mode' => ['nullable', 'in:upload,url'],
        ]);

        $newImages = ImageInput::resolveMany($request);
        $images = ! empty($newImages) ? $newImages : ($room->images ?? []);

        $room->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'capacity' => $validated['capacity'],
            'price' => $validated['price'],
            'status' => $validated['status'],
            'amenities' => collect(explode(',', $validated['amenities'] ?? ''))->map(fn ($a) => trim($a))->filter()->values()->all(),
            'images' => $images,
        ]);

        return redirect()->route('admin.rooms')->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        if (! Auth::check() || ! Auth::user()?->is_admin) {
            abort(403);
        }

        $room->delete();

        return redirect()->route('admin.rooms')->with('success', 'Room deleted successfully.');
    }

    public function availability(Request $request, Room $room): JsonResponse
    {
        $checkIn = $request->query('check_in');
        $checkOut = $request->query('check_out');

        $isAvailable = false;
        $availableCount = 0;

        if ($checkIn && $checkOut) {
            $availableCount = $room->availablePhysicalRoomCountForRange($checkIn, $checkOut);
            $isAvailable = $availableCount > 0;
        }

        return response()->json([
            'room_status' => $room->status,
            'available' => $isAvailable,
            'available_physical_rooms' => $availableCount,
            'message' => $isAvailable
                ? 'This room type is available for the selected dates.'
                : 'All rooms of this type are unavailable for the selected dates.',
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
            ->with('physicalRoom')
            ->where('room_id', $room->id)
            ->whereIn('status', Booking::BLOCKING_STATUSES)
            ->whereDate('check_in', '<=', $monthEnd)
            ->whereDate('check_out', '>', $monthStart)
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
            $matchingBookings = $bookings->filter(function (Booking $booking) use ($date) {
                return $date->betweenIncluded(
                    Carbon::parse($booking->check_in)->startOfDay(),
                    Carbon::parse($booking->check_out)->subDay()->endOfDay()
                );
            });
            $availableCount = $room->availablePhysicalRoomCountForDate($date->toDateString());

            $isCurrentMonth = $date->month === $monthStart->month && $date->year === $monthStart->year;
            
            
            if (! $isCurrentMonth) {
                $status = 'outside';
            } elseif ($date->isPast() && ! $date->isToday()) {
                $status = 'past';
            } elseif ($room->status !== 'available') {
                $status = 'unavailable';
            } elseif ($availableCount <= 0) {
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
                'availableCount' => $availableCount,
                'booking' => $matchingBookings->first() ? [
                    'check_in' => Carbon::parse($matchingBookings->first()->check_in),
                    'check_out' => Carbon::parse($matchingBookings->first()->check_out),
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
