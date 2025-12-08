<?php

namespace Modules\Enquiries\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'hoarding_id',
        'preferred_start_date',
        'preferred_end_date',
        'duration_type',
        'message',
        'status',
        'snapshot',
    ];

    protected $casts = [
        'preferred_start_date' => 'date',
        'preferred_end_date' => 'date',
        'snapshot' => 'array',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Duration type constants
     */
    const DURATION_DAYS = 'days';
    const DURATION_WEEKS = 'weeks';
    const DURATION_MONTHS = 'months';

    /**
     * Get the customer who made the enquiry
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the hoarding for this enquiry
     */
    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    /**
     * Scope for pending enquiries
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for accepted enquiries
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Check if enquiry is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if enquiry is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Check if enquiry is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Calculate duration in days
     */
    public function getDurationInDays(): int
    {
        return $this->preferred_start_date->diffInDays($this->preferred_end_date);
    }

    /**
     * Get snapshot value by key
     */
    public function getSnapshotValue(string $key, $default = null)
    {
        return $this->snapshot[$key] ?? $default;
    }
}
