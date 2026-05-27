<?php

namespace App\Http\Controllers\Admin;

use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Events\PaymentVerified;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PaymentTransaction;
use App\Models\Review;
use App\Models\Room;
use App\Models\SiteContent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

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
        $status = $request->query('status', 'all');
        $roomFilter = $request->query('room', 'all');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $filteredBookings = Booking::with(['room', 'user'])
            ->when(in_array($status, ['confirmed', 'pending', 'cancelled'], true), fn ($query) => $query->where('status', $status))
            ->when($roomFilter !== 'all' && $roomFilter !== null, fn ($query) => $query->where('room_id', $roomFilter))
            ->when($dateFrom, fn ($query) => $query->whereDate('check_in', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('check_out', '<=', $dateTo))
            ->latest()
            ->get();

        return $this->renderAdminPage('bookings', [
            'bookings' => $filteredBookings,
            'selectedRoom' => $selectedRoom,
            'calendar' => $selectedRoom ? $this->buildBookingCalendar($selectedRoom, $request->query('month')) : null,
            'filters' => [
                'status' => $status,
                'room' => $roomFilter,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'seo' => [
                'title' => 'Bookings — ' . config('app.name'),
                'description' => 'Filter, review, and manage bookings in a table-first workflow.',
            ],
        ]);
    }

    public function adminStoreWalkin(Request $request): JsonResponse|RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'contact_name' => 'required|string|max:150',
            'contact_email' => 'nullable|email|max:150',
            'contact_phone' => 'required|string|max:80',
            'guests' => 'required|integer|min:1|max:20',
            'payment_method' => 'required|in:gcash,landbank,cash,bank_transfer',
            'payment_proof' => 'nullable|image|max:5120',
            'status' => 'nullable|in:pending,confirmed,for_verification',
            'notes' => 'nullable|string|max:2000',
        ]);

        $booking = DB::transaction(function () use ($request, $validated) {
            $lockedRoom = Room::query()->whereKey($validated['room_id'])->lockForUpdate()->firstOrFail();

            if ($lockedRoom->status !== 'available') {
                throw ValidationException::withMessages([
                    'room_id' => 'This room is currently unavailable for new bookings.',
                ]);
            }

            if (Booking::overlaps($lockedRoom->id, $validated['check_in'], $validated['check_out'])) {
                throw ValidationException::withMessages([
                    'check_in' => 'The selected dates are already reserved. Please choose different dates.',
                ]);
            }

            $proofPath = $request->hasFile('payment_proof')
                ? $this->storePublicImage($request->file('payment_proof'), 'payment-proofs')
                : null;

            $status = $validated['status'] ?? 'pending';
            $paymentStatus = $status === 'confirmed' ? 'paid' : 'for_verification';
            $nights = Carbon::parse($validated['check_in'])->diffInDays(Carbon::parse($validated['check_out']));
            $total = $lockedRoom->price * max(1, $nights);

            $payload = [
                'user_id' => null,
                'room_id' => $lockedRoom->id,
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'guests' => $validated['guests'],
                'contact_name' => $validated['contact_name'],
                'contact_email' => $validated['contact_email'] ?? null,
                'contact_phone' => $validated['contact_phone'],
                'status' => $status,
                'payment_method' => $validated['payment_method'],
                'payment_proof_path' => $proofPath,
                'payment_status' => $paymentStatus,
                'paid_at' => $paymentStatus === 'paid' ? now() : null,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ];

            if ($this->bookingSupportsSource()) {
                $payload['source'] = Booking::SOURCE_WALK_IN;
            }

            $booking = Booking::create($payload);

            if ($paymentStatus === 'paid') {
                $booking->confirmPayment('walkin-' . $booking->id, $booking->payment_method, 'manual', [
                    'proof_path' => $proofPath,
                    'source' => 'admin_walkin',
                ]);
            } elseif ($proofPath) {
                PaymentTransaction::updateOrCreate(
                    [
                        'booking_id' => $booking->id,
                        'transaction_id' => 'walkin-' . $booking->id,
                    ],
                    [
                        'provider' => 'manual',
                        'amount' => $booking->total,
                        'status' => 'pending',
                        'payment_method' => $booking->payment_method,
                        'payload' => ['proof_path' => $proofPath, 'source' => 'admin_walkin'],
                        'processed_at' => null,
                    ]
                );
            }

            return $booking;
        });

        Log::info('Walk-in booking stored from admin flow', [
            'booking_id' => $booking->id,
            'room_id' => $booking->room_id,
            'admin_user_id' => Auth::id(),
            'source' => $booking->source,
        ]);

        try {
            event(new BookingCreated($booking->id));

            if ($booking->payment_status === 'paid') {
                event(new PaymentVerified($booking->id));
                event(new BookingConfirmed($booking->id));
            }
        } catch (Throwable $exception) {
            Log::error('Booking side effect failed after walk-in booking was stored', [
                'booking_id' => $booking->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'booking_id' => $booking->id,
            ]);
        }

        return redirect()->route('admin.bookings')->with('success', 'Walk-in booking created successfully.');
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

    protected function bookingSupportsSource(): bool
    {
        static $supportsSource = null;

        if ($supportsSource === null) {
            $supportsSource = Schema::hasColumn('bookings', 'source');
        }

        return $supportsSource;
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

        if ($this->hasInvalidImageLinks($validated['image_links'] ?? '')) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['image_links' => 'Enter valid HTTP or HTTPS image URLs, one per line.']);
        }

        $imagePaths = $this->parseImageLinks($validated['image_links'] ?? '');

        if ($request->hasFile('images') && $request->file('images')) {
            $uploadedImages = collect($request->file('images', []))
                ->map(fn ($image) => $this->storePublicImage($image, 'rooms'))
                ->values()
                ->all();

            $imagePaths = array_merge($imagePaths, $uploadedImages);
        }

        if (empty($imagePaths)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['images' => 'Add at least one room image by uploading a file or pasting a valid image URL.']);
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

    public function guests(Request $request): View
    {
        $users = User::with(['bookings.room'])->latest()->take(10)->get();
        $selectedGuest = $users->firstWhere('id', (int) $request->query('guest')) ?? $users->first();

        return $this->renderAdminPage('guests', [
            'users' => $users,
            'selectedGuest' => $selectedGuest,
            'seo' => [
                'title' => 'Guests — ' . config('app.name'),
                'description' => 'Profile-based guest management with recent activity and stay history.',
            ],
        ]);
    }

    public function reports(Request $request): View
    {
        $filters = $this->reportFilters($request);
        $transactionsQuery = $this->paymentTransactionQuery($filters);
        $sort = in_array($filters['sort'], ['date', 'amount', 'status'], true) ? $filters['sort'] : 'date';
        $direction = $filters['direction'] === 'asc' ? 'asc' : 'desc';

        match ($sort) {
            'amount' => $transactionsQuery->orderBy('total', $direction),
            'status' => $transactionsQuery->orderBy('payment_status', $direction),
            default => $transactionsQuery->orderByRaw('COALESCE(paid_at, updated_at, created_at) ' . $direction),
        };

        $transactions = $transactionsQuery
            ->orderBy('id', $direction)
            ->paginate(50)
            ->withQueryString();

        $summaryQuery = $this->paymentTransactionQuery($filters);
        $confirmedQuery = $this->paymentTransactionQuery(array_merge($filters, ['status' => 'confirmed']));
        $pendingQuery = $this->paymentTransactionQuery(array_merge($filters, ['status' => 'pending']));
        $failedQuery = $this->paymentTransactionQuery(array_merge($filters, ['status' => 'failed']));

        $dateExpression = match (DB::connection()->getDriverName()) {
            'mysql', 'mariadb' => "DATE_FORMAT(COALESCE(paid_at, updated_at, created_at), '%Y-%m')",
            'pgsql' => "TO_CHAR(COALESCE(paid_at, updated_at, created_at), 'YYYY-MM')",
            default => "strftime('%Y-%m', COALESCE(paid_at, updated_at, created_at))",
        };

        $monthlyRevenue = $this->paymentTransactionQuery(array_merge($filters, ['status' => 'confirmed']))
            ->selectRaw("{$dateExpression} as bucket, SUM(total) as revenue")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->limit(12)
            ->pluck('revenue', 'bucket');

        return $this->renderAdminPage('reports', [
            'reportFilters' => $filters,
            'transactions' => $transactions,
            'paymentMethods' => Booking::query()
                ->whereNotNull('payment_method')
                ->distinct()
                ->orderBy('payment_method')
                ->pluck('payment_method'),
            'transactionSummary' => [
                'count' => (clone $summaryQuery)->count(),
                'gross' => (clone $summaryQuery)->sum('total'),
                'confirmed' => (clone $confirmedQuery)->sum('total'),
                'pending' => (clone $pendingQuery)->sum('total'),
                'failed_count' => (clone $failedQuery)->count(),
            ],
            'chartLabels' => $monthlyRevenue->keys()->all(),
            'chartValues' => $monthlyRevenue->values()->map(fn ($value) => (float) $value)->all(),
            'seo' => [
                'title' => 'Payment Reports — ' . config('app.name'),
                'description' => 'Search, filter, and export payment transactions for Villa Estella.',
            ],
        ]);
    }

    protected function reportFilters(Request $request): array
    {
        $range = $request->query('range', 'this_month');
        $now = now();
        $from = null;
        $to = null;

        if ($range === 'custom') {
            $from = $request->filled('date_from') ? Carbon::parse($request->query('date_from'))->startOfDay() : null;
            $to = $request->filled('date_to') ? Carbon::parse($request->query('date_to'))->endOfDay() : null;
        } elseif ($range === 'this_year') {
            $from = $now->copy()->startOfYear();
            $to = $now->copy()->endOfYear();
        } elseif ($range === 'last_30') {
            $from = $now->copy()->subDays(30)->startOfDay();
            $to = $now->copy()->endOfDay();
        } elseif ($range === 'all_time') {
            $from = null;
            $to = null;
        } else {
            $range = 'this_month';
            $from = $now->copy()->startOfMonth();
            $to = $now->copy()->endOfMonth();
        }

        return [
            'range' => $range,
            'date_from' => $from,
            'date_to' => $to,
            'date_from_value' => $request->query('date_from', $from?->toDateString()),
            'date_to_value' => $request->query('date_to', $to?->toDateString()),
            'status' => $request->query('status', 'all'),
            'payment_method' => $request->query('payment_method', 'all'),
            'search' => trim((string) $request->query('search', '')),
            'sort' => $request->query('sort', 'date'),
            'direction' => $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc',
        ];
    }

    protected function paymentTransactionQuery(array $filters)
    {
        $query = Booking::query()->with(['room', 'user']);

        $query->when($filters['date_from'], function ($query, Carbon $from) {
            $query->where(function ($query) use ($from) {
                $query->where('paid_at', '>=', $from)
                    ->orWhere(function ($query) use ($from) {
                        $query->whereNull('paid_at')->where('updated_at', '>=', $from);
                    });
            });
        });

        $query->when($filters['date_to'], function ($query, Carbon $to) {
            $query->where(function ($query) use ($to) {
                $query->where('paid_at', '<=', $to)
                    ->orWhere(function ($query) use ($to) {
                        $query->whereNull('paid_at')->where('updated_at', '<=', $to);
                    });
            });
        });

        $query->when($filters['status'] !== 'all', function ($query) use ($filters) {
            if ($filters['status'] === 'confirmed') {
                $query->where('payment_status', 'paid');
            } elseif ($filters['status'] === 'pending') {
                $query->whereIn('payment_status', ['pending', 'for_verification']);
            } else {
                $query->where('payment_status', $filters['status']);
            }
        });

        $query->when($filters['payment_method'] !== 'all', fn ($query) => $query->where('payment_method', $filters['payment_method']));

        $query->when($filters['search'] !== '', function ($query) use ($filters) {
            $search = $filters['search'];
            $query->where(function ($query) use ($search) {
                $query->where('id', $search)
                    ->orWhere('payment_reference', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('room', fn ($query) => $query->where('name', 'like', "%{$search}%"));
            });
        });

        return $query;
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
        $siteContent = SiteContent::values(SiteContent::landingPageDefaults());

        return $this->renderAdminPage('settings', [
            'activeSettingsTab' => $request->query('tab', 'general'),
            'landingSections' => $this->landingPageSections($siteContent),
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

    protected function landingPageSections(array $siteContent): array
    {
        $sections = [
            [
                'id' => 'hero',
                'title' => 'Hero',
                'description' => 'Opening message, call-to-action, and hero background image.',
                'fields' => [
                    ['key' => 'hero_eyebrow', 'label' => 'Eyebrow', 'maxlength' => 120],
                    ['key' => 'hero_title', 'label' => 'Title', 'maxlength' => 200, 'span' => 'xl:col-span-2'],
                    ['key' => 'hero_subtitle', 'label' => 'Subtitle', 'type' => 'textarea', 'rows' => 4, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                    ['key' => 'hero_button_text', 'label' => 'Button text', 'maxlength' => 80],
                    ['key' => 'hero_background_image', 'label' => 'Background image', 'type' => 'image', 'span' => 'xl:col-span-2'],
                ],
            ],
            [
                'id' => 'booking',
                'title' => 'Booking',
                'description' => 'Section title and supporting copy for the booking quick form.',
                'fields' => [
                    ['key' => 'booking_heading', 'label' => 'Heading', 'maxlength' => 200],
                    ['key' => 'booking_subheading', 'label' => 'Subheading', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                ],
            ],
            [
                'id' => 'services',
                'title' => 'Services',
                'description' => 'Intro copy and the four service cards shown on the landing page.',
                'fields' => [
                    ['key' => 'services_eyebrow', 'label' => 'Eyebrow', 'maxlength' => 120],
                    ['key' => 'services_title', 'label' => 'Title', 'maxlength' => 200, 'span' => 'xl:col-span-2'],
                    ['key' => 'services_intro', 'label' => 'Intro text', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                    ['key' => 'service_1_title', 'label' => 'Card 1 title', 'maxlength' => 200],
                    ['key' => 'service_1_description', 'label' => 'Card 1 description', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                    ['key' => 'service_2_title', 'label' => 'Card 2 title', 'maxlength' => 200],
                    ['key' => 'service_2_description', 'label' => 'Card 2 description', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                    ['key' => 'service_3_title', 'label' => 'Card 3 title', 'maxlength' => 200],
                    ['key' => 'service_3_description', 'label' => 'Card 3 description', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                    ['key' => 'service_4_title', 'label' => 'Card 4 title', 'maxlength' => 200],
                    ['key' => 'service_4_description', 'label' => 'Card 4 description', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                ],
            ],
            [
                'id' => 'featured-rooms',
                'title' => 'Featured Rooms',
                'description' => 'Section heading and intro above the featured room cards.',
                'fields' => [
                    ['key' => 'featured_rooms_eyebrow', 'label' => 'Eyebrow', 'maxlength' => 120],
                    ['key' => 'featured_rooms_title', 'label' => 'Title', 'maxlength' => 200, 'span' => 'xl:col-span-2'],
                    ['key' => 'featured_rooms_intro', 'label' => 'Intro text', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                ],
            ],
            [
                'id' => 'about',
                'title' => 'About',
                'description' => 'Copy and image used in the about section on the landing page.',
                'fields' => [
                    ['key' => 'about_eyebrow', 'label' => 'Eyebrow', 'maxlength' => 120],
                    ['key' => 'about_heading', 'label' => 'Heading', 'maxlength' => 200, 'span' => 'xl:col-span-2'],
                    ['key' => 'about_body', 'label' => 'Body', 'type' => 'textarea', 'rows' => 4, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                    ['key' => 'about_secondary', 'label' => 'Secondary body', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                    ['key' => 'about_image', 'label' => 'Image', 'type' => 'image', 'span' => 'xl:col-span-2'],
                ],
            ],
            [
                'id' => 'amenities',
                'title' => 'Amenities',
                'description' => 'Amenities heading and the four feature labels below it.',
                'fields' => [
                    ['key' => 'facilities_eyebrow', 'label' => 'Eyebrow', 'maxlength' => 120],
                    ['key' => 'facilities_title', 'label' => 'Title', 'maxlength' => 200, 'span' => 'xl:col-span-2'],
                    ['key' => 'facilities_intro', 'label' => 'Intro text', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                    ['key' => 'facility_1_label', 'label' => 'Facility 1 label', 'maxlength' => 120],
                    ['key' => 'facility_2_label', 'label' => 'Facility 2 label', 'maxlength' => 120],
                    ['key' => 'facility_3_label', 'label' => 'Facility 3 label', 'maxlength' => 120],
                    ['key' => 'facility_4_label', 'label' => 'Facility 4 label', 'maxlength' => 120],
                ],
            ],
            [
                'id' => 'gallery',
                'title' => 'Gallery',
                'description' => 'The gallery heading and six images used in the masonry grid.',
                'fields' => [
                    ['key' => 'gallery_eyebrow', 'label' => 'Eyebrow', 'maxlength' => 120],
                    ['key' => 'gallery_title', 'label' => 'Title', 'maxlength' => 200, 'span' => 'xl:col-span-2'],
                    ['key' => 'gallery_intro', 'label' => 'Intro text', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                    ['key' => 'gallery_image_1', 'label' => 'Image 1', 'type' => 'image', 'span' => 'xl:col-span-2'],
                    ['key' => 'gallery_image_2', 'label' => 'Image 2', 'type' => 'image', 'span' => 'xl:col-span-2'],
                    ['key' => 'gallery_image_3', 'label' => 'Image 3', 'type' => 'image', 'span' => 'xl:col-span-2'],
                    ['key' => 'gallery_image_4', 'label' => 'Image 4', 'type' => 'image', 'span' => 'xl:col-span-2'],
                    ['key' => 'gallery_image_5', 'label' => 'Image 5', 'type' => 'image', 'span' => 'xl:col-span-2'],
                    ['key' => 'gallery_image_6', 'label' => 'Image 6', 'type' => 'image', 'span' => 'xl:col-span-2'],
                ],
            ],
            [
                'id' => 'testimonials',
                'title' => 'Testimonials',
                'description' => 'Guest quotes and names shown in the testimonial cards.',
                'fields' => [
                    ['key' => 'testimonial_1_name', 'label' => 'Testimonial 1 name', 'maxlength' => 150],
                    ['key' => 'testimonial_1_role', 'label' => 'Testimonial 1 role', 'maxlength' => 150],
                    ['key' => 'testimonial_1_quote', 'label' => 'Testimonial 1 quote', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                    ['key' => 'testimonial_2_name', 'label' => 'Testimonial 2 name', 'maxlength' => 150],
                    ['key' => 'testimonial_2_role', 'label' => 'Testimonial 2 role', 'maxlength' => 150],
                    ['key' => 'testimonial_2_quote', 'label' => 'Testimonial 2 quote', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                    ['key' => 'testimonial_3_name', 'label' => 'Testimonial 3 name', 'maxlength' => 150],
                    ['key' => 'testimonial_3_role', 'label' => 'Testimonial 3 role', 'maxlength' => 150],
                    ['key' => 'testimonial_3_quote', 'label' => 'Testimonial 3 quote', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                ],
            ],
            [
                'id' => 'location',
                'title' => 'Location',
                'description' => 'Map embed, contact details, and arrival information.',
                'fields' => [
                    ['key' => 'location_eyebrow', 'label' => 'Eyebrow', 'maxlength' => 120],
                    ['key' => 'location_title', 'label' => 'Title', 'maxlength' => 200, 'span' => 'xl:col-span-2'],
                    ['key' => 'location_intro', 'label' => 'Intro text', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                    ['key' => 'location_map_url', 'label' => 'Map embed URL', 'type' => 'url', 'maxlength' => 2048, 'span' => 'xl:col-span-2'],
                    ['key' => 'location_address_line1', 'label' => 'Address line 1', 'maxlength' => 250],
                    ['key' => 'location_address_line2', 'label' => 'Address line 2', 'maxlength' => 250, 'span' => 'xl:col-span-2'],
                    ['key' => 'location_phone', 'label' => 'Phone', 'type' => 'tel', 'maxlength' => 80],
                    ['key' => 'location_email', 'label' => 'Email', 'type' => 'email', 'maxlength' => 150],
                    ['key' => 'location_hours_1', 'label' => 'Hours line 1', 'maxlength' => 120],
                    ['key' => 'location_hours_2', 'label' => 'Hours line 2', 'maxlength' => 120],
                    ['key' => 'location_direction_url', 'label' => 'Directions URL', 'type' => 'url', 'maxlength' => 2048, 'span' => 'xl:col-span-2'],
                ],
            ],
            [
                'id' => 'contact-cta',
                'title' => 'Contact CTA',
                'description' => 'Final booking prompt shown at the bottom of the homepage.',
                'fields' => [
                    ['key' => 'cta_eyebrow', 'label' => 'Eyebrow', 'maxlength' => 120],
                    ['key' => 'cta_title', 'label' => 'Title', 'maxlength' => 200, 'span' => 'xl:col-span-2'],
                    ['key' => 'cta_body', 'label' => 'Body', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                    ['key' => 'cta_button_text', 'label' => 'Button text', 'maxlength' => 80],
                ],
            ],
            [
                'id' => 'footer-content',
                'title' => 'Footer content',
                'description' => 'Contact details reused in footer-adjacent public page content.',
                'fields' => [
                    ['key' => 'contact_email', 'label' => 'Contact email', 'type' => 'email', 'maxlength' => 150],
                    ['key' => 'contact_phone', 'label' => 'Contact phone', 'type' => 'tel', 'maxlength' => 80],
                    ['key' => 'contact_address', 'label' => 'Public contact address', 'maxlength' => 250, 'span' => 'xl:col-span-2'],
                ],
            ],
            [
                'id' => 'shared-copy',
                'title' => 'Shared copy',
                'description' => 'Support copy used on the about, services, FAQs, and contact pages.',
                'fields' => [
                    ['key' => 'faqs_intro', 'label' => 'FAQs intro', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'span' => 'xl:col-span-2'],
                ],
            ],
        ];

        return collect($sections)->map(function (array $section) use ($siteContent) {
            $section['endpoint'] = route('admin.site-content.section.update', $section['id']);
            $section['values'] = collect($section['fields'])
                ->mapWithKeys(fn (array $field) => [$field['key'] => $siteContent[$field['key']] ?? ''])
                ->all();
            $section['preview'] = $this->landingSectionPreview($section, $siteContent);

            return $section;
        })->all();
    }

    protected function landingSectionPreview(array $section, array $siteContent): string
    {
        $parts = collect($section['fields'])
            ->reject(fn (array $field) => ($field['type'] ?? 'text') === 'image')
            ->map(fn (array $field) => trim((string) ($siteContent[$field['key']] ?? '')))
            ->filter()
            ->take(3)
            ->implode(' · ');

        return Str::limit($parts !== '' ? $parts : 'No content added yet.', 180);
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
            $matchingBooking = $bookings->first(function (Booking $booking) use ($date) {
                return $date->betweenIncluded(
                    Carbon::parse($booking->check_in)->startOfDay(),
                    Carbon::parse($booking->check_out)->subDay()->endOfDay()
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
            'retained_images' => 'nullable|array',
            'retained_images.*' => 'string|max:2048',
        ]);

        if ($this->hasInvalidImageLinks($validated['image_links'] ?? '')) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['image_links' => 'Enter valid HTTP or HTTPS image URLs, one per line.']);
        }

        $existingImages = collect($room->images ?? [])->filter()->values();
        $retainedImages = collect($validated['retained_images'] ?? [])
            ->filter(fn ($image) => $existingImages->contains($image))
            ->values()
            ->all();

        $images = $retainedImages;

        if ($request->hasFile('images') && $request->file('images')) {
            $uploadedImages = collect($request->file('images', []))
                ->map(fn ($image) => $this->storePublicImage($image, 'rooms'))
                ->values()
                ->all();

            $images = array_merge($images, $uploadedImages);
        }

        if ($request->filled('image_links')) {
            $images = array_merge($images, $this->parseImageLinks($validated['image_links'] ?? ''));
        }

        $images = collect($images)->unique()->values()->all();

        if (empty($images)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['images' => 'Keep at least one existing image, upload a new file, or paste a valid image URL.']);
        }

        $removedImages = $existingImages
            ->reject(fn ($image) => in_array($image, $images, true))
            ->values();

        foreach ($removedImages as $removedImage) {
            if (! str_starts_with($removedImage, 'http')) {
                Storage::disk('public')->delete($removedImage);
            }
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

    public function destroyRoom(Room $room): RedirectResponse
    {
        $this->ensureAdmin();

        foreach (($room->images ?? []) as $image) {
            if (! str_starts_with($image, 'http')) {
                Storage::disk('public')->delete($image);
            }
        }

        $room->delete();

        return redirect()->route('admin.rooms')->with('success', 'Room deleted successfully.');
    }

    protected function parseImageLinks(?string $links): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $links ?? '') ?: [])
            ->map(fn ($url) => trim($url))
            ->filter(fn ($url) => $this->isValidImageUrl($url))
            ->unique()
            ->values()
            ->all();
    }

    protected function hasInvalidImageLinks(?string $links): bool
    {
        return collect(preg_split('/\r\n|\r|\n/', $links ?? '') ?: [])
            ->map(fn ($url) => trim($url))
            ->filter()
            ->contains(fn ($url) => ! $this->isValidImageUrl($url));
    }

    protected function isValidImageUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        return in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    protected function storePublicImage($image, string $directory): string
    {
        Storage::disk('public')->makeDirectory($directory);

        $path = $image->storePublicly($directory, 'public');

        if (! is_string($path) || ! Storage::disk('public')->exists($path)) {
            abort(500, 'The image could not be saved. Please check the persistent storage configuration.');
        }

        return $path;
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
            'payment_status' => 'required|in:pending,for_verification,paid,failed,refunded',
        ]);

        $status = $request->string('payment_status')->toString();
        $confirmed = false;

        DB::transaction(function () use ($booking, $status, &$confirmed) {
            $lockedBooking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if ($status === 'paid') {
                $alreadyConfirmed = $lockedBooking->payment_status === 'paid' && $lockedBooking->status === 'confirmed';
                $lockedBooking->confirmPayment($lockedBooking->payment_reference, $lockedBooking->payment_method, 'manual');
                $confirmed = ! $alreadyConfirmed;

                return;
            }

            $bookingStatus = in_array($status, ['failed', 'refunded'], true) ? 'cancelled' : $lockedBooking->status;

            $lockedBooking->update([
                'status' => $bookingStatus,
                'payment_status' => $status,
                'paid_at' => in_array($status, ['pending', 'for_verification', 'failed'], true) ? null : $lockedBooking->paid_at,
            ]);

            if ($lockedBooking->payment_reference) {
                PaymentTransaction::updateOrCreate(
                    [
                        'booking_id' => $lockedBooking->id,
                        'transaction_id' => $lockedBooking->payment_reference,
                    ],
                    [
                        'provider' => 'manual',
                        'amount' => $lockedBooking->total,
                        'status' => $status === 'refunded' ? 'refunded' : ($status === 'failed' ? 'failed' : 'pending'),
                        'payment_method' => $lockedBooking->payment_method,
                        'processed_at' => null,
                    ]
                );
            }
        });

        if ($confirmed) {
            event(new PaymentVerified($booking->id));
            event(new BookingConfirmed($booking->id));
        }

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
            $path = $this->storePublicImage($request->file('hero_background_upload'), 'site-content');
            $validated['hero_background_image'] = $path;
        }

        // Generic image upload handling: for any SiteContent key containing 'image', accept a file named {key}_upload
        foreach (array_keys(SiteContent::landingPageDefaults()) as $key) {
            if (! str_contains($key, 'image')) {
                continue;
            }

            $uploadField = $key . '_upload';
            if ($request->hasFile($uploadField)) {
                $path = $this->storePublicImage($request->file($uploadField), 'site-content');
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

    public function updateSiteContentSection(Request $request, string $section): JsonResponse|RedirectResponse
    {
        $this->ensureAdmin();

        $siteContent = SiteContent::values(SiteContent::landingPageDefaults());
        $sectionConfig = collect($this->landingPageSections($siteContent))->firstWhere('id', $section);

        abort_if(! $sectionConfig, 404);

        $validated = $request->validate($this->landingSectionRules($sectionConfig));

        foreach ($sectionConfig['fields'] as $field) {
            $key = $field['key'];

            if (($field['type'] ?? 'text') === 'image') {
                $uploadField = $key . '_upload';

                if ($request->hasFile($uploadField)) {
                    SiteContent::setValue($key, $this->storePublicImage($request->file($uploadField), 'site-content'));
                }

                continue;
            }

            if (array_key_exists($key, $validated)) {
                SiteContent::setValue($key, $validated[$key] ?? '');
            }
        }

        $freshContent = SiteContent::values(SiteContent::landingPageDefaults());
        $freshSection = collect($this->landingPageSections($freshContent))->firstWhere('id', $section);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => "{$freshSection['title']} updated successfully.",
                'section' => $freshSection,
            ]);
        }

        return redirect()
            ->route('admin.settings', ['tab' => 'landing'])
            ->with('success', "{$freshSection['title']} updated successfully.");
    }

    protected function landingSectionRules(array $section): array
    {
        $rules = [];

        foreach ($section['fields'] as $field) {
            $key = $field['key'];
            $type = $field['type'] ?? 'text';

            if ($type === 'image') {
                $rules[$key . '_upload'] = 'nullable|image|max:5120';
                continue;
            }

            $max = $field['maxlength'] ?? match (true) {
                str_contains($key, 'email') => 150,
                str_contains($key, 'url') => 2048,
                str_contains($key, 'body'), str_contains($key, 'intro'), str_contains($key, 'quote') => 3000,
                default => 500,
            };

            $rules[$key] = match ($type) {
                'email' => "nullable|email|max:{$max}",
                'url' => "nullable|url|max:{$max}",
                default => "nullable|string|max:{$max}",
            };
        }

        return $rules;
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
