<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Services\RazorpayPayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * RazorpayWebhookController
 * 
 * Handles all Razorpay webhook events:
 * - Payment events (authorized, captured, failed) - via PaymentService
 * - Account events (activated, suspended) - for vendor payouts
 */
class RazorpayWebhookController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = new PaymentService('razorpay');
    }

    /**
     * Handle Razorpay webhook events
     * POST /api/webhooks/razorpay
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            // Get raw payload and signature
            $payload = $request->getContent();
            $signature = $request->header('X-Razorpay-Signature');
            
            // Parse payload to determine event type
            $data = json_decode($payload, true);
            $event = $data['event'] ?? null;

            // Route account events separately (for vendor payouts)
            if (in_array($event, ['account.activated', 'account.suspended'])) {
                return $this->handleAccountEvent($data, $signature);
            }

            // All payment events are handled via PaymentService
            $result = $this->paymentService->handleWebhook($payload, $signature, 'razorpay');

            if ($result['success']) {
                Log::info('Razorpay webhook processed successfully', [
                    'event' => $result['event'] ?? 'unknown',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully'
                ]);
            }

            Log::warning('Razorpay webhook processing returned unsuccessful', [
                'result' => $result
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
                'error' => $result['error'] ?? 'Unknown error'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Razorpay webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle account-related events (for vendor payouts)
     */
    protected function handleAccountEvent(array $payload, ?string $signature): JsonResponse
    {
        try {
            $event = $payload['event'] ?? null;

            // Verify webhook signature
            if (!$this->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Razorpay account webhook signature verification failed');
                return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
            }

            // Route to appropriate handler
            $handled = match($event) {
                'account.activated' => $this->handleAccountActivated($payload),
                'account.suspended' => $this->handleAccountSuspended($payload),
                default => false
            };

            if ($handled) {
                return response()->json(['success' => true, 'message' => 'Account webhook processed']);
            }

            return response()->json(['success' => false, 'message' => 'Unknown account event'], 400);

        } catch (\Exception $e) {
            Log::error('Razorpay account webhook processing failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing error'
            ], 500);
        }
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
            $payoutService->handleAccountActivated($payload);

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
}
