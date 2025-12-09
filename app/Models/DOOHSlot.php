<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class DOOHSlot extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dooh_slots';

    protected $fillable = [
        'hoarding_id',
        'booking_id',
        'slot_name',
        'start_time',
        'end_time',
        'duration_seconds',
        'frequency_per_hour',
        'loop_position',
        'total_daily_displays',
        'total_hourly_displays',
        'interval_seconds',
        'price_per_display',
        'hourly_cost',
        'daily_cost',
        'monthly_cost',
        'start_date',
        'end_date',
        'total_booking_days',
        'total_booking_cost',
        'status',
        'is_active',
        'is_prime_time',
        'ads_in_loop',
        'loop_schedule',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'string',
        'end_time' => 'string',
        'duration_seconds' => 'integer',
        'frequency_per_hour' => 'integer',
        'loop_position' => 'integer',
        'total_daily_displays' => 'integer',
        'total_hourly_displays' => 'integer',
        'interval_seconds' => 'decimal:2',
        'price_per_display' => 'decimal:2',
        'hourly_cost' => 'decimal:2',
        'daily_cost' => 'decimal:2',
        'monthly_cost' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'total_booking_days' => 'integer',
        'total_booking_cost' => 'decimal:2',
        'is_active' => 'boolean',
        'is_prime_time' => 'boolean',
        'ads_in_loop' => 'integer',
        'loop_schedule' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Boot method to auto-calculate fields
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($slot) {
            $slot->calculateDisplayMetrics();
            $slot->calculateCosts();
        });

        static::updating(function ($slot) {
            if ($slot->isDirty(['start_time', 'end_time', 'duration_seconds', 'frequency_per_hour', 'ads_in_loop'])) {
                $slot->calculateDisplayMetrics();
            }
            
            if ($slot->isDirty(['total_daily_displays', 'price_per_display', 'start_date', 'end_date'])) {
                $slot->calculateCosts();
            }
        });
    }

    /**
     * Relationships
     */
    public function hoarding()
    {
        return $this->belongsTo(Hoarding::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
                    ->where('is_active', true)
                    ->whereNull('booking_id');
    }

    public function scopeBooked($query)
    {
        return $query->where('status', 'booked')
                    ->whereNotNull('booking_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimeTime($query)
    {
        return $query->where('is_prime_time', true);
    }

    public function scopeForHoarding($query, $hoardingId)
    {
        return $query->where('hoarding_id', $hoardingId);
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

    public function scopeInTimeRange($query, $startTime, $endTime)
    {
        return $query->where('start_time', '<=', $endTime)
                    ->where('end_time', '>=', $startTime);
    }

    /**
     * Calculate display metrics
     */
    public function calculateDisplayMetrics()
    {
        // Calculate hours in slot
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        $hoursInSlot = $end->diffInHours($start);
        
        // If end time is before start time, it spans midnight
        if ($hoursInSlot <= 0) {
            $hoursInSlot = 24 - $start->hour + $end->hour;
        }

        // Total displays per hour
        $this->total_hourly_displays = $this->frequency_per_hour;

        // Total daily displays
        $this->total_daily_displays = $this->frequency_per_hour * $hoursInSlot;

        // Calculate interval between displays
        // 3600 seconds in an hour, divided by frequency
        $this->interval_seconds = 3600 / $this->frequency_per_hour;

        // Generate loop schedule
        $this->generateLoopSchedule();
    }

    /**
     * Calculate costs based on pricing model
     */
    public function calculateCosts()
    {
        // Cost per display (base unit)
        // If not set, calculate from daily cost or use default
        if (!$this->price_per_display && $this->daily_cost) {
            $this->price_per_display = $this->total_daily_displays > 0 
                ? $this->daily_cost / $this->total_daily_displays 
                : 0;
        }

        // Calculate hourly cost
        $this->hourly_cost = $this->price_per_display * $this->total_hourly_displays;

        // Calculate daily cost
        $this->daily_cost = $this->price_per_display * $this->total_daily_displays;

        // Calculate monthly cost (30 days standard)
        $this->monthly_cost = $this->daily_cost * 30;

        // If booking dates are set, calculate total booking cost
        if ($this->start_date && $this->end_date) {
            $this->total_booking_days = Carbon::parse($this->start_date)
                ->diffInDays(Carbon::parse($this->end_date)) + 1;
            
            $this->total_booking_cost = $this->daily_cost * $this->total_booking_days;
        }
    }

    /**
     * Generate detailed loop schedule
     */
    public function generateLoopSchedule()
    {
        $schedule = [];
        
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        $currentTime = $start->copy();
        
        $displayNumber = 0;
        $loopCycle = 0;

        // Generate schedule for entire slot duration
        while ($currentTime->lt($end) || ($currentTime->format('H:i') === $end->format('H:i'))) {
            $displayNumber++;
            
            // Calculate which loop cycle this belongs to
            if ($this->ads_in_loop > 0) {
                $loopCycle = ceil($displayNumber / $this->ads_in_loop);
            }

            $schedule[] = [
                'display_number' => $displayNumber,
                'time' => $currentTime->format('H:i:s'),
                'loop_cycle' => $loopCycle,
                'position_in_loop' => $this->loop_position ?? (($displayNumber - 1) % $this->ads_in_loop) + 1,
                'duration_seconds' => $this->duration_seconds,
            ];

            // Move to next display time
            $currentTime->addSeconds($this->interval_seconds);

            // Break if we've exceeded reasonable limit
            if ($displayNumber > 1000) {
                break;
            }
        }

        $this->loop_schedule = $schedule;
    }

    /**
     * Check if slot conflicts with another slot
     */
    public function hasConflictWith(DOOHSlot $otherSlot): bool
    {
        // Check date range overlap
        $dateOverlap = !($this->end_date < $otherSlot->start_date || $this->start_date > $otherSlot->end_date);
        
        // Check time range overlap
        $timeOverlap = !($this->end_time < $otherSlot->start_time || $this->start_time > $otherSlot->end_time);
        
        return $dateOverlap && $timeOverlap;
    }

    /**
     * Book this slot
     */
    public function book(Booking $booking): bool
    {
        if ($this->status !== 'available') {
            return false;
        }

        $this->booking_id = $booking->id;
        $this->status = 'booked';
        $this->start_date = $booking->start_date;
        $this->end_date = $booking->end_date;
        
        // Recalculate costs for actual booking period
        $this->calculateCosts();
        
        return $this->save();
    }

    /**
     * Release this slot
     */
    public function release(): bool
    {
        $this->booking_id = null;
        $this->status = 'available';
        $this->start_date = null;
        $this->end_date = null;
        $this->total_booking_days = null;
        $this->total_booking_cost = null;
        
        return $this->save();
    }

    /**
     * Mark slot as blocked
     */
    public function block(string $reason = null): bool
    {
        $this->status = 'blocked';
        
        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['block_reason'] = $reason;
            $metadata['blocked_at'] = now()->toDateTimeString();
            $this->metadata = $metadata;
        }
        
        return $this->save();
    }

    /**
     * Mark slot as maintenance
     */
    public function markForMaintenance(string $reason = null): bool
    {
        $this->status = 'maintenance';
        
        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['maintenance_reason'] = $reason;
            $metadata['maintenance_started_at'] = now()->toDateTimeString();
            $this->metadata = $metadata;
        }
        
        return $this->save();
    }

    /**
     * Computed attributes
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'available' => 'success',
            'booked' => 'primary',
            'blocked' => 'warning',
            'maintenance' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'available' => 'Available',
            'booked' => 'Booked',
            'blocked' => 'Blocked',
            'maintenance' => 'Under Maintenance',
            default => 'Unknown',
        };
    }

    public function getSlotDurationHoursAttribute(): float
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        $hours = $end->diffInHours($start, true);
        
        // Handle slots that cross midnight
        if ($hours <= 0) {
            $hours = 24 - $start->hour + $end->hour + ($end->minute / 60);
        }
        
        return round($hours, 2);
    }

    public function getTotalDisplaysInPeriodAttribute(): int
    {
        if (!$this->total_booking_days) {
            return $this->total_daily_displays;
        }
        
        return $this->total_daily_displays * $this->total_booking_days;
    }

    public function getFormattedStartTimeAttribute(): string
    {
        return Carbon::parse($this->start_time)->format('g:i A');
    }

    public function getFormattedEndTimeAttribute(): string
    {
        return Carbon::parse($this->end_time)->format('g:i A');
    }

    public function getTimeRangeAttribute(): string
    {
        return "{$this->formatted_start_time} - {$this->formatted_end_time}";
    }

    public function getIsBookedAttribute(): bool
    {
        return $this->status === 'booked' && $this->booking_id !== null;
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available' && $this->is_active && $this->booking_id === null;
    }

    /**
     * Get display frequency description
     */
    public function getFrequencyDescriptionAttribute(): string
    {
        $displayPerHour = $this->frequency_per_hour;
        $intervalMinutes = round($this->interval_seconds / 60, 1);
        
        return "{$displayPerHour} times per hour (every {$intervalMinutes} minutes)";
    }

    /**
     * Get looping description
     */
    public function getLoopingDescriptionAttribute(): string
    {
        if ($this->ads_in_loop <= 1) {
            return "Continuous display (no loop)";
        }
        
        $position = $this->loop_position ?? 1;
        return "Position {$position} of {$this->ads_in_loop} ads in loop";
    }

    /**
     * Calculate ROI metrics
     */
    public function calculateROI(float $advertisementCost = 0): array
    {
        $totalDisplays = $this->total_displays_in_period;
        $totalCost = $this->total_booking_cost ?? $this->monthly_cost;
        
        $costPerDisplay = $totalDisplays > 0 ? $totalCost / $totalDisplays : 0;
        $costPerThousand = $costPerDisplay * 1000; // CPM (Cost Per Mille)
        
        return [
            'total_displays' => $totalDisplays,
            'total_cost' => $totalCost,
            'cost_per_display' => round($costPerDisplay, 4),
            'cpm' => round($costPerThousand, 2), // Cost Per Thousand
            'daily_reach' => $this->total_daily_displays,
            'estimated_impressions' => $totalDisplays, // Assuming 1 display = 1 impression
        ];
    }

    /**
     * Get availability for date range
     */
    public static function getAvailabilityForHoarding($hoardingId, $startDate, $endDate, $startTime = null, $endTime = null)
    {
        $query = static::where('hoarding_id', $hoardingId)
            ->where('is_active', true);

        // Check for date conflicts
        $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereNull('start_date')
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('end_date', '<', $startDate)
                     ->orWhere('start_date', '>', $endDate);
              });
        });

        // If time range specified, filter by time
        if ($startTime && $endTime) {
            $query->where(function ($q) use ($startTime, $endTime) {
                $q->where('end_time', '<=', $startTime)
                  ->orWhere('start_time', '>=', $endTime);
            });
        }

        return $query->available()->get();
    }
}
