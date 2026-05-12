<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\JsonResponse;
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

        $apiKey = config('services.xendit.key');
        if (! $apiKey) {
            return back()->with('error', 'Xendit API key not configured in .env');
        }

        $amount = (int) round($booking->total * 100);
        $externalId = 'villa-estela-booking-' . $booking->id;

        $payload = [
            'external_id' => $externalId,
            'amount' => $amount,
            'currency' => 'PHP',
            'description' => 'Booking #' . $booking->id . ' - ' . $booking->room->name,
            'invoice_duration' => 86400,
            'success_redirect_url' => route('dashboard'),
            'failure_redirect_url' => route('dashboard'),
            'customer' => [
                'given_names' => $booking->contact_name,
                'email' => $booking->contact_email,
                'mobile_number' => $booking->contact_phone,
            ],
        ];

        try {
            $response = Http::withBasicAuth($apiKey, '')
                ->acceptJson()
                ->post('https://api.xendit.co/invoices', $payload);

            if (! $response->successful()) {
                Log::error('Xendit create invoice failed', ['resp' => $response->body()]);
                return back()->with('error', 'Could not create payment session. Try uploading a payment proof instead.');
            }

            $data = $response->json();
            $checkoutUrl = data_get($data, 'invoice_url');
            $invoiceId = data_get($data, 'id');

            if (! $checkoutUrl) {
                Log::error('Xendit missing invoice_url', ['resp' => $response->body()]);
                return back()->with('error', 'Payment gateway did not return a checkout URL.');
            }

            $booking->update([
                'payment_status' => 'pending',
                'payment_reference' => $invoiceId ?: $externalId,
            ]);

            return redirect($checkoutUrl);
        } catch (\Exception $e) {
            Log::error('Xendit exception', ['message' => $e->getMessage()]);
            return back()->with('error', 'Payment creation failed: ' . $e->getMessage());
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $expectedToken = config('services.xendit.webhook_token');
        if ($expectedToken && $request->header('X-Callback-Token') !== $expectedToken) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $payload = $request->all();

        $externalId = data_get($payload, 'external_id')
            ?? data_get($payload, 'data.external_id')
            ?? data_get($payload, 'invoice.external_id');
        $status = data_get($payload, 'status') ?? data_get($payload, 'data.status');
        $eventType = data_get($payload, 'event') ?? data_get($payload, 'type');

        Log::info('Xendit webhook received', [
            'event' => $eventType,
            'status' => $status,
            'external_id' => $externalId,
        ]);

        if (! $externalId) {
            return response()->json(['status' => 'ignored']);
        }

        $bookingId = (int) preg_replace('/^villa-estela-booking-/', '', (string) $externalId);
        $booking = Booking::find($bookingId);
        if (! $booking) {
            return response()->json(['status' => 'not_found'], 404);
        }

        if ($status === 'PAID' || $status === 'paid' || str_contains(strtolower((string) $eventType), 'paid')) {
            $booking->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);

            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'ignored']);
    }
}
