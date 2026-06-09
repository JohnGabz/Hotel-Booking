<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Room;
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
            ->where('status', 'confirmed')
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
                ->where('status', 'confirmed')
                ->latest('check_out')
                ->value('id'),
            'rating' => $request->rating,
            'comment' => $request->comment,
            'approved' => false,
        ]);

        // Trigger database notification for admin
        try {
            $adminUsers = \App\Models\User::where('is_admin', true)->get();
            foreach ($adminUsers as $admin) {
                $admin->notify(new \App\Notifications\ReviewSubmittedNotification($review->id, $room->name, Auth::user()->name, $review->rating));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to notify admins on review submission: ' . $e->getMessage());
        }

        return back()->with('success', 'Thanks for your review. It will be visible once approved by staff.');
    }
}
