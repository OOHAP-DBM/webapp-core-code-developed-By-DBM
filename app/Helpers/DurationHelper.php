<?php

namespace App\Helpers;

use Carbon\Carbon;

class DurationHelper
{
    /**
     * Normalize duration like:
     *  - 1-month
     *  - 2-months
     *  - 10-days
     */
    public static function normalize(?string $duration): ?array
    {
        if (empty($duration)) {
            return null;
        }

        $duration = strtolower(trim($duration));

        // Match formats like: 1-month, 2-months, 10-days
        if (!preg_match('/(\d+)\s*-\s*(day|days|month|months)/', $duration, $matches)) {
            return null;
        }

        $value = (int) $matches[1];
        $unit  = $matches[2];

        // Normalize unit
        $unit = str_starts_with($unit, 'day') ? 'day' : 'month';

        return [
            'raw'   => $duration,
            'unit'  => $unit,
            'value' => $value,
        ];
    }

    /**
     * Calculate duration multiplier for pricing
     * - Month = 1
     * - Day   = 1 / 30
     */
    public static function multiplier(?string $duration): float
    {
        $normalized = self::normalize($duration);

        if (!$normalized) {
            return 1;
        }

        if ($normalized['unit'] === 'month') {
            return $normalized['value'];
        }

        // Day-based pricing (pro-rata)
        return round($normalized['value'] / 30, 4);
    }

    /**
     * Resolve duration using start & end date (fallback)
     */
    public static function fromDates(?string $start, ?string $end): ?array
    {
        if (!$start || !$end) {
            return null;
        }

        $startDate = Carbon::parse($start);
        $endDate   = Carbon::parse($end);

        $days = $startDate->diffInDays($endDate);

        if ($days < 30) {
            return [
                'raw'   => "{$days}-days",
                'unit'  => 'day',
                'value' => $days,
            ];
        }

        $months = (int) ceil($days / 30);

        return [
            'raw'   => "{$months}-month",
            'unit'  => 'month',
            'value' => $months,
        ];
    }
}
