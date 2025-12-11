<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * QuotationMilestone Model
 * 
 * PROMPT 70: Vendor-Controlled Milestone Payment Logic
 * 
 * Milestones are created ONLY by vendors during quotation generation.
 * Enables split payments for quotation-based bookings.
 */
class QuotationMilestone extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quotation_id',
        'title',
        'description',
        'sequence_no',
        'amount_type',
        'amount',
        'calculated_amount',
        'status',
        'due_date',
        'paid_at',
        'invoice_number',
        'payment_transaction_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'payment_details',
        'vendor_notes',
        'admin_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'calculated_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'payment_details' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';       // Not yet due
    const STATUS_DUE = 'due';              // Payment required
    const STATUS_PAID = 'paid';            // Payment completed
    const STATUS_OVERDUE = 'overdue';      // Past due date
    const STATUS_CANCELLED = 'cancelled';  // Booking cancelled

    // Amount type constants
    const AMOUNT_TYPE_FIXED = 'fixed';
    const AMOUNT_TYPE_PERCENTAGE = 'percentage';

    /**
     * Relationships
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeDue($query)
    {
        return $query->where('status', self::STATUS_DUE);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_DUE, self::STATUS_OVERDUE]);
    }

    public function scopeForQuotation($query, int $quotationId)
    {
        return $query->where('quotation_id', $quotationId)->orderBy('sequence_no');
    }

    public function scopeNextDue($query, int $quotationId)
    {
        return $query->where('quotation_id', $quotationId)
            ->whereIn('status', [self::STATUS_DUE, self::STATUS_OVERDUE])
            ->orderBy('sequence_no')
            ->first();
    }

    /**
     * Helpers
     */
    
    /**
     * Check if milestone is paid
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if milestone is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE || 
               ($this->due_date && $this->due_date->isPast() && !$this->isPaid());
    }

    /**
     * Check if milestone is due for payment
     */
    public function isDue(): bool
    {
        return in_array($this->status, [self::STATUS_DUE, self::STATUS_OVERDUE]);
    }

    /**
     * Calculate amount based on quotation total
     */
    public function calculateAmount(float $quotationTotal): float
    {
        if ($this->amount_type === self::AMOUNT_TYPE_PERCENTAGE) {
            return round(($quotationTotal * $this->amount) / 100, 2);
        }
        
        return $this->amount;
    }

    /**
     * Mark milestone as paid
     */
    public function markAsPaid(array $paymentDetails = []): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
            'payment_details' => array_merge($this->payment_details ?? [], $paymentDetails),
        ]);
    }

    /**
     * Mark milestone as due
     */
    public function markAsDue(): void
    {
        if (!$this->isPaid()) {
            $this->update(['status' => self::STATUS_DUE]);
        }
    }

    /**
     * Mark milestone as overdue
     */
    public function markAsOverdue(): void
    {
        if (!$this->isPaid()) {
            $this->update(['status' => self::STATUS_OVERDUE]);
        }
    }

    /**
     * Get formatted amount display
     */
    public function getFormattedAmount(): string
    {
        if ($this->amount_type === self::AMOUNT_TYPE_PERCENTAGE) {
            return $this->amount . '% (' . $this->getFormattedCalculatedAmount() . ')';
        }
        
        return '₹' . number_format($this->amount, 2);
    }

    /**
     * Get formatted calculated amount
     */
    public function getFormattedCalculatedAmount(): string
    {
        return '₹' . number_format($this->calculated_amount ?? $this->amount, 2);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_PAID => 'success',
            self::STATUS_DUE => 'warning',
            self::STATUS_OVERDUE => 'danger',
            self::STATUS_PENDING => 'secondary',
            self::STATUS_CANCELLED => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Get status label for UI
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PAID => 'Paid',
            self::STATUS_DUE => 'Payment Due',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_PENDING => 'Upcoming',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get days until due
     */
    public function getDaysUntilDue(): ?int
    {
        if (!$this->due_date || $this->isPaid()) {
            return null;
        }
        
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdue(): ?int
    {
        if (!$this->due_date || $this->isPaid() || !$this->due_date->isPast()) {
            return null;
        }
        
        return $this->due_date->diffInDays(now());
    }
}
