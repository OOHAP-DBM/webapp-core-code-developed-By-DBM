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
        if (!$duration) return null;

        $duration = strtolower(trim($duration));

        if (!preg_match('/(\d+)\s*-\s*(day|days|week|weeks|month|months)/', $duration, $m)) {
            return null;
        }

        $value = (int) $m[1];
        $unit  = $m[2];

        return [
            'raw'   => $duration,
            'unit'  => str_starts_with($unit, 'month') ? 'month'
                    : (str_starts_with($unit, 'week') ? 'week' : 'day'),
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
