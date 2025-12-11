<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Services\RazorpayService;
use Illuminate\Support\Facades\Log;
use Exception;

class RazorpayGateway implements PaymentGatewayInterface
{
    protected RazorpayService $razorpayService;

    public function __construct()
    {
        $this->razorpayService = new RazorpayService();
    }

    /**
     * Create a payment order
     *
     * @param float $amount Amount in base currency (INR)
     * @param string $currency Currency code
     * @param array $options Additional options
     * @return array Order data
     * @throws Exception
     */
    public function createOrder(float $amount, string $currency, array $options = []): array
    {
        $receipt = $options['receipt'] ?? 'order_' . time();
        $manualCapture = $options['manual_capture'] ?? true;
        $captureMethod = $manualCapture ? 'manual' : 'automatic';

        $orderData = $this->razorpayService->createOrder(
            $amount,
            $currency,
            $receipt,
            $captureMethod
        );

        return [
            'id' => $orderData['id'],
            'amount' => $orderData['amount'],
            'currency' => $orderData['currency'],
            'status' => $orderData['status'],
            'receipt' => $orderData['receipt'] ?? $receipt,
            'created_at' => $orderData['created_at'] ?? now()->timestamp,
        ];
    }

    /**
     * Capture an authorized payment
     *
     * @param string $paymentId Gateway payment ID
     * @param float $amount Amount to capture
     * @param array $options Additional options
     * @return array Capture data
     * @throws Exception
     */
    public function capturePayment(string $paymentId, float $amount, array $options = []): array
    {
        $currency = $options['currency'] ?? 'INR';

        $captureData = $this->razorpayService->capturePayment($paymentId, $amount, $currency);

        return [
            'id' => $captureData['id'],
            'amount' => $captureData['amount'],
            'currency' => $captureData['currency'],
            'status' => $captureData['status'],
            'captured' => $captureData['captured'] ?? true,
            'method' => $captureData['method'] ?? null,
            'fee' => isset($captureData['fee']) ? $captureData['fee'] / 100 : null,
            'tax' => isset($captureData['tax']) ? $captureData['tax'] / 100 : null,
            'captured_at' => $captureData['captured_at'] ?? now()->timestamp,
        ];
    }

    /**
     * Void an authorized payment
     *
     * @param string $paymentId Gateway payment ID
     * @param array $options Additional options
     * @return array Void data
     * @throws Exception
     */
    public function voidPayment(string $paymentId, array $options = []): array
    {
        // Razorpay doesn't have explicit void API - authorized payments auto-expire
        $paymentData = $this->razorpayService->voidPayment($paymentId);

        return [
            'id' => $paymentData['id'],
            'status' => $paymentData['status'],
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'],
        ];
    }

    /**
     * Create a refund
     *
     * @param string $paymentId Gateway payment ID
     * @param float $amount Amount to refund
     * @param array $options Additional options
     * @return array Refund data
     * @throws Exception
     */
    public function createRefund(string $paymentId, float $amount, array $options = []): array
    {
        $notes = $options['reason'] ?? '';
        $instantRefund = $options['speed'] !== 'normal';

        $refundData = $this->razorpayService->createRefund($paymentId, $amount, $notes, $instantRefund);

        return [
            'id' => $refundData['id'],
            'payment_id' => $refundData['payment_id'],
            'amount' => $refundData['amount'],
            'currency' => $refundData['currency'],
            'status' => $refundData['status'] ?? 'processed',
            'speed' => $refundData['speed_requested'] ?? 'optimum',
            'created_at' => $refundData['created_at'] ?? now()->timestamp,
        ];
    }

    /**
     * Get payment details
     *
     * @param string $paymentId Gateway payment ID
     * @return array Payment data
     * @throws Exception
     */
    public function getPayment(string $paymentId): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withBasicAuth(
                config('services.razorpay.key_id'),
                config('services.razorpay.key_secret')
            )->get(config('services.razorpay.base_url') . "/payments/{$paymentId}");

            if ($response->failed()) {
                throw new Exception("Failed to fetch payment: " . $response->body());
            }

            $payment = $response->json();

            return [
                'id' => $payment['id'],
                'order_id' => $payment['order_id'] ?? null,
                'amount' => $payment['amount'],
                'currency' => $payment['currency'],
                'status' => $payment['status'],
                'method' => $payment['method'] ?? null,
                'captured' => $payment['captured'] ?? false,
                'fee' => isset($payment['fee']) ? $payment['fee'] / 100 : null,
                'tax' => isset($payment['tax']) ? $payment['tax'] / 100 : null,
                'created_at' => $payment['created_at'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Razorpay getPayment failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get order details
     *
     * @param string $orderId Gateway order ID
     * @return array Order data
     * @throws Exception
     */
    public function getOrder(string $orderId): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withBasicAuth(
                config('services.razorpay.key_id'),
                config('services.razorpay.key_secret')
            )->get(config('services.razorpay.base_url') . "/orders/{$orderId}");

            if ($response->failed()) {
                throw new Exception("Failed to fetch order: " . $response->body());
            }

            $order = $response->json();

            return [
                'id' => $order['id'],
                'amount' => $order['amount'],
                'currency' => $order['currency'],
                'status' => $order['status'],
                'receipt' => $order['receipt'] ?? null,
                'created_at' => $order['created_at'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Razorpay getOrder failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Verify webhook signature
     *
     * @param string $payload Raw webhook payload
     * @param string $signature Signature from webhook header
     * @param string|null $secret Webhook secret (optional)
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature, ?string $secret = null): bool
    {
        $webhookSecret = $secret ?? config('services.razorpay.webhook_secret');
        
        if (empty($webhookSecret)) {
            Log::error('Razorpay webhook secret not configured');
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Parse webhook payload
     *
     * @param string $payload Raw webhook payload
     * @return array Parsed webhook data
     * @throws Exception
     */
    public function parseWebhook(string $payload): array
    {
        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid webhook payload JSON');
        }

        $event = $data['event'] ?? null;
        $entity = $data['payload']['payment']['entity'] ?? $data['payload']['refund']['entity'] ?? [];

        return [
            'event' => $event,
            'payment_id' => $entity['id'] ?? null,
            'order_id' => $entity['order_id'] ?? null,
            'amount' => isset($entity['amount']) ? $entity['amount'] / 100 : null,
            'currency' => $entity['currency'] ?? 'INR',
            'status' => $entity['status'] ?? null,
            'method' => $entity['method'] ?? null,
            'captured' => $entity['captured'] ?? false,
            'fee' => isset($entity['fee']) ? $entity['fee'] / 100 : null,
            'tax' => isset($entity['tax']) ? $entity['tax'] / 100 : null,
            'error_code' => $entity['error_code'] ?? null,
            'error_description' => $entity['error_description'] ?? null,
            'refund_id' => isset($data['payload']['refund']) ? $entity['id'] : null,
            'raw_data' => $entity,
        ];
    }

    /**
     * Create a customer
     *
     * @param array $data Customer data
     * @return array Customer data
     * @throws Exception
     */
    public function createCustomer(array $data): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withBasicAuth(
                config('services.razorpay.key_id'),
                config('services.razorpay.key_secret')
            )->post(config('services.razorpay.base_url') . '/customers', [
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'contact' => $data['phone'] ?? '',
                'notes' => $data['notes'] ?? [],
            ]);

            if ($response->failed()) {
                throw new Exception("Failed to create customer: " . $response->body());
            }

            $customer = $response->json();

            return [
                'id' => $customer['id'],
                'name' => $customer['name'],
                'email' => $customer['email'],
                'phone' => $customer['contact'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Razorpay createCustomer failed', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Save payment method for customer
     *
     * @param string $customerId Customer ID
     * @param array $data Payment method data
     * @return array Payment method data
     * @throws Exception
     */
    public function savePaymentMethod(string $customerId, array $data): array
    {
        // Razorpay doesn't have direct payment method saving API
        // This would typically be done through tokens or recurring payments
        throw new Exception('Payment method saving not implemented for Razorpay');
    }

    /**
     * Charge a saved payment method
     *
     * @param string $paymentMethodId Payment method ID
     * @param float $amount Amount to charge
     * @param array $options Additional options
     * @return array Charge data
     * @throws Exception
     */
    public function chargePaymentMethod(string $paymentMethodId, float $amount, array $options = []): array
    {
        // This would use Razorpay's recurring/subscription API
        throw new Exception('Payment method charging not implemented for Razorpay');
    }

    /**
     * Convert amount to smallest currency unit (paise)
     *
     * @param float $amount Amount in base currency
     * @param string $currency Currency code
     * @return int Amount in smallest unit
     */
    public function convertToSmallestUnit(float $amount, string $currency): int
    {
        // INR: 1 INR = 100 paise
        return (int) ($amount * 100);
    }

    /**
     * Convert amount from smallest currency unit to base
     *
     * @param int $amount Amount in smallest unit
     * @param string $currency Currency code
     * @return float Amount in base currency
     */
    public function convertFromSmallestUnit(int $amount, string $currency): float
    {
        // INR: 100 paise = 1 INR
        return $amount / 100;
    }

    /**
     * Check if payment is captured
     *
     * @param string $paymentId Gateway payment ID
     * @return bool
     */
    public function isPaymentCaptured(string $paymentId): bool
    {
        try {
            $payment = $this->getPayment($paymentId);
            return $payment['captured'] === true && $payment['status'] === 'captured';
        } catch (Exception $e) {
            Log::error('Failed to check if payment is captured', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if payment is authorized
     *
     * @param string $paymentId Gateway payment ID
     * @return bool
     */
    public function isPaymentAuthorized(string $paymentId): bool
    {
        try {
            $payment = $this->getPayment($paymentId);
            return $payment['status'] === 'authorized';
        } catch (Exception $e) {
            Log::error('Failed to check if payment is authorized', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get supported features
     *
     * @return array List of supported features
     */
    public function getSupportedFeatures(): array
    {
        return [
            'manual_capture' => true,
            'auto_capture' => true,
            'partial_capture' => true,
            'void_payment' => false, // Auto-expires, no explicit void
            'full_refund' => true,
            'partial_refund' => true,
            'webhooks' => true,
            'payment_methods' => ['card', 'upi', 'netbanking', 'wallet', 'emi'],
            'currencies' => ['INR', 'USD', 'EUR', 'GBP'],
            'recurring_payments' => true,
            'save_payment_method' => false, // Not directly supported
        ];
    }

    /**
     * Get gateway name
     *
     * @return string
     */
    public function getGatewayName(): string
    {
        return 'razorpay';
    }
}
