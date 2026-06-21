<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Room;
use App\Notifications\ReviewSubmittedNotification;
use App\Support\NotifyAdmins;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Room $room): RedirectResponse
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $hasBooking = Booking::where('user_id', Auth::id())
            ->where('room_id', $room->id)
            ->whereIn('status', ['confirmed', 'Confirmed'])
            ->whereDate('check_out', '<', now())
            ->exists();

        if (! $hasBooking) {
            return back()->with('error', 'You can only leave a review after completing a confirmed stay.');
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'room_id' => $room->id,
            'booking_id' => Booking::where('user_id', Auth::id())
                ->where('room_id', $room->id)
                ->whereIn('status', ['confirmed', 'Confirmed'])
                ->latest('check_out')
                ->value('id'),
            'rating' => $request->rating,
            'comment' => $request->comment,
            'approved' => false,
        ]);

        NotifyAdmins::send(new ReviewSubmittedNotification($review->id, $room->name, Auth::user()->name, $review->rating));

        return back()->with('success', 'Thanks for your review. It will be visible once approved by staff.');
    }
}
