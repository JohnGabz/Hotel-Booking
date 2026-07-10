<?php

namespace App\Http\Controllers;

use App\Events\PaymentPending;
use App\Events\PaymentStatusUpdated;
use App\Models\Booking;
use App\Models\PaymentTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Throwable;

class PaymentController extends Controller
{
    public function create(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== null && (! $request->user() || (int) $booking->user_id !== (int) $request->user()->id)) {
            abort(403);
        }

        $booking->loadMissing('room');

        if ($booking->payment_status === 'paid' && in_array($booking->status, ['confirmed', 'Confirmed'], true)) {
            return redirect()->route('dashboard')->with('success', 'This booking is already paid and confirmed.');
        }

        try {
            $checkoutUrl = $this->getOrCreateCheckoutUrl($booking);
            return redirect()->away($checkoutUrl);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function getOrCreateCheckoutUrl(Booking $booking): string
    {
        if ($existingCheckoutUrl = $this->existingCheckoutUrl($booking)) {
            return $existingCheckoutUrl;
        }

        $secretKey = (string) config('services.xendit.secret_key', config('services.xendit.key'));
        if ($secretKey === '') {
            throw new \Exception('Xendit test credentials are not configured yet.');
        }

        $isBalancePayment = $booking->amount_paid > 0;
        $amount = $isBalancePayment 
            ? round((float) ($booking->total - $booking->amount_paid), 2)
            : round((float) ($booking->total * 0.5), 2);

        $externalId = $isBalancePayment
            ? 'villa-estela-booking-balance-'.$booking->id
            : 'villa-estela-booking-'.$booking->id;

        $payload = $this->buildInvoicePayload($booking, $amount, $externalId);
        $invoiceUrl = rtrim((string) config('services.xendit.invoice_base_url', 'https://api.xendit.co'), '/').'/v2/invoices';

        $response = Http::withBasicAuth($secretKey, '')
            ->timeout(10)
            ->acceptJson()
            ->withHeaders(['X-Idempotency-Key' => $externalId])
            ->post($invoiceUrl, $payload);

        if (! $response->successful()) {
            Log::error('Xendit create invoice failed', ['resp' => $response->body()]);
            throw new \Exception('Could not create a payment session right now. Please try again.');
        }

        $data = $response->json();
        $checkoutUrl = data_get($data, 'invoice_url');
        $invoiceId = data_get($data, 'id');

        if (! $checkoutUrl || ! $invoiceId) {
            Log::error('Xendit missing invoice_url', ['resp' => $response->body()]);
            throw new \Exception('Payment gateway did not return a usable checkout link. Please try again.');
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
                    'amount' => $lockedBooking->amount_paid > 0 ? ($lockedBooking->total - $lockedBooking->amount_paid) : ($lockedBooking->total * 0.5),
                    'status' => 'pending',
                    'payment_method' => $lockedBooking->payment_method,
                    'payload' => array_merge($data, ['external_id' => $externalId]),
                    'processed_at' => null,
                ]
            );
        });

        event(new PaymentPending($booking->id));
        event(new PaymentStatusUpdated($booking->id));

        return $checkoutUrl;
    }

    protected function buildInvoicePayload(Booking $booking, float $amount, string $externalId): array
    {
        return [
            'external_id' => $externalId,
            'amount' => $amount,
            'currency' => 'PHP',
            'description' => 'Booking #'.$booking->id.' - '.$booking->room->name . ($booking->amount_paid > 0 ? ' (Remaining Balance)' : ' (Deposit)'),
            'invoice_duration' => 86400,
            'success_redirect_url' => URL::signedRoute('bookings.success', ['booking' => $booking->id]),
            'failure_redirect_url' => route('rooms.show', ['room' => $booking->room->slug]) . '?payment=failed&booking=' . $booking->id,
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

        $bookingId = (int) preg_replace('/^villa-estela-booking-(balance-)?/', '', (string) $externalId);
        $booking = Booking::find($bookingId);
        if (! $booking) {
            return response()->json(['status' => 'not_found'], 404);
        }

        if ($status === 'PAID' || $status === 'paid' || str_contains(strtolower((string) $eventType), 'paid')) {
            $booking->confirmPayment(
                data_get($payload, 'id') ?? data_get($payload, 'data.id') ?? data_get($payload, 'invoice.id'),
                data_get($payload, 'payment_method') ?? data_get($payload, 'data.payment_method') ?? 'xendit',
                'xendit',
                $payload
            );

            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'ignored']);
    }
}
