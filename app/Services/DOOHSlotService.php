<?php

namespace App\Services;

use App\Models\DOOHSlot;
use App\Models\Hoarding;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DOOHSlotService
{
    /**
     * Create a new DOOH slot
     */
    public function createSlot(array $data): DOOHSlot
    {
        // Set defaults if not provided
        $data['duration_seconds'] = $data['duration_seconds'] ?? 10;
        $data['frequency_per_hour'] = $data['frequency_per_hour'] ?? 6;
        $data['ads_in_loop'] = $data['ads_in_loop'] ?? 1;
        $data['status'] = $data['status'] ?? 'available';
        $data['is_active'] = $data['is_active'] ?? true;

        // Create slot (calculations happen automatically in model boot)
        $slot = DOOHSlot::create($data);

        return $slot;
    }

    /**
     * Update slot configuration and recalculate
     */
    public function updateSlot(DOOHSlot $slot, array $data): DOOHSlot
    {
        $slot->update($data);
        
        // Recalculate if configuration changed
        if (array_intersect_key($data, array_flip(['start_time', 'end_time', 'frequency_per_hour', 'duration_seconds']))) {
            $slot->calculateDisplayMetrics();
            $slot->calculateCosts();
            $slot->save();
        }

        return $slot->fresh();
    }

    /**
     * Calculate optimal slot frequency based on desired displays
     */
    public function calculateOptimalFrequency(
        int $desiredDailyDisplays,
        string $startTime,
        string $endTime
    ): array {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        $hoursInSlot = $end->diffInHours($start, true);
        
        if ($hoursInSlot <= 0) {
            $hoursInSlot = 24 - $start->hour + $end->hour;
        }

        $frequencyPerHour = ceil($desiredDailyDisplays / $hoursInSlot);
        $intervalSeconds = 3600 / $frequencyPerHour;

        return [
            'frequency_per_hour' => $frequencyPerHour,
            'interval_seconds' => round($intervalSeconds, 2),
            'interval_minutes' => round($intervalSeconds / 60, 2),
            'actual_daily_displays' => $frequencyPerHour * $hoursInSlot,
            'hours_in_slot' => $hoursInSlot,
        ];
    }

    /**
     * Calculate pricing based on different models
     */
    public function calculatePricing(array $params): array
    {
        $model = $params['pricing_model'] ?? 'per_display'; // per_display, per_hour, per_day, per_month
        $basePrice = $params['base_price'] ?? 0;
        $frequency = $params['frequency_per_hour'] ?? 6;
        $startTime = $params['start_time'] ?? '00:00:00';
        $endTime = $params['end_time'] ?? '23:59:59';
        $isPrimeTime = $params['is_prime_time'] ?? false;
        $primeMultiplier = $params['prime_multiplier'] ?? 1.5;

        // Calculate hours in slot
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        $hoursInSlot = $end->diffInHours($start, true);
        
        if ($hoursInSlot <= 0) {
            $hoursInSlot = 24 - $start->hour + $end->hour;
        }

        $dailyDisplays = $frequency * $hoursInSlot;
        $hourlyDisplays = $frequency;

        // Apply prime time multiplier
        if ($isPrimeTime) {
            $basePrice *= $primeMultiplier;
        }

        $pricing = match($model) {
            'per_display' => [
                'price_per_display' => $basePrice,
                'hourly_cost' => $basePrice * $hourlyDisplays,
                'daily_cost' => $basePrice * $dailyDisplays,
                'monthly_cost' => $basePrice * $dailyDisplays * 30,
            ],
            'per_hour' => [
                'hourly_cost' => $basePrice,
                'price_per_display' => $hourlyDisplays > 0 ? $basePrice / $hourlyDisplays : 0,
                'daily_cost' => $basePrice * $hoursInSlot,
                'monthly_cost' => $basePrice * $hoursInSlot * 30,
            ],
            'per_day' => [
                'daily_cost' => $basePrice,
                'price_per_display' => $dailyDisplays > 0 ? $basePrice / $dailyDisplays : 0,
                'hourly_cost' => $hoursInSlot > 0 ? $basePrice / $hoursInSlot : 0,
                'monthly_cost' => $basePrice * 30,
            ],
            'per_month' => [
                'monthly_cost' => $basePrice,
                'daily_cost' => $basePrice / 30,
                'price_per_display' => ($dailyDisplays * 30) > 0 ? $basePrice / ($dailyDisplays * 30) : 0,
                'hourly_cost' => ($hoursInSlot * 30) > 0 ? $basePrice / ($hoursInSlot * 30) : 0,
            ],
            default => [
                'price_per_display' => $basePrice,
                'hourly_cost' => $basePrice * $hourlyDisplays,
                'daily_cost' => $basePrice * $dailyDisplays,
                'monthly_cost' => $basePrice * $dailyDisplays * 30,
            ],
        };

        return array_merge($pricing, [
            'total_daily_displays' => $dailyDisplays,
            'total_hourly_displays' => $hourlyDisplays,
            'hours_in_slot' => round($hoursInSlot, 2),
            'is_prime_time' => $isPrimeTime,
            'pricing_model' => $model,
        ]);
    }

    /**
     * Calculate booking cost for specific date range
     */
    public function calculateBookingCost(
        DOOHSlot $slot,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $days = $startDate->diffInDays($endDate) + 1;
        $totalCost = $slot->daily_cost * $days;
        $totalDisplays = $slot->total_daily_displays * $days;
        
        $costPerDisplay = $totalDisplays > 0 ? $totalCost / $totalDisplays : 0;

        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_days' => $days,
            'daily_cost' => $slot->daily_cost,
            'total_cost' => round($totalCost, 2),
            'total_displays' => $totalDisplays,
            'cost_per_display' => round($costPerDisplay, 4),
            'cpm' => round($costPerDisplay * 1000, 2), // Cost per thousand
        ];
    }

    /**
     * Generate slot schedule for a specific date
     */
    public function generateDailySchedule(DOOHSlot $slot, Carbon $date): array
    {
        $schedule = [];
        
        $start = Carbon::parse($date->format('Y-m-d') . ' ' . $slot->start_time);
        $end = Carbon::parse($date->format('Y-m-d') . ' ' . $slot->end_time);
        
        // Handle slots that cross midnight
        if ($end->lt($start)) {
            $end->addDay();
        }

        $currentTime = $start->copy();
        $displayNumber = 0;

        while ($currentTime->lte($end)) {
            $displayNumber++;
            
            $loopCycle = $slot->ads_in_loop > 0 
                ? ceil($displayNumber / $slot->ads_in_loop) 
                : 1;
            
            $positionInLoop = $slot->loop_position ?? 
                (($displayNumber - 1) % max($slot->ads_in_loop, 1)) + 1;

            $schedule[] = [
                'display_number' => $displayNumber,
                'time' => $currentTime->format('H:i:s'),
                'formatted_time' => $currentTime->format('g:i:s A'),
                'datetime' => $currentTime->toDateTimeString(),
                'loop_cycle' => $loopCycle,
                'position_in_loop' => $positionInLoop,
                'duration_seconds' => $slot->duration_seconds,
                'slot_id' => $slot->id,
                'slot_name' => $slot->slot_name,
            ];

            $currentTime->addSeconds($slot->interval_seconds);

            // Safety limit
            if ($displayNumber > 500) {
                break;
            }
        }

        return [
            'date' => $date->format('Y-m-d'),
            'slot' => [
                'id' => $slot->id,
                'name' => $slot->slot_name,
                'time_range' => $slot->time_range,
            ],
            'total_displays' => $displayNumber,
            'schedule' => $schedule,
        ];
    }

    /**
     * Check slot availability for booking
     */
    public function checkAvailability(
        int $hoardingId,
        Carbon $startDate,
        Carbon $endDate,
        ?string $startTime = null,
        ?string $endTime = null
    ): array {
        $allSlots = DOOHSlot::where('hoarding_id', $hoardingId)
            ->where('is_active', true)
            ->get();

        $availableSlots = [];
        $bookedSlots = [];
        $conflictingSlots = [];

        foreach ($allSlots as $slot) {
            // Check time range if specified
            if ($startTime && $endTime) {
                $slotStart = Carbon::parse($slot->start_time);
                $slotEnd = Carbon::parse($slot->end_time);
                $requestStart = Carbon::parse($startTime);
                $requestEnd = Carbon::parse($endTime);

                $timeOverlap = !($slotEnd->lt($requestStart) || $slotStart->gt($requestEnd));
                
                if (!$timeOverlap) {
                    continue; // Skip slots that don't overlap with requested time
                }
            }

            // Check date availability
            if ($slot->status === 'available' && !$slot->booking_id) {
                $availableSlots[] = $slot;
            } elseif ($slot->booking_id) {
                // Check if dates conflict
                if ($slot->start_date && $slot->end_date) {
                    $dateOverlap = !($slot->end_date->lt($startDate) || $slot->start_date->gt($endDate));
                    
                    if ($dateOverlap) {
                        $conflictingSlots[] = $slot;
                    }
                }
                $bookedSlots[] = $slot;
            }
        }

        return [
            'available' => $availableSlots,
            'booked' => $bookedSlots,
            'conflicting' => $conflictingSlots,
            'total_available' => count($availableSlots),
            'total_booked' => count($bookedSlots),
            'total_conflicting' => count($conflictingSlots),
        ];
    }

    /**
     * Book multiple slots
     */
    public function bookSlots(
        array $slotIds,
        Booking $booking
    ): array {
        $booked = [];
        $failed = [];

        foreach ($slotIds as $slotId) {
            $slot = DOOHSlot::find($slotId);
            
            if (!$slot) {
                $failed[] = [
                    'slot_id' => $slotId,
                    'reason' => 'Slot not found',
                ];
                continue;
            }

            if ($slot->book($booking)) {
                $booked[] = $slot;
            } else {
                $failed[] = [
                    'slot_id' => $slotId,
                    'reason' => "Slot not available (status: {$slot->status})",
                ];
            }
        }

        return [
            'booked' => $booked,
            'failed' => $failed,
            'total_booked' => count($booked),
            'total_failed' => count($failed),
            'total_cost' => collect($booked)->sum('total_booking_cost'),
        ];
    }

    /**
     * Release all slots for a booking
     */
    public function releaseBookingSlots(Booking $booking): int
    {
        $slots = DOOHSlot::where('booking_id', $booking->id)->get();
        $released = 0;

        foreach ($slots as $slot) {
            if ($slot->release()) {
                $released++;
            }
        }

        return $released;
    }

    /**
     * Generate looping schedule for multiple ads
     */
    public function generateMultiAdLoop(
        array $slots,
        Carbon $date
    ): array {
        $loopSchedule = [];

        // Sort slots by loop position
        $sortedSlots = collect($slots)->sortBy('loop_position')->values();

        $start = Carbon::parse($date->format('Y-m-d') . ' ' . $sortedSlots->first()->start_time);
        $end = Carbon::parse($date->format('Y-m-d') . ' ' . $sortedSlots->first()->end_time);
        
        if ($end->lt($start)) {
            $end->addDay();
        }

        $currentTime = $start->copy();
        $cycleNumber = 0;

        while ($currentTime->lte($end)) {
            $cycleNumber++;

            foreach ($sortedSlots as $index => $slot) {
                $loopSchedule[] = [
                    'cycle' => $cycleNumber,
                    'position' => $index + 1,
                    'slot_id' => $slot->id,
                    'slot_name' => $slot->slot_name,
                    'time' => $currentTime->format('H:i:s'),
                    'formatted_time' => $currentTime->format('g:i:s A'),
                    'duration_seconds' => $slot->duration_seconds,
                ];

                $currentTime->addSeconds($slot->duration_seconds);
            }

            // Safety limit
            if ($cycleNumber > 200) {
                break;
            }
        }

        return [
            'date' => $date->format('Y-m-d'),
            'total_cycles' => $cycleNumber,
            'ads_in_loop' => $sortedSlots->count(),
            'schedule' => $loopSchedule,
        ];
    }

    /**
     * Calculate ROI and performance metrics
     */
    public function calculateMetrics(DOOHSlot $slot, Carbon $startDate, Carbon $endDate): array
    {
        $days = $startDate->diffInDays($endDate) + 1;
        $totalDisplays = $slot->total_daily_displays * $days;
        $totalCost = $slot->daily_cost * $days;
        
        $costPerDisplay = $totalDisplays > 0 ? $totalCost / $totalDisplays : 0;
        $cpm = $costPerDisplay * 1000;

        // Estimated reach (assuming average viewership per display)
        $avgViewersPerDisplay = 50; // Configurable
        $estimatedReach = $totalDisplays * $avgViewersPerDisplay;

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_days' => $days,
            ],
            'displays' => [
                'daily' => $slot->total_daily_displays,
                'total' => $totalDisplays,
                'per_hour' => $slot->total_hourly_displays,
            ],
            'costs' => [
                'per_display' => round($costPerDisplay, 4),
                'per_hour' => $slot->hourly_cost,
                'per_day' => $slot->daily_cost,
                'total' => round($totalCost, 2),
                'cpm' => round($cpm, 2),
            ],
            'reach' => [
                'estimated_total_views' => $estimatedReach,
                'avg_viewers_per_display' => $avgViewersPerDisplay,
                'estimated_daily_reach' => $slot->total_daily_displays * $avgViewersPerDisplay,
            ],
            'frequency' => [
                'per_hour' => $slot->frequency_per_hour,
                'interval_seconds' => $slot->interval_seconds,
                'interval_minutes' => round($slot->interval_seconds / 60, 2),
            ],
        ];
    }

    /**
     * Optimize slot configuration for budget
     */
    public function optimizeForBudget(
        float $monthlyBudget,
        string $startTime,
        string $endTime,
        float $pricePerDisplay
    ): array {
        // Calculate hours in slot
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        $hoursInSlot = $end->diffInHours($start, true);
        
        if ($hoursInSlot <= 0) {
            $hoursInSlot = 24 - $start->hour + $end->hour;
        }

        // Calculate affordable daily displays
        $dailyBudget = $monthlyBudget / 30;
        $affordableDisplays = floor($dailyBudget / $pricePerDisplay);
        
        // Calculate optimal frequency
        $frequencyPerHour = floor($affordableDisplays / $hoursInSlot);
        $intervalSeconds = $frequencyPerHour > 0 ? 3600 / $frequencyPerHour : 3600;

        $actualDailyDisplays = $frequencyPerHour * $hoursInSlot;
        $actualDailyCost = $actualDailyDisplays * $pricePerDisplay;
        $actualMonthlyCost = $actualDailyCost * 30;

        return [
            'budget' => [
                'monthly' => $monthlyBudget,
                'daily' => $dailyBudget,
            ],
            'optimized_config' => [
                'frequency_per_hour' => max($frequencyPerHour, 1),
                'interval_seconds' => round($intervalSeconds, 2),
                'interval_minutes' => round($intervalSeconds / 60, 2),
                'daily_displays' => $actualDailyDisplays,
            ],
            'actual_cost' => [
                'daily' => round($actualDailyCost, 2),
                'monthly' => round($actualMonthlyCost, 2),
            ],
            'savings' => [
                'daily' => round($dailyBudget - $actualDailyCost, 2),
                'monthly' => round($monthlyBudget - $actualMonthlyCost, 2),
            ],
            'utilization' => round(($actualMonthlyCost / $monthlyBudget) * 100, 2),
        ];
    }

    /**
     * Get slot statistics for hoarding
     */
    public function getHoardingSlotStats(int $hoardingId): array
    {
        $slots = DOOHSlot::where('hoarding_id', $hoardingId)->get();

        return [
            'total_slots' => $slots->count(),
            'available' => $slots->where('status', 'available')->count(),
            'booked' => $slots->where('status', 'booked')->count(),
            'blocked' => $slots->where('status', 'blocked')->count(),
            'maintenance' => $slots->where('status', 'maintenance')->count(),
            'total_daily_displays' => $slots->sum('total_daily_displays'),
            'total_monthly_revenue_potential' => $slots->where('status', 'available')->sum('monthly_cost'),
            'total_monthly_revenue_actual' => $slots->where('status', 'booked')->sum('monthly_cost'),
            'occupancy_rate' => $slots->count() > 0 
                ? round(($slots->where('status', 'booked')->count() / $slots->count()) * 100, 2) 
                : 0,
        ];
    }
}
