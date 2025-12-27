<?php

namespace Modules\Hoardings\Services;

use App\Models\Hoarding;
use App\Models\Booking;
use App\Models\BookingDraft;
use App\Models\User;
use Modules\DOOH\Models\DOOHPackage;
use Modules\Settings\Services\SettingsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HoardingBookingService
{
    protected DynamicPriceCalculator $priceCalculator;
    protected SettingsService $settingsService;

    public function __construct(
        DynamicPriceCalculator $priceCalculator,
        SettingsService $settingsService
    ) {
        $this->priceCalculator = $priceCalculator;
        $this->settingsService = $settingsService;
    }

    /**
     * Step 1: Fetch complete hoarding details with availability
     */
    public function getHoardingDetails(int $hoardingId): array
    {
        $hoarding = Hoarding::with([
            'vendor',
            'hoardingGeo',
            'doohScreens.packages' => function ($query) {
                $query->where('is_active', true);
            }
        ])->findOrFail($hoardingId);

        // Check if hoarding is available
        if ($hoarding->status !== Hoarding::STATUS_ACTIVE) {
            throw new Exception('This hoarding is currently not available for booking');
        }

        return [
            'hoarding' => [
                'id' => $hoarding->id,
                'title' => $hoarding->title,
                'description' => $hoarding->description,
                'type' => $hoarding->type,
                'width' => $hoarding->width,
                'height' => $hoarding->height,
                'lighting_type' => $hoarding->lighting_type,
                'monthly_price' => $hoarding->monthly_price,
                'weekly_price' => $hoarding->weekly_price,
                'enable_weekly_booking' => $hoarding->enable_weekly_booking,
                'location' => [
                    'address' => $hoarding->address,
                    'city' => $hoarding->city,
                    'state' => $hoarding->state,
                    'pincode' => $hoarding->pincode,
                    'latitude' => $hoarding->latitude,
                    'longitude' => $hoarding->longitude,
                ],
                'images' => $hoarding->images ? json_decode($hoarding->images, true) : [],
                'rating' => [
                    'average' => $hoarding->rating ?? 0,
                    'count' => $hoarding->rating_count ?? 0,
                ],
                'status' => $hoarding->status,
            ],
            'vendor' => [
                'id' => $hoarding->vendor_id,
                'name' => $hoarding->vendor->name,
                'email' => $hoarding->vendor->email,
                'phone' => $hoarding->vendor->phone,
                'rating' => $hoarding->vendor->rating ?? 0,
            ],
            'availability' => $this->getHoardingAvailability($hoardingId),
            'booking_rules' => $this->getBookingRules($hoarding),
        ];
    }

    /**
     * Get hoarding availability (booked dates, blocked dates, holds, maintenance)
     */
    protected function getHoardingAvailability(int $hoardingId): array
    {
        $today = Carbon::today();
        $maxFutureDate = $today->copy()->addYear();

        // Get booked dates
        $bookedPeriods = Booking::where('hoarding_id', $hoardingId)
            ->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_PAYMENT_HOLD,
                Booking::STATUS_PENDING_PAYMENT_HOLD
            ])
            ->where('end_date', '>=', $today)
            ->select('start_date', 'end_date', 'status')
            ->get()
            ->map(fn($booking) => [
                'start_date' => $booking->start_date->format('Y-m-d'),
                'end_date' => $booking->end_date->format('Y-m-d'),
                'type' => 'booked',
                'status' => $booking->status,
            ]);

        // Get maintenance blocks (if exists in your system)
        $maintenancePeriods = []; // TODO: Implement if you have maintenance_blocks table

        // Get temporary holds that are not expired
        $holdPeriods = Booking::where('hoarding_id', $hoardingId)
            ->where('status', Booking::STATUS_PENDING_PAYMENT_HOLD)
            ->where('hold_expiry_at', '>', now())
            ->select('start_date', 'end_date', 'hold_expiry_at')
            ->get()
            ->map(fn($hold) => [
                'start_date' => $hold->start_date->format('Y-m-d'),
                'end_date' => $hold->end_date->format('Y-m-d'),
                'type' => 'hold',
                'expires_at' => $hold->hold_expiry_at->toIso8601String(),
            ]);

        return [
            'booked_periods' => $bookedPeriods->toArray(),
            'maintenance_periods' => $maintenancePeriods,
            'hold_periods' => $holdPeriods->toArray(),
            'available_from' => $today->format('Y-m-d'),
            'available_until' => $maxFutureDate->format('Y-m-d'),
        ];
    }

    /**
     * Get booking rules for the hoarding
     */
    protected function getBookingRules(Hoarding $hoarding): array
    {
        return [
            'minimum_duration_days' => (int) $this->settingsService->get('min_booking_duration_days', 1),
            'maximum_duration_days' => (int) $this->settingsService->get('max_booking_duration_days', 365),
            'minimum_advance_days' => (int) $this->settingsService->get('min_advance_booking_days', 0),
            'maximum_advance_days' => (int) $this->settingsService->get('max_advance_booking_days', 365),
            'allow_weekly_booking' => $hoarding->enable_weekly_booking,
            'weekly_price_available' => $hoarding->weekly_price > 0,
            'hold_duration_minutes' => (int) $this->settingsService->get('booking_hold_duration_minutes', 30),
        ];
    }

    /**
     * Step 2: Get available packages for this hoarding
     */
    public function getAvailablePackages(int $hoardingId): array
    {
        $hoarding = Hoarding::findOrFail($hoardingId);

        // Get DOOH packages if hoarding has DOOH screens
        $doohPackages = DOOHPackage::whereHas('doohScreen', function ($query) use ($hoardingId) {
            $query->where('hoarding_id', $hoardingId);
        })
            ->where('is_active', true)
            ->orderBy('price_per_month')
            ->get()
            ->map(fn($package) => [
                'id' => $package->id,
                'name' => $package->package_name,
                'description' => $package->description,
                'price_per_month' => $package->price_per_month,
                'discount_percent' => $package->discount_percent,
                'min_booking_months' => $package->min_booking_months,
                'max_booking_months' => $package->max_booking_months,
                'features' => json_decode($package->features, true) ?? [],
                'type' => 'dooh',
                'offer_tag' => $package->discount_percent > 0 ? "Save {$package->discount_percent}%" : null,
            ]);

        // Add standard monthly/weekly options
        $standardPackages = [];

        if ($hoarding->monthly_price > 0) {
            $standardPackages[] = [
                'id' => null,
                'name' => 'Standard Monthly',
                'description' => 'Pay per month basis',
                'price_per_month' => $hoarding->monthly_price,
                'discount_percent' => 0,
                'min_booking_months' => 1,
                'max_booking_months' => 12,
                'features' => [],
                'type' => 'standard',
                'offer_tag' => null,
            ];
        }

        if ($hoarding->enable_weekly_booking && $hoarding->weekly_price > 0) {
            $standardPackages[] = [
                'id' => null,
                'name' => 'Weekly Booking',
                'description' => 'Pay per week basis (up to 3 weeks)',
                'price_per_week' => $hoarding->weekly_price,
                'discount_percent' => 0,
                'min_booking_weeks' => 1,
                'max_booking_weeks' => 3,
                'features' => [],
                'type' => 'weekly',
                'offer_tag' => null,
            ];
        }

        return [
            'dooh_packages' => $doohPackages->toArray(),
            'standard_packages' => $standardPackages,
        ];
    }

    /**
     * Step 3: Validate date selection against availability
     */
    public function validateDateSelection(
        int $hoardingId,
        string $startDate,
        string $endDate,
        ?int $packageId = null
    ): array {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $hoarding = Hoarding::findOrFail($hoardingId);

        // Validate dates are not in past
        if ($start->isPast()) {
            throw new Exception('Start date cannot be in the past');
        }

        if ($end->lessThanOrEqualTo($start)) {
            throw new Exception('End date must be after start date');
        }

        // Calculate duration
        $durationDays = $start->diffInDays($end) + 1;

        // Check minimum/maximum duration
        $minDays = (int) $this->settingsService->get('min_booking_duration_days', 1);
        $maxDays = (int) $this->settingsService->get('max_booking_duration_days', 365);

        if ($durationDays < $minDays) {
            throw new Exception("Minimum booking duration is {$minDays} days");
        }

        if ($durationDays > $maxDays) {
            throw new Exception("Maximum booking duration is {$maxDays} days");
        }

        // Check advance booking rules
        $minAdvance = (int) $this->settingsService->get('min_advance_booking_days', 0);
        $maxAdvance = (int) $this->settingsService->get('max_advance_booking_days', 365);

        $advanceDays = Carbon::today()->diffInDays($start, false);

        if ($advanceDays < $minAdvance) {
            throw new Exception("Bookings must be made at least {$minAdvance} days in advance");
        }

        if ($advanceDays > $maxAdvance) {
            throw new Exception("Bookings cannot be made more than {$maxAdvance} days in advance");
        }

        // Check for overlapping bookings
        $hasOverlap = Booking::where('hoarding_id', $hoardingId)
            ->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_PAYMENT_HOLD,
                Booking::STATUS_PENDING_PAYMENT_HOLD
            ])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->exists();

        if ($hasOverlap) {
            throw new Exception('Selected dates overlap with existing booking. Please choose different dates.');
        }

        // Validate package constraints if package selected
        if ($packageId) {
            $package = DOOHPackage::findOrFail($packageId);
            $months = ceil($durationDays / 30);

            if ($months < $package->min_booking_months) {
                throw new Exception("This package requires minimum {$package->min_booking_months} months");
            }

            if ($months > $package->max_booking_months) {
                throw new Exception("This package allows maximum {$package->max_booking_months} months");
            }
        }

        return [
            'valid' => true,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'duration_days' => $durationDays,
            'duration_type' => $this->determineDurationType($durationDays),
            'message' => 'Dates are available for booking',
        ];
    }

    /**
     * Determine duration type based on days
     */
    protected function determineDurationType(int $days): string
    {
        if ($days >= 30) {
            return BookingDraft::DURATION_MONTHS;
        } elseif ($days >= 7 && $days % 7 == 0) {
            return BookingDraft::DURATION_WEEKS;
        }

        return BookingDraft::DURATION_DAYS;
    }

    /**
     * Step 4: Create or update draft booking with price snapshot
     */
    public function createOrUpdateDraft(
        User $customer,
        int $hoardingId,
        ?int $packageId = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $couponCode = null
    ): BookingDraft {
        DB::beginTransaction();

        try {
            // Find existing active draft for this customer and hoarding
            $draft = BookingDraft::where('customer_id', $customer->id)
                ->where('hoarding_id', $hoardingId)
                ->where('is_converted', false)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();

            if (!$draft) {
                $draft = new BookingDraft();
                $draft->customer_id = $customer->id;
                $draft->hoarding_id = $hoardingId;
                $draft->step = BookingDraft::STEP_HOARDING_SELECTED;
            }

            // Update package if provided
            if ($packageId !== null) {
                $draft->package_id = $packageId;
                $draft->updateStep(BookingDraft::STEP_PACKAGE_SELECTED);
            }

            // Update dates if provided
            if ($startDate && $endDate) {
                // Validate dates first
                $this->validateDateSelection($hoardingId, $startDate, $endDate, $packageId);

                $draft->start_date = Carbon::parse($startDate);
                $draft->end_date = Carbon::parse($endDate);
                $draft->duration_days = $draft->calculateDuration();
                $draft->duration_type = $this->determineDurationType($draft->duration_days);
                $draft->updateStep(BookingDraft::STEP_DATES_SELECTED);

                // Calculate and freeze price
                $this->calculateAndFreezeDraftPrice($draft, $couponCode);
            }

            // Update coupon if provided
            if ($couponCode) {
                $draft->coupon_code = $couponCode;
            }

            // Refresh expiry
            $holdMinutes = (int) $this->settingsService->get('draft_expiry_minutes', 30);
            $draft->refreshExpiry($holdMinutes);

            $draft->save();

            DB::commit();

            return $draft->fresh(['hoarding', 'package', 'customer']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate price and create snapshot
     */
    protected function calculateAndFreezeDraftPrice(BookingDraft $draft, ?string $couponCode = null): void
    {
        if (!$draft->hasValidDates()) {
            return;
        }

        // Prepare vendor discounts (if any)
        $vendorDiscounts = [];
        
        // TODO: Apply coupon/offer logic here
        // if ($couponCode) {
        //     $offer = Offer::where('code', $couponCode)->valid()->first();
        //     if ($offer) {
        //         $vendorDiscounts = [
        //             'type' => $offer->discount_type,
        //             'value' => $offer->discount_value,
        //         ];
        //     }
        // }

        // Calculate price using DynamicPriceCalculator
        $priceResult = $this->priceCalculator->calculate(
            $draft->hoarding_id,
            $draft->start_date->format('Y-m-d'),
            $draft->end_date->format('Y-m-d'),
            $draft->package_id,
            $vendorDiscounts
        );

        // Freeze price in draft
        $draft->price_snapshot = $priceResult;
        $draft->base_price = $priceResult['base_price'];
        $draft->discount_amount = $priceResult['discount_applied'];
        $draft->gst_amount = $priceResult['gst'];
        $draft->total_amount = $priceResult['final_price'];

        // Store applied offers
        if (!empty($vendorDiscounts)) {
            $draft->applied_offers = [
                'coupon_code' => $couponCode,
                'discount_type' => $vendorDiscounts['type'] ?? null,
                'discount_value' => $vendorDiscounts['value'] ?? null,
                'discount_amount' => $draft->discount_amount,
            ];
        }
    }

    /**
     * Step 5: Get review summary
     */
    public function getReviewSummary(BookingDraft $draft): array
    {
        if (!$draft->hasValidDates() || !$draft->total_amount) {
            throw new Exception('Draft is incomplete. Please select dates first.');
        }

        $hoarding = $draft->hoarding;
        $package = $draft->package;

        return [
            'draft_id' => $draft->id,
            'hoarding' => [
                'id' => $hoarding->id,
                'title' => $hoarding->title,
                'location' => $hoarding->city . ', ' . $hoarding->state,
                'type' => $hoarding->type,
                'image' => $hoarding->images ? json_decode($hoarding->images, true)[0] ?? null : null,
            ],
            'package' => $package ? [
                'id' => $package->id,
                'name' => $package->package_name,
                'features' => json_decode($package->features, true) ?? [],
            ] : [
                'name' => 'Standard Booking',
                'type' => $draft->duration_type,
            ],
            'booking_period' => [
                'start_date' => $draft->start_date->format('d M Y'),
                'end_date' => $draft->end_date->format('d M Y'),
                'duration_days' => $draft->duration_days,
                'duration_display' => $this->formatDuration($draft->duration_days, $draft->duration_type),
            ],
            'pricing' => [
                'base_price' => $draft->base_price,
                'discount_amount' => $draft->discount_amount,
                'gst_amount' => $draft->gst_amount,
                'total_amount' => $draft->total_amount,
                'price_snapshot' => $draft->price_snapshot,
                'applied_offers' => $draft->applied_offers,
            ],
            'expires_at' => $draft->expires_at->toIso8601String(),
            'hold_duration_minutes' => (int) $this->settingsService->get('booking_hold_duration_minutes', 30),
        ];
    }

    /**
     * Format duration for display
     */
    protected function formatDuration(int $days, string $type): string
    {
        switch ($type) {
            case BookingDraft::DURATION_MONTHS:
                $months = floor($days / 30);
                $remainingDays = $days % 30;
                $text = $months . ($months == 1 ? ' month' : ' months');
                if ($remainingDays > 0) {
                    $text .= ' and ' . $remainingDays . ($remainingDays == 1 ? ' day' : ' days');
                }
                return $text;

            case BookingDraft::DURATION_WEEKS:
                $weeks = ceil($days / 7);
                return $weeks . ($weeks == 1 ? ' week' : ' weeks');

            default:
                return $days . ($days == 1 ? ' day' : ' days');
        }
    }

    /**
     * Step 6: Confirm booking & lock inventory
     */
    public function confirmAndLockBooking(BookingDraft $draft): Booking
    {
        DB::beginTransaction();

        try {
            // Final validation
            if ($draft->isExpired()) {
                throw new Exception('Draft has expired. Please start again.');
            }

            if ($draft->is_converted) {
                throw new Exception('This draft has already been converted to a booking.');
            }

            // Check availability one more time
            $this->validateDateSelection(
                $draft->hoarding_id,
                $draft->start_date->format('Y-m-d'),
                $draft->end_date->format('Y-m-d'),
                $draft->package_id
            );

            // Create booking with hold
            $holdDuration = (int) $this->settingsService->get('booking_hold_duration_minutes', 30);

            $booking = new Booking();
            $booking->customer_id = $draft->customer_id;
            $booking->vendor_id = $draft->hoarding->vendor_id;
            $booking->hoarding_id = $draft->hoarding_id;
            $booking->start_date = $draft->start_date;
            $booking->end_date = $draft->end_date;
            $booking->duration_type = $draft->duration_type;
            $booking->duration_days = $draft->duration_days;
            $booking->total_amount = $draft->total_amount;
            $booking->status = Booking::STATUS_PENDING_PAYMENT_HOLD;
            $booking->payment_status = 'pending';
            $booking->hold_expiry_at = now()->addMinutes($holdDuration);

            // Store snapshot from draft
            $booking->booking_snapshot = $draft->price_snapshot;

            $booking->save();

            // Mark draft as converted
            $draft->markConverted($booking);

            // Update draft step
            $draft->updateStep(BookingDraft::STEP_PAYMENT_PENDING);

            DB::commit();

            return $booking->fresh(['hoarding', 'customer', 'vendor']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Clean up expired drafts
     */
    public function cleanupExpiredDrafts(): int
    {
        return BookingDraft::expired()->delete();
    }

    /**
     * Release expired booking holds
     */
    public function releaseExpiredHolds(): int
    {
        $expiredBookings = Booking::where('status', Booking::STATUS_PENDING_PAYMENT_HOLD)
            ->where('hold_expiry_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($expiredBookings as $booking) {
            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancellation_reason' => 'Payment hold expired',
            ]);
            $count++;
        }

        return $count;
    }
}
