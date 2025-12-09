<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearch extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'latitude',
        'longitude',
        'location_name',
        'radius_km',
        'filters',
        'results_count',
        'last_executed_at',
        'execution_count',
        'notify_new_results',
        'last_notified_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'last_executed_at' => 'datetime',
        'last_notified_at' => 'datetime',
        'notify_new_results' => 'boolean',
    ];

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for user's searches
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for notification enabled
     */
    public function scopeWithNotifications($query)
    {
        return $query->where('notify_new_results', true);
    }

    /**
     * Mark as executed
     */
    public function markExecuted(int $resultsCount): void
    {
        $this->increment('execution_count');
        $this->update([
            'last_executed_at' => now(),
            'results_count' => $resultsCount,
        ]);
    }

    /**
     * Mark as notified
     */
    public function markNotified(): void
    {
        $this->update(['last_notified_at' => now()]);
    }

    /**
     * Get formatted location
     */
    public function getFormattedLocationAttribute(): string
    {
        if ($this->location_name) {
            return $this->location_name;
        }

        if ($this->latitude && $this->longitude) {
            return sprintf('%.4f, %.4f', $this->latitude, $this->longitude);
        }

        return 'Unknown location';
    }

    /**
     * Check if needs notification
     */
    public function needsNotification(): bool
    {
        if (!$this->notify_new_results) {
            return false;
        }

        // Notify if never notified or last notified more than 24 hours ago
        return !$this->last_notified_at ||
            $this->last_notified_at->lt(now()->subHours(24));
    }
}
