<?php

namespace App\Services;

use App\Models\Snapshot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SnapshotService
{
    /**
     * Create a snapshot for a model
     */
    public function create(
        Model $model,
        string $snapshotType,
        string $event,
        ?array $changes = null,
        ?array $metadata = null
    ): Snapshot {
        // Get the next version number
        $version = $this->getNextVersion($model);
        
        // Prepare snapshot data
        $snapshotData = [
            'snapshotable_type' => get_class($model),
            'snapshotable_id' => $model->id,
            'snapshot_type' => $snapshotType,
            'event' => $event,
            'version' => $version,
            'data' => $this->prepareData($model),
            'changes' => $changes,
            'metadata' => $this->prepareMetadata($metadata),
            'created_by' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];
        
        return Snapshot::create($snapshotData);
    }
    
    /**
     * Create snapshot for offer
     */
    public function snapshotOffer(Model $offer, string $event, ?array $changes = null): Snapshot
    {
        return $this->create($offer, 'offer', $event, $changes);
    }
    
    /**
     * Create snapshot for quotation
     */
    public function snapshotQuotation(Model $quotation, string $event, ?array $changes = null): Snapshot
    {
        return $this->create($quotation, 'quotation', $event, $changes);
    }
    
    /**
     * Create snapshot for price update
     */
    public function snapshotPriceUpdate(Model $model, array $priceChanges): Snapshot
    {
        $metadata = [
            'old_price' => $priceChanges['old'] ?? null,
            'new_price' => $priceChanges['new'] ?? null,
            'change_amount' => isset($priceChanges['new'], $priceChanges['old']) 
                ? $priceChanges['new'] - $priceChanges['old'] 
                : null,
            'change_percentage' => isset($priceChanges['new'], $priceChanges['old']) && $priceChanges['old'] > 0
                ? (($priceChanges['new'] - $priceChanges['old']) / $priceChanges['old']) * 100
                : null,
        ];
        
        return $this->create($model, 'price_update', 'price_changed', ['price' => $priceChanges], $metadata);
    }
    
    /**
     * Create snapshot for booking confirmation
     */
    public function snapshotBookingConfirmation(Model $booking, ?array $changes = null): Snapshot
    {
        return $this->create($booking, 'booking', 'confirmed', $changes);
    }
    
    /**
     * Create snapshot for commission rule
     */
    public function snapshotCommissionRule(Model $commissionRule, string $event, ?array $changes = null): Snapshot
    {
        return $this->create($commissionRule, 'commission_rule', $event, $changes);
    }
    
    /**
     * Get the next version number for a model
     */
    protected function getNextVersion(Model $model): int
    {
        $latestSnapshot = Snapshot::forModel($model)
            ->orderBy('version', 'desc')
            ->first();
        
        return $latestSnapshot ? $latestSnapshot->version + 1 : 1;
    }
    
    /**
     * Prepare model data for snapshot
     * Converts model to array, including relationships if needed
     */
    protected function prepareData(Model $model): array
    {
        $data = $model->toArray();
        
        // Include important relationships based on model type
        $this->includeRelationships($model, $data);
        
        return $data;
    }
    
    /**
     * Include relevant relationships in snapshot data
     */
    protected function includeRelationships(Model $model, array &$data): void
    {
        $modelClass = get_class($model);
        
        // Offer relationships
        if (str_contains($modelClass, 'Offer')) {
            if ($model->relationLoaded('customer')) {
                $data['customer_snapshot'] = $model->customer->only(['id', 'name', 'email', 'phone']);
            }
            if ($model->relationLoaded('hoarding')) {
                $data['hoarding_snapshot'] = $model->hoarding->only(['id', 'name', 'location', 'price']);
            }
        }
        
        // Quotation relationships
        if (str_contains($modelClass, 'Quotation')) {
            if ($model->relationLoaded('customer')) {
                $data['customer_snapshot'] = $model->customer->only(['id', 'name', 'email']);
            }
            if ($model->relationLoaded('items')) {
                $data['items_snapshot'] = $model->items->toArray();
            }
        }
        
        // Booking relationships
        if (str_contains($modelClass, 'Booking')) {
            if ($model->relationLoaded('customer')) {
                $data['customer_snapshot'] = $model->customer->only(['id', 'name', 'email', 'phone']);
            }
            if ($model->relationLoaded('vendor')) {
                $data['vendor_snapshot'] = $model->vendor->only(['id', 'name', 'email']);
            }
            if ($model->relationLoaded('hoarding')) {
                $data['hoarding_snapshot'] = $model->hoarding->only(['id', 'name', 'location', 'price']);
            }
        }
    }
    
    /**
     * Prepare metadata with additional context
     */
    protected function prepareMetadata(?array $metadata = null): array
    {
        $baseMetadata = [
            'timestamp' => now()->toIso8601String(),
            'environment' => app()->environment(),
        ];
        
        if (Auth::check()) {
            $baseMetadata['user'] = [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'role' => Auth::user()->role ?? null,
            ];
        }
        
        return array_merge($baseMetadata, $metadata ?? []);
    }
    
    /**
     * Get snapshot history for a model
     */
    public function getHistory(Model $model, int $limit = null)
    {
        $query = Snapshot::forModel($model)
            ->orderBy('version', 'desc')
            ->with('creator');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Get latest snapshot for a model
     */
    public function getLatest(Model $model): ?Snapshot
    {
        return Snapshot::getLatest($model);
    }
    
    /**
     * Compare two versions
     */
    public function compareVersions(Model $model, int $version1, int $version2): array
    {
        $snapshot1 = Snapshot::getVersion($model, $version1);
        $snapshot2 = Snapshot::getVersion($model, $version2);
        
        if (!$snapshot1 || !$snapshot2) {
            throw new \Exception('One or both snapshot versions not found');
        }
        
        return $snapshot1->compareWith($snapshot2);
    }
    
    /**
     * Detect changes between old and new attributes
     */
    public function detectChanges(array $oldAttributes, array $newAttributes): array
    {
        $changes = [];
        
        foreach ($newAttributes as $key => $newValue) {
            $oldValue = $oldAttributes[$key] ?? null;
            
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }
        
        return $changes;
    }
    
    /**
     * Create snapshot on model creation
     */
    public function snapshotCreated(Model $model, string $type): Snapshot
    {
        return $this->create($model, $type, 'created');
    }
    
    /**
     * Create snapshot on model update
     */
    public function snapshotUpdated(Model $model, string $type, array $changes): Snapshot
    {
        return $this->create($model, $type, 'updated', $changes);
    }
    
    /**
     * Create snapshot on status change
     */
    public function snapshotStatusChange(Model $model, string $type, string $oldStatus, string $newStatus): Snapshot
    {
        $changes = [
            'status' => [
                'old' => $oldStatus,
                'new' => $newStatus,
            ],
        ];
        
        $metadata = [
            'status_change' => true,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ];
        
        return $this->create($model, $type, 'status_changed', $changes, $metadata);
    }
    
    /**
     * Get snapshots by type
     */
    public function getByType(string $type, int $limit = 100)
    {
        return Snapshot::ofType($type)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->with(['creator', 'snapshotable'])
            ->get();
    }
    
    /**
     * Get snapshots by event
     */
    public function getByEvent(string $event, int $limit = 100)
    {
        return Snapshot::ofEvent($event)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->with(['creator', 'snapshotable'])
            ->get();
    }
    
    /**
     * Get recent snapshots across all types
     */
    public function getRecentSnapshots(int $limit = 50)
    {
        return Snapshot::orderBy('created_at', 'desc')
            ->limit($limit)
            ->with(['creator', 'snapshotable'])
            ->get();
    }
    
    /**
     * Get statistics about snapshots
     */
    public function getStatistics(): array
    {
        return [
            'total_snapshots' => Snapshot::count(),
            'by_type' => Snapshot::selectRaw('snapshot_type, COUNT(*) as count')
                ->groupBy('snapshot_type')
                ->pluck('count', 'snapshot_type')
                ->toArray(),
            'by_event' => Snapshot::selectRaw('event, COUNT(*) as count')
                ->groupBy('event')
                ->pluck('count', 'event')
                ->toArray(),
            'recent_activity' => Snapshot::where('created_at', '>=', now()->subDay())
                ->count(),
            'last_snapshot' => Snapshot::latest('created_at')->first()?->created_at,
        ];
    }
}
