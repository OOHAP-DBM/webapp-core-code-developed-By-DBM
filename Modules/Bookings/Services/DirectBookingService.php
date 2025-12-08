<?php

namespace Modules\Bookings\Services;

use App\Models\Booking;
use App\Models\User;
use Modules\Hoardings\Models\Hoarding;
use Modules\Settings\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * Direct Booking Service
 * Handles customer direct bookings without quotation flow
 */
class DirectBookingService
{
    protected SettingsService $settingsService;
    protected BookingService $bookingService;

    public function __construct(
        SettingsService $settingsService,
        BookingService $bookingService
    ) {
        $this->settingsService = $settingsService;
        $this->bookingService = $bookingService;
    }

    /**
     * Validate and create a direct booking
     *
     * @param array $data
     * @return Booking
     * @throws Exception
     */
    public function createDirectBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            // Validate hoarding exists and is active
            $hoarding = Hoarding::where('id', $data['hoarding_id'])
                ->where('status', 'active')
                ->first();

            if (!$hoarding) {
                throw new Exception('Hoarding not found or not available for booking');
            }

            // Validate customer
            $customer = User::where('id', $data['customer_id'])
                ->where('role', 'customer')
                ->first();

            if (!$customer) {
                throw new Exception('Invalid customer');
            }

            // Parse dates
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);
            $now = Carbon::now();

            // Validation: Check date order
            if ($endDate->lte($startDate)) {
                throw new Exception('End date must be after start date');
            }

            // Validation: Check if start date is in the future
            if ($startDate->lt($now->startOfDay())) {
                throw new Exception('Start date cannot be in the past');
            }

            // Validation: Max future start date
            $maxFutureMonths = $this->getMaxFutureBookingStartMonths();
            $maxAllowedStartDate = $now->copy()->addMonths($maxFutureMonths);
            
            if ($startDate->gt($maxAllowedStartDate)) {
                throw new Exception("Booking start date cannot be more than {$maxFutureMonths} months in the future");
            }

            // Validation: Minimum duration
            $durationDays = $startDate->diffInDays($endDate) + 1; // Include both start and end day
            $minDurationDays = $this->getBookingMinDurationDays();
            
            if ($durationDays < $minDurationDays) {
                throw new Exception("Minimum booking duration is {$minDurationDays} days");
            }

            // Validation: Maximum duration
            $maxDurationMonths = $this->getBookingMaxDurationMonths();
            $maxDurationDays = $maxDurationMonths * 30; // Rough conversion
            
            if ($durationDays > $maxDurationDays) {
                throw new Exception("Maximum booking duration is {$maxDurationMonths} months");
            }

            // Validation: Check availability (including grace period)
            $this->validateAvailability($hoarding->id, $startDate, $endDate);

            // Calculate pricing
            $pricing = $this->calculatePricing($hoarding, $startDate, $endDate);

            // Create booking snapshot
            $snapshot = $this->createBookingSnapshot($hoarding, $customer, $startDate, $endDate, $pricing);

            // Get hold expiry time
            $holdMinutes = $this->getBookingHoldMinutes();
            $holdExpiryAt = Carbon::now()->addMinutes($holdMinutes);

            // Create booking record
            $booking = Booking::create([
                'quotation_id' => null, // Direct booking has no quotation
                'customer_id' => $customer->id,
                'vendor_id' => $hoarding->vendor_id,
                'hoarding_id' => $hoarding->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'duration_type' => 'days',
                'duration_days' => $durationDays,
                'total_amount' => $pricing['total'],
                'status' => Booking::STATUS_PENDING_PAYMENT_HOLD,
                'payment_status' => 'pending',
                'hold_expiry_at' => $holdExpiryAt,
                'booking_snapshot' => $snapshot,
                'customer_notes' => $data['customer_notes'] ?? null,
            ]);

            // Log status
            \App\Models\BookingStatusLog::create([
                'booking_id' => $booking->id,
                'from_status' => null,
                'to_status' => Booking::STATUS_PENDING_PAYMENT_HOLD,
                'changed_by' => $customer->id,
                'notes' => 'Direct booking created - waiting for payment',
            ]);

            Log::info('Direct booking created', [
                'booking_id' => $booking->id,
                'customer_id' => $customer->id,
                'hoarding_id' => $hoarding->id,
                'amount' => $pricing['total'],
                'duration_days' => $durationDays,
            ]);

            return $booking->fresh(['hoarding', 'customer', 'vendor']);
        });
    }

    /**
     * Validate hoarding availability for the given date range
     * Includes grace period check
     *
     * @param int $hoardingId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $excludeBookingId
     * @throws Exception
     */
    protected function validateAvailability(
        int $hoardingId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeBookingId = null
    ): void {
        $gracePeriodMinutes = $this->getGracePeriodMinutes();
        
        // Adjust dates to include grace period
        // If a booking ends on startDate-1, we need grace period buffer
        $adjustedStartDate = $startDate->copy()->subMinutes($gracePeriodMinutes);
        $adjustedEndDate = $endDate->copy()->addMinutes($gracePeriodMinutes);

        // Check for overlapping bookings
        $query = Booking::where('hoarding_id', $hoardingId)
            ->whereNotIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_REFUNDED])
            ->where(function ($q) use ($adjustedStartDate, $adjustedEndDate) {
                $q->where(function ($query) use ($adjustedStartDate, $adjustedEndDate) {
                    // Booking starts during our period
                    $query->whereBetween('start_date', [
                        $adjustedStartDate->format('Y-m-d'),
                        $adjustedEndDate->format('Y-m-d')
                    ]);
                })
                ->orWhere(function ($query) use ($adjustedStartDate, $adjustedEndDate) {
                    // Booking ends during our period
                    $query->whereBetween('end_date', [
                        $adjustedStartDate->format('Y-m-d'),
                        $adjustedEndDate->format('Y-m-d')
                    ]);
                })
                ->orWhere(function ($query) use ($adjustedStartDate, $adjustedEndDate) {
                    // Booking completely encompasses our period
                    $query->where('start_date', '<=', $adjustedStartDate->format('Y-m-d'))
                          ->where('end_date', '>=', $adjustedEndDate->format('Y-m-d'));
                });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        $conflictingBooking = $query->first();

        if ($conflictingBooking) {
            throw new Exception(
                'Hoarding is not available for the selected dates. ' .
                'Please choose different dates or another hoarding.'
            );
        }

        // Also check POS bookings if they exist
        if (class_exists(\Modules\POS\Models\POSBooking::class)) {
            $posConflict = \Modules\POS\Models\POSBooking::where('hoarding_id', $hoardingId)
                ->whereNotIn('status', ['cancelled'])
                ->where(function ($q) use ($adjustedStartDate, $adjustedEndDate) {
                    $q->where(function ($query) use ($adjustedStartDate, $adjustedEndDate) {
                        $query->whereBetween('start_date', [
                            $adjustedStartDate->format('Y-m-d'),
                            $adjustedEndDate->format('Y-m-d')
                        ]);
                    })
                    ->orWhere(function ($query) use ($adjustedStartDate, $adjustedEndDate) {
                        $query->whereBetween('end_date', [
                            $adjustedStartDate->format('Y-m-d'),
                            $adjustedEndDate->format('Y-m-d')
                        ]);
                    })
                    ->orWhere(function ($query) use ($adjustedStartDate, $adjustedEndDate) {
                        $query->where('start_date', '<=', $adjustedStartDate->format('Y-m-d'))
                              ->where('end_date', '>=', $adjustedEndDate->format('Y-m-d'));
                    });
                })
                ->first();

            if ($posConflict) {
                throw new Exception(
                    'Hoarding is not available for the selected dates. ' .
                    'Please choose different dates or another hoarding.'
                );
            }
        }
    }

    /**
     * Calculate pricing for the booking
     *
     * @param Hoarding $hoarding
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    protected function calculatePricing(Hoarding $hoarding, Carbon $startDate, Carbon $endDate): array
    {
        $durationDays = $startDate->diffInDays($endDate) + 1;
        
        // Base price per day
        $pricePerDay = (float) $hoarding->price_per_day;
        
        // Subtotal
        $subtotal = $pricePerDay * $durationDays;
        
        // Tax (GST - default 18%)
        $taxRate = (float) $this->settingsService->get('booking_tax_rate', 18.00);
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        
        // Total amount
        $total = round($subtotal + $taxAmount, 2);

        return [
            'price_per_day' => $pricePerDay,
            'duration_days' => $durationDays,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ];
    }

    /**
     * Create booking snapshot for audit trail
     *
     * @param Hoarding $hoarding
     * @param User $customer
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $pricing
     * @return array
     */
    protected function createBookingSnapshot(
        Hoarding $hoarding,
        User $customer,
        Carbon $startDate,
        Carbon $endDate,
        array $pricing
    ): array {
        return [
            'booking_type' => 'direct',
            'created_at' => now()->toIso8601String(),
            'hoarding' => [
                'id' => $hoarding->id,
                'name' => $hoarding->name,
                'location' => $hoarding->location,
                'width' => $hoarding->width,
                'height' => $hoarding->height,
                'price_per_day' => $hoarding->price_per_day,
                'vendor_id' => $hoarding->vendor_id,
            ],
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ],
            'dates' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'duration_days' => $pricing['duration_days'],
            ],
            'pricing' => $pricing,
            'settings_applied' => [
                'booking_hold_minutes' => $this->getBookingHoldMinutes(),
                'grace_period_minutes' => $this->getGracePeriodMinutes(),
                'tax_rate' => $pricing['tax_rate'],
            ],
        ];
    }

    /**
     * Get available hoardings for booking with filters
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAvailableHoardings(array $filters = [])
    {
        $query = Hoarding::where('status', 'active')
            ->with(['vendor', 'media']);

        // Filter by location
        if (!empty($filters['location'])) {
            $query->where('location', 'like', '%' . $filters['location'] . '%');
        }

        // Filter by city
        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        // Filter by type
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter by price range
        if (!empty($filters['min_price'])) {
            $query->where('price_per_day', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price_per_day', '<=', $filters['max_price']);
        }

        // Filter by size
        if (!empty($filters['min_width'])) {
            $query->where('width', '>=', $filters['min_width']);
        }

        if (!empty($filters['min_height'])) {
            $query->where('height', '>=', $filters['min_height']);
        }

        // Check availability for specific dates
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $startDate = Carbon::parse($filters['start_date']);
            $endDate = Carbon::parse($filters['end_date']);
            
            $gracePeriodMinutes = $this->getGracePeriodMinutes();
            $adjustedStartDate = $startDate->copy()->subMinutes($gracePeriodMinutes);
            $adjustedEndDate = $endDate->copy()->addMinutes($gracePeriodMinutes);

            // Exclude hoardings with conflicting bookings
            $query->whereDoesntHave('bookings', function ($q) use ($adjustedStartDate, $adjustedEndDate) {
                $q->whereNotIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_REFUNDED])
                  ->where(function ($query) use ($adjustedStartDate, $adjustedEndDate) {
                      $query->whereBetween('start_date', [
                          $adjustedStartDate->format('Y-m-d'),
                          $adjustedEndDate->format('Y-m-d')
                      ])
                      ->orWhereBetween('end_date', [
                          $adjustedStartDate->format('Y-m-d'),
                          $adjustedEndDate->format('Y-m-d')
                      ])
                      ->orWhere(function ($q2) use ($adjustedStartDate, $adjustedEndDate) {
                          $q2->where('start_date', '<=', $adjustedStartDate->format('Y-m-d'))
                             ->where('end_date', '>=', $adjustedEndDate->format('Y-m-d'));
                      });
                  });
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'price_per_day';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Check if a specific hoarding is available for given dates
     *
     * @param int $hoardingId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function checkHoardingAvailability(int $hoardingId, string $startDate, string $endDate): array
    {
        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            $this->validateAvailability($hoardingId, $start, $end);

            return [
                'available' => true,
                'message' => 'Hoarding is available for the selected dates',
            ];
        } catch (Exception $e) {
            return [
                'available' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    // Helper methods to get settings

    protected function getBookingHoldMinutes(): int
    {
        return (int) $this->settingsService->get('booking_hold_minutes', 30);
    }

    protected function getGracePeriodMinutes(): int
    {
        return (int) $this->settingsService->get('grace_period_minutes', 15);
    }

    protected function getMaxFutureBookingStartMonths(): int
    {
        return (int) $this->settingsService->get('max_future_booking_start_months', 12);
    }

    protected function getBookingMinDurationDays(): int
    {
        return (int) $this->settingsService->get('booking_min_duration_days', 7);
    }

    protected function getBookingMaxDurationMonths(): int
    {
        return (int) $this->settingsService->get('booking_max_duration_months', 12);
    }
}
