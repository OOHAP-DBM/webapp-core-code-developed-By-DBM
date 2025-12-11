<?php

namespace Modules\DOOH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Booking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * DOOH Creative Schedule Model
 * PROMPT 67: Manages scheduling of creatives on DOOH screens
 */
class DOOHCreativeSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dooh_creative_schedules';

    protected $fillable = [
        'creative_id',
        'dooh_screen_id',
        'booking_id',
        'customer_id',
        'schedule_name',
        'description',
        'start_date',
        'end_date',
        'total_days',
        'time_slots',
        'daily_start_time',
        'daily_end_time',
        'slots_per_loop',
        'loop_frequency',
        'displays_per_hour',
        'displays_per_day',
        'total_displays',
        'priority',
        'position_in_loop',
        'active_days',
        'is_recurring',
        'cost_per_display',
        'daily_cost',
        'total_cost',
        'validation_status',
        'validation_errors',
        'availability_confirmed',
        'availability_checked_at',
        'approved_by',
        'approved_at',
        'approval_notes',
        'status',
        'scheduled_start_at',
        'scheduled_end_at',
        'activated_at',
        'completed_at',
        'paused_at',
        'cancelled_at',
        'cancellation_reason',
        'actual_displays',
        'completion_rate',
        'daily_stats',
        'conflict_warnings',
        'auto_resolve_conflicts',
        'conflict_resolution_priority',
        'metadata',
        'customer_notes',
        'admin_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'integer',
        'time_slots' => 'array',
        'daily_start_time' => 'datetime:H:i:s',
        'daily_end_time' => 'datetime:H:i:s',
        'slots_per_loop' => 'integer',
        'loop_frequency' => 'integer',
        'displays_per_hour' => 'integer',
        'displays_per_day' => 'integer',
        'total_displays' => 'integer',
        'priority' => 'integer',
        'position_in_loop' => 'integer',
        'active_days' => 'array',
        'is_recurring' => 'boolean',
        'cost_per_display' => 'decimal:4',
        'daily_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'availability_confirmed' => 'boolean',
        'availability_checked_at' => 'datetime',
        'approved_at' => 'datetime',
        'scheduled_start_at' => 'datetime',
        'scheduled_end_at' => 'datetime',
        'activated_at' => 'datetime',
        'completed_at' => 'datetime',
        'paused_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'actual_displays' => 'integer',
        'completion_rate' => 'decimal:2',
        'daily_stats' => 'array',
        'conflict_warnings' => 'array',
        'auto_resolve_conflicts' => 'boolean',
        'conflict_resolution_priority' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    // Validation status
    const VALIDATION_PENDING = 'pending';
    const VALIDATION_CHECKING = 'checking_availability';
    const VALIDATION_APPROVED = 'approved';
    const VALIDATION_REJECTED = 'rejected';
    const VALIDATION_CONFLICTS = 'conflicts_found';

    /**
     * Relationships
     */
    public function creative(): BelongsTo
    {
        return $this->belongsTo(DOOHCreative::class, 'creative_id');
    }

    public function doohScreen(): BelongsTo
    {
        return $this->belongsTo(DOOHScreen::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('status', [
            self::STATUS_APPROVED,
            self::STATUS_ACTIVE
        ]);
    }

    public function scopeForScreen($query, int $screenId)
    {
        return $query->where('dooh_screen_id', $screenId);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });
    }

    public function scopeOverlapping($query, $startDate, $endDate, $screenId, ?int $excludeId = null)
    {
        $query->where('dooh_screen_id', $screenId)
            ->whereIn('status', [
                self::STATUS_APPROVED,
                self::STATUS_ACTIVE,
                self::STATUS_PENDING_APPROVAL
            ])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            });
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query;
    }

    /**
     * Helper methods
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isApproved(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_ACTIVE]);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function hasConflicts(): bool
    {
        return !empty($this->conflict_warnings);
    }

    /**
     * Calculate total days between start and end date
     */
    public function calculateTotalDays(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Calculate displays per day based on time slots and frequency
     */
    public function calculateDisplaysPerDay(): int
    {
        if (empty($this->time_slots)) {
            // Default: 24 hours with frequency
            return $this->displays_per_hour * 24;
        }
        
        $totalMinutes = 0;
        foreach ($this->time_slots as $slot) {
            $start = Carbon::parse($slot['start_time']);
            $end = Carbon::parse($slot['end_time']);
            $totalMinutes += $start->diffInMinutes($end);
        }
        
        $hours = $totalMinutes / 60;
        return (int) ($hours * $this->displays_per_hour);
    }

    /**
     * Calculate total displays for entire schedule period
     */
    public function calculateTotalDisplays(): int
    {
        $days = $this->calculateTotalDays();
        
        // If specific days of week are set
        if (!empty($this->active_days)) {
            $activeDaysCount = 0;
            $period = CarbonPeriod::create($this->start_date, $this->end_date);
            
            foreach ($period as $date) {
                if (in_array($date->dayOfWeek, $this->active_days)) {
                    $activeDaysCount++;
                }
            }
            
            return $activeDaysCount * $this->displays_per_day;
        }
        
        return $days * $this->displays_per_day;
    }

    /**
     * Calculate total cost
     */
    public function calculateTotalCost(): float
    {
        return $this->calculateTotalDisplays() * (float) $this->cost_per_display;
    }

    /**
     * Calculate daily cost
     */
    public function calculateDailyCost(): float
    {
        return $this->displays_per_day * (float) $this->cost_per_display;
    }

    /**
     * Check if schedule is currently running
     */
    public function isCurrentlyRunning(): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        
        $now = now();
        return $now->greaterThanOrEqualTo($this->start_date) 
            && $now->lessThanOrEqualTo($this->end_date);
    }

    /**
     * Check if schedule starts in future
     */
    public function isUpcoming(): bool
    {
        return $this->isApproved() && now()->lessThan($this->start_date);
    }

    /**
     * Check if schedule has expired
     */
    public function hasExpired(): bool
    {
        return now()->greaterThan($this->end_date);
    }

    /**
     * Get days remaining
     */
    public function getDaysRemainingAttribute(): int
    {
        if (!$this->isActive() || $this->hasExpired()) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->end_date, false));
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute(): float
    {
        if (!$this->isActive()) {
            return 0;
        }
        
        $totalDays = $this->calculateTotalDays();
        $elapsedDays = $this->start_date->diffInDays(now()) + 1;
        
        return min(100, ($elapsedDays / $totalDays) * 100);
    }

    /**
     * Check if schedule is running in current time slot
     */
    public function isInCurrentTimeSlot(): bool
    {
        if (!$this->isCurrentlyRunning()) {
            return false;
        }
        
        $now = now();
        $currentTime = $now->format('H:i:s');
        
        // Check if we have specific time slots
        if (!empty($this->time_slots)) {
            foreach ($this->time_slots as $slot) {
                if ($currentTime >= $slot['start_time'] && $currentTime <= $slot['end_time']) {
                    return true;
                }
            }
            return false;
        }
        
        // Check daily range
        if ($this->daily_start_time && $this->daily_end_time) {
            return $currentTime >= $this->daily_start_time->format('H:i:s')
                && $currentTime <= $this->daily_end_time->format('H:i:s');
        }
        
        return true; // 24/7 if no restrictions
    }

    /**
     * Approve schedule
     */
    public function approve(?int $approverId = null, ?string $notes = null): bool
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'validation_status' => self::VALIDATION_APPROVED,
            'approved_by' => $approverId ?? auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $notes,
            'scheduled_start_at' => Carbon::parse($this->start_date)->startOfDay(),
            'scheduled_end_at' => Carbon::parse($this->end_date)->endOfDay(),
        ]);
        
        return true;
    }

    /**
     * Activate schedule (when start date arrives)
     */
    public function activate(): bool
    {
        if (!$this->isApproved()) {
            return false;
        }
        
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'activated_at' => now(),
        ]);
        
        return true;
    }

    /**
     * Pause schedule
     */
    public function pause(): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        
        $this->update([
            'status' => self::STATUS_PAUSED,
            'paused_at' => now(),
        ]);
        
        return true;
    }

    /**
     * Resume schedule
     */
    public function resume(): bool
    {
        if ($this->status !== self::STATUS_PAUSED) {
            return false;
        }
        
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'paused_at' => null,
        ]);
        
        return true;
    }

    /**
     * Complete schedule
     */
    public function complete(): bool
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'completion_rate' => $this->calculateCompletionRate(),
        ]);
        
        return true;
    }

    /**
     * Cancel schedule
     */
    public function cancel(string $reason): bool
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
        
        return true;
    }

    /**
     * Calculate completion rate
     */
    public function calculateCompletionRate(): float
    {
        if ($this->total_displays == 0) {
            return 0;
        }
        
        return min(100, ($this->actual_displays / $this->total_displays) * 100);
    }

    /**
     * Record display (called by playback system)
     */
    public function recordDisplay(): void
    {
        $this->increment('actual_displays');
        
        // Update daily stats
        $today = now()->format('Y-m-d');
        $stats = $this->daily_stats ?? [];
        
        if (!isset($stats[$today])) {
            $stats[$today] = ['displays' => 0, 'date' => $today];
        }
        
        $stats[$today]['displays']++;
        
        $this->update(['daily_stats' => $stats]);
    }

    /**
     * Get scheduled time slots for a specific date
     */
    public function getTimeSlotsForDate(Carbon $date): array
    {
        // Check if date is within range
        if ($date->lt($this->start_date) || $date->gt($this->end_date)) {
            return [];
        }
        
        // Check day of week if recurring
        if (!empty($this->active_days) && !in_array($date->dayOfWeek, $this->active_days)) {
            return [];
        }
        
        return $this->time_slots ?? [];
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($schedule) {
            // Calculate values before creation
            $schedule->total_days = $schedule->start_date->diffInDays($schedule->end_date) + 1;
            $schedule->displays_per_day = $schedule->displays_per_day ?? $schedule->calculateDisplaysPerDay();
            $schedule->total_displays = $schedule->calculateTotalDisplays();
            $schedule->daily_cost = $schedule->calculateDailyCost();
            $schedule->total_cost = $schedule->calculateTotalCost();
        });
        
        static::updating(function ($schedule) {
            // Recalculate if dates or frequency changed
            if ($schedule->isDirty(['start_date', 'end_date', 'displays_per_hour', 'cost_per_display'])) {
                $schedule->total_days = $schedule->start_date->diffInDays($schedule->end_date) + 1;
                $schedule->total_displays = $schedule->calculateTotalDisplays();
                $schedule->daily_cost = $schedule->calculateDailyCost();
                $schedule->total_cost = $schedule->calculateTotalCost();
            }
        });
    }
}
