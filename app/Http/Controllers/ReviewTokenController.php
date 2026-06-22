<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use App\Notifications\ReviewSubmittedNotification;
use App\Support\ActivityLogger;
use App\Support\NotifyAdmins;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class ReviewTokenController extends Controller
{
    public function show(string $token)
    {
        $booking = Booking::with('room')->where('review_token', $token)->first();

        if (! $booking || ! $booking->reviewTokenIsValid()) {
            return response()->view('errors._page', [
                'status' => 403,
                'title' => 'Invalid or Expired Link',
                'message' => 'This review link is invalid, has expired, or the stay is not yet completed.',
            ], 403);
        }

        if (Review::where('booking_id', $booking->id)->exists()) {
            return response()->view('errors._page', [
                'status' => 400,
                'title' => 'Already Reviewed',
                'message' => "You've already reviewed this stay.",
            ], 400);
        }

        return view('pages.reviews.submit-via-token', [
            'booking' => $booking,
            'token' => $token,
            'submitted' => false,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $booking = Booking::with('room')->where('review_token', $token)->first();

        if (! $booking || ! $booking->reviewTokenIsValid()) {
            return response()->view('errors._page', [
                'status' => 403,
                'title' => 'Invalid or Expired Link',
                'message' => 'This review link is invalid, has expired, or the stay is not yet completed.',
            ], 403);
        }

        if (Review::where('booking_id', $booking->id)->exists()) {
            return response()->view('errors._page', [
                'status' => 400,
                'title' => 'Already Reviewed',
                'message' => "You've already reviewed this stay.",
            ], 400);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        $review = Review::create([
            'user_id' => $booking->user_id,
            'room_id' => $booking->room_id,
            'booking_id' => $booking->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'approved' => false,
        ]);

        $booking->update([
            'review_token_used_at' => now(),
        ]);

        NotifyAdmins::send(new ReviewSubmittedNotification(
            $review->id,
            $booking->room->name,
            $booking->contact_name ?? 'Guest',
            $review->rating
        ));

        ActivityLogger::log(
            'review.submitted',
            'booking',
            "Token review submitted for {$booking->room->name}.",
            $review,
            ['booking_id' => $booking->id, 'rating' => $review->rating]
        );

        return view('pages.reviews.submit-via-token', [
            'booking' => $booking,
            'token' => $token,
            'submitted' => true,
        ]);
    }
}
