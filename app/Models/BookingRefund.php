<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class BookingRefund extends Model
{
    protected $fillable = [
        'booking_id',
        'booking_type',
        'cancellation_policy_id',
        'refund_reference',
        'refund_type',
        'refund_method',
        'booking_amount',
        'refundable_amount',
        'customer_fee',
        'vendor_penalty',
        'refund_amount',
        'pg_refund_id',
        'pg_payment_id',
        'pg_status',
        'pg_error',
        'status',
        'initiated_at',
        'processed_at',
        'completed_at',
        'cancelled_by_role',
        'cancelled_by',
        'cancellation_reason',
        'hours_before_start',
        'policy_snapshot',
        'calculation_details',
        'approved_by',
        'approved_at',
        'admin_notes',
        'admin_override',
    ];

    protected $casts = [
        'booking_amount' => 'decimal:2',
        'refundable_amount' => 'decimal:2',
        'customer_fee' => 'decimal:2',
        'vendor_penalty' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'policy_snapshot' => 'array',
        'calculation_details' => 'array',
        'initiated_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'admin_override' => 'boolean',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($refund) {
            if (!$refund->refund_reference) {
                $refund->refund_reference = 'REF-' . strtoupper(Str::random(12));
            }
        });
    }

    /**
     * Relationships
     */
    public function booking(): MorphTo
    {
        return $this->morphTo();
    }

    public function cancellationPolicy(): BelongsTo
    {
        return $this->belongsTo(CancellationPolicy::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('cancelled_by_role', $role);
    }

    public function scopeAutoRefund($query)
    {
        return $query->where('refund_method', 'auto');
    }

    public function scopeManualRefund($query)
    {
        return $query->where('refund_method', 'manual');
    }

    /**
     * Check if refund is pending approval
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if refund is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if refund failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark as approved
     */
    public function markAsApproved(int $approvedBy, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(string $pgRefundId): void
    {
        $this->update([
            'status' => 'completed',
            'pg_refund_id' => $pgRefundId,
            'pg_status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'pg_status' => 'failed',
            'pg_error' => $error,
        ]);
    }

    /**
     * Get formatted refund amount
     */
    public function getFormattedRefundAmountAttribute(): string
    {
        return '₹' . number_format($this->refund_amount, 2);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'processing' => 'primary',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }
}
