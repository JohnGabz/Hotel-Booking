<?php

namespace App\Http\Controllers\Webhook;

use App\Events\BookingConfirmed;
use App\Events\PaymentVerified;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\WebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        if (! $this->hasValidSignature($request)) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $payload = $request->all();
        $provider = (string) ($payload['provider'] ?? 'xendit');
        $eventId = $this->eventId($request, $payload);
        $eventType = (string) (data_get($payload, 'event') ?? data_get($payload, 'type') ?? data_get($payload, 'data.event') ?? 'payment.updated');
        $bookingId = $this->bookingId($payload);
        $status = Str::lower((string) (data_get($payload, 'status') ?? data_get($payload, 'data.status') ?? data_get($payload, 'invoice.status')));
        $paymentReference = $this->paymentReference($payload);
        $paymentMethod = data_get($payload, 'payment_method') ?? data_get($payload, 'data.payment_method') ?? data_get($payload, 'payment_channel');

        $result = DB::transaction(function () use ($provider, $eventId, $eventType, $bookingId, $payload, $status, $paymentReference, $paymentMethod) {
            $existingEvent = WebhookEvent::query()
                ->where('provider', $provider)
                ->where('event_id', $eventId)
                ->lockForUpdate()
                ->first();

            if ($existingEvent?->processed_at) {
                return ['status' => 'duplicate', 'booking_id' => $existingEvent->booking_id, 'confirmed' => false];
            }

            $webhookEvent = $existingEvent ?: WebhookEvent::create([
                'provider' => $provider,
                'event_id' => $eventId,
                'idempotency_key' => $this->idempotencyKey($eventId, $payload),
                'event_type' => $eventType,
                'payload' => $payload,
            ]);

            $booking = $this->findBooking($bookingId, $paymentReference);

            if (! $booking) {
                $webhookEvent->update(['processed_at' => now()]);

                return ['status' => 'not_found', 'booking_id' => null, 'confirmed' => false];
            }

            $booking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $webhookEvent->update([
                'booking_id' => $booking->id,
                'payload' => $payload,
            ]);

            $isPaid = in_array($status, ['paid', 'settled', 'succeeded', 'success', 'captured'], true)
                || str_contains(Str::lower($eventType), 'paid');

            if ($isPaid) {
                $alreadyConfirmed = $booking->payment_status === 'paid' && $booking->status === 'confirmed';
                $booking->confirmPayment($paymentReference, $paymentMethod, $provider, $payload);
                $webhookEvent->update(['processed_at' => now()]);

                return ['status' => 'ok', 'booking_id' => $booking->id, 'confirmed' => ! $alreadyConfirmed];
            }

            if (in_array($status, ['failed', 'expired', 'voided'], true)) {
                $booking->update(['payment_status' => 'failed']);
            }

            $webhookEvent->update(['processed_at' => now()]);

            return ['status' => 'ignored', 'booking_id' => $booking->id, 'confirmed' => false];
        });

        if ($result['confirmed'] && $result['booking_id']) {
            event(new PaymentVerified($result['booking_id']));
            event(new BookingConfirmed($result['booking_id']));
        }

        Log::info('Payment webhook handled', $result + ['event_id' => $eventId, 'provider' => $provider]);

        return response()->json(['status' => $result['status']]);
    }

    protected function hasValidSignature(Request $request): bool
    {
        $secret = config('services.payment.webhook_secret') ?: config('services.xendit.webhook_token');

        if (! $secret) {
            return true;
        }

        $signature = $request->header('X-Payment-Signature');
        if ($signature) {
            $expected = hash_hmac('sha256', $request->getContent(), $secret);

            return hash_equals($expected, $signature);
        }

        return hash_equals((string) $secret, (string) $request->header('X-Callback-Token'));
    }

    protected function eventId(Request $request, array $payload): string
    {
        return (string) (
            $request->header('X-Idempotency-Key')
            ?: data_get($payload, 'id')
            ?: data_get($payload, 'event_id')
            ?: data_get($payload, 'data.id')
            ?: data_get($payload, 'invoice.id')
            ?: hash('sha256', $request->getContent())
        );
    }

    protected function idempotencyKey(string $eventId, array $payload): string
    {
        return (string) (data_get($payload, 'idempotency_key') ?: $eventId);
    }

    protected function bookingId(array $payload): ?int
    {
        $directId = data_get($payload, 'booking_id') ?? data_get($payload, 'data.booking_id');

        if ($directId) {
            return (int) $directId;
        }

        $externalId = data_get($payload, 'external_id')
            ?? data_get($payload, 'data.external_id')
            ?? data_get($payload, 'invoice.external_id');

        if (! $externalId) {
            return null;
        }

        return (int) preg_replace('/^villa-estela-booking-/', '', (string) $externalId);
    }

    protected function paymentReference(array $payload): ?string
    {
        $reference = data_get($payload, 'payment_reference')
            ?? data_get($payload, 'data.payment_reference')
            ?? data_get($payload, 'data.id')
            ?? data_get($payload, 'invoice.id');

        if ($reference) {
            return (string) $reference;
        }

        return data_get($payload, 'id') ? (string) data_get($payload, 'id') : null;
    }

    protected function findBooking(?int $bookingId, ?string $paymentReference): ?Booking
    {
        if ($bookingId) {
            return Booking::find($bookingId);
        }

        if ($paymentReference) {
            return Booking::where('payment_reference', $paymentReference)->first();
        }

        return null;
    }
}
