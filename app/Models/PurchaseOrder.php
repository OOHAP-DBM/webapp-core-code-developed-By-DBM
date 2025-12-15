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
        'subtotal',
        'tax',
        'tax_rate',
        'discount',
        'grand_total',
        // Currency
        'currency_code',
        'currency_symbol',
        // GST breakdown
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'igst_rate',
        'igst_amount',
        // TCS
        'has_tcs',
        'tcs_rate',
        'tcs_amount',
        'tcs_section',
        // TDS
        'has_tds',
        'tds_rate',
        'tds_amount',
        'tds_section',
        // Tax metadata
        'is_intra_state',
        'is_reverse_charge',
        'place_of_supply',
        'tax_calculation_details',
        // Payment
        'has_milestones',
        'payment_mode',
        'milestone_count',
        'milestone_summary',
        // PDF
        'pdf_path',
        'pdf_generated_at',
        // Status
        'status',
        'sent_at',
        'confirmed_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'customer_approved_at',
        'vendor_acknowledged_at',
        // Thread
        'thread_id',
        'thread_message_id',
        // Notes
        'notes',
        'terms_and_conditions',
    ];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        // GST
        'cgst_rate' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_rate' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_rate' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        // TCS
        'has_tcs' => 'boolean',
        'tcs_rate' => 'decimal:2',
        'tcs_amount' => 'decimal:2',
        // TDS
        'has_tds' => 'boolean',
        'tds_rate' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        // Tax metadata
        'is_intra_state' => 'boolean',
        'is_reverse_charge' => 'boolean',
        'tax_calculation_details' => 'array',
        // Payment
        'has_milestones' => 'boolean',
        'milestone_count' => 'integer',
        'milestone_summary' => 'array',
        // Timestamps
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

    // /**
    //  * Check if PO can be confirmed
    //  */ (PROMPT 109: Currency-aware)
    //  */
    public function getFormattedGrandTotal(): string
    {
        $symbol = $this->currency_symbol ?? '₹';
        return $symbol . ' ' . number_format($this->grand_total, 2);
    }

    /**
     * Get formatted amount with currency
     */
    public function formatAmount(float $amount): string
    {
        $symbol = $this->currency_symbol ?? '₹';
        return $symbol . ' ' . number_format($amount, 2);
    }

    /**
     * Get tax breakdown for display
     */
    public function getTaxBreakdown(): array
    {
        if ($this->is_intra_state) {
            return [
                'type' => 'Intra-State GST',
                'cgst' => [
                    'rate' => $this->cgst_rate,
                    'amount' => $this->cgst_amount,
                    'formatted' => $this->formatAmount($this->cgst_amount),
                ],
                'sgst' => [
                    'rate' => $this->sgst_rate,
                    'amount' => $this->sgst_amount,
                    'formatted' => $this->formatAmount($this->sgst_amount),
                ],
            ];
        } else {
            return [
                'type' => 'Inter-State GST',
                'igst' => [
                    'rate' => $this->igst_rate,
                    'amount' => $this->igst_amount,
                    'formatted' => $this->formatAmount($this->igst_amount),
                ],
            ];
        }
    }

    /**
     * Check if TCS is applicable
     */
    public function hasTCS(): bool
    {
        return $this->has_tcs && $this->tcs_amount > 0;
    }

    /**
     * Check if TDS is applicable
     */
    public function hasTDS(): bool
    {
        return $this->has_tds && $this->tds_amount > 0;
    }

    /**
     * Get complete tax summary
     */
    public function getTaxSummary(): array
    {
        return [
            'subtotal' => $this->formatAmount($this->subtotal ?? $this->total_amount),
            'gst' => [
                'rate' => $this->tax_rate,
                'amount' => $this->formatAmount($this->tax),
                'breakdown' => $this->getTaxBreakdown(),
            ],
            'tcs' => $this->hasTCS() ? [
                'rate' => $this->tcs_rate,
                'amount' => $this->formatAmount($this->tcs_amount),
                'section' => $this->tcs_section,
            ] : null,
            'tds' => $this->hasTDS() ? [
                'rate' => $this->tds_rate,
                'amount' => $this->formatAmount($this->tds_amount),
                'section' => $this->tds_section,
            ] : null,
            'grand_total' => $this->getFormattedGrandTotal(),
            ];
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
    // public function getFormattedGrandTotal(): string
    // {
    //     return '₹ ' . number_format($this->grand_total, 2);
    // }

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
