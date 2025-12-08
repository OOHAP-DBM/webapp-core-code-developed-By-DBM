<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\RazorpayLog;
use Exception;

class RazorpayService
{
    protected string $keyId;
    protected string $keySecret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->keyId = config('services.razorpay.key_id');
        $this->keySecret = config('services.razorpay.key_secret');
        $this->baseUrl = config('services.razorpay.base_url', 'https://api.razorpay.com/v1');
    }

    /**
     * Create a Razorpay order with manual capture
     *
     * @param float $amount Amount in INR (will be converted to paise)
     * @param string $currency Currency code (default: INR)
     * @param string $receipt Receipt/reference ID
     * @param string $captureMethod Capture method (default: manual)
     * @return array Order data from Razorpay
     * @throws Exception
     */
    public function createOrder(
        float $amount,
        string $currency = 'INR',
        string $receipt = '',
        string $captureMethod = 'manual'
    ): array {
        $amountInPaise = (int) ($amount * 100);

        $payload = [
            'amount' => $amountInPaise,
            'currency' => $currency,
            'receipt' => $receipt,
            'payment' => [
                'capture' => $captureMethod,
                'capture_options' => [
                    'manual_expiry_period' => 30, // 30 minutes
                    'refund_speed' => 'optimum'
                ]
            ]
        ];

        // Log the request
        $this->logRequest('create_order', $payload);

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post("{$this->baseUrl}/orders", $payload);

            $responseData = $response->json();

            // Log the response
            $this->logResponse('create_order', $payload, $responseData, $response->status());

            if ($response->successful()) {
                return $responseData;
            }

            // Handle error response
            $errorMessage = $responseData['error']['description'] ?? 'Unknown error occurred';
            throw new Exception("Razorpay order creation failed: {$errorMessage}");

        } catch (Exception $e) {
            // Log the exception
            $this->logResponse('create_order', $payload, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);

            Log::error('Razorpay order creation failed', [
                'payload' => $payload,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Capture a payment manually
     *
     * @param string $paymentId Razorpay payment ID
     * @param float $amount Amount to capture in INR
     * @param string $currency Currency code
     * @return array Capture response
     * @throws Exception
     */
    public function capturePayment(string $paymentId, float $amount, string $currency = 'INR'): array
    {
        $amountInPaise = (int) ($amount * 100);

        $payload = [
            'amount' => $amountInPaise,
            'currency' => $currency
        ];

        $this->logRequest('capture_payment', $payload, ['payment_id' => $paymentId]);

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post("{$this->baseUrl}/payments/{$paymentId}/capture", $payload);

            $responseData = $response->json();

            $this->logResponse('capture_payment', $payload, $responseData, $response->status(), ['payment_id' => $paymentId]);

            if ($response->successful()) {
                return $responseData;
            }

            $errorMessage = $responseData['error']['description'] ?? 'Unknown error occurred';
            throw new Exception("Razorpay payment capture failed: {$errorMessage}");

        } catch (Exception $e) {
            $this->logResponse('capture_payment', $payload, [
                'error' => $e->getMessage()
            ], 500, ['payment_id' => $paymentId]);

            Log::error('Razorpay payment capture failed', [
                'payment_id' => $paymentId,
                'payload' => $payload,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Void/Cancel an authorized payment (before capture)
     * Note: Razorpay doesn't have explicit void API, authorized payments auto-expire
     *
     * @param string $paymentId Razorpay payment ID
     * @return array Payment details
     * @throws \Exception
     */
    public function voidPayment(string $paymentId): array
    {
        try {
            // Fetch payment details
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->get("{$this->baseUrl}/payments/{$paymentId}");

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch payment details: " . $response->body());
            }

            $payment = $response->json();

            // Log the void request
            $this->logRequest('void_payment', [
                'payment_id' => $paymentId,
                'status' => $payment['status'] ?? null,
            ], $payment, true);

            // Verify payment is in authorized state
            if (!isset($payment['status']) || $payment['status'] !== 'authorized') {
                throw new \Exception("Payment cannot be voided. Current status: " . ($payment['status'] ?? 'unknown'));
            }

            return $payment;
        } catch (\Exception $e) {
            $this->logRequest('void_payment', [
                'payment_id' => $paymentId,
            ], [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ], false);

            throw $e;
        }
    }

    /**
     * Verify payment signature
     *
     * @param string $orderId Razorpay order ID
     * @param string $paymentId Razorpay payment ID
     * @param string $signature Razorpay signature
     * @return bool
     */
    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Log Razorpay request
     *
     * @param string $action
     * @param array $requestPayload
     * @param array $metadata
     * @return void
     */
    protected function logRequest(string $action, array $requestPayload, array $metadata = []): void
    {
        try {
            RazorpayLog::create([
                'action' => $action,
                'request_payload' => $requestPayload,
                'response_payload' => null,
                'status_code' => null,
                'metadata' => $metadata
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log Razorpay request', [
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log Razorpay response
     *
     * @param string $action
     * @param array $requestPayload
     * @param array $responsePayload
     * @param int $statusCode
     * @param array $metadata
     * @return void
     */
    protected function logResponse(
        string $action,
        array $requestPayload,
        array $responsePayload,
        int $statusCode,
        array $metadata = []
    ): void {
        try {
            RazorpayLog::where('action', $action)
                ->whereNull('response_payload')
                ->orderBy('id', 'desc')
                ->first()
                ?->update([
                    'response_payload' => $responsePayload,
                    'status_code' => $statusCode
                ]);
        } catch (Exception $e) {
            Log::error('Failed to log Razorpay response', [
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create a Razorpay Route sub-account for vendor
     *
     * @param array $data Sub-account data
     * @return array Sub-account response from Razorpay
     * @throws Exception
     */
    public function createSubAccount(array $data): array
    {
        try {
            $this->logRequest('create_subaccount', $data);

            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post("{$this->baseUrl}/accounts", $data);

            $responseData = $response->json();
            $this->logResponse('create_subaccount', $data, $responseData, $response->status());

            if ($response->failed()) {
                throw new Exception(
                    $responseData['error']['description'] ?? 'Failed to create Razorpay sub-account'
                );
            }

            Log::info('Razorpay sub-account created', [
                'account_id' => $responseData['id'] ?? null,
            ]);

            return $responseData;

        } catch (Exception $e) {
            Log::error('Razorpay sub-account creation failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Fetch sub-account details
     *
     * @param string $accountId Sub-account ID
     * @return array
     * @throws Exception
     */
    public function fetchSubAccount(string $accountId): array
    {
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->get("{$this->baseUrl}/accounts/{$accountId}");

            $responseData = $response->json();

            if ($response->failed()) {
                throw new Exception(
                    $responseData['error']['description'] ?? 'Failed to fetch sub-account'
                );
            }

            return $responseData;

        } catch (Exception $e) {
            Log::error('Razorpay sub-account fetch failed', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

