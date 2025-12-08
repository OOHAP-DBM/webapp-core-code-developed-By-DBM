<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'commission_logs';

    /**
     * Disable updated_at timestamp (immutable log)
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'booking_id',
        'gross_amount',
        'admin_commission',
        'vendor_payout',
        'pg_fee',
        'tax',
        'commission_rate',
        'commission_type',
        'booking_payment_id',
        'calculation_snapshot',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'gross_amount' => 'decimal:2',
        'admin_commission' => 'decimal:2',
        'vendor_payout' => 'decimal:2',
        'pg_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'calculation_snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to Booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Relationship: Belongs to BookingPayment
     */
    public function bookingPayment(): BelongsTo
    {
        return $this->belongsTo(BookingPayment::class);
    }

    /**
     * Get total deductions
     */
    public function getTotalDeductionsAttribute(): float
    {
        return (float) ($this->admin_commission + $this->pg_fee + $this->tax);
    }

    /**
     * Get net vendor payout (should match vendor_payout)
     */
    public function getNetVendorPayoutAttribute(): float
    {
        return (float) ($this->gross_amount - $this->total_deductions);
    }

    /**
     * Verify calculation integrity
     */
    public function verifyCalculation(): bool
    {
        $calculatedPayout = $this->gross_amount - $this->admin_commission - $this->pg_fee - $this->tax;
        return abs($calculatedPayout - $this->vendor_payout) < 0.01; // Allow 1 paisa tolerance for rounding
    }

    /**
     * Get commission summary
     */
    public function getSummaryAttribute(): array
    {
        return [
            'gross_amount' => (float) $this->gross_amount,
            'admin_commission' => (float) $this->admin_commission,
            'pg_fee' => (float) $this->pg_fee,
            'tax' => (float) $this->tax,
            'total_deductions' => $this->total_deductions,
            'vendor_payout' => (float) $this->vendor_payout,
            'commission_rate' => (float) $this->commission_rate,
            'commission_type' => $this->commission_type,
            'calculation_valid' => $this->verifyCalculation(),
        ];
    }
}
