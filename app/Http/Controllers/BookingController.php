<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function store(Request $request, Room $room): RedirectResponse
    {
        if ($room->status !== 'available') {
            return back()->with('error', 'This room is currently unavailable for new bookings.');
        }

        $validated = $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'contact_name' => 'required|string|max:150',
            'contact_email' => 'required|email|max:150',
            'contact_phone' => 'required|string|max:80',
            'payment_method' => 'required|in:gcash,landbank',
        ]);

        $overlap = Booking::where('room_id', $room->id)
            ->where('status', 'confirmed')
            ->where(function ($query) use ($validated) {
                $query->whereBetween('check_in', [$validated['check_in'], $validated['check_out']])
                      ->orWhereBetween('check_out', [$validated['check_in'], $validated['check_out']])
                      ->orWhere(function ($query) use ($validated) {
                          $query->where('check_in', '<=', $validated['check_in'])
                                ->where('check_out', '>=', $validated['check_out']);
                      });
            })
            ->exists();

        if ($overlap) {
            return back()->withErrors(['check_in' => 'The selected dates are already reserved. Please choose different dates.']);
        }

        $nights = Carbon::parse($validated['check_in'])->diffInDays(Carbon::parse($validated['check_out']));
        $total = $room->price * max(1, $nights);

        Booking::create([
            'user_id' => Auth::id(),
            'room_id' => $room->id,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'guests' => 1,
            'contact_name' => $validated['contact_name'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'],
            'status' => 'confirmed',
            'payment_method' => $validated['payment_method'],
            'payment_status' => 'pending',
            'total' => $total,
        ]);

        if (Auth::check()) {
            return redirect()->route('dashboard')->with('success', 'Your reservation is confirmed. Please complete payment via your selected method.');
        }

        return redirect()->route('rooms.show', $room)->with('success', 'Your reservation is confirmed. We will contact you using the details provided.');
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

        if ($booking->payment_proof_path) {
            Storage::disk('public')->delete($booking->payment_proof_path);
        }

        $proofPath = $request->file('payment_proof')->store('payment-proofs', 'public');

        $booking->update([
            'payment_reference' => $request->string('payment_reference')->toString(),
            'payment_proof_path' => $proofPath,
            'payment_status' => 'for_verification',
        ]);

        return redirect()->route('dashboard')->with('success', 'Payment proof uploaded. Our staff will verify your payment shortly.');
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
                'title' => 'Dashboard — ' . config('app.name'),
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
            ->whereDoesntHave('bookings', function ($query) use ($checkIn, $checkOut) {
                $query->where('status', 'confirmed')
                    ->where(function ($q) use ($checkIn, $checkOut) {
                        $q->whereBetween('check_in', [$checkIn, $checkOut])
                          ->orWhereBetween('check_out', [$checkIn, $checkOut])
                          ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                              $q2->where('check_in', '<=', $checkIn)
                                 ->where('check_out', '>=', $checkOut);
                          });
                    });
            })
            ->get();

        return view('pages.rooms.index', compact('rooms'), [
            'seo' => [
                'title' => 'Search Results — ' . config('app.name'),
                'description' => 'Available rooms for your selected dates.',
            ],
        ]);
    }
}
