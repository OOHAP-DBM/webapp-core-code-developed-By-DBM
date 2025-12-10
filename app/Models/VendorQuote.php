<?php

namespace App\Models;

use App\Traits\HasSnapshots;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VendorQuote extends Model
{
    use HasSnapshots, Auditable, SoftDeletes;

    protected $snapshotType = 'vendor_quote';
    protected $snapshotOnCreate = true;
    protected $snapshotOnUpdate = true;

    protected $auditModule = 'vendor_quote';
    protected $priceFields = ['grand_total', 'subtotal', 'base_price'];

    protected $fillable = [
        'quote_request_id',
        'enquiry_id',
        'hoarding_id',
        'customer_id',
        'vendor_id',
        'quote_number',
        'version',
        'parent_quote_id',
        'start_date',
        'end_date',
        'duration_days',
        'duration_type',
        'hoarding_snapshot',
        'base_price',
        'printing_cost',
        'mounting_cost',
        'survey_cost',
        'lighting_cost',
        'maintenance_cost',
        'other_charges',
        'other_charges_description',
        'subtotal',
        'discount_amount',
        'discount_percentage',
        'tax_amount',
        'tax_percentage',
        'grand_total',
        'vendor_notes',
        'terms_and_conditions',
        'status',
        'sent_at',
        'viewed_at',
        'accepted_at',
        'rejected_at',
        'expires_at',
        'rejection_reason',
        'pdf_path',
        'pdf_generated_at',
        'quote_snapshot',
        'booking_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'hoarding_snapshot' => 'array',
        'base_price' => 'decimal:2',
        'printing_cost' => 'decimal:2',
        'mounting_cost' => 'decimal:2',
        'survey_cost' => 'decimal:2',
        'lighting_cost' => 'decimal:2',
        'maintenance_cost' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'terms_and_conditions' => 'array',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expires_at' => 'datetime',
        'pdf_generated_at' => 'datetime',
        'quote_snapshot' => 'array',
    ];

    // Status Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_VIEWED = 'viewed';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REVISED = 'revised';

    // Duration Types
    const DURATION_DAYS = 'days';
    const DURATION_WEEKS = 'weeks';
    const DURATION_MONTHS = 'months';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            if (empty($quote->quote_number)) {
                $quote->quote_number = self::generateQuoteNumber();
            }
            if (empty($quote->expires_at)) {
                $quote->expires_at = now()->addDays(7); // Default 7 days validity
            }
        });

        static::created(function ($quote) {
            // Update quote request count
            if ($quote->quote_request_id) {
                $quote->quoteRequest->increment('quotes_received_count');
                $quote->quoteRequest->update(['status' => QuoteRequest::STATUS_QUOTES_RECEIVED]);
            }
        });
    }

    /**
     * Generate unique quote number
     */
    public static function generateQuoteNumber(): string
    {
        do {
            $number = 'VQ-' . strtoupper(Str::random(10));
        } while (self::where('quote_number', $number)->exists());

        return $number;
    }

    /**
     * Relationships
     */
    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function parentQuote(): BelongsTo
    {
        return $this->belongsTo(VendorQuote::class, 'parent_quote_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(VendorQuote::class, 'parent_quote_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
            ->orWhere(function ($q) {
                $q->whereNotIn('status', [self::STATUS_ACCEPTED, self::STATUS_REJECTED])
                    ->where('expires_at', '<', now());
            });
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_SENT, self::STATUS_VIEWED])
            ->where('expires_at', '>', now());
    }

    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Status Checks
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT || $this->status === self::STATUS_VIEWED;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               (!in_array($this->status, [self::STATUS_ACCEPTED, self::STATUS_REJECTED]) && 
                $this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Action Checks
     */
    public function canSend(): bool
    {
        return $this->isDraft();
    }

    public function canAccept(): bool
    {
        return $this->isSent() && !$this->isExpired();
    }

    public function canReject(): bool
    {
        return $this->isSent() && !$this->isExpired();
    }

    public function canRevise(): bool
    {
        return $this->isSent() || $this->isRejected();
    }

    /**
     * Calculations
     */
    public function calculateSubtotal(): float
    {
        return (float) (
            $this->base_price +
            $this->printing_cost +
            $this->mounting_cost +
            $this->survey_cost +
            $this->lighting_cost +
            $this->maintenance_cost +
            $this->other_charges
        );
    }

    public function calculateTax(float $subtotal = null): float
    {
        $subtotal = $subtotal ?? $this->calculateSubtotal();
        return (float) ($subtotal * ($this->tax_percentage / 100));
    }

    public function calculateGrandTotal(): float
    {
        $subtotal = $this->calculateSubtotal();
        $tax = $this->calculateTax($subtotal);
        return (float) ($subtotal - $this->discount_amount + $tax);
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = $this->calculateSubtotal();
        $this->tax_amount = $this->calculateTax($this->subtotal);
        $this->grand_total = $this->calculateGrandTotal();
    }

    /**
     * Actions
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    public function markAsViewed(): void
    {
        if ($this->status === self::STATUS_SENT && !$this->viewed_at) {
            $this->update([
                'status' => self::STATUS_VIEWED,
                'viewed_at' => now(),
            ]);
        }
    }

    public function accept(): void
    {
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'accepted_at' => now(),
            'quote_snapshot' => $this->toArray(),
        ]);

        // Update quote request
        if ($this->quote_request_id) {
            $this->quoteRequest->update([
                'selected_quote_id' => $this->id,
                'quote_selected_at' => now(),
                'status' => QuoteRequest::STATUS_QUOTE_ACCEPTED,
            ]);
        }
    }

    public function reject(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function createRevision(): VendorQuote
    {
        $revision = $this->replicate();
        $revision->parent_quote_id = $this->id;
        $revision->version = $this->version + 1;
        $revision->quote_number = self::generateQuoteNumber();
        $revision->status = self::STATUS_DRAFT;
        $revision->sent_at = null;
        $revision->viewed_at = null;
        $revision->accepted_at = null;
        $revision->rejected_at = null;
        $revision->pdf_path = null;
        $revision->pdf_generated_at = null;
        $revision->save();

        $this->update(['status' => self::STATUS_REVISED]);

        return $revision;
    }

    /**
     * Helpers
     */
    public function getPdfFilename(): string
    {
        return "quote-{$this->quote_number}-v{$this->version}.pdf";
    }

    public function getFormattedGrandTotal(): string
    {
        return '₹' . number_format($this->grand_total, 2);
    }

    public function getFormattedSubtotal(): string
    {
        return '₹' . number_format($this->subtotal, 2);
    }

    public function getDaysUntilExpiry(): int
    {
        return $this->expires_at ? now()->diffInDays($this->expires_at, false) : 0;
    }

    public function getValidityDaysRemaining(): int
    {
        return max(0, $this->getDaysUntilExpiry());
    }
}
