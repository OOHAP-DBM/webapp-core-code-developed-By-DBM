<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FraudEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'event_category',
        'user_id',
        'ip_address',
        'user_agent',
        'session_id',
        'event_data',
        'is_suspicious',
        'risk_score',
        'eventable_type',
        'eventable_id',
        'fraud_alert_id',
        'country',
        'city',
    ];

    protected $casts = [
        'event_data' => 'array',
        'is_suspicious' => 'boolean',
        'risk_score' => 'decimal:2',
    ];

    /**
     * Get the eventable entity
     */
    public function eventable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who triggered this event
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the fraud alert if this event triggered one
     */
    public function fraudAlert(): BelongsTo
    {
        return $this->belongsTo(FraudAlert::class);
    }

    /**
     * Scope for suspicious events
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * Scope for events by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('event_category', $category);
    }

    /**
     * Scope for recent events
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}
