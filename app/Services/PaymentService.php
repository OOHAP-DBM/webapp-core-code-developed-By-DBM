<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentTransaction;
use App\Services\Gateways\RazorpayGateway;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * PaymentService
 * PROMPT 69: Payment Gateway Integration Wrapper
 * 
 * Unified service for all payment gateway operations
 * Handles order creation, payment capture, refunds, webhooks
 */
class PaymentService
{
    protected PaymentGatewayInterface $gateway;
    protected string $currentGateway;

    /**
     * Initialize with specified gateway
     */
    public function __construct(?string $gateway = null)
    {
        $this->currentGateway = $gateway ?? config('services.payment.default_gateway', 'razorpay');
        $this->gateway = $this->resolveGateway($this->currentGateway);
    }

    /**
     * Resolve gateway instance
     */
    protected function resolveGateway(string $gateway): PaymentGatewayInterface
    {
        return match ($gateway) {
            'razorpay' => app(RazorpayGateway::class),
            // 'stripe' => app(StripeGateway::class), // Phase 2
            default => throw new Exception("Unsupported payment gateway: {$gateway}"),
        };
    }

    /**
     * Create payment order
     *
     * @param float $amount Amount in base currency
     * @param string $currency Currency code
     * @param array $options [
     *   'reference_type' => 'Booking',
     *   'reference_id' => 123,
     *   'user_id' => 456,
     *   'receipt' => 'BOOKING_123',
     *   'description' => 'Booking payment',
     *   'customer_name' => 'John Doe',
     *   'customer_email' => 'john@example.com',
     *   'customer_phone' => '9876543210',
     *   'manual_capture' => true,
     *   'capture_expiry_minutes' => 30,
     *   'metadata' => []
     * ]
     * @return array [
     *   'success' => bool,
     *   'transaction' => PaymentTransaction,
     *   'order_data' => array (gateway response)
     * ]
     */
    public function createOrder(float $amount, string $currency = 'INR', array $options = []): array
    {
        try {
            // Create gateway order
            $orderData = $this->gateway->createOrder($amount, $currency, $options);

            // Calculate amounts
            $smallestUnitAmount = $this->gateway->convertToSmallestUnit($amount, $currency);

            // Create transaction record
            $transaction = PaymentTransaction::create([
                'gateway' => $this->currentGateway,
                'transaction_type' => PaymentTransaction::TYPE_ORDER,
                'gateway_order_id' => $orderData['id'] ?? $orderData['order_id'] ?? null,
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id' => $options['reference_id'] ?? null,
                'user_id' => $options['user_id'] ?? null,
                'amount' => $amount,
                'currency' => $currency,
                'amount_in_smallest_unit' => $smallestUnitAmount,
                'status' => PaymentTransaction::STATUS_CREATED,
                'manual_capture' => $options['manual_capture'] ?? false,
                'capture_expires_at' => isset($options['capture_expiry_minutes']) 
                    ? now()->addMinutes($options['capture_expiry_minutes'])
                    : null,
                'customer_name' => $options['customer_name'] ?? null,
                'customer_email' => $options['customer_email'] ?? null,
                'customer_phone' => $options['customer_phone'] ?? null,
                'receipt_number' => $options['receipt'] ?? null,
                'description' => $options['description'] ?? null,
                'request_payload' => $options,
                'response_payload' => $orderData,
                'metadata' => $options['metadata'] ?? null,
            ]);

            Log::info('Payment order created', [
                'gateway' => $this->currentGateway,
                'transaction_id' => $transaction->id,
                'order_id' => $transaction->gateway_order_id,
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'transaction' => $transaction,
                'order_data' => $orderData,
            ];

        } catch (Exception $e) {
            Log::error('Payment order creation failed', [
                'gateway' => $this->currentGateway,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            // Create failed transaction record
            $transaction = PaymentTransaction::create([
                'gateway' => $this->currentGateway,
                'transaction_type' => PaymentTransaction::TYPE_ORDER,
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id' => $options['reference_id'] ?? null,
                'user_id' => $options['user_id'] ?? null,
                'amount' => $amount,
                'currency' => $currency,
                'status' => PaymentTransaction::STATUS_FAILED,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'request_payload' => $options,
            ]);

            return [
                'success' => false,
                'transaction' => $transaction,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Capture authorized payment
     *
     * @param string $paymentId Gateway payment ID
     * @param float $amount Amount to capture
     * @param array $options Additional options
     * @return array [
     *   'success' => bool,
     *   'transaction' => PaymentTransaction,
     *   'capture_data' => array
     * ]
     */
    public function capturePayment(string $paymentId, float $amount, array $options = []): array
    {
        try {
            // Find transaction
            $transaction = PaymentTransaction::where('gateway_payment_id', $paymentId)
                ->where('gateway', $this->currentGateway)
                ->firstOrFail();

            // Capture via gateway
            $captureData = $this->gateway->capturePayment($paymentId, $amount, $options);

            // Update transaction
            $transaction->markCaptured($amount, $captureData);

            // Create capture transaction record
            $captureTransaction = PaymentTransaction::create([
                'gateway' => $this->currentGateway,
                'transaction_type' => PaymentTransaction::TYPE_CAPTURE,
                'gateway_order_id' => $transaction->gateway_order_id,
                'gateway_payment_id' => $paymentId,
                'reference_type' => $transaction->reference_type,
                'reference_id' => $transaction->reference_id,
                'user_id' => $transaction->user_id,
                'amount' => $amount,
                'currency' => $transaction->currency,
                'amount_in_smallest_unit' => $this->gateway->convertToSmallestUnit($amount, $transaction->currency),
                'status' => PaymentTransaction::STATUS_CAPTURED,
                'captured_at' => now(),
                'request_payload' => $options,
                'response_payload' => $captureData,
            ]);

            Log::info('Payment captured', [
                'gateway' => $this->currentGateway,
                'payment_id' => $paymentId,
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'transaction' => $transaction->fresh(),
                'capture_transaction' => $captureTransaction,
                'capture_data' => $captureData,
            ];

        } catch (Exception $e) {
            Log::error('Payment capture failed', [
                'gateway' => $this->currentGateway,
                'payment_id' => $paymentId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create refund
     *
     * @param string $paymentId Gateway payment ID
     * @param float $amount Amount to refund (full or partial)
     * @param array $options [
     *   'reason' => 'Customer request',
     *   'notes' => 'Additional notes',
     *   'speed' => 'optimum' (razorpay) or 'instant'
     * ]
     * @return array
     */
    public function createRefund(string $paymentId, float $amount, array $options = []): array
    {
        try {
            // Find payment transaction
            $transaction = PaymentTransaction::where('gateway_payment_id', $paymentId)
                ->where('gateway', $this->currentGateway)
                ->where('status', PaymentTransaction::STATUS_CAPTURED)
                ->firstOrFail();

            // Validate refund amount
            if ($amount > $transaction->getRefundableAmount()) {
                throw new Exception('Refund amount exceeds refundable amount');
            }

            // Create refund via gateway
            $refundData = $this->gateway->createRefund($paymentId, $amount, $options);

            // Update transaction
            $transaction->addRefund($amount, $refundData['id'] ?? $refundData['refund_id'], array_merge($refundData, [
                'reason' => $options['reason'] ?? null,
            ]));

            // Create refund transaction record
            $refundTransaction = PaymentTransaction::create([
                'gateway' => $this->currentGateway,
                'transaction_type' => PaymentTransaction::TYPE_REFUND,
                'gateway_order_id' => $transaction->gateway_order_id,
                'gateway_payment_id' => $paymentId,
                'gateway_refund_id' => $refundData['id'] ?? $refundData['refund_id'],
                'reference_type' => $transaction->reference_type,
                'reference_id' => $transaction->reference_id,
                'user_id' => $transaction->user_id,
                'amount' => $amount,
                'currency' => $transaction->currency,
                'amount_in_smallest_unit' => $this->gateway->convertToSmallestUnit($amount, $transaction->currency),
                'status' => PaymentTransaction::STATUS_REFUNDED,
                'refunded_at' => now(),
                'refund_reason' => $options['reason'] ?? null,
                'request_payload' => $options,
                'response_payload' => $refundData,
            ]);

            Log::info('Refund created', [
                'gateway' => $this->currentGateway,
                'payment_id' => $paymentId,
                'refund_id' => $refundTransaction->gateway_refund_id,
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'transaction' => $transaction->fresh(),
                'refund_transaction' => $refundTransaction,
                'refund_data' => $refundData,
            ];

        } catch (Exception $e) {
            Log::error('Refund creation failed', [
                'gateway' => $this->currentGateway,
                'payment_id' => $paymentId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle webhook
     *
     * @param string $payload Raw webhook payload
     * @param string $signature Webhook signature
     * @param string|null $gateway Gateway name (if not using current)
     * @return array
     */
    public function handleWebhook(string $payload, string $signature, ?string $gateway = null): array
    {
        try {
            $gateway = $gateway ?? $this->currentGateway;
            $gatewayInstance = $this->resolveGateway($gateway);

            // Verify signature
            if (!$gatewayInstance->verifyWebhookSignature($payload, $signature)) {
                throw new Exception('Invalid webhook signature');
            }

            // Parse webhook
            $webhookData = $gatewayInstance->parseWebhook($payload);

            Log::info('Webhook received', [
                'gateway' => $gateway,
                'event' => $webhookData['event'] ?? 'unknown',
            ]);

            // Process webhook based on event type
            $result = $this->processWebhookEvent($webhookData, $gateway);

            return [
                'success' => true,
                'event' => $webhookData['event'] ?? null,
                'result' => $result,
            ];

        } catch (Exception $e) {
            Log::error('Webhook processing failed', [
                'gateway' => $gateway ?? $this->currentGateway,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process webhook event
     */
    protected function processWebhookEvent(array $webhookData, string $gateway): array
    {
        $event = $webhookData['event'] ?? '';
        $data = $webhookData['data'] ?? [];

        // Find related transaction
        $paymentId = $data['payment_id'] ?? $data['id'] ?? null;
        $orderId = $data['order_id'] ?? null;

        $transaction = null;
        if ($paymentId) {
            $transaction = PaymentTransaction::where('gateway_payment_id', $paymentId)
                ->where('gateway', $gateway)
                ->first();
        } elseif ($orderId) {
            $transaction = PaymentTransaction::where('gateway_order_id', $orderId)
                ->where('gateway', $gateway)
                ->first();
        }

        if ($transaction) {
            $transaction->recordWebhook($event, $data);
        }

        // Handle specific events
        return match (true) {
            str_contains($event, 'payment.authorized') => $this->handlePaymentAuthorized($data, $transaction, $gateway),
            str_contains($event, 'payment.captured') => $this->handlePaymentCaptured($data, $transaction, $gateway),
            str_contains($event, 'payment.failed') => $this->handlePaymentFailed($data, $transaction, $gateway),
            str_contains($event, 'refund') => $this->handleRefundEvent($data, $transaction, $gateway),
            default => ['processed' => false, 'reason' => 'Unhandled event type'],
        };
    }

    /**
     * Handle payment authorized webhook
     */
    protected function handlePaymentAuthorized(array $data, ?PaymentTransaction $transaction, string $gateway): array
    {
        if (!$transaction) {
            return ['processed' => false, 'reason' => 'Transaction not found'];
        }

        if ($transaction->status === PaymentTransaction::STATUS_AUTHORIZED) {
            return ['processed' => false, 'reason' => 'Already authorized'];
        }

        $transaction->markAuthorized([
            'payment_id' => $data['id'] ?? $data['payment_id'],
            'method' => $data['method'] ?? null,
            'method_details' => $data['card']['last4'] ?? $data['vpa'] ?? null,
        ]);

        return ['processed' => true, 'transaction_id' => $transaction->id];
    }

    /**
     * Handle payment captured webhook
     */
    protected function handlePaymentCaptured(array $data, ?PaymentTransaction $transaction, string $gateway): array
    {
        if (!$transaction) {
            return ['processed' => false, 'reason' => 'Transaction not found'];
        }

        if ($transaction->status === PaymentTransaction::STATUS_CAPTURED) {
            return ['processed' => false, 'reason' => 'Already captured'];
        }

        $amount = isset($data['amount']) 
            ? $this->gateway->convertFromSmallestUnit($data['amount'], $transaction->currency)
            : $transaction->amount;

        $transaction->markCaptured($amount, [
            'fee' => isset($data['fee']) ? $this->gateway->convertFromSmallestUnit($data['fee'], $transaction->currency) : null,
            'tax' => isset($data['tax']) ? $this->gateway->convertFromSmallestUnit($data['tax'], $transaction->currency) : null,
        ]);

        return ['processed' => true, 'transaction_id' => $transaction->id];
    }

    /**
     * Handle payment failed webhook
     */
    protected function handlePaymentFailed(array $data, ?PaymentTransaction $transaction, string $gateway): array
    {
        if (!$transaction) {
            return ['processed' => false, 'reason' => 'Transaction not found'];
        }

        $transaction->markFailed(
            $data['error_code'] ?? null,
            $data['error_description'] ?? $data['error_message'] ?? null,
            $data
        );

        return ['processed' => true, 'transaction_id' => $transaction->id];
    }

    /**
     * Handle refund event webhook
     */
    protected function handleRefundEvent(array $data, ?PaymentTransaction $transaction, string $gateway): array
    {
        if (!$transaction) {
            return ['processed' => false, 'reason' => 'Transaction not found'];
        }

        $amount = isset($data['amount']) 
            ? $this->gateway->convertFromSmallestUnit($data['amount'], $transaction->currency)
            : 0;

        if ($amount > 0) {
            $transaction->addRefund($amount, $data['id'] ?? $data['refund_id'], $data);
        }

        return ['processed' => true, 'transaction_id' => $transaction->id];
    }

    /**
     * Get transaction by gateway payment ID
     */
    public function getTransactionByPaymentId(string $paymentId): ?PaymentTransaction
    {
        return PaymentTransaction::where('gateway_payment_id', $paymentId)
            ->where('gateway', $this->currentGateway)
            ->first();
    }

    /**
     * Get transaction by gateway order ID
     */
    public function getTransactionByOrderId(string $orderId): ?PaymentTransaction
    {
        return PaymentTransaction::where('gateway_order_id', $orderId)
            ->where('gateway', $this->currentGateway)
            ->first();
    }

    /**
     * Get transactions for reference
     */
    public function getTransactionsForReference(string $type, int $id): \Illuminate\Database\Eloquent\Collection
    {
        return PaymentTransaction::forReference($type, $id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get current gateway instance
     */
    public function getGateway(): PaymentGatewayInterface
    {
        return $this->gateway;
    }

    /**
     * Get current gateway name
     */
    public function getGatewayName(): string
    {
        return $this->currentGateway;
    }

    /**
     * Switch to different gateway
     */
    public function useGateway(string $gateway): self
    {
        $this->currentGateway = $gateway;
        $this->gateway = $this->resolveGateway($gateway);
        return $this;
    }
     /**
     * Check if a webhook event has already been processed (idempotency).
     *
     * @param string $eventId
     * @return bool
     */
    public function isWebhookProcessed(string $eventId): bool
    {
        if (!$eventId) return false;
        return PaymentTransaction::whereJsonContains('metadata->processed_webhook_events', $eventId)->exists();
    }

    /**
     * Mark a webhook event as processed (idempotency).
     *
     * @param string $eventId
     * @return void
     */
    public function markWebhookProcessed(string $eventId): void
    {
        if (!$eventId) return;
        // Find all transactions related to this event (if any)
        $transactions = PaymentTransaction::where(function($q) use ($eventId) {
            $q->whereJsonDoesntContain('metadata->processed_webhook_events', $eventId)
              ->orWhereNull('metadata');
        })->get();
        foreach ($transactions as $transaction) {
            $meta = $transaction->metadata ?? [];
            $events = $meta['processed_webhook_events'] ?? [];
            if (!in_array($eventId, $events, true)) {
                $events[] = $eventId;
                $meta['processed_webhook_events'] = $events;
                $transaction->metadata = $meta;
                $transaction->save();
            }
        }
    }

    /**
     * Mark a payment as successful (atomic, idempotent).
     *
     * @param string $gatewayPaymentId
     * @param array $payload
     * @return void
     */
    public function markPaymentSuccess(string $gatewayPaymentId, array $payload = []): void
    {
        if (!$gatewayPaymentId) return;
        \DB::transaction(function () use ($gatewayPaymentId, $payload) {
            $transaction = PaymentTransaction::where('gateway_payment_id', $gatewayPaymentId)->lockForUpdate()->first();
            if (!$transaction) return;
            if ($transaction->status === PaymentTransaction::STATUS_SUCCESS) return;
            $meta = $transaction->metadata ?? [];
            $meta['last_success_payload'] = $payload;
            $transaction->metadata = $meta;
            $transaction->status = PaymentTransaction::STATUS_SUCCESS;
            $transaction->paid_at = now();
            $transaction->save();
        });
    }

    /**
     * Mark a payment as failed (atomic, idempotent).
     *
     * @param string $gatewayPaymentId
     * @param array $payload
     * @return void
     */
    public function markPaymentFailed(string $gatewayPaymentId, array $payload = []): void
    {
        if (!$gatewayPaymentId) return;
        \DB::transaction(function () use ($gatewayPaymentId, $payload) {
            $transaction = PaymentTransaction::where('gateway_payment_id', $gatewayPaymentId)->lockForUpdate()->first();
            if (!$transaction) return;
            if (in_array($transaction->status, [PaymentTransaction::STATUS_FAILED, PaymentTransaction::STATUS_SUCCESS], true)) return;
            $meta = $transaction->metadata ?? [];
            $meta['last_failed_payload'] = $payload;
            $transaction->metadata = $meta;
            $transaction->status = PaymentTransaction::STATUS_FAILED;
            $transaction->failure_reason = $payload['error_reason'] ?? $payload['reason'] ?? null;
            $transaction->failure_code = $payload['error_code'] ?? null;
            $transaction->save();
        });
    }
}
