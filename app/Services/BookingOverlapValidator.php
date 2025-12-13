<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\MaintenanceBlock;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * PROMPT 101: Booking Overlap Validation Engine
 * PROMPT 102: Integrated with Maintenance Blocks
 * 
 * Comprehensive service for detecting and validating booking overlaps
 * Checks against:
 * - Confirmed bookings
 * - Active payment holds (not expired)
 * - Cancelled/refunded bookings (excluded)
 * - Grace periods between bookings
 * - POS bookings (if exists)
 * - Maintenance blocks (PROMPT 102)
 */
class BookingOverlapValidator
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Main validation method - checks if dates are available
     * 
     * @param int $hoardingId
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @param int|null $excludeBookingId Optional booking ID to exclude (for updates)
     * @param bool $includeGracePeriod Whether to include grace period buffer
     * @return array ['available' => bool, 'conflicts' => Collection, 'message' => string]
     */
    public function validateAvailability(
        int $hoardingId,
        $startDate,
        $endDate,
        ?int $excludeBookingId = null,
        bool $includeGracePeriod = true
    ): array {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        // Adjust dates for grace period if enabled
        if ($includeGracePeriod) {
            $gracePeriodMinutes = $this->getGracePeriodMinutes();
            $adjustedStart = $start->copy()->subMinutes($gracePeriodMinutes);
            $adjustedEnd = $end->copy()->addMinutes($gracePeriodMinutes);
        } else {
            $adjustedStart = $start;
            $adjustedEnd = $end;
        }

        // Get all conflicting bookings
        $conflicts = $this->getConflictingBookings(
            $hoardingId,
            $adjustedStart,
            $adjustedEnd,
            $excludeBookingId
        );

        // Check for active holds
        $holdConflicts = $this->getConflictingHolds(
            $hoardingId,
            $adjustedStart,
            $adjustedEnd,
            $excludeBookingId
        );

        // Check POS bookings if module exists
        $posConflicts = $this->getConflictingPOSBookings(
            $hoardingId,
            $adjustedStart,
            $adjustedEnd,
            $excludeBookingId
        );

        // Check maintenance blocks (PROMPT 102)
        $maintenanceConflicts = $this->getConflictingMaintenanceBlocks(
            $hoardingId,
            $adjustedStart,
            $adjustedEnd
        );

        // Merge all conflicts
        $allConflicts = $conflicts->merge($holdConflicts)->merge($posConflicts)->merge($maintenanceConflicts);

        if ($allConflicts->isEmpty()) {
            return [
                'available' => true,
                'conflicts' => collect([]),
                'message' => 'Dates are available for booking',
                'checked_period' => [
                    'start' => $adjustedStart->format('Y-m-d H:i:s'),
                    'end' => $adjustedEnd->format('Y-m-d H:i:s'),
                ],
            ];
        }

        return [
            'available' => false,
            'conflicts' => $allConflicts,
            'message' => $this->buildConflictMessage($allConflicts, $start, $end),
            'conflict_details' => $this->formatConflictDetails($allConflicts),
            'checked_period' => [
                'start' => $adjustedStart->format('Y-m-d H:i:s'),
                'end' => $adjustedEnd->format('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * Get conflicting confirmed bookings
     * 
     * @param int $hoardingId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $excludeBookingId
     * @return Collection
     */
    protected function getConflictingBookings(
        int $hoardingId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeBookingId = null
    ): Collection {
        $query = Booking::where('hoarding_id', $hoardingId)
            ->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_PAYMENT_HOLD,
            ])
            ->where(function ($q) use ($startDate, $endDate) {
                // Overlap detection: (StartA <= EndB) AND (EndA >= StartB)
                $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where('end_date', '>=', $startDate->format('Y-m-d'));
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->with(['customer', 'vendor'])
            ->get()
            ->map(function ($booking) {
                return [
                    'type' => 'booking',
                    'id' => $booking->id,
                    'start_date' => $booking->start_date->format('Y-m-d'),
                    'end_date' => $booking->end_date->format('Y-m-d'),
                    'status' => $booking->status,
                    'customer_name' => $booking->customer->name ?? 'N/A',
                    'amount' => $booking->total_amount,
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                ];
            });
    }

    /**
     * Get conflicting active payment holds (not expired)
     * 
     * @param int $hoardingId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $excludeBookingId
     * @return Collection
     */
    protected function getConflictingHolds(
        int $hoardingId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeBookingId = null
    ): Collection {
        $query = Booking::where('hoarding_id', $hoardingId)
            ->where('status', Booking::STATUS_PENDING_PAYMENT_HOLD)
            ->where('hold_expiry_at', '>', now()) // Only active holds
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where('end_date', '>=', $startDate->format('Y-m-d'));
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->with(['customer'])
            ->get()
            ->map(function ($booking) {
                return [
                    'type' => 'hold',
                    'id' => $booking->id,
                    'start_date' => $booking->start_date->format('Y-m-d'),
                    'end_date' => $booking->end_date->format('Y-m-d'),
                    'status' => 'pending_payment_hold',
                    'customer_name' => $booking->customer->name ?? 'N/A',
                    'expires_at' => $booking->hold_expiry_at?->format('Y-m-d H:i:s'),
                    'minutes_remaining' => $booking->getHoldMinutesRemaining(),
                    'amount' => $booking->total_amount,
                ];
            });
    }

    /**
     * Get conflicting POS bookings (if module exists)
     * 
     * @param int $hoardingId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $excludeBookingId
     * @return Collection
     */
    protected function getConflictingPOSBookings(
        int $hoardingId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeBookingId = null
    ): Collection {
        // Check if POS module exists
        if (!class_exists(\Modules\POS\Models\POSBooking::class)) {
            return collect([]);
        }

        $query = \Modules\POS\Models\POSBooking::where('hoarding_id', $hoardingId)
            ->whereIn('status', ['confirmed', 'active'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where('end_date', '>=', $startDate->format('Y-m-d'));
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->get()
            ->map(function ($posBooking) {
                return [
                    'type' => 'pos_booking',
                    'id' => $posBooking->id,
                    'start_date' => $posBooking->start_date,
                    'end_date' => $posBooking->end_date,
                    'status' => $posBooking->status,
                    'vendor_name' => $posBooking->vendor->name ?? 'N/A',
                    'amount' => $posBooking->total_amount ?? 0,
                    'created_at' => $posBooking->created_at->format('Y-m-d H:i:s'),
                ];
            });
    }

    /**
     * Get conflicting maintenance blocks (PROMPT 102)
     * 
     * @param int $hoardingId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    protected function getConflictingMaintenanceBlocks(
        int $hoardingId,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        return MaintenanceBlock::forHoarding($hoardingId)
            ->active()
            ->overlapping($startDate, $endDate)
            ->with('creator:id,name')
            ->get()
            ->map(function ($block) {
                return [
                    'type' => 'maintenance_block',
                    'id' => $block->id,
                    'title' => $block->title,
                    'start_date' => $block->start_date->format('Y-m-d'),
                    'end_date' => $block->end_date->format('Y-m-d'),
                    'block_type' => $block->block_type,
                    'description' => $block->description,
                    'created_by' => $block->creator->name ?? 'N/A',
                    'created_at' => $block->created_at->format('Y-m-d H:i:s'),
                ];
            });
    }

    /**
     * Build human-readable conflict message
     * 
     * @param Collection $conflicts
     * @param Carbon $requestedStart
     * @param Carbon $requestedEnd
     * @return string
     */
    protected function buildConflictMessage(Collection $conflicts, Carbon $requestedStart, Carbon $requestedEnd): string
    {
        $count = $conflicts->count();
        $types = $conflicts->pluck('type')->unique();

        if ($count === 1) {
            $conflict = $conflicts->first();
            $typeLabel = match ($conflict['type']) {
                'booking' => 'confirmed booking',
                'hold' => 'active payment hold',
                'pos_booking' => 'POS booking',
                'maintenance_block' => 'maintenance block: ' . ($conflict['title'] ?? 'maintenance'),
                default => 'booking',
            };

            return sprintf(
                'Hoarding not available: Conflicts with %s from %s to %s',
                $typeLabel,
                $conflict['start_date'],
                $conflict['end_date']
            );
        }

        $typeCounts = [];
        foreach ($types as $type) {
            $typeCount = $conflicts->where('type', $type)->count();
            $label = match($type) {
                'hold' => 'hold(s)',
                'maintenance_block' => 'maintenance block(s)',
                default => str_replace('_', ' ', $type) . '(s)',
            };
            $typeCounts[] = "$typeCount $label";
        }

        return sprintf(
            'Hoarding not available for %s to %s: Conflicts with %s',
            $requestedStart->format('Y-m-d'),
            $requestedEnd->format('Y-m-d'),
            implode(', ', $typeCounts)
        );
    }

    /**
     * Format conflict details for API response
     * 
     * @param Collection $conflicts
     * @return array
     */
    protected function formatConflictDetails(Collection $conflicts): array
    {
        return [
            'total_conflicts' => $conflicts->count(),
            'by_type' => [
                'confirmed_bookings' => $conflicts->where('type', 'booking')->count(),
                'active_holds' => $conflicts->where('type', 'hold')->count(),
                'pos_bookings' => $conflicts->where('type', 'pos_booking')->count(),
            ],
            'conflicts' => $conflicts->take(10)->values()->toArray(), // Limit to 10 for performance
        ];
    }

    /**
     * Quick availability check (returns boolean only)
     * 
     * @param int $hoardingId
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @param int|null $excludeBookingId
     * @return bool
     */
    public function isAvailable(
        int $hoardingId,
        $startDate,
        $endDate,
        ?int $excludeBookingId = null
    ): bool {
        $result = $this->validateAvailability($hoardingId, $startDate, $endDate, $excludeBookingId);
        return $result['available'];
    }

    /**
     * Get all overlapping dates within a date range
     * Useful for calendar views
     * 
     * @param int $hoardingId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getOccupiedDates(int $hoardingId, Carbon $startDate, Carbon $endDate): array
    {
        $bookings = Booking::where('hoarding_id', $hoardingId)
            ->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_PAYMENT_HOLD,
            ])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where('end_date', '>=', $startDate->format('Y-m-d'));
            })
            ->get();

        $holds = Booking::where('hoarding_id', $hoardingId)
            ->where('status', Booking::STATUS_PENDING_PAYMENT_HOLD)
            ->where('hold_expiry_at', '>', now())
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where('end_date', '>=', $startDate->format('Y-m-d'));
            })
            ->get();

        $occupiedDates = [];

        // Mark dates from confirmed bookings
        foreach ($bookings as $booking) {
            $current = Carbon::parse($booking->start_date);
            $end = Carbon::parse($booking->end_date);

            while ($current->lte($end)) {
                $dateKey = $current->format('Y-m-d');
                if (!isset($occupiedDates[$dateKey])) {
                    $occupiedDates[$dateKey] = [
                        'date' => $dateKey,
                        'bookings' => [],
                        'holds' => [],
                    ];
                }
                $occupiedDates[$dateKey]['bookings'][] = $booking->id;
                $current->addDay();
            }
        }

        // Mark dates from active holds
        foreach ($holds as $hold) {
            $current = Carbon::parse($hold->start_date);
            $end = Carbon::parse($hold->end_date);

            while ($current->lte($end)) {
                $dateKey = $current->format('Y-m-d');
                if (!isset($occupiedDates[$dateKey])) {
                    $occupiedDates[$dateKey] = [
                        'date' => $dateKey,
                        'bookings' => [],
                        'holds' => [],
                    ];
                }
                $occupiedDates[$dateKey]['holds'][] = $hold->id;
                $current->addDay();
            }
        }

        return array_values($occupiedDates);
    }

    /**
     * Find next available date range for a given duration
     * 
     * @param int $hoardingId
     * @param int $durationDays
     * @param Carbon|null $searchFrom
     * @return array|null ['start_date' => Carbon, 'end_date' => Carbon] or null if not found
     */
    public function findNextAvailableSlot(
        int $hoardingId,
        int $durationDays,
        ?Carbon $searchFrom = null,
        int $maxSearchDays = 90
    ): ?array {
        $searchStart = $searchFrom ?? Carbon::today()->addDay();
        $searchLimit = $searchStart->copy()->addDays($maxSearchDays);

        $currentStart = $searchStart->copy();

        while ($currentStart->lte($searchLimit)) {
            $currentEnd = $currentStart->copy()->addDays($durationDays - 1);

            if ($this->isAvailable($hoardingId, $currentStart, $currentEnd)) {
                return [
                    'start_date' => $currentStart,
                    'end_date' => $currentEnd,
                    'duration_days' => $durationDays,
                ];
            }

            // Move to next day
            $currentStart->addDay();
        }

        return null; // No available slot found
    }

    /**
     * Get comprehensive availability report for a hoarding
     * 
     * @param int $hoardingId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getAvailabilityReport(int $hoardingId, Carbon $startDate, Carbon $endDate): array
    {
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        $confirmedBookings = Booking::where('hoarding_id', $hoardingId)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where('end_date', '>=', $startDate->format('Y-m-d'));
            })
            ->count();

        $activeHolds = Booking::where('hoarding_id', $hoardingId)
            ->where('status', Booking::STATUS_PENDING_PAYMENT_HOLD)
            ->where('hold_expiry_at', '>', now())
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where('end_date', '>=', $startDate->format('Y-m-d'));
            })
            ->count();

        $occupiedDates = $this->getOccupiedDates($hoardingId, $startDate, $endDate);
        $occupiedDaysCount = count($occupiedDates);
        $availableDaysCount = $totalDays - $occupiedDaysCount;
        $occupancyRate = $totalDays > 0 ? round(($occupiedDaysCount / $totalDays) * 100, 2) : 0;

        return [
            'hoarding_id' => $hoardingId,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'total_days' => $totalDays,
            ],
            'statistics' => [
                'confirmed_bookings' => $confirmedBookings,
                'active_holds' => $activeHolds,
                'occupied_days' => $occupiedDaysCount,
                'available_days' => $availableDaysCount,
                'occupancy_rate' => $occupancyRate,
            ],
            'occupied_dates' => $occupiedDates,
        ];
    }

    /**
     * Validate multiple date ranges at once (batch validation)
     * 
     * @param int $hoardingId
     * @param array $dateRanges [['start' => '2025-01-01', 'end' => '2025-01-10'], ...]
     * @return array
     */
    public function validateMultipleDateRanges(int $hoardingId, array $dateRanges): array
    {
        $results = [];

        foreach ($dateRanges as $index => $range) {
            $start = Carbon::parse($range['start']);
            $end = Carbon::parse($range['end']);

            $results[] = [
                'index' => $index,
                'start_date' => $range['start'],
                'end_date' => $range['end'],
                'validation' => $this->validateAvailability($hoardingId, $start, $end),
            ];
        }

        return [
            'total_ranges_checked' => count($results),
            'all_available' => collect($results)->every(fn($r) => $r['validation']['available']),
            'results' => $results,
        ];
    }

    /**
     * Get grace period minutes from settings
     * 
     * @return int
     */
    protected function getGracePeriodMinutes(): int
    {
        return (int) $this->settingsService->get('grace_period_minutes', 15);
    }

    /**
     * Check if overlapping bookings are allowed (from settings)
     * 
     * @return bool
     */
    protected function allowOverlappingBookings(): bool
    {
        return (bool) $this->settingsService->get('allow_overlapping_bookings', false);
    }
}
