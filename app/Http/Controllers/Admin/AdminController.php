<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Room;
use App\Models\SiteContent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        return $this->renderAdminPage('bookings', [
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

    public function updateRoomStatus(Request $request, Room $room): RedirectResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'status' => 'required|in:available,occupied,maintenance',
        ]);

        $room->update(['status' => $request->status]);

        return redirect()->route('admin.dashboard')->with('success', 'Room status updated successfully.');
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
                str_contains($key, 'url') || str_contains($key, 'image') => 'nullable|string|max:2048',
                str_contains($key, 'body'), str_contains($key, 'intro'), str_contains($key, 'quote') => 'nullable|string|max:3000',
                default => 'nullable|string|max:500',
            };
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('hero_background_upload')) {
            $path = $request->file('hero_background_upload')->storePublicly('site-content', 'public');
            $validated['hero_background_image'] = $path;
        }

        foreach ($validated as $key => $value) {
            if ($key === 'hero_background_upload') {
                continue;
            }

            SiteContent::setValue($key, $value ?? '');
        }

        return redirect()->route('admin.settings', ['tab' => 'landing'])->with('success', 'Site content updated successfully.');
    }
}
