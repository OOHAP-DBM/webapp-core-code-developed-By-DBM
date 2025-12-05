<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookingPayment extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'booking_payments';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'booking_id',
        'gross_amount',
        'admin_commission_amount',
        'vendor_payout_amount',
        'pg_fee_amount',
        'razorpay_payment_id',
        'razorpay_order_id',
        'razorpay_transfer_ids',
        'vendor_payout_status',
        'payout_mode',
        'payout_reference',
        'paid_at',
        'status',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'gross_amount' => 'decimal:2',
        'admin_commission_amount' => 'decimal:2',
        'vendor_payout_amount' => 'decimal:2',
        'pg_fee_amount' => 'decimal:2',
        'razorpay_transfer_ids' => 'array',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to Booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Relationship: Has One Commission Log
     */
    public function commissionLog(): HasOne
    {
        return $this->hasOne(CommissionLog::class);
    }

    /**
     * Scopes
     */
    public function scopePendingPayout($query)
    {
        return $query->where('vendor_payout_status', 'pending');
    }

    public function scopeCompletedPayout($query)
    {
        return $query->where('vendor_payout_status', 'completed');
    }

    public function scopeOnHold($query)
    {
        return $query->where('vendor_payout_status', 'on_hold');
    }

    public function scopeCaptured($query)
    {
        return $query->where('status', 'captured');
    }

    /**
     * Check if payout is pending
     */
    public function isPayoutPending(): bool
    {
        return $this->vendor_payout_status === 'pending';
    }

    /**
     * Check if payout is completed
     */
    public function isPayoutCompleted(): bool
    {
        return $this->vendor_payout_status === 'completed';
    }

    /**
     * Check if payout is on hold
     */
    public function isPayoutOnHold(): bool
    {
        return $this->vendor_payout_status === 'on_hold';
    }

    /**
     * Get net platform revenue (commission - PG fees)
     */
    public function getNetPlatformRevenueAttribute(): float
    {
        return (float) ($this->admin_commission_amount - $this->pg_fee_amount);
    }

    /**
     * Get commission percentage
     */
    public function getCommissionPercentageAttribute(): float
    {
        if ($this->gross_amount <= 0) {
            return 0.0;
        }

        return round(($this->admin_commission_amount / $this->gross_amount) * 100, 2);
    }

    /**
     * Get payment summary
     */
    public function getPaymentSummaryAttribute(): array
    {
        return [
            'gross_amount' => (float) $this->gross_amount,
            'admin_commission' => (float) $this->admin_commission_amount,
            'vendor_payout' => (float) $this->vendor_payout_amount,
            'pg_fee' => (float) $this->pg_fee_amount,
            'net_platform_revenue' => $this->net_platform_revenue,
            'commission_percentage' => $this->commission_percentage,
        ];
    }

    /**
     * Mark payout as completed
     */
    public function markPayoutCompleted(string $payoutMode, string $payoutReference = null, array $metadata = []): void
    {
        $this->update([
            'vendor_payout_status' => 'completed',
            'payout_mode' => $payoutMode,
            'payout_reference' => $payoutReference,
            'paid_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], $metadata),
        ]);
    }

    /**
     * Mark payout as on hold
     */
    public function markPayoutOnHold(string $reason = null): void
    {
        $this->update([
            'vendor_payout_status' => 'on_hold',
            'metadata' => array_merge($this->metadata ?? [], [
                'on_hold_reason' => $reason,
                'on_hold_at' => now()->toIso8601String(),
            ]),
        ]);
    }
}
