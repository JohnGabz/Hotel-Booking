<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class XenditRefundService
{
    public function supportsBooking(Booking $booking): bool
    {
        if ($booking->payment_status !== 'paid' || ! $booking->payment_reference) {
            return false;
        }

        if ($booking->payment_method === 'xendit') {
            return true;
        }

        return PaymentTransaction::query()
            ->where('booking_id', $booking->id)
            ->where('provider', 'xendit')
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function createRefund(Booking $booking): array
    {
        $secretKey = (string) config('services.xendit.secret_key', config('services.xendit.key'));
        if ($secretKey === '') {
            throw new RuntimeException('Xendit credentials are not configured.');
        }

        $baseUrl = rtrim((string) config('services.xendit.invoice_base_url', 'https://api.xendit.co'), '/');
        $paymentRequestId = $this->resolvePaymentRequestId($booking, $secretKey, $baseUrl);

        $payload = [
            'reference_id' => 'villa-estela-refund-booking-'.$booking->id,
            'currency' => 'PHP',
            'amount' => round((float) $booking->total, 2),
            'reason' => 'REQUESTED_BY_CUSTOMER',
        ];

        if ($paymentRequestId) {
            $payload['payment_request_id'] = $paymentRequestId;
        } else {
            $payload['invoice_id'] = (string) $booking->payment_reference;
        }

        $response = Http::withBasicAuth($secretKey, '')
            ->acceptJson()
            ->withHeaders([
                'Idempotency-Key' => 'refund-booking-'.$booking->id,
            ])
            ->post($baseUrl.'/refunds', $payload);

        if (! $response->successful()) {
            Log::error('Xendit create refund failed', [
                'booking_id' => $booking->id,
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);

            $message = data_get($response->json(), 'message')
                ?: 'Xendit could not process the refund. Please try again or process it manually in the Xendit dashboard.';

            throw new RuntimeException($message);
        }

        $data = $response->json();

        PaymentTransaction::updateOrCreate(
            [
                'booking_id' => $booking->id,
                'transaction_id' => (string) (data_get($data, 'id') ?: 'refund-'.$booking->id),
            ],
            [
                'provider' => 'xendit',
                'amount' => $booking->total,
                'status' => strtolower((string) data_get($data, 'status', 'pending')),
                'payment_method' => $booking->payment_method,
                'payload' => array_merge($data ?? [], [
                    'refund_for_invoice' => $booking->payment_reference,
                    'payment_request_id' => $paymentRequestId,
                ]),
                'processed_at' => null,
            ]
        );

        Log::info('Xendit refund created', [
            'booking_id' => $booking->id,
            'refund_id' => data_get($data, 'id'),
            'status' => data_get($data, 'status'),
        ]);

        return is_array($data) ? $data : [];
    }

    protected function resolvePaymentRequestId(Booking $booking, string $secretKey, string $baseUrl): ?string
    {
        $transaction = PaymentTransaction::query()
            ->where('booking_id', $booking->id)
            ->where('provider', 'xendit')
            ->latest('id')
            ->first();

        $candidates = [
            data_get($transaction?->payload, 'payment_request_id'),
            data_get($transaction?->payload, 'data.payment_request_id'),
            data_get($transaction?->payload, 'payment_request.id'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && str_starts_with($candidate, 'pr-')) {
                return $candidate;
            }
        }

        $invoiceId = $booking->payment_reference;
        if (! $invoiceId) {
            return null;
        }

        $response = Http::withBasicAuth($secretKey, '')
            ->acceptJson()
            ->get($baseUrl.'/v2/invoices/'.$invoiceId);

        if (! $response->successful()) {
            Log::warning('Unable to fetch Xendit invoice for refund resolution', [
                'booking_id' => $booking->id,
                'invoice_id' => $invoiceId,
                'status' => $response->status(),
            ]);

            return null;
        }

        $invoice = $response->json();

        foreach ([
            data_get($invoice, 'payment_request_id'),
            data_get($invoice, 'payment_request.id'),
            data_get($invoice, 'payment_method_id'),
            data_get($invoice, 'payment_id'),
        ] as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                if (str_starts_with($candidate, 'pr-')) {
                    return $candidate;
                }
            }
        }

        return null;
    }
}
