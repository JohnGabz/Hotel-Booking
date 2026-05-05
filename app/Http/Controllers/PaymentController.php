<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function create(Request $request, Booking $booking): RedirectResponse
    {
        // Only the owner can create a payment for their booking
        if ((int) $booking->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $provider = env('PAYMENT_PROVIDER', 'paymongo');
        if ($provider !== 'paymongo') {
            return back()->with('error', 'No payment provider configured.');
        }

        $secret = env('PAYMONGO_SECRET');
        if (! $secret) {
            return back()->with('error', 'PayMongo secret not configured in .env');
        }

        // Amount in centavos
        $amount = (int) round($booking->total * 100);

        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => $amount,
                    'currency' => 'PHP',
                    'description' => 'Booking #' . $booking->id . ' — ' . $booking->room->name,
                    'redirect' => [
                        'success' => route('dashboard'),
                        'failed' => route('dashboard'),
                    ],
                    'metadata' => [
                        'booking_id' => $booking->id,
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withBasicAuth($secret, '')
                ->post('https://api.paymongo.com/v1/payment_links', $payload);

            if (! $response->successful()) {
                Log::error('PayMongo create payment link failed', ['resp' => $response->body()]);
                return back()->with('error', 'Could not create payment session. Try uploading a payment proof instead.');
            }

            $data = $response->json('data.attributes');
            $checkoutUrl = $data['checkout_url'] ?? ($data['checkout_url'] ?? null);

            if (! $checkoutUrl) {
                Log::error('PayMongo missing checkout_url', ['resp' => $response->body()]);
                return back()->with('error', 'Payment gateway did not return a checkout URL.');
            }

            // mark booking as pending payment link created
            $booking->update([
                'payment_status' => 'pending',
            ]);

            return redirect($checkoutUrl);
        } catch (\Exception $e) {
            Log::error('PayMongo exception', ['message' => $e->getMessage()]);
            return back()->with('error', 'Payment creation failed: ' . $e->getMessage());
        }
    }

    // Webhook endpoint for PayMongo
    public function webhook(Request $request)
    {
        $payload = $request->all();

        // Try to extract booking ID from metadata
        $bookingId = data_get($payload, 'data.attributes.metadata.booking_id') ?? data_get($payload, 'data.metadata.booking_id');

        // event name could be in 'type' or nested
        $eventType = data_get($payload, 'type') ?? data_get($payload, 'data.type');

        Log::info('PayMongo webhook received', ['type' => $eventType, 'booking_id' => $bookingId]);

        if (! $bookingId) {
            return response()->json(['status' => 'ignored']);
        }

        $booking = Booking::find($bookingId);
        if (! $booking) {
            return response()->json(['status' => 'not_found'], 404);
        }

        // Handle a generic paid event: set booking paid
        // Different providers use different event names; accept common keywords
        if (str_contains((string) $eventType, 'paid') || data_get($payload, 'data.attributes.status') === 'paid') {
            $booking->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);

            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'ignored']);
    }
}
