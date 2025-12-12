<?php

namespace App\Services;

use App\Models\Hoarding;
use Carbon\Carbon;
use Illuminate\Validation\Validator;

class GracePeriodService
{
    /**
     * Calculate the earliest allowed start date for a hoarding
     *
     * @param Hoarding $hoarding
     * @return Carbon
     */
    public function calculateEarliestStartDate(Hoarding $hoarding): Carbon
    {
        return $hoarding->getEarliestAllowedStartDate();
    }

    /**
     * Validate if a start date meets grace period requirements
     *
     * @param Hoarding $hoarding
     * @param Carbon|string $requestedStartDate
     * @return bool
     */
    public function validateStartDate(Hoarding $hoarding, $requestedStartDate): bool
    {
        $startDate = $requestedStartDate instanceof Carbon 
            ? $requestedStartDate 
            : Carbon::parse($requestedStartDate);

        return $hoarding->isStartDateAllowed($startDate);
    }

    /**
     * Get validation error message for invalid start date
     *
     * @param Hoarding $hoarding
     * @return string
     */
    public function getValidationMessage(Hoarding $hoarding): string
    {
        return $hoarding->getGracePeriodValidationMessage();
    }

    /**
     * Get grace period details for a hoarding (API response)
     *
     * @param Hoarding $hoarding
     * @return array
     */
    public function getGracePeriodDetails(Hoarding $hoarding): array
    {
        return [
            'grace_period_days' => $hoarding->getGracePeriodDays(),
            'earliest_allowed_start_date' => $hoarding->getEarliestAllowedStartDate()->format('Y-m-d'),
            'earliest_allowed_start_date_formatted' => $hoarding->getEarliestAllowedStartDate()->format('d M Y'),
            'is_using_default' => $hoarding->grace_period_days === null,
            'default_grace_period_days' => (int) config('booking.grace_period_days', env('BOOKING_GRACE_PERIOD_DAYS', 2)),
        ];
    }

    /**
     * Create custom validation rule for grace period
     *
     * @param Hoarding $hoarding
     * @return string
     */
    public function getValidationRule(Hoarding $hoarding): string
    {
        $earliestDate = $hoarding->getEarliestAllowedStartDate()->format('Y-m-d');
        return "date|after_or_equal:{$earliestDate}";
    }

    /**
     * Add grace period validation to Laravel validator
     *
     * @param Validator $validator
     * @param string $field
     * @param Hoarding $hoarding
     * @return void
     */
    public function addValidationRule(Validator $validator, string $field, Hoarding $hoarding): void
    {
        $validator->after(function ($validator) use ($field, $hoarding) {
            $startDate = $validator->getData()[$field] ?? null;
            
            if ($startDate && !$this->validateStartDate($hoarding, $startDate)) {
                $validator->errors()->add($field, $this->getValidationMessage($hoarding));
            }
        });
    }

    /**
     * Calculate the number of days until earliest allowed start
     *
     * @param Hoarding $hoarding
     * @return int
     */
    public function getDaysUntilEarliestStart(Hoarding $hoarding): int
    {
        return Carbon::today()->diffInDays($hoarding->getEarliestAllowedStartDate(), false);
    }

    /**
     * Check if grace period can be overridden by vendor
     * (for future admin permission control)
     *
     * @param Hoarding $hoarding
     * @return bool
     */
    public function canOverrideGracePeriod(Hoarding $hoarding): bool
    {
        // Can be extended with vendor permission checks
        return true;
    }

    /**
     * Get suggested grace periods for vendor selection
     *
     * @return array
     */
    public function getSuggestedGracePeriods(): array
    {
        return [
            0 => 'Same day booking (No grace period)',
            1 => '1 day in advance',
            2 => '2 days in advance (Recommended)',
            3 => '3 days in advance',
            5 => '5 days in advance',
            7 => '1 week in advance',
            14 => '2 weeks in advance',
            30 => '1 month in advance',
        ];
    }

    /**
     * Validate grace period days value
     *
     * @param int|null $days
     * @return bool
     */
    public function isValidGracePeriod(?int $days): bool
    {
        if ($days === null) {
            return true; // null means use default
        }

        return $days >= 0 && $days <= 90; // Max 90 days grace period
    }
}
