<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * PROMPT 58: PayoutRequest Model
 * 
 * Manages vendor payout requests with commission, adjustments, GST, and approval workflow
 */
class PayoutRequest extends Model
{
    use SoftDeletes;

    /**
     * Statuses
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Payout modes
     */
    const PAYOUT_MODE_BANK_TRANSFER = 'bank_transfer';
    const PAYOUT_MODE_RAZORPAY_TRANSFER = 'razorpay_transfer';
    const PAYOUT_MODE_UPI = 'upi';
    const PAYOUT_MODE_CHEQUE = 'cheque';
    const PAYOUT_MODE_MANUAL = 'manual';

    protected $fillable = [
        'request_reference',
        'vendor_id',
        'booking_revenue',
        'commission_amount',
        'commission_percentage',
        'pg_fees',
        'adjustment_amount',
        'adjustment_reason',
        'gst_amount',
        'gst_percentage',
        'final_payout_amount',
        'period_start',
        'period_end',
        'bookings_count',
        'status',
        'bank_name',
        'account_number',
        'account_holder_name',
        'ifsc_code',
        'upi_id',
        'payout_mode',
        'payout_reference',
        'payout_notes',
        'paid_at',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'receipt_pdf_path',
        'receipt_generated_at',
        'booking_ids',
        'metadata',
    ];

    protected $casts = [
        'booking_revenue' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'pg_fees' => 'decimal:2',
        'adjustment_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'gst_percentage' => 'decimal:2',
        'final_payout_amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'receipt_generated_at' => 'datetime',
        'booking_ids' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payoutRequest) {
            if (!$payoutRequest->request_reference) {
                $payoutRequest->request_reference = 'PR-' . date('Ymd') . '-' . strtoupper(Str::random(4));
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

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Scopes
     */
    public function scopeByVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopePendingApproval($query)
    {
        return $query->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_PENDING_APPROVAL]);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('period_start', [$startDate, $endDate])
              ->orWhereBetween('period_end', [$startDate, $endDate]);
        });
    }

    /**
     * Status checks
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isPendingApproval(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_PENDING_APPROVAL]);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canSubmit(): bool
    {
        return $this->isDraft();
    }

    public function canApprove(): bool
    {
        return $this->isPendingApproval();
    }

    public function canReject(): bool
    {
        return $this->isPendingApproval();
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SUBMITTED]);
    }

    /**
     * Calculated attributes
     */
    public function getNetAmountBeforeGstAttribute(): float
    {
        return (float) ($this->booking_revenue - $this->commission_amount - $this->pg_fees + $this->adjustment_amount);
    }

    public function getCommissionRateAttribute(): float
    {
        if ($this->booking_revenue <= 0) {
            return 0;
        }
        return (float) (($this->commission_amount / $this->booking_revenue) * 100);
    }

    public function getTotalDeductionsAttribute(): float
    {
        return (float) ($this->commission_amount + $this->pg_fees + abs(min($this->adjustment_amount, 0)));
    }

    public function getTotalAdditionsAttribute(): float
    {
        return (float) max($this->adjustment_amount, 0);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_SUBMITTED, self::STATUS_PENDING_APPROVAL => 'warning',
            self::STATUS_APPROVED => 'info',
            self::STATUS_PROCESSING => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_REJECTED, self::STATUS_FAILED => 'danger',
            self::STATUS_CANCELLED => 'dark',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get financial summary
     */
    public function getFinancialSummary(): array
    {
        return [
            'booking_revenue' => (float) $this->booking_revenue,
            'commission' => [
                'amount' => (float) $this->commission_amount,
                'percentage' => (float) $this->commission_percentage,
            ],
            'pg_fees' => (float) $this->pg_fees,
            'adjustment' => [
                'amount' => (float) $this->adjustment_amount,
                'reason' => $this->adjustment_reason,
            ],
            'net_before_gst' => $this->net_amount_before_gst,
            'gst' => [
                'amount' => (float) $this->gst_amount,
                'percentage' => (float) $this->gst_percentage,
            ],
            'final_payout' => (float) $this->final_payout_amount,
            'bookings_count' => $this->bookings_count,
            'period' => [
                'start' => $this->period_start->format('Y-m-d'),
                'end' => $this->period_end->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Get booking payments included in this request
     */
    public function getBookingPayments()
    {
        if (empty($this->booking_ids)) {
            return collect();
        }

        return BookingPayment::with(['booking.hoarding', 'booking.customer'])
            ->whereIn('id', $this->booking_ids)
            ->get();
    }

    /**
     * Submit request for approval
     */
    public function submit(User $user): bool
    {
        if (!$this->canSubmit()) {
            return false;
        }

        $this->status = self::STATUS_PENDING_APPROVAL;
        $this->submitted_by = $user->id;
        $this->submitted_at = now();
        
        return $this->save();
    }

    /**
     * Approve request
     */
    public function approve(User $admin, ?string $notes = null): bool
    {
        if (!$this->canApprove()) {
            return false;
        }

        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $admin->id;
        $this->approved_at = now();
        $this->approval_notes = $notes;
        
        return $this->save();
    }

    /**
     * Reject request
     */
    public function reject(User $admin, string $reason): bool
    {
        if (!$this->canReject()) {
            return false;
        }

        $this->status = self::STATUS_REJECTED;
        $this->rejected_by = $admin->id;
        $this->rejected_at = now();
        $this->rejection_reason = $reason;
        
        return $this->save();
    }

    /**
     * Mark as processing
     */
    public function markProcessing(): bool
    {
        if (!$this->isApproved()) {
            return false;
        }

        $this->status = self::STATUS_PROCESSING;
        return $this->save();
    }

    /**
     * Mark as completed
     */
    public function markCompleted(string $payoutMode, string $payoutReference, ?string $notes = null): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->payout_mode = $payoutMode;
        $this->payout_reference = $payoutReference;
        $this->payout_notes = $notes;
        $this->paid_at = now();
        
        return $this->save();
    }

    /**
     * Mark as failed
     */
    public function markFailed(string $reason): bool
    {
        $this->status = self::STATUS_FAILED;
        $this->payout_notes = $reason;
        
        return $this->save();
    }

    /**
     * Cancel request
     */
    public function cancel(): bool
    {
        if (!$this->canCancel()) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }
}
