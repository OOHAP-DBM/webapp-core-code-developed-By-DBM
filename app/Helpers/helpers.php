<?php



    function display_price($price, $currency = '₹') {
        if (is_null($price) || $price <= 0) {
            return '';
        }
        // Format without decimals
        return $currency . number_format($price, 0);
    }

    function getTieredMonths($startDate, $endDate) {
        \Log::info("Calculating tiered months for startDate: $startDate, endDate: $endDate");
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

    // app/helpers.php
    function amountInWords($amount)
    {
        if (class_exists('\\NumberFormatter')) {
            $formatter = new \NumberFormatter('en_IN', \NumberFormatter::SPELLOUT);
            return ucwords($formatter->format($amount)) . ' Rupees Only';
        }

        // fallback (safe)
        return trim(convertNumberToWords($amount)) . ' Rupees Only';
    }


    function formatDateDDMMYYYY($dateStr) {
        if (!$dateStr) return 'N/A';
        
        // Parse as local date without timezone shift
        $parts = explode('-', $dateStr); // "YYYY-MM-DD"
        if (count($parts) < 3) return $dateStr;

        $year = (int)$parts[0];
        $month = (int)$parts[1];
        $day = (int)$parts[2];

        $date = new \DateTime("$year-$month-$day");

        return $date->format('d/m/Y');
    }




