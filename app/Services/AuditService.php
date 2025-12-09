<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an audit event
     */
    public function log(
        $model,
        string $action,
        array $oldValues = [],
        array $newValues = [],
        array $options = []
    ): AuditLog {
        $changedFields = $this->getChangedFields($oldValues, $newValues);
        
        $data = [
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id ?? null,
            'user_id' => Auth::id(),
            'user_type' => $this->getUserType(),
            'user_name' => $this->getUserName(),
            'user_email' => $this->getUserEmail(),
            'action' => $action,
            'event' => $options['event'] ?? null,
            'description' => $options['description'] ?? $this->generateDescription($model, $action, $changedFields),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_fields' => $changedFields,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'request_method' => Request::method(),
            'request_url' => Request::fullUrl(),
            'metadata' => $options['metadata'] ?? [],
            'module' => $options['module'] ?? $this->detectModule($model),
            'tags' => $options['tags'] ?? null,
        ];
        
        return AuditLog::create($data);
    }

    /**
     * Log a creation event
     */
    public function logCreated($model, array $options = []): AuditLog
    {
        $newValues = $model->getAttributes();
        
        return $this->log(
            $model,
            'created',
            [],
            $newValues,
            array_merge($options, [
                'description' => $options['description'] ?? "Created {$this->getModelName($model)}",
            ])
        );
    }

    /**
     * Log an update event
     */
    public function logUpdated($model, array $changes, array $options = []): AuditLog
    {
        $oldValues = [];
        $newValues = [];
        
        foreach ($changes as $field => $change) {
            $oldValues[$field] = $change['old'] ?? null;
            $newValues[$field] = $change['new'] ?? null;
        }
        
        return $this->log(
            $model,
            'updated',
            $oldValues,
            $newValues,
            $options
        );
    }

    /**
     * Log a deletion event
     */
    public function logDeleted($model, array $options = []): AuditLog
    {
        $oldValues = $model->getAttributes();
        
        return $this->log(
            $model,
            'deleted',
            $oldValues,
            [],
            array_merge($options, [
                'description' => $options['description'] ?? "Deleted {$this->getModelName($model)}",
            ])
        );
    }

    /**
     * Log a restoration event
     */
    public function logRestored($model, array $options = []): AuditLog
    {
        return $this->log(
            $model,
            'restored',
            [],
            $model->getAttributes(),
            array_merge($options, [
                'description' => $options['description'] ?? "Restored {$this->getModelName($model)}",
            ])
        );
    }

    /**
     * Log a status change
     */
    public function logStatusChange($model, string $oldStatus, string $newStatus, array $options = []): AuditLog
    {
        return $this->log(
            $model,
            'status_changed',
            ['status' => $oldStatus],
            ['status' => $newStatus],
            array_merge($options, [
                'description' => $options['description'] ?? "Changed status from {$oldStatus} to {$newStatus}",
                'metadata' => array_merge($options['metadata'] ?? [], [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]),
            ])
        );
    }

    /**
     * Log a price change
     */
    public function logPriceChange($model, $oldPrice, $newPrice, array $options = []): AuditLog
    {
        $changeAmount = $newPrice - $oldPrice;
        $changePercentage = $oldPrice > 0 ? (($changeAmount / $oldPrice) * 100) : 0;
        
        return $this->log(
            $model,
            'price_changed',
            ['price' => $oldPrice],
            ['price' => $newPrice],
            array_merge($options, [
                'description' => $options['description'] ?? "Changed price from ₹{$oldPrice} to ₹{$newPrice}",
                'metadata' => array_merge($options['metadata'] ?? [], [
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'change_amount' => $changeAmount,
                    'change_percentage' => round($changePercentage, 2),
                ]),
            ])
        );
    }

    /**
     * Log a custom event
     */
    public function logCustomEvent(
        $model,
        string $event,
        string $description,
        array $oldValues = [],
        array $newValues = [],
        array $options = []
    ): AuditLog {
        return $this->log(
            $model,
            'other',
            $oldValues,
            $newValues,
            array_merge($options, [
                'event' => $event,
                'description' => $description,
            ])
        );
    }

    /**
     * Get changed fields from old and new values
     */
    protected function getChangedFields(array $oldValues, array $newValues): array
    {
        $changed = [];
        
        // Check for modified fields
        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;
            
            // Skip timestamps if they're the only change
            if (in_array($key, ['created_at', 'updated_at'])) {
                continue;
            }
            
            if ($oldValue != $newValue) {
                $changed[] = $key;
            }
        }
        
        // Check for removed fields
        foreach ($oldValues as $key => $oldValue) {
            if (!isset($newValues[$key]) && !in_array($key, $changed)) {
                $changed[] = $key;
            }
        }
        
        return $changed;
    }

    /**
     * Generate description for audit log
     */
    protected function generateDescription($model, string $action, array $changedFields): string
    {
        $modelName = $this->getModelName($model);
        
        switch ($action) {
            case 'created':
                return "Created {$modelName}";
            case 'updated':
                $fields = implode(', ', array_map(function($field) {
                    return ucwords(str_replace('_', ' ', $field));
                }, $changedFields));
                return "Updated {$modelName}: {$fields}";
            case 'deleted':
                return "Deleted {$modelName}";
            case 'restored':
                return "Restored {$modelName}";
            default:
                return ucfirst($action) . " {$modelName}";
        }
    }

    /**
     * Get model name
     */
    protected function getModelName($model): string
    {
        $className = class_basename($model);
        return preg_replace('/(?<!^)[A-Z]/', ' $0', $className);
    }

    /**
     * Detect module from model
     */
    protected function detectModule($model): ?string
    {
        $className = class_basename($model);
        
        return match(true) {
            str_contains($className, 'Booking') => 'booking',
            str_contains($className, 'Payment') => 'payment',
            str_contains($className, 'Commission') => 'commission',
            str_contains($className, 'Settlement') => 'settlement',
            str_contains($className, 'Offer') => 'offer',
            str_contains($className, 'Quotation') => 'quotation',
            str_contains($className, 'Enquiry') => 'enquiry',
            str_contains($className, 'Hoarding') => 'hoarding',
            str_contains($className, 'Vendor') => 'vendor',
            str_contains($className, 'User') => 'user',
            str_contains($className, 'Notification') => 'notification',
            default => null,
        };
    }

    /**
     * Get user type
     */
    protected function getUserType(): string
    {
        if (!Auth::check()) {
            return 'system';
        }
        
        $user = Auth::user();
        
        if ($user->hasRole('admin')) {
            return 'admin';
        } elseif ($user->hasRole('vendor')) {
            return 'vendor';
        } elseif ($user->hasRole('customer')) {
            return 'customer';
        }
        
        return 'user';
    }

    /**
     * Get user name
     */
    protected function getUserName(): ?string
    {
        if (!Auth::check()) {
            return 'System';
        }
        
        return Auth::user()->name;
    }

    /**
     * Get user email
     */
    protected function getUserEmail(): ?string
    {
        if (!Auth::check()) {
            return null;
        }
        
        return Auth::user()->email;
    }

    /**
     * Get audit history for model
     */
    public function getHistory($model, int $limit = null)
    {
        return AuditLog::getHistory($model, $limit);
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity(int $limit = 50)
    {
        return AuditLog::recentActivity($limit);
    }

    /**
     * Get user activity
     */
    public function getUserActivity(int $userId, int $limit = 100)
    {
        return AuditLog::getUserActivity($userId, $limit);
    }

    /**
     * Get statistics
     */
    public function getStatistics(array $filters = [])
    {
        return AuditLog::getStatistics($filters);
    }

    /**
     * Search audit logs
     */
    public function search(array $filters = [])
    {
        $query = AuditLog::query()->with(['user', 'auditable']);
        
        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        
        if (isset($filters['module'])) {
            $query->where('module', $filters['module']);
        }
        
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        if (isset($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }
        
        if (isset($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }
        
        if (isset($filters['model_type'])) {
            $query->where('auditable_type', $filters['model_type']);
        }
        
        if (isset($filters['model_id'])) {
            $query->where('auditable_id', $filters['model_id']);
        }
        
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%");
            });
        }
        
        return $query->latest('created_at');
    }
}
