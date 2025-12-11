<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FraudAlert extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'alert_type',
        'severity',
        'status',
        'alertable_type',
        'alertable_id',
        'user_id',
        'user_type',
        'user_email',
        'user_phone',
        'description',
        'metadata',
        'risk_score',
        'confidence_level',
        'related_bookings',
        'related_transactions',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'user_blocked',
        'automatic_block',
        'action_taken',
    ];

    protected $casts = [
        'metadata' => 'array',
        'related_bookings' => 'array',
        'related_transactions' => 'array',
        'risk_score' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'user_blocked' => 'boolean',
        'automatic_block' => 'boolean',
    ];

    /**
     * Get the alertable entity (Booking, User, Quotation, etc.)
     */
    public function alertable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user associated with this alert
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who reviewed this alert
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope to get pending alerts
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get high severity alerts
     */
    public function scopeHighSeverity($query)
    {
        return $query->whereIn('severity', ['high', 'critical']);
    }

    /**
     * Scope to get unreviewed alerts
     */
    public function scopeUnreviewed($query)
    {
        return $query->whereNull('reviewed_at');
    }

    /**
     * Check if alert is critical
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical' || $this->risk_score >= 80;
    }

    /**
     * Mark as reviewed
     */
    public function markAsReviewed(int $reviewerId, string $notes = null, string $action = null): void
    {
        $this->update([
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
            'action_taken' => $action,
        ]);
    }

    /**
     * Resolve alert
     */
    public function resolve(string $resolution, int $reviewerId, string $notes = null): void
    {
        $this->update([
            'status' => $resolution, // 'resolved', 'false_positive', 'confirmed_fraud'
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }
}
