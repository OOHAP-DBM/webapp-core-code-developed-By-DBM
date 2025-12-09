<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingTimelineEvent extends Model
{
    protected $fillable = [
        'booking_id',
        'event_type',
        'event_category',
        'title',
        'description',
        'status',
        'reference_id',
        'reference_type',
        'version',
        'user_id',
        'user_name',
        'metadata',
        'scheduled_at',
        'started_at',
        'completed_at',
        'duration_minutes',
        'order',
        'icon',
        'color',
        'notify_customer',
        'notify_vendor',
        'notified_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'notified_at' => 'datetime',
        'notify_customer' => 'boolean',
        'notify_vendor' => 'boolean',
    ];

    // Event type constants
    const TYPE_ENQUIRY = 'enquiry';
    const TYPE_OFFER = 'offer';
    const TYPE_QUOTATION = 'quotation';
    const TYPE_PAYMENT_HOLD = 'payment_hold';
    const TYPE_PAYMENT_SETTLED = 'payment_settled';
    const TYPE_GRAPHICS = 'graphics';
    const TYPE_PRINTING = 'printing';
    const TYPE_MOUNTING = 'mounting';
    const TYPE_PROOF = 'proof';
    const TYPE_CAMPAIGN_START = 'campaign_start';
    const TYPE_CAMPAIGN_RUNNING = 'campaign_running';
    const TYPE_CAMPAIGN_COMPLETED = 'campaign_completed';

    // Event category constants
    const CATEGORY_BOOKING = 'booking';
    const CATEGORY_PAYMENT = 'payment';
    const CATEGORY_PRODUCTION = 'production';
    const CATEGORY_CAMPAIGN = 'campaign';

    /**
     * Get the booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: By booking
     */
    public function scopeForBooking($query, int $bookingId)
    {
        return $query->where('booking_id', $bookingId);
    }

    /**
     * Scope: By event type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope: By event category
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('event_category', $category);
    }

    /**
     * Scope: By status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Pending events
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Completed events
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: In progress events
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope: Ordered timeline
     */
    public function scopeTimeline($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }

    /**
     * Mark event as started
     */
    public function markAsStarted()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark event as completed
     */
    public function markAsCompleted()
    {
        $startedAt = $this->started_at ?? $this->created_at;
        $duration = $startedAt ? now()->diffInMinutes($startedAt) : null;

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'duration_minutes' => $duration,
        ]);
    }

    /**
     * Mark event as failed
     */
    public function markAsFailed()
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark event as cancelled
     */
    public function markAsCancelled()
    {
        $this->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'secondary',
            'in_progress' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Get event icon
     */
    public function getEventIconAttribute(): string
    {
        if ($this->icon) {
            return $this->icon;
        }

        return match($this->event_type) {
            self::TYPE_ENQUIRY => 'fa-question-circle',
            self::TYPE_OFFER => 'fa-file-alt',
            self::TYPE_QUOTATION => 'fa-file-invoice',
            self::TYPE_PAYMENT_HOLD => 'fa-lock',
            self::TYPE_PAYMENT_SETTLED => 'fa-check-circle',
            self::TYPE_GRAPHICS => 'fa-paint-brush',
            self::TYPE_PRINTING => 'fa-print',
            self::TYPE_MOUNTING => 'fa-hammer',
            self::TYPE_PROOF => 'fa-camera',
            self::TYPE_CAMPAIGN_START => 'fa-play-circle',
            self::TYPE_CAMPAIGN_RUNNING => 'fa-broadcast-tower',
            self::TYPE_CAMPAIGN_COMPLETED => 'fa-flag-checkered',
            default => 'fa-circle',
        };
    }

    /**
     * Get event color
     */
    public function getEventColorAttribute(): string
    {
        if ($this->color) {
            return $this->color;
        }

        return match($this->event_category) {
            self::CATEGORY_BOOKING => 'primary',
            self::CATEGORY_PAYMENT => 'success',
            self::CATEGORY_PRODUCTION => 'info',
            self::CATEGORY_CAMPAIGN => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Check if event is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->scheduled_at || $this->status === 'completed') {
            return false;
        }

        return now()->isAfter($this->scheduled_at);
    }

    /**
     * Get duration formatted
     */
    public function getDurationFormattedAttribute(): ?string
    {
        if (!$this->duration_minutes) {
            return null;
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Check if event is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if event is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if event is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Static: Get event type label
     */
    public static function getEventTypeLabel(string $type): string
    {
        return match($type) {
            self::TYPE_ENQUIRY => 'Enquiry Received',
            self::TYPE_OFFER => 'Offer Created',
            self::TYPE_QUOTATION => 'Quotation Generated',
            self::TYPE_PAYMENT_HOLD => 'Payment Hold',
            self::TYPE_PAYMENT_SETTLED => 'Payment Settled',
            self::TYPE_GRAPHICS => 'Graphics Design',
            self::TYPE_PRINTING => 'Printing',
            self::TYPE_MOUNTING => 'Mounting',
            self::TYPE_PROOF => 'Proof of Display',
            self::TYPE_CAMPAIGN_START => 'Campaign Started',
            self::TYPE_CAMPAIGN_RUNNING => 'Campaign Running',
            self::TYPE_CAMPAIGN_COMPLETED => 'Campaign Completed',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }
}
