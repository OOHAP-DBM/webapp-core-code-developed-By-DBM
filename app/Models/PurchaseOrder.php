<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * PROMPT 107: Purchase Order Model
 * 
 * Auto-generated PO from approved quotations
 */
class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'quotation_id',
        'customer_id',
        'vendor_id',
        'enquiry_id',
        'offer_id',
        'items',
        'total_amount',
        'tax',
        'discount',
        'grand_total',
        'has_milestones',
        'payment_mode',
        'milestone_count',
        'milestone_summary',
        'pdf_path',
        'pdf_generated_at',
        'status',
        'sent_at',
        'confirmed_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'customer_approved_at',
        'vendor_acknowledged_at',
        'thread_id',
        'thread_message_id',
        'notes',
        'terms_and_conditions',
    ];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'has_milestones' => 'boolean',
        'milestone_count' => 'integer',
        'milestone_summary' => 'array',
        'pdf_generated_at' => 'datetime',
        'sent_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'customer_approved_at' => 'datetime',
        'vendor_acknowledged_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';

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

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(\Modules\Offers\Models\Offer::class);
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(\Modules\Threads\Models\Thread::class);
    }

    public function threadMessage(): BelongsTo
    {
        return $this->belongsTo(\Modules\Threads\Models\ThreadMessage::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Business Logic
     */
    
    /**
     * Generate unique PO number
     */
    public static function generatePoNumber(): string
    {
        $prefix = 'PO';
        $date = now()->format('Ym'); // YYYYMM format
        
        // Get last PO number for this month
        $lastPo = static::where('po_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('po_number', 'desc')
            ->first();
        
        if ($lastPo) {
            // Extract sequence number and increment
            $lastNumber = (int) substr($lastPo->po_number, -4);
            $sequence = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            // First PO of the month
            $sequence = '0001';
        }
        
        return "{$prefix}-{$date}-{$sequence}";
    }

    /**
     * Mark PO as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark PO as confirmed by customer
     */
    public function markAsConfirmed(): void
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => now(),
            'customer_approved_at' => now(),
        ]);
    }

    /**
     * Vendor acknowledges PO
     */
    public function vendorAcknowledge(): void
    {
        $this->update([
            'vendor_acknowledged_at' => now(),
        ]);
    }

    /**
     * Cancel PO
     */
    public function cancel(string $reason, $cancelledBy = 'system'): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Check if PO can be confirmed
     */
    public function canConfirm(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_SENT]);
    }

    /**
     * Check if PO can be cancelled
     */
    public function canCancel(): bool
    {
        return $this->status !== self::STATUS_CANCELLED;
    }

    /**
     * Get PDF filename
     */
    public function getPdfFilename(): string
    {
        return "purchase-order-{$this->po_number}.pdf";
    }

    /**
     * Get formatted grand total
     */
    public function getFormattedGrandTotal(): string
    {
        return '₹ ' . number_format($this->grand_total, 2);
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SENT => 'Sent to Vendor',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_SENT => 'info',
            self::STATUS_CONFIRMED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Check if PDF is generated
     */
    public function hasPdf(): bool
    {
        return !empty($this->pdf_path) && !empty($this->pdf_generated_at);
    }
}
