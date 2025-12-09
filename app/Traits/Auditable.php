<?php

namespace App\Traits;

use App\Models\AuditLog;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Auditable
{
    /**
     * Boot the trait
     */
    protected static function bootAuditable()
    {
        // Log creation
        if (static::shouldAuditOnCreate()) {
            static::created(function ($model) {
                $model->auditCreated();
            });
        }

        // Log updates
        if (static::shouldAuditOnUpdate()) {
            static::updated(function ($model) {
                $model->auditUpdated();
            });
        }

        // Log deletion
        if (static::shouldAuditOnDelete()) {
            static::deleted(function ($model) {
                $model->auditDeleted();
            });
        }

        // Log restoration (for soft deletes)
        if (method_exists(static::class, 'restored') && static::shouldAuditOnRestore()) {
            static::restored(function ($model) {
                $model->auditRestored();
            });
        }
    }

    /**
     * Get audit logs for this model
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')
                    ->latest('created_at');
    }

    /**
     * Audit creation event
     */
    protected function auditCreated()
    {
        $service = app(AuditService::class);
        $service->logCreated($this, $this->getAuditOptions());
    }

    /**
     * Audit update event
     */
    protected function auditUpdated()
    {
        $changes = $this->getChanges();
        
        if (empty($changes)) {
            return;
        }

        // Remove timestamps from changes unless specifically tracked
        if (!$this->shouldAuditTimestamps()) {
            unset($changes['created_at'], $changes['updated_at']);
        }

        if (empty($changes)) {
            return;
        }

        $service = app(AuditService::class);
        
        // Detect special changes
        if (isset($changes['status']) && $this->shouldAuditStatusChanges()) {
            $oldStatus = $this->getOriginal('status');
            $newStatus = $changes['status'];
            $service->logStatusChange($this, $oldStatus, $newStatus, $this->getAuditOptions());
            
            // Remove status from changes to avoid duplicate logging
            unset($changes['status']);
        }

        // Detect price changes
        $priceFields = $this->getPriceFields();
        foreach ($priceFields as $priceField) {
            if (isset($changes[$priceField])) {
                $oldPrice = $this->getOriginal($priceField);
                $newPrice = $changes[$priceField];
                $service->logPriceChange($this, $oldPrice, $newPrice, array_merge($this->getAuditOptions(), [
                    'metadata' => ['field' => $priceField],
                ]));
                
                // Remove price field from changes to avoid duplicate logging
                unset($changes[$priceField]);
            }
        }

        // Log remaining changes
        if (!empty($changes)) {
            $formattedChanges = [];
            foreach ($changes as $field => $newValue) {
                $formattedChanges[$field] = [
                    'old' => $this->getOriginal($field),
                    'new' => $newValue,
                ];
            }
            
            $service->logUpdated($this, $formattedChanges, $this->getAuditOptions());
        }
    }

    /**
     * Audit deletion event
     */
    protected function auditDeleted()
    {
        $service = app(AuditService::class);
        $service->logDeleted($this, $this->getAuditOptions());
    }

    /**
     * Audit restoration event
     */
    protected function auditRestored()
    {
        $service = app(AuditService::class);
        $service->logRestored($this, $this->getAuditOptions());
    }

    /**
     * Manual audit log
     */
    public function audit(string $action, array $oldValues = [], array $newValues = [], array $options = [])
    {
        $service = app(AuditService::class);
        return $service->log($this, $action, $oldValues, $newValues, array_merge($this->getAuditOptions(), $options));
    }

    /**
     * Get audit options
     */
    protected function getAuditOptions(): array
    {
        return [
            'module' => $this->getAuditModule(),
            'tags' => $this->getAuditTags(),
        ];
    }

    /**
     * Get audit module name
     */
    protected function getAuditModule(): ?string
    {
        if (property_exists($this, 'auditModule')) {
            return $this->auditModule;
        }
        
        return null;
    }

    /**
     * Get audit tags
     */
    protected function getAuditTags(): ?string
    {
        if (property_exists($this, 'auditTags')) {
            return is_array($this->auditTags) 
                ? implode(',', $this->auditTags) 
                : $this->auditTags;
        }
        
        return null;
    }

    /**
     * Get price fields to track
     */
    protected function getPriceFields(): array
    {
        if (property_exists($this, 'priceFields')) {
            return $this->priceFields;
        }
        
        // Default price field names
        return ['price', 'amount', 'total', 'weekly_price', 'monthly_price', 'commission_value'];
    }

    /**
     * Should audit on create
     */
    protected static function shouldAuditOnCreate(): bool
    {
        return property_exists(static::class, 'auditOnCreate') 
            ? static::$auditOnCreate 
            : true;
    }

    /**
     * Should audit on update
     */
    protected static function shouldAuditOnUpdate(): bool
    {
        return property_exists(static::class, 'auditOnUpdate') 
            ? static::$auditOnUpdate 
            : true;
    }

    /**
     * Should audit on delete
     */
    protected static function shouldAuditOnDelete(): bool
    {
        return property_exists(static::class, 'auditOnDelete') 
            ? static::$auditOnDelete 
            : true;
    }

    /**
     * Should audit on restore
     */
    protected static function shouldAuditOnRestore(): bool
    {
        return property_exists(static::class, 'auditOnRestore') 
            ? static::$auditOnRestore 
            : true;
    }

    /**
     * Should audit timestamps
     */
    protected function shouldAuditTimestamps(): bool
    {
        return property_exists($this, 'auditTimestamps') 
            ? $this->auditTimestamps 
            : false;
    }

    /**
     * Should audit status changes specially
     */
    protected function shouldAuditStatusChanges(): bool
    {
        return property_exists($this, 'auditStatusChanges') 
            ? $this->auditStatusChanges 
            : true;
    }

    /**
     * Get latest audit log
     */
    public function getLatestAuditLog()
    {
        return $this->auditLogs()->first();
    }

    /**
     * Get audit history
     */
    public function getAuditHistory(int $limit = null)
    {
        $query = $this->auditLogs()->with('user');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    /**
     * Get audit logs by action
     */
    public function getAuditLogsByAction(string $action, int $limit = null)
    {
        $query = $this->auditLogs()->where('action', $action);
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
}
