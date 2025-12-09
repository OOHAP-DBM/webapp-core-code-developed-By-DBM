<?php

namespace App\Traits;

use App\Models\Snapshot;
use App\Services\SnapshotService;

trait HasSnapshots
{
    /**
     * Boot the trait
     */
    public static function bootHasSnapshots()
    {
        // Snapshot on creation
        static::created(function ($model) {
            if ($model->shouldSnapshotOnCreate()) {
                $snapshotService = app(SnapshotService::class);
                $snapshotService->snapshotCreated($model, $model->getSnapshotType());
            }
        });
        
        // Snapshot on update
        static::updated(function ($model) {
            if ($model->shouldSnapshotOnUpdate()) {
                $snapshotService = app(SnapshotService::class);
                
                // Detect what changed
                $changes = $snapshotService->detectChanges(
                    $model->getOriginal(),
                    $model->getAttributes()
                );
                
                if (!empty($changes)) {
                    // Check for price changes
                    if (isset($changes['price'])) {
                        $snapshotService->snapshotPriceUpdate($model, $changes['price']);
                    }
                    
                    // Check for status changes
                    if (isset($changes['status'])) {
                        $snapshotService->snapshotStatusChange(
                            $model,
                            $model->getSnapshotType(),
                            $changes['status']['old'],
                            $changes['status']['new']
                        );
                    }
                    
                    // Regular update snapshot
                    $snapshotService->snapshotUpdated($model, $model->getSnapshotType(), $changes);
                }
            }
        });
    }
    
    /**
     * Get the snapshot type for this model
     */
    public function getSnapshotType(): string
    {
        // Can be overridden in model
        return property_exists($this, 'snapshotType') 
            ? $this->snapshotType 
            : strtolower(class_basename($this));
    }
    
    /**
     * Should snapshot on create?
     */
    public function shouldSnapshotOnCreate(): bool
    {
        return property_exists($this, 'snapshotOnCreate') 
            ? $this->snapshotOnCreate 
            : true;
    }
    
    /**
     * Should snapshot on update?
     */
    public function shouldSnapshotOnUpdate(): bool
    {
        return property_exists($this, 'snapshotOnUpdate') 
            ? $this->snapshotOnUpdate 
            : true;
    }
    
    /**
     * Relationship to snapshots
     */
    public function snapshots()
    {
        return $this->morphMany(Snapshot::class, 'snapshotable')
            ->orderBy('version', 'desc');
    }
    
    /**
     * Get latest snapshot
     */
    public function getLatestSnapshot(): ?Snapshot
    {
        return $this->snapshots()->first();
    }
    
    /**
     * Get snapshot history
     */
    public function getSnapshotHistory()
    {
        return $this->snapshots()->orderBy('version', 'asc')->get();
    }
    
    /**
     * Get snapshot by version
     */
    public function getSnapshotVersion(int $version): ?Snapshot
    {
        return $this->snapshots()->where('version', $version)->first();
    }
    
    /**
     * Manually create a snapshot
     */
    public function createSnapshot(string $event, ?array $changes = null, ?array $metadata = null): Snapshot
    {
        $snapshotService = app(SnapshotService::class);
        return $snapshotService->create($this, $this->getSnapshotType(), $event, $changes, $metadata);
    }
    
    /**
     * Compare with a previous version
     */
    public function compareWithVersion(int $version): array
    {
        $snapshot = $this->getSnapshotVersion($version);
        
        if (!$snapshot) {
            throw new \Exception("Snapshot version {$version} not found");
        }
        
        $currentData = $this->toArray();
        $snapshotData = $snapshot->data;
        
        $differences = [];
        
        foreach ($currentData as $key => $value) {
            if (!isset($snapshotData[$key]) || $snapshotData[$key] !== $value) {
                $differences[$key] = [
                    'snapshot' => $snapshotData[$key] ?? null,
                    'current' => $value,
                ];
            }
        }
        
        return $differences;
    }
}
