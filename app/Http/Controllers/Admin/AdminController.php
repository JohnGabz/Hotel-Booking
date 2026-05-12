<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Room;
use App\Models\SiteContent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    protected function ensureAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()?->is_admin) {
            abort(403);
        }
    }

    public function dashboard(): View
    {
        return $this->renderAdminPage('dashboard', [
            'seo' => [
                'title' => 'Admin Dashboard — ' . config('app.name'),
                'description' => 'Overview of bookings, revenue, and room operations for Villa Estella.',
            ],
        ]);
    }

    public function bookings(Request $request): View
    {
        $rooms = Room::latest()->get();
        $selectedRoom = $rooms->firstWhere('id', (int) $request->query('room')) ?? $rooms->first();

        return $this->renderAdminPage('bookings', [
            'selectedRoom' => $selectedRoom,
            'calendar' => $selectedRoom ? $this->buildBookingCalendar($selectedRoom, $request->query('month')) : null,
            'filters' => [
                'status' => $request->query('status', 'all'),
                'room' => $request->query('room', 'all'),
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
            ],
            'seo' => [
                'title' => 'Bookings — ' . config('app.name'),
                'description' => 'Filter, review, and manage bookings in a table-first workflow.',
            ],
        ]);
    }

    public function rooms(Request $request): View
    {
        return $this->renderAdminPage('rooms', [
            'viewMode' => $request->query('view', 'grid'),
            'seo' => [
                'title' => 'Rooms — ' . config('app.name'),
                'description' => 'View room cards, availability, pricing, and quick room actions.',
            ],
        ]);
    }

    public function storeRoom(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'required|string|max:3000',
            'capacity' => 'required|integer|min:1|max:20',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:available,occupied,maintenance',
            'amenities' => 'nullable|string|max:3000',
            'images' => 'nullable|array|min:1',
            'images.*' => 'image|max:5120',
            'image_links' => 'nullable|string|max:5000',
        ]);

        $imagePaths = [];

        // Handle file uploads
        if ($request->hasFile('images') && $request->file('images')) {
            $imagePaths = collect($request->file('images', []))
                ->map(fn ($image) => $image->storePublicly('rooms', 'uploads'))
                ->values()
                ->all();
        }

        // Handle image URLs
        if ($request->filled('image_links')) {
            $imageLinks = collect(explode("\n", $validated['image_links'] ?? ''))
                ->map(fn ($url) => trim($url))
                ->filter(fn ($url) => $url && filter_var($url, FILTER_VALIDATE_URL))
                ->values()
                ->all();
            $imagePaths = array_merge($imagePaths, $imageLinks);
        }

        if (empty($imagePaths)) {
            return redirect()->back()->withErrors(['images' => 'At least one image is required (upload or link).']);
        }

        Room::create([
            'name' => $validated['name'],
            'slug' => $this->makeUniqueRoomSlug($validated['name']),
            'description' => $validated['description'],
            'capacity' => $validated['capacity'],
            'price' => $validated['price'],
            'status' => $validated['status'],
            'amenities' => collect(explode(',', $validated['amenities'] ?? ''))
                ->map(fn ($amenity) => trim($amenity))
                ->filter()
                ->values()
                ->all(),
            'images' => $imagePaths,
        ]);

        return redirect()->route('admin.rooms')->with('success', 'Room created successfully.');
    }

    public function guests(): View
    {
        return $this->renderAdminPage('guests', [
            'seo' => [
                'title' => 'Guests — ' . config('app.name'),
                'description' => 'Profile-based guest management with recent activity and stay history.',
            ],
        ]);
    }

    public function amenities(): View
    {
        return $this->renderAdminPage('amenities', [
            'seo' => [
                'title' => 'Amenities — ' . config('app.name'),
                'description' => 'Maintain service icons, facility descriptions, and inline updates.',
            ],
        ]);
    }

    public function reports(Request $request): View
    {
        return $this->renderAdminPage('reports', [
            'reportRange' => $request->query('range', '30d'),
            'chartLabels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'chartValues' => [18, 24, 20, 32, 29, 35, 28],
            'seo' => [
                'title' => 'Reports — ' . config('app.name'),
                'description' => 'Track revenue, occupancy, and trends using a chart-first layout.',
            ],
        ]);
    }

    public function messages(): View
    {
        return $this->renderAdminPage('messages', [
            'seo' => [
                'title' => 'Messages — ' . config('app.name'),
                'description' => 'Manage inquiries with a split conversation and reply layout.',
            ],
        ]);
    }

    public function settings(Request $request): View
    {
        return $this->renderAdminPage('settings', [
            'activeSettingsTab' => $request->query('tab', 'general'),
            'seo' => [
                'title' => 'Settings — ' . config('app.name'),
                'description' => 'General, account, and preferences settings for the admin panel.',
            ],
        ]);
    }

    protected function renderAdminPage(string $page, array $extra = []): View
    {
        $this->ensureAdmin();
        $siteContent = SiteContent::values(SiteContent::landingPageDefaults());

        $baseData = [
            'rooms' => Room::latest()->get(),
            'bookings' => Booking::with(['room', 'user'])->latest()->take(12)->get(),
            'pendingPayments' => Booking::with(['room', 'user'])
                ->whereIn('payment_status', ['pending', 'for_verification'])
                ->latest()
                ->take(12)
                ->get(),
            'reviews' => Review::where('approved', false)->with(['user', 'room'])->latest()->get(),
            'users' => User::with(['bookings.room'])->latest()->take(10)->get(),
            'siteContent' => $siteContent,
            'bookingsCount' => Booking::count(),
            'confirmedCount' => Booking::where('status', 'confirmed')->count(),
            'reviewsCount' => Review::count(),
            'availableRooms' => Room::where('status', 'available')->count(),
            'totalRevenue' => Booking::sum('total'),
            'occupancyRate' => Booking::count() > 0 ? round((Booking::where('status', 'confirmed')->count() / Booking::count()) * 100) : 0,
            'seo' => [
                'title' => 'Admin — ' . config('app.name'),
                'description' => 'Staff management and reporting for Villa Estella.',
            ],
        ];

        return view('pages.admin.' . $page, array_merge($baseData, $extra));
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

    public function updateRoomStatus(Request $request, Room $room): RedirectResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'status' => 'required|in:available,occupied,maintenance',
        ]);

        $room->update(['status' => $request->status]);

        return redirect()->route('admin.dashboard')->with('success', 'Room status updated successfully.');
    }

    public function updateRoom(Request $request, Room $room): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'required|string|max:3000',
            'capacity' => 'required|integer|min:1|max:20',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:available,occupied,maintenance',
            'amenities' => 'nullable|string|max:3000',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
            'image_links' => 'nullable|string|max:5000',
        ]);

        $images = [];

        // Handle file uploads
        if ($request->hasFile('images') && $request->file('images')) {
            // Delete old uploaded images (those starting with 'rooms/')
            foreach (($room->images ?? []) as $existingImage) {
                if (str_starts_with($existingImage, 'rooms/') || !str_starts_with($existingImage, 'http')) {
                    Storage::disk('public')->delete($existingImage);
                }
            }

            $images = collect($request->file('images', []))
                ->map(fn ($image) => $image->storePublicly('rooms', 'uploads'))
                ->values()
                ->all();
        } else {
            // Preserve existing uploaded images if no new uploads
            $images = collect($room->images ?? [])
                ->filter(fn ($image) => str_starts_with($image, 'http') || str_starts_with($image, 'rooms/'))
                ->values()
                ->all();
        }

        // Handle image URLs
        if ($request->filled('image_links')) {
            $imageLinks = collect(explode("\n", $validated['image_links'] ?? ''))
                ->map(fn ($url) => trim($url))
                ->filter(fn ($url) => $url && filter_var($url, FILTER_VALIDATE_URL))
                ->values()
                ->all();
            $images = array_merge($images, $imageLinks);
        }

        $room->update([
            'name' => $validated['name'],
            'slug' => $this->makeUniqueRoomSlug($validated['name'], $room),
            'description' => $validated['description'],
            'capacity' => $validated['capacity'],
            'price' => $validated['price'],
            'status' => $validated['status'],
            'amenities' => collect(explode(',', $validated['amenities'] ?? ''))
                ->map(fn ($amenity) => trim($amenity))
                ->filter()
                ->values()
                ->all(),
            'images' => $images,
        ]);

        return redirect()->route('admin.rooms')->with('success', 'Room updated successfully.');
    }

    public function approveReview(Review $review): RedirectResponse
    {
        $this->ensureAdmin();

        $review->update(['approved' => true]);

        return redirect()->route('admin.dashboard')->with('success', 'Review approved successfully.');
    }

    public function updatePaymentStatus(Request $request, Booking $booking): RedirectResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'payment_status' => 'required|in:pending,for_verification,paid,failed',
        ]);

        $status = $request->string('payment_status')->toString();

        $booking->update([
            'payment_status' => $status,
            'paid_at' => $status === 'paid' ? now() : null,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Payment status updated successfully.');
    }

    public function updateSiteContent(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $rules = [
            'about_heading' => 'nullable|string|max:200',
            'about_body' => 'nullable|string|max:3000',
            'services_intro' => 'nullable|string|max:2000',
            'faqs_intro' => 'nullable|string|max:2000',
            'contact_email' => 'nullable|email|max:150',
            'contact_phone' => 'nullable|string|max:80',
            'contact_address' => 'nullable|string|max:250',
            'hero_background_image' => 'nullable|string|max:2048',
            'hero_background_upload' => 'nullable|image|max:5120',
        ];

        foreach (array_keys(SiteContent::landingPageDefaults()) as $key) {
            $rules[$key] = match (true) {
                str_contains($key, 'email') => 'nullable|email|max:150',
                str_contains($key, 'url') => 'nullable|string|max:2048',
                str_contains($key, 'image') => 'nullable|string|max:2048',
                str_contains($key, 'body'), str_contains($key, 'intro'), str_contains($key, 'quote') => 'nullable|string|max:3000',
                default => 'nullable|string|max:500',
            };
        }

        // Allow file uploads for any site-content image fields using the convention: {key}_upload
        // Add validation rules for *_upload fields
        foreach (array_keys(SiteContent::landingPageDefaults()) as $key) {
            if (str_contains($key, 'image')) {
                $rules[$key . '_upload'] = 'nullable|image|max:5120';
            }
        }

        $validated = $request->validate($rules);

        // Handle hero background upload (existing behaviour)
        if ($request->hasFile('hero_background_upload')) {
            $path = $request->file('hero_background_upload')->storePublicly('site-content', 'public');
            $validated['hero_background_image'] = $path;
        }

        // Generic image upload handling: for any SiteContent key containing 'image', accept a file named {key}_upload
        foreach (array_keys(SiteContent::landingPageDefaults()) as $key) {
            if (! str_contains($key, 'image')) {
                continue;
            }

            $uploadField = $key . '_upload';
            if ($request->hasFile($uploadField)) {
                $path = $request->file($uploadField)->storePublicly('site-content', 'public');
                // override the logical image value to the stored path
                $validated[$key] = $path;
            }
        }

        // Persist validated values (skip upload fields)
        foreach ($validated as $key => $value) {
            if (str_ends_with($key, '_upload')) {
                continue;
            }

            SiteContent::setValue($key, $value ?? '');
        }

        return redirect()->route('admin.settings', ['tab' => 'landing'])->with('success', 'Site content updated successfully.');
    }

    protected function makeUniqueRoomSlug(string $name, ?Room $ignoreRoom = null): string
    {
        $baseSlug = Str::slug($name) ?: 'room';
        $slug = $baseSlug;
        $counter = 2;

        while (Room::query()
            ->when($ignoreRoom, fn ($query) => $query->where('id', '!=', $ignoreRoom->id))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
