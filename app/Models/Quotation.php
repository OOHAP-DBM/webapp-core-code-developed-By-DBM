<?php

namespace App\Models;

use App\Traits\HasSnapshots;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use HasSnapshots, Auditable;
    
    protected $snapshotType = 'quotation';
    protected $snapshotOnCreate = true;
    protected $snapshotOnUpdate = true;
    
    protected $auditModule = 'quotation';
    protected $priceFields = ['total_amount'];
    
    protected $fillable = [
        'offer_id',
        'customer_id',
        'vendor_id',
        'version',
        'items',
        'total_amount',
        'tax',
        'discount',
        'grand_total',
        'has_milestones',
        'payment_mode',
        'milestone_count',
        'milestone_summary',
        'approved_snapshot',
        'status',
        'notes',
        'approved_at',
    ];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'has_milestones' => 'boolean',
        'milestone_summary' => 'array',
        'approved_snapshot' => 'array',
        'approved_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REVISED = 'revised';

    /**
     * Relationships
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * PROMPT 70: Milestone relationship
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(QuotationMilestone::class)->orderBy('sequence_no');
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

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeRevised($query)
    {
        return $query->where('status', self::STATUS_REVISED);
    }

    /**
     * Status checks
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isRevised(): bool
    {
        return $this->status === self::STATUS_REVISED;
    }

    /**
     * Action checks
     */
    public function canSend(): bool
    {
        return $this->isDraft();
    }

    public function canApprove(): bool
    {
        return $this->isSent();
    }

    public function canRevise(): bool
    {
        return $this->isSent() || $this->isRejected();
    }

    /**
     * Helpers
     */
    public function getSnapshotValue(string $key, $default = null)
    {
        return data_get($this->approved_snapshot, $key, $default);
    }

    public function getItemsValue(string $key, $default = null)
    {
        return data_get($this->items, $key, $default);
    }

    public function calculateGrandTotal(): float
    {
        return (float) ($this->total_amount + $this->tax - $this->discount);
    }

    public function getFormattedGrandTotal(): string
    {
        return '₹' . number_format($this->grand_total, 2);
    }

    public function getFormattedTotalAmount(): string
    {
        return '₹' . number_format($this->total_amount, 2);
    }

    public function getFormattedTax(): string
    {
        return '₹' . number_format($this->tax, 2);
    }

    public function getFormattedDiscount(): string
    {
        return '₹' . number_format($this->discount, 2);
    }

    public function getItemsCount(): int
    {
        return is_array($this->items) ? count($this->items) : 0;
    }

    public function getTotalQuantity(): float
    {
        if (!is_array($this->items)) {
            return 0;
        }

        return array_reduce($this->items, function ($carry, $item) {
            return $carry + (float) ($item['quantity'] ?? 0);
        }, 0);
    }

    /**
     * PROMPT 70: Milestone helper methods
     */
    
    /**
     * Check if quotation has milestone payment mode
     */
    public function hasMilestones(): bool
    {
        return $this->has_milestones === true && $this->payment_mode === 'milestone';
    }

    /**
     * Check if quotation uses full payment mode
     */
    public function isFullPayment(): bool
    {
        return $this->payment_mode === 'full' || !$this->has_milestones;
    }

    /**
     * Get milestone payment summary
     */
    public function getMilestoneSummary(): array
    {
        if (!$this->hasMilestones()) {
            return [];
        }

        return $this->milestone_summary ?? [];
    }

    /**
     * Recalculate milestone summary
     */
    public function recalculateMilestoneSummary(): void
    {
        if (!$this->hasMilestones()) {
            $this->update([
                'milestone_summary' => null,
                'milestone_count' => 0,
            ]);
            return;
        }

        $milestones = $this->milestones;
        $totalPercentage = 0;
        $totalAmount = 0;

        foreach ($milestones as $milestone) {
            if ($milestone->amount_type === 'percentage') {
                $totalPercentage += $milestone->amount;
            }
            $totalAmount += $milestone->calculated_amount ?? 0;
        }

        $this->update([
            'milestone_count' => $milestones->count(),
            'milestone_summary' => [
                'total_milestones' => $milestones->count(),
                'total_percentage' => $totalPercentage,
                'total_amount' => $totalAmount,
                'paid_milestones' => $milestones->where('status', 'paid')->count(),
                'pending_milestones' => $milestones->whereIn('status', ['pending', 'due', 'overdue'])->count(),
            ],
        ]);
    }
}
