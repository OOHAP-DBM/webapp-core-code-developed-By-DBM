<?php
namespace App\Helpers;

class GreetingHelper
{
    /**
     * Returns 'Morning', 'Afternoon', or 'Evening' based on Indian time (IST)
     */
    public static function getGreeting(): string
    {
        // Get current time in Asia/Kolkata
        $hour = now()->setTimezone('Asia/Kolkata')->hour;
        if ($hour >= 5 && $hour < 12) {
            return ' Good Morning';
        } elseif ($hour >= 12 && $hour < 17) {
            return ' Good Afternoon';
        } else {
            return ' Good Evening';
        }
    }
}
