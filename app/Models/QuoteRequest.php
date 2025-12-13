<?php

namespace App\Models;

use App\Traits\HasSnapshots;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class QuoteRequest extends Model
{
    use HasFactory, HasSnapshots, Auditable, SoftDeletes;

    protected $snapshotType = 'quote_request';
    protected $snapshotOnCreate = true;

    protected $auditModule = 'quote_request';

    protected $fillable = [
        'request_number',
        'customer_id',
        'hoarding_id',
        'preferred_start_date',
        'preferred_end_date',
        'duration_days',
        'duration_type',
        'requirements',
        'printing_required',
        'mounting_required',
        'lighting_required',
        'additional_services',
        'budget_min',
        'budget_max',
        'vendor_selection_mode',
        'invited_vendor_ids',
        'open_to_all_vendors',
        'status',
        'published_at',
        'response_deadline',
        'decision_deadline',
        'selected_quote_id',
        'quote_selected_at',
        'quotes_received_count',
        'quotes_viewed_count',
        'hoarding_snapshot',
    ];

    protected $casts = [
        'preferred_start_date' => 'date',
        'preferred_end_date' => 'date',
        'printing_required' => 'boolean',
        'mounting_required' => 'boolean',
        'lighting_required' => 'boolean',
        'additional_services' => 'array',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'invited_vendor_ids' => 'array',
        'open_to_all_vendors' => 'boolean',
        'published_at' => 'datetime',
        'response_deadline' => 'datetime',
        'decision_deadline' => 'datetime',
        'quote_selected_at' => 'datetime',
        'hoarding_snapshot' => 'array',
    ];

    // Status Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_QUOTES_RECEIVED = 'quotes_received';
    const STATUS_QUOTE_ACCEPTED = 'quote_accepted';
    const STATUS_CLOSED = 'closed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    // Vendor Selection Modes
    const MODE_SINGLE = 'single';
    const MODE_MULTIPLE = 'multiple';

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

        static::creating(function ($request) {
            if (empty($request->request_number)) {
                $request->request_number = self::generateRequestNumber();
            }
            if (empty($request->duration_days)) {
                $request->duration_days = $request->preferred_start_date->diffInDays($request->preferred_end_date);
            }
        });

        static::created(function ($request) {
            // Snapshot hoarding details
            if ($request->hoarding && empty($request->hoarding_snapshot)) {
                $request->update([
                    'hoarding_snapshot' => [
                        'id' => $request->hoarding->id,
                        'title' => $request->hoarding->title,
                        'location' => $request->hoarding->location,
                        'type' => $request->hoarding->type,
                        'dimensions' => $request->hoarding->dimensions,
                        'monthly_price' => $request->hoarding->monthly_price,
                        'vendor_id' => $request->hoarding->vendor_id,
                        'vendor_name' => $request->hoarding->vendor->name ?? null,
                    ]
                ]);
            }
        });
    }

    /**
     * Generate unique request number
     */
    public static function generateRequestNumber(): string
    {
        do {
            $number = 'QR-' . strtoupper(Str::random(10));
        } while (self::where('request_number', $number)->exists());

        return $number;
    }

    /**
     * Relationships
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(VendorQuote::class);
    }

    public function selectedQuote(): BelongsTo
    {
        return $this->belongsTo(VendorQuote::class, 'selected_quote_id');
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_QUOTES_RECEIVED])
            ->where('response_deadline', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
            ->orWhere(function ($q) {
                $q->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_QUOTES_RECEIVED])
                    ->where('response_deadline', '<', now());
            });
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

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function hasQuotes(): bool
    {
        return $this->quotes_received_count > 0;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED ||
               (in_array($this->status, [self::STATUS_PUBLISHED, self::STATUS_QUOTES_RECEIVED]) &&
                $this->response_deadline && $this->response_deadline->isPast());
    }

    public function hasQuoteSelected(): bool
    {
        return !is_null($this->selected_quote_id);
    }

    /**
     * Action Checks
     */
    public function canPublish(): bool
    {
        return $this->isDraft();
    }

    public function canReceiveQuotes(): bool
    {
        return $this->isPublished() && !$this->isExpired();
    }

    public function canSelectQuote(): bool
    {
        return $this->hasQuotes() && !$this->hasQuoteSelected() && !$this->isExpired();
    }

    /**
     * Actions
     */
    public function publish(): void
    {
        $this->update([
            'status' => self::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        // Set default deadlines if not set
        if (!$this->response_deadline) {
            $this->update(['response_deadline' => now()->addDays(7)]);
        }
        if (!$this->decision_deadline) {
            $this->update(['decision_deadline' => now()->addDays(14)]);
        }
    }

    public function selectQuote(VendorQuote $quote): void
    {
        $this->update([
            'selected_quote_id' => $quote->id,
            'quote_selected_at' => now(),
            'status' => self::STATUS_QUOTE_ACCEPTED,
        ]);

        $quote->accept();
    }

    public function close(): void
    {
        $this->update(['status' => self::STATUS_CLOSED]);
    }

    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    public function markExpired(): void
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Helpers
     */
    public function getEligibleVendors(): array
    {
        if (!$this->open_to_all_vendors && $this->invited_vendor_ids) {
            return $this->invited_vendor_ids;
        }

        // If open to all, return hoarding's vendor or all vendors
        if ($this->hoarding && $this->hoarding->vendor_id) {
            return [$this->hoarding->vendor_id];
        }

        return [];
    }

    public function isVendorEligible(int $vendorId): bool
    {
        if (!$this->open_to_all_vendors) {
            return in_array($vendorId, $this->invited_vendor_ids ?? []);
        }

        // If open to all, check if vendor owns the hoarding
        return $this->hoarding && $this->hoarding->vendor_id == $vendorId;
    }

    public function hasVendorSubmittedQuote(int $vendorId): bool
    {
        return $this->quotes()->where('vendor_id', $vendorId)->exists();
    }

    public function getDaysUntilDeadline(): int
    {
        return $this->response_deadline ? now()->diffInDays($this->response_deadline, false) : 0;
    }

    public function getQuotesComparison(): array
    {
        return $this->quotes()
            ->with('vendor')
            ->whereIn('status', [VendorQuote::STATUS_SENT, VendorQuote::STATUS_VIEWED])
            ->get()
            ->map(function ($quote) {
                return [
                    'quote_id' => $quote->id,
                    'vendor_name' => $quote->vendor->name,
                    'grand_total' => $quote->grand_total,
                    'base_price' => $quote->base_price,
                    'survey_cost' => $quote->survey_cost,
                    'lighting_cost' => $quote->lighting_cost,
                    'tax_amount' => $quote->tax_amount,
                    'vendor_notes' => $quote->vendor_notes,
                    'expires_at' => $quote->expires_at,
                    'sent_at' => $quote->sent_at,
                ];
            })
            ->sortBy('grand_total')
            ->values()
            ->toArray();
    }
}
