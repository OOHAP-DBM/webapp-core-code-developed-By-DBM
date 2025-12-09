<?php

namespace App\Traits;

use App\Models\DOOHSlot;
use App\Services\DOOHSlotService;
use Carbon\Carbon;

trait HasDOOHSlots
{
    /**
     * Boot the trait
     */
    protected static function bootHasDOOHSlots()
    {
        // You can add automatic behaviors here if needed
    }

    /**
     * Relationship: Get all DOOH slots for this hoarding
     */
    public function doohSlots()
    {
        return $this->hasMany(DOOHSlot::class, 'hoarding_id')->orderBy('start_time');
    }

    /**
     * Get available slots
     */
    public function availableSlots()
    {
        return $this->hasMany(DOOHSlot::class, 'hoarding_id')
            ->where('status', 'available')
            ->where('is_active', true)
            ->whereNull('booking_id');
    }

    /**
     * Get booked slots
     */
    public function bookedSlots()
    {
        return $this->hasMany(DOOHSlot::class, 'hoarding_id')
            ->where('status', 'booked')
            ->whereNotNull('booking_id');
    }

    /**
     * Create a new DOOH slot for this hoarding
     */
    public function createDOOHSlot(array $data): DOOHSlot
    {
        $data['hoarding_id'] = $this->id;
        
        $service = app(DOOHSlotService::class);
        return $service->createSlot($data);
    }

    /**
     * Check if this hoarding has DOOH capability
     */
    public function isDOOH(): bool
    {
        // Check if hoarding type is digital
        return in_array($this->hoarding_type ?? '', ['dooh', 'digital', 'led_screen', 'digital_billboard']);
    }

    /**
     * Enable DOOH for this hoarding
     */
    public function enableDOOH(): void
    {
        if (!$this->isDOOH()) {
            $this->hoarding_type = 'dooh';
            $this->save();
        }
    }

    /**
     * Get slot availability for date range
     */
    public function getSlotAvailability(Carbon $startDate, Carbon $endDate, ?string $startTime = null, ?string $endTime = null): array
    {
        $service = app(DOOHSlotService::class);
        return $service->checkAvailability($this->id, $startDate, $endDate, $startTime, $endTime);
    }

    /**
     * Get total daily display capacity
     */
    public function getTotalDailyDisplays(): int
    {
        return $this->doohSlots()
            ->where('is_active', true)
            ->sum('total_daily_displays');
    }

    /**
     * Get monthly revenue potential
     */
    public function getMonthlyRevenuePotential(): float
    {
        return $this->doohSlots()
            ->where('status', 'available')
            ->where('is_active', true)
            ->sum('monthly_cost');
    }

    /**
     * Get current monthly revenue (booked slots)
     */
    public function getCurrentMonthlyRevenue(): float
    {
        return $this->doohSlots()
            ->where('status', 'booked')
            ->sum('monthly_cost');
    }

    /**
     * Get slot occupancy rate
     */
    public function getSlotOccupancyRate(): float
    {
        $totalSlots = $this->doohSlots()->count();
        
        if ($totalSlots === 0) {
            return 0;
        }

        $bookedSlots = $this->doohSlots()
            ->where('status', 'booked')
            ->count();

        return round(($bookedSlots / $totalSlots) * 100, 2);
    }

    /**
     * Get DOOH statistics
     */
    public function getDOOHStats(): array
    {
        $service = app(DOOHSlotService::class);
        return $service->getHoardingSlotStats($this->id);
    }

    /**
     * Create default slot configuration
     */
    public function setupDefaultSlots(): array
    {
        $slots = [];

        // Morning slot (6 AM - 12 PM)
        $slots[] = $this->createDOOHSlot([
            'slot_name' => 'Morning Slot',
            'start_time' => '06:00:00',
            'end_time' => '12:00:00',
            'duration_seconds' => 10,
            'frequency_per_hour' => 6,
            'price_per_display' => 2.00,
            'is_prime_time' => false,
        ]);

        // Afternoon slot (12 PM - 6 PM)
        $slots[] = $this->createDOOHSlot([
            'slot_name' => 'Afternoon Prime',
            'start_time' => '12:00:00',
            'end_time' => '18:00:00',
            'duration_seconds' => 10,
            'frequency_per_hour' => 6,
            'price_per_display' => 3.00,
            'is_prime_time' => true,
        ]);

        // Evening slot (6 PM - 11 PM)
        $slots[] = $this->createDOOHSlot([
            'slot_name' => 'Evening Prime',
            'start_time' => '18:00:00',
            'end_time' => '23:00:00',
            'duration_seconds' => 10,
            'frequency_per_hour' => 6,
            'price_per_display' => 4.00,
            'is_prime_time' => true,
        ]);

        // Night slot (11 PM - 6 AM)
        $slots[] = $this->createDOOHSlot([
            'slot_name' => 'Night Slot',
            'start_time' => '23:00:00',
            'end_time' => '06:00:00',
            'duration_seconds' => 10,
            'frequency_per_hour' => 4,
            'price_per_display' => 1.50,
            'is_prime_time' => false,
        ]);

        return $slots;
    }

    /**
     * Get prime time slots
     */
    public function primeTimeSlots()
    {
        return $this->doohSlots()
            ->where('is_prime_time', true)
            ->where('is_active', true);
    }

    /**
     * Get regular time slots
     */
    public function regularTimeSlots()
    {
        return $this->doohSlots()
            ->where('is_prime_time', false)
            ->where('is_active', true);
    }

    /**
     * Calculate total cost for booking period
     */
    public function calculateBookingCost(Carbon $startDate, Carbon $endDate, array $slotIds): array
    {
        $service = app(DOOHSlotService::class);
        $totalCost = 0;
        $totalDisplays = 0;
        $slotDetails = [];

        foreach ($slotIds as $slotId) {
            $slot = $this->doohSlots()->find($slotId);
            
            if ($slot) {
                $cost = $service->calculateBookingCost($slot, $startDate, $endDate);
                $totalCost += $cost['total_cost'];
                $totalDisplays += $cost['total_displays'];
                $slotDetails[] = [
                    'slot' => $slot,
                    'cost_details' => $cost,
                ];
            }
        }

        return [
            'total_cost' => round($totalCost, 2),
            'total_displays' => $totalDisplays,
            'cost_per_display' => $totalDisplays > 0 ? round($totalCost / $totalDisplays, 4) : 0,
            'slot_details' => $slotDetails,
        ];
    }

    /**
     * Get daily schedule for all slots
     */
    public function getDailySchedule(Carbon $date): array
    {
        $service = app(DOOHSlotService::class);
        $schedules = [];

        foreach ($this->doohSlots()->where('is_active', true)->get() as $slot) {
            $schedules[] = $service->generateDailySchedule($slot, $date);
        }

        return [
            'date' => $date->format('Y-m-d'),
            'hoarding' => [
                'id' => $this->id,
                'title' => $this->title ?? $this->name ?? '',
            ],
            'schedules' => $schedules,
            'total_daily_displays' => collect($schedules)->sum('total_displays'),
        ];
    }

    /**
     * Block slots for maintenance
     */
    public function blockSlotsForMaintenance(Carbon $startDate, Carbon $endDate, string $reason = null): int
    {
        $blocked = 0;

        foreach ($this->doohSlots()->where('status', 'available')->get() as $slot) {
            if ($slot->markForMaintenance($reason)) {
                $blocked++;
            }
        }

        return $blocked;
    }

    /**
     * Release all blocked slots
     */
    public function releaseBlockedSlots(): int
    {
        $released = 0;

        foreach ($this->doohSlots()->where('status', 'blocked')->orWhere('status', 'maintenance')->get() as $slot) {
            $slot->status = 'available';
            
            if ($slot->save()) {
                $released++;
            }
        }

        return $released;
    }

    /**
     * Optimize slots for budget
     */
    public function optimizeForBudget(float $monthlyBudget): array
    {
        $service = app(DOOHSlotService::class);
        $recommendations = [];

        foreach ($this->availableSlots()->get() as $slot) {
            $optimization = $service->optimizeForBudget(
                $monthlyBudget,
                $slot->start_time,
                $slot->end_time,
                $slot->price_per_display
            );

            $recommendations[] = [
                'slot' => $slot,
                'optimization' => $optimization,
            ];
        }

        return $recommendations;
    }

    /**
     * Get slot by time range
     */
    public function findSlotByTimeRange(string $startTime, string $endTime)
    {
        return $this->doohSlots()
            ->where('start_time', '<=', $startTime)
            ->where('end_time', '>=', $endTime)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get overlapping slots
     */
    public function getOverlappingSlots(string $startTime, string $endTime)
    {
        return $this->doohSlots()
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->where('is_active', true)
            ->get();
    }

    /**
     * Check if hoarding has capacity for additional slots
     */
    public function hasSlotCapacity(): bool
    {
        $totalSlots = $this->doohSlots()->count();
        $maxSlots = 24; // Maximum 24 slots (hourly slots for 24 hours)

        return $totalSlots < $maxSlots;
    }

    /**
     * Get slot utilization percentage
     */
    public function getSlotUtilization(): array
    {
        $slots = $this->doohSlots()->get();
        $totalSlots = $slots->count();

        if ($totalSlots === 0) {
            return [
                'total_slots' => 0,
                'utilized_hours' => 0,
                'available_hours' => 24,
                'utilization_percentage' => 0,
            ];
        }

        $utilizedHours = 0;

        foreach ($slots as $slot) {
            $start = Carbon::parse($slot->start_time);
            $end = Carbon::parse($slot->end_time);
            $hours = $end->diffInHours($start, true);
            
            if ($hours <= 0) {
                $hours = 24 - $start->hour + $end->hour;
            }
            
            $utilizedHours += $hours;
        }

        return [
            'total_slots' => $totalSlots,
            'utilized_hours' => round($utilizedHours, 2),
            'available_hours' => round(24 - $utilizedHours, 2),
            'utilization_percentage' => round(($utilizedHours / 24) * 100, 2),
        ];
    }
}
