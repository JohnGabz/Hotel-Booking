<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentController extends Controller
{
    public function create(Request $request, Booking $booking): RedirectResponse
    {
        if ((int) $booking->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $booking->loadMissing('room');

        if ($booking->payment_status === 'paid' && $booking->status === 'confirmed') {
            return redirect()->route('dashboard')->with('success', 'This booking is already paid and confirmed.');
        }

        if ($existingCheckoutUrl = $this->existingCheckoutUrl($booking)) {
            return redirect()->away($existingCheckoutUrl);
        }

        $secretKey = (string) config('services.xendit.secret_key', config('services.xendit.key'));
        if ($secretKey === '') {
            return back()->with('error', 'Xendit test credentials are not configured yet.');
        }

        $amount = round((float) $booking->total, 2);
        $externalId = 'villa-estela-booking-' . $booking->id;
        $payload = $this->buildInvoicePayload($booking, $amount, $externalId);
        $invoiceUrl = rtrim((string) config('services.xendit.invoice_base_url', 'https://api.xendit.co'), '/') . '/v2/invoices';

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->acceptJson()
                ->withHeaders(['X-Idempotency-Key' => $externalId])
                ->post($invoiceUrl, $payload);

            if (! $response->successful()) {
                Log::error('Xendit create invoice failed', ['resp' => $response->body()]);
                return back()->with('error', 'Could not create a payment session right now. You can upload payment proof instead.');
            }

            $data = $response->json();
            $checkoutUrl = data_get($data, 'invoice_url');
            $invoiceId = data_get($data, 'id');

            if (! $checkoutUrl || ! $invoiceId) {
                Log::error('Xendit missing invoice_url', ['resp' => $response->body()]);
                return back()->with('error', 'Payment gateway did not return a usable checkout link. Please try again.');
            }

            DB::transaction(function () use ($booking, $invoiceId, $externalId, $data) {
                $lockedBooking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

                $lockedBooking->update([
                    'payment_status' => 'pending',
                    'payment_reference' => $invoiceId,
                ]);

                PaymentTransaction::updateOrCreate(
                    [
                        'booking_id' => $lockedBooking->id,
                        'transaction_id' => $invoiceId,
                    ],
                    [
                        'provider' => 'xendit',
                        'amount' => $lockedBooking->total,
                        'status' => 'pending',
                        'payment_method' => $lockedBooking->payment_method,
                        'payload' => array_merge($data, ['external_id' => $externalId]),
                        'processed_at' => null,
                    ]
                );
            });

            return redirect()->away($checkoutUrl);
        } catch (Throwable $e) {
            Log::error('Xendit invoice creation exception', [
                'booking_id' => $booking->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to start payment right now. Please try again or upload payment proof.');
        }
    }

    protected function buildInvoicePayload(Booking $booking, float $amount, string $externalId): array
    {
        return [
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
    }

    protected function existingCheckoutUrl(Booking $booking): ?string
    {
        $transaction = PaymentTransaction::query()
            ->where('booking_id', $booking->id)
            ->where('provider', 'xendit')
            ->whereIn('status', ['pending', 'created', 'processing'])
            ->latest('id')
            ->first();

        if (! $transaction) {
            return null;
        }

        $checkoutUrl = data_get($transaction->payload, 'invoice_url');

        return $checkoutUrl ? (string) $checkoutUrl : null;
    }

    public function webhook(Request $request): \Illuminate\Http\JsonResponse
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
