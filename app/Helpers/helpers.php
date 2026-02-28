<?php


use App\Services\FcmService;
use Kreait\Firebase\Exception\Messaging\MessagingException;
use Kreait\Firebase\Exception\FirebaseException;
use Illuminate\Support\Facades\Log;
use App\Models\User;

function display_price($price, $currency = '₹')
{
    if (is_null($price) || $price <= 0) {
        return '';
    }
    // Format without decimals
    return $currency . number_format($price, 0);
}

function getTieredMonths($startDate, $endDate)
{
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

function getTieredDuration($startDate, $endDate)
{
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


function formatDateDDMMYYYY($dateStr)
{
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



function calculateOffPercentage(float $basePrice, float $finalPrice): int
{
    if ($basePrice <= 0 || $finalPrice >= $basePrice) {
        return 0;
    }

    $discount = (($basePrice - $finalPrice) / $basePrice) * 100;

    return (int) round($discount);
}

/**
 * Calculate the final price after applying a percentage discount.
 */
function calculateDiscountedPrice(float $basePrice, float $discountPercent): float
{
    if ($discountPercent <= 0) {
        return round($basePrice, 2);
    }

    $discountAmount = ($basePrice * $discountPercent) / 100;
    $finalPrice = $basePrice - $discountAmount;

    return round($finalPrice, 2);
}

// function send($target, string $title, string $body, array $data = []): bool
// {
//     try {
//         $fcm = app(FcmService::class);

//         if (is_array($target)) {
//             // Multiple tokens
//             $response = $fcm->sendToMultiple($target, $title, $body, $data);
//             Log::info('FCM sent to multiple tokens', ['response' => $response]);
//         } elseif (is_string($target) && str_starts_with($target, 'topic:')) {
//             // Topic
//             $topic = substr($target, 6);
//             $response = $fcm->sendToTopic($topic, $title, $body, $data);
//             Log::info("FCM sent to topic '{$topic}'", ['response' => $response]);
//         } else {
//             // Single token
//             $response = $fcm->sendToToken($target, $title, $body, $data);
//             Log::info("FCM sent to token '{$target}'", ['response' => $response]);
//         }

//         return true;
//     } catch (MessagingException | FirebaseException $e) {
//         Log::error('FCM Messaging Error: ' . $e->getMessage(), [
//             'target' => $target,
//             'title' => $title,
//             'body' => $body,
//             'data' => $data
//         ]);
//         return false;
//     } catch (\Exception $e) {
//         Log::error('FCM Unexpected Error: ' . $e->getMessage(), [
//             'target' => $target,
//             'title' => $title,
//             'body' => $body,
//             'data' => $data
//         ]);
//         return false;
//     }
// }

function send($target, $title, $body, $data = [])
{
    try {
        $fcm = app(FcmService::class);

        /*
        |--------------------------------------------------------------------------
        | If Target is User Model
        |--------------------------------------------------------------------------
        */
        if ($target instanceof \App\Models\User) {

            // Push disabled
            if (!$target->notification_push) {
                return false;
            }

            // No token
            if (empty($target->fcm_token)) {
                return false;
            }

            $target = $target->fcm_token;
        }

        /*
        |--------------------------------------------------------------------------
        | Multiple Tokens
        |--------------------------------------------------------------------------
        */
        if (is_array($target)) {
            $response = $fcm->sendToMultiple($target, $title, $body, $data);
            Log::info('FCM sent to multiple tokens', ['response' => $response]);
        }

        /*
        |--------------------------------------------------------------------------
        | Topic
        |--------------------------------------------------------------------------
        */ elseif (is_string($target) && str_starts_with($target, 'topic:')) {
            $topic = substr($target, 6);
            $response = $fcm->sendToTopic($topic, $title, $body, $data);
            Log::info("FCM sent to topic '{$topic}'", ['response' => $response]);
        }

        /*
        |--------------------------------------------------------------------------
        | Single Token
        |--------------------------------------------------------------------------
        */ else {
            $response = $fcm->sendToToken($target, $title, $body, $data);
            Log::info("FCM sent to token", ['response' => $response]);
        }

        return true;
    } catch (MessagingException | FirebaseException $e) {

        Log::error('FCM Messaging Error: ' . $e->getMessage(), [
            'target' => $target,
            'title' => $title,
            'body' => $body,
            'data' => $data
        ]);

        return false;
    } catch (\Exception $e) {

        Log::error('FCM Unexpected Error: ' . $e->getMessage(), [
            'target' => $target,
            'title' => $title,
            'body' => $body,
            'data' => $data
        ]);

        return false;
    }
}
