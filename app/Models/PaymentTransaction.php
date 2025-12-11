<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * PaymentTransaction Model
 * PROMPT 69: Payment Gateway Integration Wrapper
 * 
 * Unified payment transaction tracking across all payment gateways
 */
class PaymentTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'gateway',
        'transaction_type',
        'gateway_order_id',
        'gateway_payment_id',
        'gateway_refund_id',
        'gateway_customer_id',
        'reference_type',
        'reference_id',
        'user_id',
        'amount',
        'currency',
        'amount_in_smallest_unit',
        'fee',
        'tax',
        'net_amount',
        'status',
        'payment_method',
        'payment_method_details',
        'manual_capture',
        'authorized_at',
        'captured_at',
        'capture_expires_at',
        'captured_amount',
        'refunded_amount',
        'refunded_at',
        'refund_status',
        'refund_reason',
        'webhook_received',
        'webhook_received_at',
        'webhook_event_type',
        'webhook_payload',
        'request_payload',
        'response_payload',
        'metadata',
        'error_code',
        'error_message',
        'error_description',
        'error_details',
        'customer_name',
        'customer_email',
        'customer_phone',
        'receipt_number',
        'notes',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_in_smallest_unit' => 'integer',
        'fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'captured_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'manual_capture' => 'boolean',
        'webhook_received' => 'boolean',
        'authorized_at' => 'datetime',
        'captured_at' => 'datetime',
        'capture_expires_at' => 'datetime',
        'refunded_at' => 'datetime',
        'webhook_received_at' => 'datetime',
        'webhook_payload' => 'array',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'metadata' => 'array',
        'error_details' => 'array',
    ];

    // Transaction Types
    const TYPE_ORDER = 'order';
    const TYPE_PAYMENT = 'payment';
    const TYPE_CAPTURE = 'capture';
    const TYPE_REFUND = 'refund';
    const TYPE_VOID = 'void';

    // Status
    const STATUS_CREATED = 'created';
    const STATUS_PENDING = 'pending';
    const STATUS_AUTHORIZED = 'authorized';
    const STATUS_CAPTURED = 'captured';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';
    const STATUS_VOIDED = 'voided';
    const STATUS_EXPIRED = 'expired';

    // Gateways
    const GATEWAY_RAZORPAY = 'razorpay';
    const GATEWAY_STRIPE = 'stripe';
    const GATEWAY_PAYPAL = 'paypal';

    /**
     * Get the associated reference (Booking, Invoice, etc.)
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who initiated the transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Filter by gateway
     */
    public function scopeForGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope: Filter by transaction type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Successful transactions
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', [self::STATUS_CAPTURED, self::STATUS_AUTHORIZED]);
    }

    /**
     * Scope: Failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope: Refunded transactions
     */
    public function scopeRefunded($query)
    {
        return $query->whereIn('status', [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    /**
     * Scope: Pending capture
     */
    public function scopePendingCapture($query)
    {
        return $query->where('status', self::STATUS_AUTHORIZED)
            ->where('manual_capture', true)
            ->whereNotNull('capture_expires_at')
            ->where('capture_expires_at', '>', now());
    }

    /**
     * Scope: Expired captures
     */
    public function scopeExpiredCaptures($query)
    {
        return $query->where('status', self::STATUS_AUTHORIZED)
            ->where('manual_capture', true)
            ->whereNotNull('capture_expires_at')
            ->where('capture_expires_at', '<=', now());
    }

    /**
     * Scope: For specific reference
     */
    public function scopeForReference($query, string $type, int $id)
    {
        return $query->where('reference_type', $type)
            ->where('reference_id', $id);
    }

    /**
     * Scope: Recent transactions
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if transaction is successful
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, [self::STATUS_CAPTURED, self::STATUS_AUTHORIZED]);
    }

    /**
     * Check if transaction is refundable
     */
    public function isRefundable(): bool
    {
        if ($this->status !== self::STATUS_CAPTURED) {
            return false;
        }

        $refundableAmount = $this->amount - $this->refunded_amount;
        return $refundableAmount > 0;
    }

    /**
     * Get refundable amount
     */
    public function getRefundableAmount(): float
    {
        if (!$this->isRefundable()) {
            return 0;
        }

        return $this->amount - $this->refunded_amount;
    }

    /**
     * Check if capture is expired
     */
    public function isCaptureExpired(): bool
    {
        if (!$this->manual_capture || $this->status !== self::STATUS_AUTHORIZED) {
            return false;
        }

        return $this->capture_expires_at && $this->capture_expires_at->isPast();
    }

    /**
     * Get time remaining for capture
     */
    public function getCaptureTimeRemaining(): ?int
    {
        if (!$this->capture_expires_at || $this->isCaptureExpired()) {
            return null;
        }

        return now()->diffInSeconds($this->capture_expires_at);
    }

    /**
     * Mark as authorized
     */
    public function markAuthorized(array $paymentData = []): void
    {
        $this->update([
            'status' => self::STATUS_AUTHORIZED,
            'authorized_at' => now(),
            'gateway_payment_id' => $paymentData['payment_id'] ?? $this->gateway_payment_id,
            'payment_method' => $paymentData['method'] ?? $this->payment_method,
            'payment_method_details' => $paymentData['method_details'] ?? $this->payment_method_details,
            'response_payload' => array_merge($this->response_payload ?? [], $paymentData),
        ]);
    }

    /**
     * Mark as captured
     */
    public function markCaptured(float $amount = null, array $captureData = []): void
    {
        $capturedAmount = $amount ?? $this->amount;

        $this->update([
            'status' => self::STATUS_CAPTURED,
            'captured_at' => now(),
            'captured_amount' => $capturedAmount,
            'fee' => $captureData['fee'] ?? $this->fee,
            'tax' => $captureData['tax'] ?? $this->tax,
            'net_amount' => $capturedAmount - ($captureData['fee'] ?? 0) - ($captureData['tax'] ?? 0),
            'response_payload' => array_merge($this->response_payload ?? [], $captureData),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markFailed(string $errorCode = null, string $errorMessage = null, array $errorDetails = []): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'error_details' => $errorDetails,
        ]);
    }

    /**
     * Add refund amount
     */
    public function addRefund(float $amount, string $refundId, array $refundData = []): void
    {
        $newRefundedAmount = $this->refunded_amount + $amount;
        $isFullRefund = $newRefundedAmount >= $this->amount;

        $this->update([
            'refunded_amount' => $newRefundedAmount,
            'refunded_at' => now(),
            'refund_status' => $refundData['status'] ?? 'processed',
            'refund_reason' => $refundData['reason'] ?? $this->refund_reason,
            'status' => $isFullRefund ? self::STATUS_REFUNDED : self::STATUS_PARTIALLY_REFUNDED,
            'gateway_refund_id' => $refundId,
            'response_payload' => array_merge($this->response_payload ?? [], $refundData),
        ]);
    }

    /**
     * Record webhook receipt
     */
    public function recordWebhook(string $eventType, array $payload): void
    {
        $this->update([
            'webhook_received' => true,
            'webhook_received_at' => now(),
            'webhook_event_type' => $eventType,
            'webhook_payload' => $payload,
        ]);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmount(): string
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    /**
     * Get status badge color
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_CAPTURED => 'success',
            self::STATUS_AUTHORIZED => 'info',
            self::STATUS_PENDING, self::STATUS_CREATED => 'warning',
            self::STATUS_FAILED, self::STATUS_EXPIRED => 'danger',
            self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED => 'secondary',
            self::STATUS_VOIDED => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Get gateway display name
     */
    public function getGatewayDisplayName(): string
    {
        return match ($this->gateway) {
            self::GATEWAY_RAZORPAY => 'Razorpay',
            self::GATEWAY_STRIPE => 'Stripe',
            self::GATEWAY_PAYPAL => 'PayPal',
            default => ucfirst($this->gateway),
        };
    }
}
