<?php

namespace App\Http\Controllers;

use App\Events\AdminNotificationCreated;
use App\Models\Booking;
use App\Notifications\RefundRequestedNotification;
use App\Support\ActivityLogger;
use App\Support\NotifyAdmins;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingManageController extends Controller
{
    public function show(string $token): View
    {
        $booking = $this->findBookingByToken($token);

        return view('pages.bookings.manage', [
            'booking' => $booking,
            'token' => $token,
            'seo' => [
                'title' => 'Manage Booking — '.config('app.name'),
                'description' => 'View and manage your Villa Estella booking.',
            ],
        ]);
    }

    public function requestRefund(Request $request, string $token): RedirectResponse
    {
        $booking = $this->findBookingByToken($token);

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
            "Refund requested for booking #{$booking->id} via manage link.",
            $booking,
            ['reason' => $validated['cancellation_reason'] ?? null]
        );

        NotifyAdmins::send(new RefundRequestedNotification(
            $booking->id,
            $booking->room?->name ?? 'Room',
            $booking->contact_name ?? 'Guest'
        ));

        event(new AdminNotificationCreated);

        return redirect()->route('bookings.manage', $token)->with('success', 'Your refund request has been submitted. Our team will review it shortly.');
    }

    protected function findBookingByToken(string $token): Booking
    {
        $booking = Booking::with('room')->where('manage_token', $token)->first();

        if (! $booking) {
            abort(404, 'Booking not found.');
        }

        return $booking;
    }
}
