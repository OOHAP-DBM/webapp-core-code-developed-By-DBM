<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Snapshot extends Model
{
    /**
     * Immutable: Snapshots cannot be updated or deleted
     */
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null; // No updated_at column
    
    protected $fillable = [
        'snapshotable_type',
        'snapshotable_id',
        'snapshot_type',
        'event',
        'version',
        'data',
        'changes',
        'metadata',
        'created_by',
        'ip_address',
        'user_agent',
    ];
    
    protected $casts = [
        'data' => 'array',
        'changes' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];
    
    /**
     * Prevent updates to snapshots (immutability)
     */
    public static function boot()
    {
        parent::boot();
        
        static::updating(function ($snapshot) {
            throw new \Exception('Snapshots are immutable and cannot be updated.');
        });
        
        static::deleting(function ($snapshot) {
            throw new \Exception('Snapshots are immutable and cannot be deleted.');
        });
    }
    
    /**
     * Get the parent snapshotable model
     */
    public function snapshotable(): MorphTo
    {
        return $this->morphTo();
    }
    
    /**
     * Get the user who created this snapshot
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Scope: Get snapshots by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('snapshot_type', $type);
    }
    
    /**
     * Scope: Get snapshots by event
     */
    public function scopeOfEvent($query, string $event)
    {
        return $query->where('event', $event);
    }
    
    /**
     * Scope: Get snapshots for a specific model
     */
    public function scopeForModel($query, Model $model)
    {
        return $query->where('snapshotable_type', get_class($model))
                    ->where('snapshotable_id', $model->id);
    }
    
    /**
     * Get the latest snapshot for this entity
     */
    public static function getLatest(Model $model): ?self
    {
        return static::forModel($model)
            ->latest('created_at')
            ->first();
    }
    
    /**
     * Get all snapshots for an entity
     */
    public static function getHistory(Model $model)
    {
        return static::forModel($model)
            ->orderBy('version', 'asc')
            ->get();
    }
    
    /**
     * Get snapshot by version number
     */
    public static function getVersion(Model $model, int $version): ?self
    {
        return static::forModel($model)
            ->where('version', $version)
            ->first();
    }
    
    /**
     * Compare this snapshot with another
     */
    public function compareWith(Snapshot $other): array
    {
        $thisData = $this->data;
        $otherData = $other->data;
        
        $differences = [];
        
        foreach ($thisData as $key => $value) {
            if (!isset($otherData[$key]) || $otherData[$key] !== $value) {
                $differences[$key] = [
                    'old' => $otherData[$key] ?? null,
                    'new' => $value,
                ];
            }
        }
        
        // Check for removed keys
        foreach ($otherData as $key => $value) {
            if (!isset($thisData[$key]) && !isset($differences[$key])) {
                $differences[$key] = [
                    'old' => $value,
                    'new' => null,
                ];
            }
        }
        
        return $differences;
    }
    
    /**
     * Restore the snapshotted entity to this state
     * WARNING: This creates a new snapshot, doesn't actually restore
     */
    public function restore(): Model
    {
        $modelClass = $this->snapshotable_type;
        $model = $modelClass::find($this->snapshotable_id);
        
        if (!$model) {
            throw new \Exception("Model {$modelClass}#{$this->snapshotable_id} not found");
        }
        
        // Update model with snapshot data
        foreach ($this->data as $key => $value) {
            if ($model->isFillable($key)) {
                $model->$key = $value;
            }
        }
        
        $model->save();
        
        return $model;
    }
    
    /**
     * Get human-readable snapshot type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->snapshot_type) {
            'offer' => 'Offer',
            'quotation' => 'Quotation',
            'price_update' => 'Price Update',
            'booking' => 'Booking Confirmation',
            'commission_rule' => 'Commission Rule',
            default => ucfirst(str_replace('_', ' ', $this->snapshot_type)),
        };
    }
    
    /**
     * Get human-readable event label
     */
    public function getEventLabelAttribute(): string
    {
        return match($this->event) {
            'created' => 'Created',
            'updated' => 'Updated',
            'price_changed' => 'Price Changed',
            'confirmed' => 'Confirmed',
            'status_changed' => 'Status Changed',
            'cancelled' => 'Cancelled',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst(str_replace('_', ' ', $this->event)),
        };
    }
    
    /**
     * Check if this is the first snapshot (version 1)
     */
    public function isFirstVersion(): bool
    {
        return $this->version === 1;
    }
    
    /**
     * Get the previous version snapshot
     */
    public function previous(): ?self
    {
        if ($this->version <= 1) {
            return null;
        }
        
        return static::forModel($this->snapshotable)
            ->where('version', $this->version - 1)
            ->first();
    }
    
    /**
     * Get the next version snapshot
     */
    public function next(): ?self
    {
        return static::forModel($this->snapshotable)
            ->where('version', $this->version + 1)
            ->first();
    }
}
