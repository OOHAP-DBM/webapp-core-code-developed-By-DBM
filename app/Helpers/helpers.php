<?php



    function display_price($price, $currency = '₹') {
        if (is_null($price) || $price <= 0) {
            return '';
        }
        // Format without decimals
        return $currency . number_format($price, 0);
    }

    function getTieredMonths($startDate, $endDate) {
        // Returns integer months (inclusive, rounded up by 30-day buckets)
        if (empty($startDate) || empty($endDate)) {
            return 0;
        }

        try {
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);

            // Include both start and end dates in the calculation (inclusive)
            $interval = $start->diff($end);
            $days = (int) $interval->days + 1;

            return (int) ceil($days / 30);
        } catch (\Exception $e) {
            // In case of invalid dates, return a sensible default
            return 0;
        }
    }

    function getTieredDuration($startDate, $endDate) {
        // Returns a human-friendly duration string in months, rounding up to the next month.
        $months = getTieredMonths($startDate, $endDate);
        if ($months <= 0) return "0 Months";
        return $months === 1 ? "1 Month" : "{$months} Months";
    }

