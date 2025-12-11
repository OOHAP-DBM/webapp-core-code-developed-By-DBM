<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Invoice status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_ISSUED = 'issued';
    const STATUS_SENT = 'sent';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIALLY_PAID = 'partially_paid';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_VOID = 'void';

    /**
     * Invoice type constants
     */
    const TYPE_FULL_PAYMENT = 'full_payment';
    const TYPE_MILESTONE = 'milestone';
    const TYPE_SUBSCRIPTION = 'subscription';
    const TYPE_POS = 'pos';
    const TYPE_PRINTING = 'printing';
    const TYPE_REMOUNTING = 'remounting';
    const TYPE_VENDOR_SERVICE = 'vendor_service';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'invoice_number',
        'financial_year',
        'invoice_date',
        'invoice_type',
        'booking_id',
        'booking_payment_id',
        'milestone_id',
        'pos_booking_id',
        'seller_name',
        'seller_gstin',
        'seller_address',
        'seller_city',
        'seller_state',
        'seller_state_code',
        'seller_pincode',
        'seller_pan',
        'customer_id',
        'buyer_name',
        'buyer_gstin',
        'buyer_address',
        'buyer_city',
        'buyer_state',
        'buyer_state_code',
        'buyer_pincode',
        'buyer_pan',
        'buyer_type',
        'buyer_email',
        'buyer_phone',
        'place_of_supply',
        'is_reverse_charge',
        'is_intra_state',
        'supply_type',
        'subtotal',
        'discount_amount',
        'taxable_amount',
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'igst_rate',
        'igst_amount',
        'total_tax',
        'total_amount',
        'round_off',
        'grand_total',
        'notes',
        'terms_conditions',
        'payment_terms',
        'pdf_path',
        'qr_code_path',
        'qr_code_data',
        'is_emailed',
        'emailed_at',
        'email_count',
        'email_recipients',
        'status',
        'issued_at',
        'paid_at',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
        'paid_amount',
        'due_date',
        'created_by',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'invoice_date' => 'date',
        'is_reverse_charge' => 'boolean',
        'is_intra_state' => 'boolean',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'cgst_rate' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_rate' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_rate' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'round_off' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'is_emailed' => 'boolean',
        'emailed_at' => 'datetime',
        'email_count' => 'integer',
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Relationships
     */
    
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function bookingPayment(): BelongsTo
    {
        return $this->belongsTo(BookingPayment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Scopes
     */
    
    public function scopeByFinancialYear($query, string $financialYear)
    {
        return $query->where('financial_year', $financialYear);
    }

    public function scopeIssued($query)
    {
        return $query->where('status', self::STATUS_ISSUED);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [self::STATUS_ISSUED, self::STATUS_SENT, self::STATUS_OVERDUE]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE)
            ->orWhere(function ($q) {
                $q->whereIn('status', [self::STATUS_ISSUED, self::STATUS_SENT])
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', now());
            });
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeIntraState($query)
    {
        return $query->where('is_intra_state', true);
    }

    public function scopeInterState($query)
    {
        return $query->where('is_intra_state', false);
    }

    /**
     * Status check methods
     */
    
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === self::STATUS_PARTIALLY_PAID;
    }

    public function isOverdue(): bool
    {
        if ($this->status === self::STATUS_OVERDUE) {
            return true;
        }
        
        if ($this->due_date && $this->due_date < now() && $this->isUnpaid()) {
            return true;
        }
        
        return false;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isUnpaid(): bool
    {
        return in_array($this->status, [self::STATUS_ISSUED, self::STATUS_SENT, self::STATUS_OVERDUE, self::STATUS_PARTIALLY_PAID]);
    }

    /**
     * Helper methods
     */
    
    public function hasPDF(): bool
    {
        return !empty($this->pdf_path) && Storage::exists($this->pdf_path);
    }

    public function getPDFUrl(): ?string
    {
        return $this->hasPDF() ? Storage::url($this->pdf_path) : null;
    }

    public function hasQRCode(): bool
    {
        return !empty($this->qr_code_path) && Storage::exists($this->qr_code_path);
    }

    public function getQRCodeUrl(): ?string
    {
        return $this->hasQRCode() ? Storage::url($this->qr_code_path) : null;
    }

    public function getFormattedInvoiceNumber(): string
    {
        return $this->invoice_number;
    }

    public function getFormattedGrandTotal(): string
    {
        return '₹' . number_format($this->grand_total, 2);
    }

    public function getFormattedTaxableAmount(): string
    {
        return '₹' . number_format($this->taxable_amount, 2);
    }

    public function getFormattedTotalTax(): string
    {
        return '₹' . number_format($this->total_tax, 2);
    }

    public function getBalanceDue(): float
    {
        return max(0, $this->grand_total - $this->paid_amount);
    }

    public function getFormattedBalanceDue(): string
    {
        return '₹' . number_format($this->getBalanceDue(), 2);
    }

    /**
     * Get tax breakdown
     */
    public function getTaxBreakdown(): array
    {
        if ($this->is_intra_state) {
            return [
                'type' => 'intra_state',
                'cgst' => [
                    'rate' => $this->cgst_rate,
                    'amount' => $this->cgst_amount,
                ],
                'sgst' => [
                    'rate' => $this->sgst_rate,
                    'amount' => $this->sgst_amount,
                ],
                'total' => $this->total_tax,
            ];
        } else {
            return [
                'type' => 'inter_state',
                'igst' => [
                    'rate' => $this->igst_rate,
                    'amount' => $this->igst_amount,
                ],
                'total' => $this->total_tax,
            ];
        }
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent(?string $emailRecipients = null): bool
    {
        $this->status = self::STATUS_SENT;
        $this->is_emailed = true;
        $this->emailed_at = now();
        $this->email_count++;
        
        if ($emailRecipients) {
            $this->email_recipients = $emailRecipients;
        }
        
        return $this->save();
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(?float $paidAmount = null, ?\DateTime $paidAt = null): bool
    {
        $this->status = self::STATUS_PAID;
        $this->paid_amount = $paidAmount ?? $this->grand_total;
        $this->paid_at = $paidAt ?? now();
        
        return $this->save();
    }

    /**
     * Record partial payment
     */
    public function recordPartialPayment(float $amount): bool
    {
        $this->paid_amount += $amount;
        
        if ($this->paid_amount >= $this->grand_total) {
            $this->status = self::STATUS_PAID;
            $this->paid_at = now();
        } else {
            $this->status = self::STATUS_PARTIALLY_PAID;
        }
        
        return $this->save();
    }

    /**
     * Cancel invoice
     */
    public function cancel(string $reason, ?int $cancelledBy = null): bool
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;
        $this->cancelled_by = $cancelledBy;
        
        return $this->save();
    }

    /**
     * Check if invoice can be edited
     */
    public function canEdit(): bool
    {
        return $this->isDraft();
    }

    /**
     * Check if invoice can be cancelled
     */
    public function canCancel(): bool
    {
        return !$this->isCancelled() && !$this->isPaid();
    }

    /**
     * Get invoice age in days
     */
    public function getAgeInDays(): int
    {
        return $this->invoice_date->diffInDays(now());
    }

    /**
     * Get days until/past due date
     */
    public function getDaysUntilDue(): ?int
    {
        if (!$this->due_date) {
            return null;
        }
        
        return now()->diffInDays($this->due_date, false);
    }
}
