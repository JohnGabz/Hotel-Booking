<?php

namespace App\Http\Controllers;

use App\Events\AdminNotificationCreated;
use App\Events\BookingCancelled;
use App\Events\BookingCreated;
use App\Events\BookingStatusChanged;
use App\Events\PaymentStatusUpdated;
use App\Models\Booking;
use App\Models\PaymentTransaction;
use App\Models\Room;
use App\Models\User;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\PaymentProofUploadedNotification;
use App\Notifications\RefundRequestedNotification;
use App\Support\ActivityLogger;
use App\Support\ImageStorage;
use App\Support\NotifyAdmins;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class BookingController extends Controller
{
    public function store(Request $request, Room $room): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'contact_name' => 'required|string|max:150',
            'contact_email' => 'required|email|max:150',
            'contact_phone' => 'required|string|max:80',
            'payment_method' => 'required|in:gcash,landbank,xendit',
            'with_breakfast' => 'nullable|boolean',
        ]);

        $booking = DB::transaction(function () use ($room, $validated) {
            $lockedRoom = Room::query()->whereKey($room->id)->lockForUpdate()->firstOrFail();

            if ($lockedRoom->status !== 'available') {
                return null;
            }

            $assignedPhysicalRoom = $lockedRoom->availablePhysicalRoomFor($validated['check_in'], $validated['check_out'], true);

            if (! $assignedPhysicalRoom) {
                return false;
            }

            $nights = Carbon::parse($validated['check_in'])->diffInDays(Carbon::parse($validated['check_out']));
            $nights = max(1, $nights);

            $withBreakfast = (bool) ($validated['with_breakfast'] ?? false);
            $guestsCount = 1; // Default for public flow

            $breakfastCharge = $withBreakfast ? (50 * $lockedRoom->capacity * $nights) : 0;
            $total = ($lockedRoom->price * $nights) + $breakfastCharge;

            $payload = [
                'user_id' => Auth::id(),
                'room_id' => $lockedRoom->id,
                'physical_room_id' => $assignedPhysicalRoom->id,
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'guests' => $guestsCount,
                'contact_name' => $validated['contact_name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'status' => 'Pending Payment',
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'total' => $total,
                'with_breakfast' => $withBreakfast,
                'breakfast_charge' => $breakfastCharge,
            ];

            if ($this->bookingSupportsSource()) {
                $payload['source'] = Booking::SOURCE_ONLINE;
            }

            return Booking::create($payload);
        });

        if ($booking === null) {
            return back()->with('error', 'This room is currently unavailable for new bookings.');
        }

        if ($booking === false) {
            return back()->withErrors(['check_in' => 'The selected dates are already reserved. Please choose different dates.']);
        }

        Log::info('Booking stored from public flow', [
            'booking_id' => $booking->id,
            'room_id' => $booking->room_id,
            'user_id' => $booking->user_id,
            'source' => $booking->source,
        ]);

        NotifyAdmins::send(new BookingCreatedNotification($booking->id, $room->name, $booking->contact_name, false));

        ActivityLogger::log(
            'booking.created',
            'booking',
            "Online booking created for {$room->name}.",
            $booking,
            ['source' => Booking::SOURCE_ONLINE]
        );

        try {
            event(new BookingCreated($booking->id));
        } catch (Throwable $exception) {
            Log::error('BookingCreated side effect failed after booking was stored', [
                'booking_id' => $booking->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }

        try {
            $paymentController = new \App\Http\Controllers\PaymentController();
            $checkoutUrl = $paymentController->getOrCreateCheckoutUrl($booking);
        } catch (Throwable $e) {
            Log::error('Public booking Xendit session creation failed, deleting booking', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            $booking->delete();

            if ($request->expectsJson()) {
                return response()->json(['errors' => ['payment' => [$e->getMessage()]]], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'checkout_url' => $checkoutUrl]);
        }

        return redirect()->away($checkoutUrl);
    }

    public function uploadPaymentProof(Request $request, Booking $booking): RedirectResponse
    {
        if ((int) $booking->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $request->validate([
            'payment_reference' => 'required|string|max:120',
            'payment_proof' => 'required|image|max:5120',
        ]);

        $proofPath = ImageStorage::store($request->file('payment_proof'), 'payment-proofs');
        $oldProofPath = $booking->payment_proof_path;

        DB::transaction(function () use ($booking, $request, $proofPath) {
            $lockedBooking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $reference = $request->string('payment_reference')->toString();

            $lockedBooking->update([
                'payment_reference' => $reference,
                'payment_proof_path' => $proofPath,
                'payment_status' => 'for_verification',
            ]);

            PaymentTransaction::updateOrCreate(
                [
                    'booking_id' => $lockedBooking->id,
                    'transaction_id' => $reference,
                ],
                [
                    'provider' => 'manual',
                    'amount' => $lockedBooking->total,
                    'status' => 'pending',
                    'payment_method' => $lockedBooking->payment_method,
                    'payload' => ['proof_path' => $proofPath],
                    'processed_at' => null,
                ]
            );
        });

        if ($oldProofPath) {
            ImageStorage::delete($oldProofPath);
        }

        // Trigger database notification for admin
        try {
            $adminUsers = User::where('is_admin', true)->get();
            foreach ($adminUsers as $admin) {
                $admin->notify(new PaymentProofUploadedNotification($booking->id, $booking->contact_name ?? $booking->user?->name ?? 'Guest', $request->payment_reference));
            }
        } catch (Throwable $e) {
            Log::error('Failed to notify admins on payment proof upload: '.$e->getMessage());
        }

        event(new AdminNotificationCreated);
        event(new PaymentStatusUpdated($booking->id));

        return redirect()->route('dashboard')->with('success', 'Payment proof uploaded. Our staff will verify your payment shortly.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->ensureGuestOwnsBooking($booking);

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($booking, $validated) {
                $lockedBooking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

                if (! $lockedBooking->canGuestCancel()) {
                    throw ValidationException::withMessages([
                        'booking' => 'This booking is no longer eligible for cancellation.',
                    ]);
                }

                $lockedBooking->update([
                    'status' => 'cancelled',
                    'payment_status' => 'failed',
                    'cancellation_reason' => $validated['cancellation_reason'] ?? null,
                    'cancelled_at' => now(),
                ]);
            });
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->with('error', 'This booking cannot be cancelled.');
        }

        $booking->refresh();

        ActivityLogger::log(
            'booking.cancelled',
            'booking',
            "Guest cancelled booking #{$booking->id}.",
            $booking,
            ['reason' => $validated['cancellation_reason'] ?? null]
        );

        event(new BookingCancelled($booking->id));
        event(new BookingStatusChanged($booking->id));
        event(new PaymentStatusUpdated($booking->id));

        return redirect()->route('dashboard')->with('success', 'Your booking has been cancelled.');
    }

    public function requestRefund(Request $request, Booking $booking): RedirectResponse
    {
        $this->ensureGuestOwnsBooking($booking);

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($booking, $validated) {
                $lockedBooking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

                if (! $lockedBooking->canGuestRequestRefund()) {
                    throw ValidationException::withMessages([
                        'booking' => 'This booking is not eligible for a refund request.',
                    ]);
                }

                $lockedBooking->update([
                    'refund_requested_at' => now(),
                    'cancellation_reason' => $validated['cancellation_reason'] ?? null,
                ]);
            });
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->with('error', 'This booking is not eligible for a refund request.');
        }

        $booking->refresh()->loadMissing('room');

        ActivityLogger::log(
            'refund.requested',
            'payment',
            "Refund requested for booking #{$booking->id}.",
            $booking,
            ['reason' => $validated['cancellation_reason'] ?? null]
        );

        NotifyAdmins::send(new RefundRequestedNotification(
            $booking->id,
            $booking->room?->name ?? 'Room',
            $booking->contact_name ?? Auth::user()?->name ?? 'Guest'
        ));

        event(new AdminNotificationCreated);

        return redirect()->route('dashboard')->with('success', 'Your refund request has been submitted. Our team will review it shortly.');
    }

    protected function ensureGuestOwnsBooking(Booking $booking): void
    {
        if ((int) $booking->user_id !== (int) Auth::id()) {
            abort(403);
        }
    }

    protected function bookingSupportsSource(): bool
    {
        static $supportsSource = null;

        if ($supportsSource === null) {
            $supportsSource = Schema::hasColumn('bookings', 'source');
        }

        return $supportsSource;
    }

    public function dashboard(): View
    {
        $bookings = Booking::with('room')
            ->where('user_id', Auth::id())
            ->orderByRaw("CASE WHEN payment_status = 'pending' THEN 0 WHEN payment_status = 'for_verification' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->get();

        $recentRooms = Room::available()->take(3)->get();

        return view('pages.dashboard', compact('bookings', 'recentRooms'), [
            'seo' => [
                'title' => 'Dashboard — '.config('app.name'),
                'description' => 'Manage your reservations and reviews at Villa Estella.',
            ],
        ]);
    }

    public function search(Request $request): View
    {
        $validated = $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'nullable|integer|min:1',
        ]);

        $checkIn = $validated['check_in'];
        $checkOut = $validated['check_out'];
        $guests = $validated['guests'] ?? 1;

        $rooms = Room::available()
            ->where('capacity', '>=', $guests)
            ->get()
            ->filter(fn (Room $room) => $room->isAvailableFor($checkIn, $checkOut))
            ->values();

        return view('pages.rooms.index', compact('rooms'), [
            'seo' => [
                'title' => 'Search Results — '.config('app.name'),
                'description' => 'Available rooms for your selected dates.',
            ],
        ]);
    }

    public function success(Request $request, Booking $booking): View
    {
        $booking->loadMissing('room');

        return view('pages.bookings.success', compact('booking'), [
            'seo' => [
                'title' => 'Booking Confirmed — '.config('app.name'),
                'description' => 'Your booking details and confirmation status.',
            ],
        ]);
    }

    public function statusApi(Booking $booking): JsonResponse
    {
        return response()->json([
            'status' => $booking->status,
            'payment_status' => $booking->payment_status,
        ]);
    }
}