<?php

namespace Modules\Offers\Services;

use Modules\Offers\Models\Offer;
use Modules\Offers\Models\Setting;
use Carbon\Carbon;

class OfferExpiryService
{
    const DEFAULT_EXPIRY_DAYS = 7;
    const SETTING_DEFAULT_EXPIRY_DAYS = 'offer_default_expiry_days';

    public function getDefaultExpiryDays(): int
    {
        $setting = Setting::where('key', self::SETTING_DEFAULT_EXPIRY_DAYS)
            ->whereNull('tenant_id')
            ->first();
        if ($setting) {
            return (int) $setting->value;
        }
        return self::DEFAULT_EXPIRY_DAYS;
    }

    public function calculateExpiryTimestamp(Offer $offer): ?Carbon
    {
        if ($offer->status !== Offer::STATUS_SENT) {
            return null;
        }
        // ...rest of logic
    }
}
