<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\PaymentAuthorized;
use App\Events\PaymentCaptured;
use App\Events\PaymentFailed;
use Modules\Payment\Models\RazorpayLog;
use App\Services\RazorpayPayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    /**
     * Handle Razorpay webhook events
     * POST /api/webhooks/razorpay
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            // Get webhook payload
            $payload = $request->all();
            $signature = $request->header('X-Razorpay-Signature');

            // Log incoming webhook
            $this->logWebhook('webhook_received', $payload, $signature);

            // Validate webhook signature
            if (!$this->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Razorpay webhook signature verification failed', [
                    'signature' => $signature,
                    'payload' => $payload
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook signature'
                ], 401);
            }

            // Extract event type
            $event = $payload['event'] ?? null;

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event type missing'
                ], 400);
            }

            // Route to appropriate handler
            $handled = match($event) {
                'payment.authorized' => $this->handlePaymentAuthorized($payload),
                'payment.captured' => $this->handlePaymentCaptured($payload),
                'payment.failed' => $this->handlePaymentFailed($payload),
                'order.paid' => $this->handleOrderPaid($payload),
                'account.activated' => $this->handleAccountActivated($payload),
                'account.suspended' => $this->handleAccountSuspended($payload),
                default => $this->handleUnknownEvent($event, $payload)
            };

            if ($handled) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Razorpay webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment.authorized event
     */
    protected function handlePaymentAuthorized(array $payload): bool
    {
        try {
            $paymentData = $payload['payload']['payment']['entity'] ?? null;

            if (!$paymentData) {
                Log::error('Payment data missing in payment.authorized webhook');
                return false;
            }

            $paymentId = $paymentData['id'];
            $orderId = $paymentData['order_id'];

            Log::info('Processing payment.authorized webhook', [
                'payment_id' => $paymentId,
                'order_id' => $orderId
            ]);

            // Fire event for listener to handle
            event(new PaymentAuthorized($paymentId, $orderId, $payload));

            $this->logWebhook('payment.authorized', $payload, null, 200);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to handle payment.authorized webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return false;
        }
    }

    /**
     * Handle payment.captured event
     */
    protected function handlePaymentCaptured(array $payload): bool
    {
        try {
            $paymentData = $payload['payload']['payment']['entity'] ?? null;

            if (!$paymentData) {
                Log::error('Payment data missing in payment.captured webhook');
                return false;
            }

            $paymentId = $paymentData['id'];
            $orderId = $paymentData['order_id'];
            $amount = ($paymentData['amount'] ?? 0) / 100; // Convert paise to rupees

            Log::info('Processing payment.captured webhook', [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'amount' => $amount
            ]);

            // Fire event for listener to handle
            event(new PaymentCaptured($paymentId, $orderId, $amount, $payload));

            $this->logWebhook('payment.captured', $payload, null, 200);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to handle payment.captured webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return false;
        }
    }

    /**
     * Handle payment.failed event
     */
    protected function handlePaymentFailed(array $payload): bool
    {
        try {
            $paymentData = $payload['payload']['payment']['entity'] ?? null;

            if (!$paymentData) {
                Log::error('Payment data missing in payment.failed webhook');
                return false;
            }

            $paymentId = $paymentData['id'];
            $orderId = $paymentData['order_id'];
            $errorCode = $paymentData['error_code'] ?? 'UNKNOWN_ERROR';
            $errorDescription = $paymentData['error_description'] ?? 'Payment failed';

            Log::info('Processing payment.failed webhook', [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'error_code' => $errorCode
            ]);

            // Fire event for listener to handle
            event(new PaymentFailed($paymentId, $orderId, $errorCode, $errorDescription, $payload));

            $this->logWebhook('payment.failed', $payload, null, 200);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to handle payment.failed webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return false;
        }
    }

    /**
     * Handle order.paid event
     */
    protected function handleOrderPaid(array $payload): bool
    {
        // Order paid event can be used for reconciliation
        Log::info('Received order.paid webhook', [
            'payload' => $payload
        ]);

        $this->logWebhook('order.paid', $payload, null, 200);

        return true;
    }

    /**
     * Handle unknown event types
     */
    protected function handleUnknownEvent(string $event, array $payload): bool
    {
        Log::info('Received unknown Razorpay webhook event', [
            'event' => $event,
            'payload' => $payload
        ]);

        $this->logWebhook("unknown_event:{$event}", $payload, null, 200);

        return true;
    }

    /**
     * Handle account.activated event (Razorpay Route)
     */
    protected function handleAccountActivated(array $payload): bool
    {
        try {
            Log::info('Processing account.activated webhook', [
                'payload' => $payload
            ]);

            $payoutService = new RazorpayPayoutService();
            $payoutService->handleAccountVerified($payload);

            $this->logWebhook('account.activated', $payload, null, 200);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to handle account.activated webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return false;
        }
    }

    /**
     * Handle account.suspended event (Razorpay Route)
     */
    protected function handleAccountSuspended(array $payload): bool
    {
        try {
            Log::info('Processing account.suspended webhook', [
                'payload' => $payload
            ]);

            $payoutService = new RazorpayPayoutService();
            $payoutService->handleAccountRejected($payload);

            $this->logWebhook('account.suspended', $payload, null, 200);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to handle account.suspended webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return false;
        }
    }

    /**
     * Verify webhook signature
     * https://razorpay.com/docs/webhooks/validate-test/#signature-validation
     */
    protected function verifyWebhookSignature(array $payload, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }

        $webhookSecret = config('services.razorpay.webhook_secret');

        if (!$webhookSecret) {
            Log::warning('Razorpay webhook secret not configured');
            return false;
        }

        // Generate expected signature
        $expectedSignature = hash_hmac('sha256', json_encode($payload), $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Log webhook to database
     */
    protected function logWebhook(string $action, array $payload, ?string $signature, int $statusCode = 200): void
    {
        try {
            RazorpayLog::create([
                'action' => $action,
                'request_payload' => $payload,
                'response_payload' => null,
                'status_code' => $statusCode,
                'metadata' => [
                    'signature' => $signature,
                    'event' => $payload['event'] ?? null,
                    'payment_id' => $payload['payload']['payment']['entity']['id'] ?? null,
                    'order_id' => $payload['payload']['payment']['entity']['order_id'] ?? null,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log Razorpay webhook', [
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }
}

