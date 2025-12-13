<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminOverride extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'overridable_type',
        'overridable_id',
        'action',
        'field_changed',
        'original_data',
        'new_data',
        'changes',
        'reason',
        'notes',
        'is_reverted',
        'reverted_at',
        'reverted_by',
        'revert_reason',
        'revert_data',
        'ip_address',
        'user_agent',
        'override_type',
        'severity',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'original_data' => 'array',
        'new_data' => 'array',
        'changes' => 'array',
        'metadata' => 'array',
        'revert_data' => 'array',
        'is_reverted' => 'boolean',
        'reverted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the overridable model.
     */
    public function overridable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the admin user who performed the override.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the admin user who reverted the override.
     */
    public function reverter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reverted_by');
    }

    /**
     * Scope: Filter by override type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('override_type', $type);
    }

    /**
     * Scope: Filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by reverted status.
     */
    public function scopeReverted($query)
    {
        return $query->where('is_reverted', true);
    }

    /**
     * Scope: Filter by not reverted.
     */
    public function scopeNotReverted($query)
    {
        return $query->where('is_reverted', false);
    }

    /**
     * Scope: Filter by severity.
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope: Recent overrides (last 30 days).
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    /**
     * Get a human-readable summary of the override.
     */
    public function getSummaryAttribute(): string
    {
        $action = ucfirst(str_replace('_', ' ', $this->action));
        $type = ucfirst($this->override_type);
        
        return "{$action} on {$type} #{$this->overridable_id} by {$this->user_name}";
    }

    /**
     * Get formatted changes for display.
     */
    public function getFormattedChangesAttribute(): array
    {
        $formatted = [];
        
        foreach ($this->changes ?? [] as $field => $change) {
            $formatted[] = [
                'field' => ucfirst(str_replace('_', ' ', $field)),
                'old' => $change['old'] ?? 'N/A',
                'new' => $change['new'] ?? 'N/A',
            ];
        }
        
        return $formatted;
    }

    /**
     * Check if the override can be reverted.
     */
    public function canRevert(): bool
    {
        return !$this->is_reverted && $this->overridable()->exists();
    }

    /**
     * Mark the override as reverted.
     */
    public function markReverted(User $admin, string $reason, array $revertData = []): void
    {
        $this->update([
            'is_reverted' => true,
            'reverted_at' => now(),
            'reverted_by' => $admin->id,
            'revert_reason' => $reason,
            'revert_data' => $revertData,
        ]);
    }
}
