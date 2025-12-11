<?php

namespace App\Contracts;

/**
 * PaymentGatewayInterface
 * PROMPT 69: Payment Gateway Integration Wrapper
 * 
 * Contract for payment gateway implementations (Razorpay, Stripe, etc.)
 */
interface PaymentGatewayInterface
{
    /**
     * Create a payment order
     *
     * @param float $amount Amount in base currency
     * @param string $currency Currency code (INR, USD, etc.)
     * @param array $options Additional options (receipt, customer_id, metadata, etc.)
     * @return array Order data with order_id, amount, currency, status
     */
    public function createOrder(float $amount, string $currency, array $options = []): array;

    /**
     * Capture an authorized payment
     *
     * @param string $paymentId Payment identifier
     * @param float $amount Amount to capture
     * @param array $options Additional capture options
     * @return array Captured payment details
     */
    public function capturePayment(string $paymentId, float $amount, array $options = []): array;

    /**
     * Create a refund for a payment
     *
     * @param string $paymentId Payment identifier
     * @param float $amount Amount to refund (partial or full)
     * @param array $options Refund options (reason, notes, speed, etc.)
     * @return array Refund details
     */
    public function createRefund(string $paymentId, float $amount, array $options = []): array;

    /**
     * Retrieve payment details
     *
     * @param string $paymentId Payment identifier
     * @return array Payment information
     */
    public function getPayment(string $paymentId): array;

    /**
     * Retrieve order details
     *
     * @param string $orderId Order identifier
     * @return array Order information
     */
    public function getOrder(string $orderId): array;

    /**
     * Verify webhook signature
     *
     * @param string $payload Raw webhook payload
     * @param string $signature Webhook signature header
     * @param string|null $secret Webhook secret (if different from default)
     * @return bool True if signature is valid
     */
    public function verifyWebhookSignature(string $payload, string $signature, ?string $secret = null): bool;

    /**
     * Parse webhook payload
     *
     * @param string $payload Raw webhook payload
     * @return array Parsed webhook data with event type and data
     */
    public function parseWebhook(string $payload): array;

    /**
     * Get gateway name
     *
     * @return string Gateway identifier (razorpay, stripe, etc.)
     */
    public function getGatewayName(): string;

    /**
     * Check if payment is captured
     *
     * @param string $paymentId Payment identifier
     * @return bool True if payment is captured
     */
    public function isPaymentCaptured(string $paymentId): bool;

    /**
     * Check if payment is authorized but not captured
     *
     * @param string $paymentId Payment identifier
     * @return bool True if payment is authorized only
     */
    public function isPaymentAuthorized(string $paymentId): bool;

    /**
     * Void/cancel an authorized payment (if supported)
     *
     * @param string $paymentId Payment identifier
     * @return array Void/cancel response
     */
    public function voidPayment(string $paymentId): array;

    /**
     * Create a customer profile (if supported)
     *
     * @param array $customerData Customer information
     * @return array Customer profile data
     */
    public function createCustomer(array $customerData): array;

    /**
     * Save payment method for future use (if supported)
     *
     * @param string $customerId Customer identifier
     * @param array $paymentMethodData Payment method details
     * @return array Saved payment method data
     */
    public function savePaymentMethod(string $customerId, array $paymentMethodData): array;

    /**
     * Process a payment with saved payment method (if supported)
     *
     * @param string $paymentMethodId Saved payment method identifier
     * @param float $amount Amount to charge
     * @param array $options Additional options
     * @return array Payment result
     */
    public function chargePaymentMethod(string $paymentMethodId, float $amount, array $options = []): array;

    /**
     * Get supported features for this gateway
     *
     * @return array List of supported features (manual_capture, webhooks, partial_refund, etc.)
     */
    public function getSupportedFeatures(): array;

    /**
     * Convert amount to gateway's smallest currency unit (paise, cents, etc.)
     *
     * @param float $amount Amount in base currency
     * @param string $currency Currency code
     * @return int Amount in smallest unit
     */
    public function convertToSmallestUnit(float $amount, string $currency): int;

    /**
     * Convert amount from gateway's smallest unit to base currency
     *
     * @param int $amount Amount in smallest unit
     * @param string $currency Currency code
     * @return float Amount in base currency
     */
    public function convertFromSmallestUnit(int $amount, string $currency): float;
}
