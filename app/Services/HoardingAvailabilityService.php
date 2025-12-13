<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Hoarding;
use App\Models\MaintenanceBlock;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * PROMPT 104: Hoarding Availability API for Frontend Calendar
 * 
 * Service for generating calendar availability data
 * Returns date slots with statuses: available, booked, blocked, hold, partial
 */
class HoardingAvailabilityService
{
    /**
     * Get availability calendar for a hoarding
     * Returns day-by-day status for calendar heatmap UI
     * 
     * @param int $hoardingId
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @param bool $includeDetails Include detailed information (bookings, blocks, etc.)
     * @return array
     */
    public function getAvailabilityCalendar(
        int $hoardingId,
        $startDate,
        $endDate,
        bool $includeDetails = false
    ): array {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        // Get all data sources
        $bookings = $this->getBookingsInRange($hoardingId, $start, $end);
        $holds = $this->getHoldsInRange($hoardingId, $start, $end);
        $blocks = $this->getMaintenanceBlocksInRange($hoardingId, $start, $end);
        $posBookings = $this->getPOSBookingsInRange($hoardingId, $start, $end);

        // Generate date range
        $period = CarbonPeriod::create($start, $end);
        $calendar = [];

        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');
            
            $dayData = [
                'date' => $dateKey,
                'day_of_week' => $date->dayName,
                'status' => $this->determineDateStatus(
                    $date,
                    $bookings,
                    $holds,
                    $blocks,
                    $posBookings
                ),
            ];

            if ($includeDetails) {
                $dayData['details'] = $this->getDateDetails(
                    $date,
                    $bookings,
                    $holds,
                    $blocks,
                    $posBookings
                );
            }

            $calendar[] = $dayData;
        }

        return [
            'hoarding_id' => $hoardingId,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'total_days' => count($calendar),
            'summary' => $this->generateSummary($calendar),
            'calendar' => $calendar,
        ];
    }

    /**
     * Get availability summary (counts by status)
     * 
     * @param int $hoardingId
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return array
     */
    public function getAvailabilitySummary(
        int $hoardingId,
        $startDate,
        $endDate
    ): array {
        $calendar = $this->getAvailabilityCalendar($hoardingId, $startDate, $endDate, false);
        
        return $calendar['summary'];
    }

    /**
     * Get month view calendar (optimized for month display)
     * 
     * @param int $hoardingId
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getMonthCalendar(int $hoardingId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return $this->getAvailabilityCalendar($hoardingId, $startDate, $endDate, true);
    }

    /**
     * Get availability for specific dates (batch check)
     * 
     * @param int $hoardingId
     * @param array $dates Array of date strings
     * @return array
     */
    public function checkMultipleDates(int $hoardingId, array $dates): array
    {
        if (empty($dates)) {
            return [];
        }

        $parsedDates = array_map(fn($d) => Carbon::parse($d), $dates);
        $minDate = min($parsedDates);
        $maxDate = max($parsedDates);

        $calendar = $this->getAvailabilityCalendar($hoardingId, $minDate, $maxDate, true);

        // Filter to only requested dates
        $results = [];
        foreach ($dates as $date) {
            $dateKey = Carbon::parse($date)->format('Y-m-d');
            $dayData = collect($calendar['calendar'])->firstWhere('date', $dateKey);
            
            if ($dayData) {
                $results[] = $dayData;
            }
        }

        return $results;
    }

    /**
     * Get next N available dates
     * 
     * @param int $hoardingId
     * @param int $count Number of available dates to find
     * @param string|Carbon|null $startFrom Start searching from this date (default: today)
     * @param int $maxSearchDays Maximum days to search (default: 365)
     * @return array
     */
    public function getNextAvailableDates(
        int $hoardingId,
        int $count = 10,
        $startFrom = null,
        int $maxSearchDays = 365
    ): array {
        $startDate = $startFrom ? Carbon::parse($startFrom) : Carbon::today();
        $endDate = $startDate->copy()->addDays($maxSearchDays);

        $calendar = $this->getAvailabilityCalendar($hoardingId, $startDate, $endDate, false);

        $availableDates = collect($calendar['calendar'])
            ->where('status', 'available')
            ->take($count)
            ->values()
            ->toArray();

        return [
            'hoarding_id' => $hoardingId,
            'requested_count' => $count,
            'found_count' => count($availableDates),
            'searched_until' => $endDate->format('Y-m-d'),
            'dates' => $availableDates,
        ];
    }

    /**
     * Determine status for a specific date
     * 
     * @param Carbon $date
     * @param Collection $bookings
     * @param Collection $holds
     * @param Collection $blocks
     * @param Collection $posBookings
     * @return string
     */
    protected function determineDateStatus(
        Carbon $date,
        Collection $bookings,
        Collection $holds,
        Collection $blocks,
        Collection $posBookings
    ): string {
        $statuses = [];

        // Check maintenance blocks (highest priority)
        $hasBlock = $blocks->contains(function ($block) use ($date) {
            return $date->between($block->start_date, $block->end_date);
        });

        if ($hasBlock) {
            $statuses[] = 'blocked';
        }

        // Check confirmed bookings
        $hasBooking = $bookings->contains(function ($booking) use ($date) {
            return $date->between(
                Carbon::parse($booking->start_date),
                Carbon::parse($booking->end_date)
            );
        });

        if ($hasBooking) {
            $statuses[] = 'booked';
        }

        // Check active holds
        $hasHold = $holds->contains(function ($hold) use ($date) {
            return $date->between(
                Carbon::parse($hold->start_date),
                Carbon::parse($hold->end_date)
            );
        });

        if ($hasHold) {
            $statuses[] = 'hold';
        }

        // Check POS bookings
        $hasPOS = $posBookings->contains(function ($pos) use ($date) {
            return $date->between(
                Carbon::parse($pos->start_date),
                Carbon::parse($pos->end_date)
            );
        });

        if ($hasPOS) {
            $statuses[] = 'booked'; // Treat POS as booked
        }

        // Determine final status
        if (empty($statuses)) {
            return 'available';
        }

        if (count($statuses) > 1) {
            return 'partial'; // Multiple statuses on same date
        }

        return $statuses[0];
    }

    /**
     * Get detailed information for a specific date
     * 
     * @param Carbon $date
     * @param Collection $bookings
     * @param Collection $holds
     * @param Collection $blocks
     * @param Collection $posBookings
     * @return array
     */
    protected function getDateDetails(
        Carbon $date,
        Collection $bookings,
        Collection $holds,
        Collection $blocks,
        Collection $posBookings
    ): array {
        $details = [
            'bookings' => [],
            'holds' => [],
            'blocks' => [],
            'pos_bookings' => [],
        ];

        // Get bookings for this date
        $dayBookings = $bookings->filter(function ($booking) use ($date) {
            return $date->between(
                Carbon::parse($booking->start_date),
                Carbon::parse($booking->end_date)
            );
        });

        foreach ($dayBookings as $booking) {
            $details['bookings'][] = [
                'id' => $booking->id,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'status' => $booking->status,
                'customer_name' => $booking->customer->name ?? 'N/A',
            ];
        }

        // Get holds for this date
        $dayHolds = $holds->filter(function ($hold) use ($date) {
            return $date->between(
                Carbon::parse($hold->start_date),
                Carbon::parse($hold->end_date)
            );
        });

        foreach ($dayHolds as $hold) {
            $details['holds'][] = [
                'id' => $hold->id,
                'start_date' => $hold->start_date,
                'end_date' => $hold->end_date,
                'expires_at' => $hold->hold_expiry_at?->format('Y-m-d H:i:s'),
                'customer_name' => $hold->customer->name ?? 'N/A',
            ];
        }

        // Get maintenance blocks for this date
        $dayBlocks = $blocks->filter(function ($block) use ($date) {
            return $date->between($block->start_date, $block->end_date);
        });

        foreach ($dayBlocks as $block) {
            $details['blocks'][] = [
                'id' => $block->id,
                'title' => $block->title,
                'start_date' => $block->start_date->format('Y-m-d'),
                'end_date' => $block->end_date->format('Y-m-d'),
                'block_type' => $block->block_type,
            ];
        }

        // Get POS bookings for this date
        $dayPOS = $posBookings->filter(function ($pos) use ($date) {
            return $date->between(
                Carbon::parse($pos->start_date),
                Carbon::parse($pos->end_date)
            );
        });

        foreach ($dayPOS as $pos) {
            $details['pos_bookings'][] = [
                'id' => $pos->id,
                'start_date' => $pos->start_date,
                'end_date' => $pos->end_date,
                'status' => $pos->status,
            ];
        }

        return $details;
    }

    /**
     * Generate summary statistics from calendar data
     * 
     * @param array $calendar
     * @return array
     */
    protected function generateSummary(array $calendar): array
    {
        $statuses = collect($calendar)->pluck('status')->countBy();

        return [
            'available_days' => $statuses->get('available', 0),
            'booked_days' => $statuses->get('booked', 0),
            'blocked_days' => $statuses->get('blocked', 0),
            'hold_days' => $statuses->get('hold', 0),
            'partial_days' => $statuses->get('partial', 0),
            'occupancy_rate' => $this->calculateOccupancyRate($calendar),
        ];
    }

    /**
     * Calculate occupancy rate (percentage of non-available days)
     * 
     * @param array $calendar
     * @return float
     */
    protected function calculateOccupancyRate(array $calendar): float
    {
        if (empty($calendar)) {
            return 0.0;
        }

        $totalDays = count($calendar);
        $unavailableDays = collect($calendar)
            ->whereNotIn('status', ['available'])
            ->count();

        return round(($unavailableDays / $totalDays) * 100, 2);
    }

    /**
     * Get confirmed bookings in date range
     * 
     * @param int $hoardingId
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    protected function getBookingsInRange(int $hoardingId, Carbon $start, Carbon $end): Collection
    {
        return Booking::where('hoarding_id', $hoardingId)
            ->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_PAYMENT_HOLD,
            ])
            ->where(function ($q) use ($start, $end) {
                $q->where('start_date', '<=', $end->format('Y-m-d'))
                  ->where('end_date', '>=', $start->format('Y-m-d'));
            })
            ->with('customer:id,name,email')
            ->get();
    }

    /**
     * Get active payment holds in date range
     * 
     * @param int $hoardingId
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    protected function getHoldsInRange(int $hoardingId, Carbon $start, Carbon $end): Collection
    {
        return Booking::where('hoarding_id', $hoardingId)
            ->where('status', Booking::STATUS_PENDING_PAYMENT_HOLD)
            ->where('hold_expiry_at', '>', now())
            ->where(function ($q) use ($start, $end) {
                $q->where('start_date', '<=', $end->format('Y-m-d'))
                  ->where('end_date', '>=', $start->format('Y-m-d'));
            })
            ->with('customer:id,name,email')
            ->get();
    }

    /**
     * Get active maintenance blocks in date range
     * 
     * @param int $hoardingId
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    protected function getMaintenanceBlocksInRange(int $hoardingId, Carbon $start, Carbon $end): Collection
    {
        return MaintenanceBlock::forHoarding($hoardingId)
            ->active()
            ->overlapping($start, $end)
            ->get();
    }

    /**
     * Get POS bookings in date range (if module exists)
     * 
     * @param int $hoardingId
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    protected function getPOSBookingsInRange(int $hoardingId, Carbon $start, Carbon $end): Collection
    {
        if (!class_exists(\Modules\POS\Models\POSBooking::class)) {
            return collect([]);
        }

        return \Modules\POS\Models\POSBooking::where('hoarding_id', $hoardingId)
            ->whereIn('status', ['confirmed', 'active'])
            ->where(function ($q) use ($start, $end) {
                $q->where('start_date', '<=', $end->format('Y-m-d'))
                  ->where('end_date', '>=', $start->format('Y-m-d'));
            })
            ->get();
    }
}
