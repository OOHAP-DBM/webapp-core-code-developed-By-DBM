<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class VendorLedger extends Model
{
    protected $fillable = [
        'vendor_id',
        'transaction_reference',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'related_type',
        'related_id',
        'booking_payment_id',
        'settlement_batch_id',
        'status',
        'kyc_verified_at_time',
        'kyc_status_snapshot',
        'payout_status_snapshot',
        'is_on_hold',
        'hold_released_at',
        'hold_released_by',
        'description',
        'notes',
        'metadata',
        'created_by',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'kyc_verified_at_time' => 'boolean',
        'is_on_hold' => 'boolean',
        'hold_released_at' => 'datetime',
        'transaction_date' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Boot method to auto-generate transaction reference
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ledger) {
            if (!$ledger->transaction_reference) {
                $ledger->transaction_reference = 'TXN-' . strtoupper(Str::random(12));
            }
            
            if (!$ledger->transaction_date) {
                $ledger->transaction_date = now();
            }
        });
    }

    /**
     * Relationships
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function bookingPayment(): BelongsTo
    {
        return $this->belongsTo(BookingPayment::class);
    }

    public function settlementBatch(): BelongsTo
    {
        return $this->belongsTo(SettlementBatch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function holdReleaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hold_released_by');
    }

    /**
     * Scopes
     */
    public function scopeByVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeCredits($query)
    {
        return $query->where('amount', '>', 0);
    }

    public function scopeDebits($query)
    {
        return $query->where('amount', '<', 0);
    }

    public function scopeOnHold($query)
    {
        return $query->where('is_on_hold', true);
    }

    public function scopeNotOnHold($query)
    {
        return $query->where('is_on_hold', false);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Status checks
     */
    public function isCredit(): bool
    {
        return $this->amount > 0;
    }

    public function isDebit(): bool
    {
        return $this->amount < 0;
    }

    public function isOnHold(): bool
    {
        return $this->is_on_hold;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Release hold
     */
    public function releaseHold(int $releasedBy, ?string $notes = null): bool
    {
        if (!$this->is_on_hold) {
            return false;
        }

        return $this->update([
            'is_on_hold' => false,
            'hold_released_at' => now(),
            'hold_released_by' => $releasedBy,
            'notes' => $notes ? ($this->notes . "\n[HOLD RELEASED] " . $notes) : $this->notes,
        ]);
    }

    /**
     * Computed attributes
     */
    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->amount >= 0 ? '+' : '';
        return $prefix . '₹' . number_format(abs($this->amount), 2);
    }

    public function getTransactionTypeColorAttribute(): string
    {
        return match ($this->transaction_type) {
            'booking_earning' => 'success',
            'hold_release' => 'info',
            'commission_deduction' => 'warning',
            'payout' => 'primary',
            'refund_debit' => 'danger',
            'penalty' => 'danger',
            'adjustment' => 'secondary',
            default => 'secondary',
        };
    }

    public function getTransactionTypeLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            'booking_earning' => 'Booking Earning',
            'commission_deduction' => 'Commission Deduction',
            'payout' => 'Payout',
            'refund_debit' => 'Refund',
            'penalty' => 'Penalty',
            'adjustment' => 'Adjustment',
            'hold_release' => 'Hold Release',
            default => ucfirst(str_replace('_', ' ', $this->transaction_type)),
        };
    }

    /**
     * Calculate vendor balance
     */
    public static function calculateVendorBalance(int $vendorId): array
    {
        $ledger = static::where('vendor_id', $vendorId);

        $totalEarnings = (float) $ledger->clone()->byType('booking_earning')->sum('amount');
        $totalCommissions = abs((float) $ledger->clone()->byType('commission_deduction')->sum('amount'));
        $totalPayouts = abs((float) $ledger->clone()->byType('payout')->sum('amount'));
        $totalRefunds = abs((float) $ledger->clone()->byType('refund_debit')->sum('amount'));
        $totalPenalties = abs((float) $ledger->clone()->byType('penalty')->sum('amount'));
        $totalAdjustments = (float) $ledger->clone()->byType('adjustment')->sum('amount');
        $onHoldAmount = (float) $ledger->clone()->onHold()->sum('amount');

        $currentBalance = $ledger->clone()->orderBy('id', 'desc')->value('balance_after') ?? 0;

        return [
            'current_balance' => $currentBalance,
            'available_balance' => $currentBalance - $onHoldAmount,
            'on_hold_amount' => $onHoldAmount,
            'total_earnings' => $totalEarnings,
            'total_commissions' => $totalCommissions,
            'total_payouts' => $totalPayouts,
            'total_refunds' => $totalRefunds,
            'total_penalties' => $totalPenalties,
            'total_adjustments' => $totalAdjustments,
        ];
    }

    /**
     * Create ledger entry with automatic balance calculation
     */
    public static function recordTransaction(array $data): self
    {
        // Get vendor's current balance
        $currentBalance = static::where('vendor_id', $data['vendor_id'])
            ->orderBy('id', 'desc')
            ->value('balance_after') ?? 0;

        $data['balance_before'] = $currentBalance;
        $data['balance_after'] = $currentBalance + $data['amount'];

        // Set KYC snapshot if not provided
        if (!isset($data['kyc_verified_at_time'])) {
            $vendor = User::find($data['vendor_id']);
            $vendorKyc = $vendor?->vendorKYC;
            
            $data['kyc_verified_at_time'] = $vendorKyc && $vendorKyc->isApproved() && $vendorKyc->payout_status === 'verified';
            $data['kyc_status_snapshot'] = $vendorKyc?->verification_status;
            $data['payout_status_snapshot'] = $vendorKyc?->payout_status;
        }

        return static::create($data);
    }
}
