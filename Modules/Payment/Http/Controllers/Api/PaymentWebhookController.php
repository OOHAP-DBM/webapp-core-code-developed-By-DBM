<?php

declare(strict_types=1);

namespace Modules\Payment\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\PaymentService;
use Modules\Bookings\Services\BookingService;
use Throwable;

class PaymentWebhookController extends Controller
{
    protected PaymentService $paymentService;
    protected BookingService $bookingService;

    public function __construct(PaymentService $paymentService, BookingService $bookingService)
    {
        $this->paymentService = $paymentService;
        $this->bookingService = $bookingService;
    }

    /**
     * Handle Razorpay webhook events.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $webhookSecret = config('services.razorpay.webhook_secret');

        // Log raw payload (mask sensitive fields if needed)
        Log::info('Razorpay Webhook Received', [
            'payload' => $this->maskPayload(json_decode($payload, true)),
            'signature' => $signature,
        ]);

        // 1️⃣ Verify signature
        if (!$this->verifySignature($payload, $signature, $webhookSecret)) {
            Log::warning('Razorpay Webhook: Invalid signature', ['signature' => $signature]);
            return response()->json(['status' => 'invalid signature'], 200);
        }

        $data = json_decode($payload, true);
        if (!isset($data['event']) || !isset($data['payload'])) {
            Log::warning('Razorpay Webhook: Missing event or payload');
            return response()->json(['status' => 'invalid payload'], 200);
        }

        $event = $data['event'];
        $eventId = $data['id'] ?? null;

        // 3️⃣ Idempotency check
        if ($this->paymentService->isWebhookProcessed($eventId, $data)) {
            Log::info('Razorpay Webhook: Idempotent event', ['event_id' => $eventId]);
            return response()->json(['status' => 'already processed'], 200);
        }

        try {
            switch ($event) {
                case 'payment.captured':
                    $this->handlePaymentCaptured($data);
                    break;
                case 'payment.failed':
                    $this->handlePaymentFailed($data);
                    break;
                case 'refund.processed':
                    // Stub for future implementation
                    break;
                default:
                    Log::info('Razorpay Webhook: Unhandled event', ['event' => $event]);
                    break;
            }
            $this->paymentService->markWebhookProcessed($eventId, $data);
            return response()->json(['status' => 'success'], 200);
        } catch (Throwable $e) {
            Log::error('Razorpay Webhook: Exception', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
            // Never throw to Razorpay, always return 200
            return response()->json(['status' => 'error'], 200);
        }
    }

    /**
     * Handle payment.captured event.
     */
    protected function handlePaymentCaptured(array $data): void
    {
        $payment = $data['payload']['payment']['entity'] ?? [];
        $razorpayPaymentId = $payment['id'] ?? null;
        if (!$razorpayPaymentId) {
            Log::warning('Razorpay Webhook: Missing payment id');
            return;
        }
        DB::transaction(function () use ($razorpayPaymentId, $payment) {
            // Update payment record
            $this->paymentService->markPaymentSuccess($razorpayPaymentId, $payment);
            // Update related booking
            $this->bookingService->markBookingPaidByPaymentId($razorpayPaymentId);
            // Auto-create campaign if needed
            $this->bookingService->autoCreateCampaignIfRequired($razorpayPaymentId);
        });
        Log::info('Razorpay Webhook: payment.captured processed', ['payment_id' => $razorpayPaymentId]);
    }

    /**
     * Handle payment.failed event.
     */
    protected function handlePaymentFailed(array $data): void
    {
        $payment = $data['payload']['payment']['entity'] ?? [];
        $razorpayPaymentId = $payment['id'] ?? null;
        $reason = $payment['error_reason'] ?? 'unknown';
        if (!$razorpayPaymentId) {
            Log::warning('Razorpay Webhook: Missing payment id (failed)');
            return;
        }
        $this->paymentService->markPaymentFailed($razorpayPaymentId, $reason, $payment);
        Log::info('Razorpay Webhook: payment.failed processed', ['payment_id' => $razorpayPaymentId]);
    }

    /**
     * Verify Razorpay webhook signature.
     */
    protected function verifySignature(string $payload, ?string $signature, string $secret): bool
    {
        if (!$signature || !$secret) {
            return false;
        }
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    /**
     * Mask sensitive fields in payload for logging.
     */
    protected function maskPayload(array $payload): array
    {
        // Example: mask card details, email, phone
        if (isset($payload['payload']['payment']['entity']['card'])) {
            $payload['payload']['payment']['entity']['card'] = '***';
        }
        if (isset($payload['payload']['payment']['entity']['email'])) {
            $payload['payload']['payment']['entity']['email'] = '***';
        }
        if (isset($payload['payload']['payment']['entity']['contact'])) {
            $payload['payload']['payment']['entity']['contact'] = '***';
        }
        return $payload;
    }
}
