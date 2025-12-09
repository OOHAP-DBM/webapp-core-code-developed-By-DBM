<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SettlementBatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'batch_reference',
        'batch_name',
        'batch_description',
        'period_start',
        'period_end',
        'status',
        'total_bookings_amount',
        'total_admin_commission',
        'total_vendor_payout',
        'total_pg_fees',
        'total_bookings_count',
        'vendors_count',
        'pending_kyc_count',
        'created_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'processed_at',
        'completed_at',
        'processing_errors',
        'split_config',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_bookings_amount' => 'decimal:2',
        'total_admin_commission' => 'decimal:2',
        'total_vendor_payout' => 'decimal:2',
        'total_pg_fees' => 'decimal:2',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'processing_errors' => 'array',
        'split_config' => 'array',
    ];

    /**
     * Boot method to auto-generate batch reference
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($batch) {
            if (!$batch->batch_reference) {
                $batch->batch_reference = 'STL-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
    }

    /**
     * Relationships
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(VendorLedger::class);
    }

    public function bookingPayments(): HasMany
    {
        return $this->hasMany(BookingPayment::class);
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
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

    /**
     * Status checks
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * State transitions
     */
    public function markAsPendingApproval(): bool
    {
        return $this->update([
            'status' => 'pending_approval',
        ]);
    }

    public function markAsApproved(int $approvedBy, ?string $notes = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => 'processing',
            'processed_at' => now(),
        ]);
    }

    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(array $errors): bool
    {
        return $this->update([
            'status' => 'failed',
            'processing_errors' => $errors,
        ]);
    }

    /**
     * Computed attributes
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'pending_approval' => 'warning',
            'approved' => 'info',
            'processing' => 'primary',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'dark',
            default => 'secondary',
        };
    }

    public function getFormattedPeriodAttribute(): string
    {
        return $this->period_start->format('M d') . ' - ' . $this->period_end->format('M d, Y');
    }

    /**
     * Calculate totals from booking payments
     */
    public function calculateTotals(): array
    {
        $bookingPayments = BookingPayment::whereBetween('created_at', [
            $this->period_start->startOfDay(),
            $this->period_end->endOfDay()
        ])->get();

        return [
            'total_bookings_amount' => $bookingPayments->sum('gross_amount'),
            'total_admin_commission' => $bookingPayments->sum('admin_commission_amount'),
            'total_vendor_payout' => $bookingPayments->sum('vendor_payout_amount'),
            'total_pg_fees' => $bookingPayments->sum('pg_fee_amount'),
            'total_bookings_count' => $bookingPayments->count(),
            'vendors_count' => $bookingPayments->pluck('booking.vendor_id')->unique()->count(),
        ];
    }
}
