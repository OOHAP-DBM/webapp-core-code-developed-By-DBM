<?php

namespace Modules\Offers\Listeners;

use Modules\Offers\Events\OfferSent;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NotifyCustomer
{
    /**
     * Handle the event.
     */
    public function handle(OfferSent $event): void
    {
        $offer = $event->offer;
        $customer = $offer->enquiry->customer;

        // Log the offer sent
        Log::info('Offer sent to customer', [
            'offer_id' => $offer->id,
            'version' => $offer->version,
            'enquiry_id' => $offer->enquiry_id,
            'customer_id' => $customer->id,
            'vendor_id' => $offer->vendor_id,
            'price' => $offer->price,
            'price_type' => $offer->price_type,
        ]);

        // TODO: Send email notification to customer
        // Notification::send($customer, new NewOfferNotification($offer));

        // For now, just log that we would notify
        Log::info('Customer notification queued', [
            'customer_email' => $customer->email,
            'offer_id' => $offer->id,
            'hoarding_title' => $offer->getSnapshotValue('hoarding_title'),
        ]);
    }
}

