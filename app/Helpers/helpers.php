<?php


use App\Services\FcmService;
use App\Models\User;

function display_price($price, $currency = '₹')
{
    if (is_null($price) || $price <= 0) {
        return '';
    }
    // Format without decimals
    return $currency . number_format($price, 0);
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

if (!function_exists('sendPushNotification')) {

    function sendPushNotification($user, $title, $body, $data = [])
    {
        try {

            // If user object passed
            if ($user instanceof User) {
                $fcmToken = $user->fcm_token;
            }
            // If user ID passed
            elseif (is_numeric($user)) {
                $userModel = User::find($user);
                $fcmToken = $userModel?->fcm_token;
            } else {
                return false;
            }

            if (!$fcmToken) {
                return false;
            }
            return app(FcmService::class)->send(
                $fcmToken,
                $title,
                $body,
                $data
            );
        } catch (\Exception $e) {
            \Log::error('Push Notification Error: ' . $e->getMessage());
            return false;
        }
    }
}
