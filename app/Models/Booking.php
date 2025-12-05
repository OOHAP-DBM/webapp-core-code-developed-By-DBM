<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'quotation_id',
        'customer_id',
        'vendor_id',
        'hoarding_id',
        'start_date',
        'end_date',
        'duration_type',
        'duration_days',
        'total_amount',
        'status',
        'payment_status',
        'hold_expiry_at',
        'razorpay_order_id',
        'razorpay_payment_id',
        'payment_error_code',
        'payment_error_description',
        'payment_authorized_at',
        'payment_captured_at',
        'payment_failed_at',
        'capture_attempted_at',
        'pod_approved_at',
        'booking_snapshot',
        'customer_notes',
        'cancellation_reason',
        'confirmed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_amount' => 'decimal:2',
        'hold_expiry_at' => 'datetime',
        'payment_authorized_at' => 'datetime',
        'payment_captured_at' => 'datetime',
        'payment_failed_at' => 'datetime',
        'capture_attempted_at' => 'datetime',
        'pod_approved_at' => 'datetime',
        'booking_snapshot' => 'array',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING_PAYMENT_HOLD = 'pending_payment_hold';
    const STATUS_PAYMENT_HOLD = 'payment_hold';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    // Duration type constants
    const DURATION_DAYS = 'days';
    const DURATION_WEEKS = 'weeks';
    const DURATION_MONTHS = 'months';

    /**
     * Relationships
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class)->orderBy('created_at', 'desc');
    }

    public function priceSnapshot(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BookingPriceSnapshot::class);
    }

    public function bookingPayment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BookingPayment::class);
    }

    public function commissionLog(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CommissionLog::class);
    }

    /**
     * Scopes
     */
    public function scopePendingPaymentHold($query)
    {
        return $query->where('status', self::STATUS_PENDING_PAYMENT_HOLD);
    }

    public function scopePaymentHold($query)
    {
        return $query->where('status', self::STATUS_PAYMENT_HOLD);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', self::STATUS_REFUNDED);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PAYMENT_HOLD,
            self::STATUS_CONFIRMED
        ]);
    }

    public function scopeExpiredHolds($query)
    {
        return $query->where('status', self::STATUS_PAYMENT_HOLD)
            ->where('hold_expiry_at', '<', now());
    }

    /**
     * Status checks
     */
    public function isPendingPaymentHold(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT_HOLD;
    }

    public function isPaymentHold(): bool
    {
        return $this->status === self::STATUS_PAYMENT_HOLD;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function isHoldExpired(): bool
    {
        return $this->isPaymentHold() 
            && $this->hold_expiry_at 
            && $this->hold_expiry_at->isPast();
    }

    /**
     * Action checks
     */
    public function canConfirm(): bool
    {
        return $this->isPaymentHold() && !$this->isHoldExpired();
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_PAYMENT_HOLD,
            self::STATUS_PAYMENT_HOLD,
            self::STATUS_CONFIRMED
        ]);
    }

    /**
     * Helpers
     */
    public function getSnapshotValue(string $key, $default = null)
    {
        return data_get($this->booking_snapshot, $key, $default);
    }

    public function getFormattedTotalAmount(): string
    {
        return '₹' . number_format($this->total_amount, 2);
    }

    public function getDurationInDays(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    public function getHoldTimeRemaining(): ?int
    {
        if (!$this->hold_expiry_at) {
            return null;
        }

        $seconds = now()->diffInSeconds($this->hold_expiry_at, false);
        return max(0, $seconds);
    }

    public function getHoldMinutesRemaining(): ?int
    {
        $seconds = $this->getHoldTimeRemaining();
        return $seconds !== null ? (int) ceil($seconds / 60) : null;
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING_PAYMENT_HOLD => 'bg-warning text-dark',
            self::STATUS_PAYMENT_HOLD => 'bg-info',
            self::STATUS_CONFIRMED => 'bg-success',
            self::STATUS_CANCELLED => 'bg-danger',
            self::STATUS_REFUNDED => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING_PAYMENT_HOLD => 'Pending Payment Hold',
            self::STATUS_PAYMENT_HOLD => 'Payment Hold',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REFUNDED => 'Refunded',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Get booking proofs (POD)
     */
    public function bookingProofs(): HasMany
    {
        return $this->hasMany(BookingProof::class);
    }

    /**
     * Get approved booking proof
     */
    public function approvedProof()
    {
        return $this->hasOne(BookingProof::class)->where('status', 'approved');
    }
}
