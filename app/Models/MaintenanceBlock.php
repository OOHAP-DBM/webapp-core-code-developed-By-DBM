<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * PROMPT 102: Admin Blocking Periods (Maintenance/Repairs)
 * 
 * Model for maintenance blocks that make hoardings unavailable for booking
 * Admin or Vendor can create blocks for maintenance, repairs, inspections, etc.
 * 
 * @property int $id
 * @property int $hoarding_id
 * @property int $created_by
 * @property string $title
 * @property string|null $description
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property string $status (active, completed, cancelled)
 * @property string $block_type (maintenance, repair, inspection, other)
 * @property string|null $affected_by
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class MaintenanceBlock extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'hoarding_id',
        'created_by',
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'block_type',
        'affected_by',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Block type constants
     */
    const TYPE_MAINTENANCE = 'maintenance';
    const TYPE_REPAIR = 'repair';
    const TYPE_INSPECTION = 'inspection';
    const TYPE_OTHER = 'other';

    /**
     * Get the hoarding this block belongs to
     */
    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    /**
     * Get the user who created this block
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Filter by hoarding
     */
    public function scopeForHoarding(Builder $query, int $hoardingId): Builder
    {
        return $query->where('hoarding_id', $hoardingId);
    }

    /**
     * Scope: Only active blocks
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by block type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('block_type', $type);
    }

    /**
     * Scope: Blocks overlapping with a date range
     * Uses same overlap logic as BookingOverlapValidator:
     * (StartA <= EndB) AND (EndA >= StartB)
     */
    public function scopeOverlapping(Builder $query, $startDate, $endDate): Builder
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        return $query->where(function ($q) use ($start, $end) {
            $q->where('start_date', '<=', $end->format('Y-m-d'))
              ->where('end_date', '>=', $start->format('Y-m-d'));
        });
    }

    /**
     * Scope: Future blocks (end date is in the future)
     */
    public function scopeFuture(Builder $query): Builder
    {
        return $query->where('end_date', '>=', Carbon::today());
    }

    /**
     * Scope: Past blocks (end date is in the past)
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('end_date', '<', Carbon::today());
    }

    /**
     * Scope: Current blocks (today is within the block period)
     */
    public function scopeCurrent(Builder $query): Builder
    {
        $today = Carbon::today();
        return $query->where('start_date', '<=', $today)
                     ->where('end_date', '>=', $today);
    }

    /**
     * Check if this block overlaps with a given date range
     * 
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return bool
     */
    public function overlapsWith($startDate, $endDate): bool
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        return $this->start_date->lte($end) && $this->end_date->gte($start);
    }

    /**
     * Check if block is currently active
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Mark block as completed
     * 
     * @return bool
     */
    public function markCompleted(): bool
    {
        return $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Mark block as cancelled
     * 
     * @return bool
     */
    public function markCancelled(): bool
    {
        return $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Get duration in days
     * 
     * @return int
     */
    public function getDurationDays(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1; // +1 to include both start and end
    }

    /**
     * Static: Check if hoarding has active blocks in date range
     * 
     * @param int $hoardingId
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return bool
     */
    public static function hasActiveBlocks(int $hoardingId, $startDate, $endDate): bool
    {
        return self::forHoarding($hoardingId)
            ->active()
            ->overlapping($startDate, $endDate)
            ->exists();
    }

    /**
     * Static: Get all active blocks for hoarding in date range
     * 
     * @param int $hoardingId
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveBlocks(int $hoardingId, $startDate, $endDate)
    {
        return self::forHoarding($hoardingId)
            ->active()
            ->overlapping($startDate, $endDate)
            ->with('creator:id,name,email')
            ->orderBy('start_date')
            ->get();
    }
}
