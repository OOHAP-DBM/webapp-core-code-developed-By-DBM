<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    /**
     * Audit logs are immutable - no updates allowed
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'user_id',
        'user_type',
        'user_name',
        'user_email',
        'action',
        'event',
        'description',
        'old_values',
        'new_values',
        'changed_fields',
        'ip_address',
        'user_agent',
        'request_method',
        'request_url',
        'metadata',
        'module',
        'tags',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Boot method to enforce immutability
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent updates
        static::updating(function ($model) {
            throw new \Exception('Audit logs are immutable and cannot be updated.');
        });

        // Prevent deletes
        static::deleting(function ($model) {
            throw new \Exception('Audit logs are immutable and cannot be deleted.');
        });
    }

    /**
     * Get the auditable model
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Filter by action
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Filter by module
     */
    public function scopeOfModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by auditable model
     */
    public function scopeForModel($query, string $type, int $id)
    {
        return $query->where('auditable_type', $type)
                     ->where('auditable_id', $id);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }
        return $query;
    }

    /**
     * Scope: Recent logs
     */
    public function scopeRecent($query, int $limit = 100)
    {
        return $query->latest('created_at')->limit($limit);
    }

    /**
     * Get human-readable action label
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'status_changed' => 'Status Changed',
            'price_changed' => 'Price Changed',
            'other' => ucfirst($this->event ?? 'Other'),
            default => 'Unknown',
        };
    }

    /**
     * Get human-readable model name
     */
    public function getModelNameAttribute(): string
    {
        if (!$this->auditable_type) {
            return 'Unknown';
        }

        $parts = explode('\\', $this->auditable_type);
        $className = end($parts);
        
        // Convert CamelCase to Title Case with spaces
        return preg_replace('/(?<!^)[A-Z]/', ' $0', $className);
    }

    /**
     * Get changes summary for display
     */
    public function getChangesSummaryAttribute(): array
    {
        $summary = [];
        
        if ($this->changed_fields && is_array($this->changed_fields)) {
            foreach ($this->changed_fields as $field) {
                $oldValue = $this->old_values[$field] ?? null;
                $newValue = $this->new_values[$field] ?? null;
                
                $summary[] = [
                    'field' => $this->formatFieldName($field),
                    'old' => $this->formatValue($oldValue),
                    'new' => $this->formatValue($newValue),
                ];
            }
        }
        
        return $summary;
    }

    /**
     * Format field name for display
     */
    protected function formatFieldName(string $field): string
    {
        // Convert snake_case to Title Case
        return ucwords(str_replace('_', ' ', $field));
    }

    /**
     * Format value for display
     */
    protected function formatValue($value): string
    {
        if (is_null($value)) {
            return '(empty)';
        }
        
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        
        if (is_array($value)) {
            return json_encode($value);
        }
        
        if (is_numeric($value) && strlen($value) > 10) {
            return number_format($value, 2);
        }
        
        return (string) $value;
    }

    /**
     * Get tags as array
     */
    public function getTagsArrayAttribute(): array
    {
        if (empty($this->tags)) {
            return [];
        }
        
        return array_map('trim', explode(',', $this->tags));
    }

    /**
     * Check if log is from today
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->created_at->isToday();
    }

    /**
     * Check if log is from this week
     */
    public function getIsThisWeekAttribute(): bool
    {
        return $this->created_at->isCurrentWeek();
    }

    /**
     * Get relative time
     */
    public function getRelativeTimeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Static method: Get recent activity
     */
    public static function recentActivity(int $limit = 50)
    {
        return static::with(['user', 'auditable'])
                     ->latest('created_at')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Static method: Get activity for model
     */
    public static function getHistory($model, int $limit = null)
    {
        $query = static::forModel(get_class($model), $model->id)
                       ->with('user')
                       ->latest('created_at');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    /**
     * Static method: Get user activity
     */
    public static function getUserActivity(int $userId, int $limit = 100)
    {
        return static::byUser($userId)
                     ->with(['auditable'])
                     ->latest('created_at')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Static method: Get statistics
     */
    public static function getStatistics(array $filters = [])
    {
        $query = static::query();
        
        // Apply filters
        if (isset($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }
        if (isset($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }
        if (isset($filters['module'])) {
            $query->where('module', $filters['module']);
        }
        
        $total = $query->count();
        
        $byAction = static::selectRaw('action, COUNT(*) as count')
                          ->groupBy('action')
                          ->pluck('count', 'action');
        
        $byModule = static::selectRaw('module, COUNT(*) as count')
                          ->whereNotNull('module')
                          ->groupBy('module')
                          ->pluck('count', 'module');
        
        $byUser = static::selectRaw('user_id, user_name, COUNT(*) as count')
                        ->whereNotNull('user_id')
                        ->groupBy('user_id', 'user_name')
                        ->orderByDesc('count')
                        ->limit(10)
                        ->get();
        
        return [
            'total' => $total,
            'by_action' => $byAction,
            'by_module' => $byModule,
            'top_users' => $byUser,
            'today' => static::whereDate('created_at', today())->count(),
            'this_week' => static::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => static::whereMonth('created_at', now()->month)->count(),
        ];
    }
}
