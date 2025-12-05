<?php

namespace Modules\Quotations\Listeners;

use Modules\Quotations\Events\QuotationSent;
use Illuminate\Support\Facades\Log;

class NotifyCustomerOnSent
{
    public function handle(QuotationSent $event): void
    {
        $quotation = $event->quotation;

        // Log quotation sent
        Log::info('Quotation sent to customer', [
            'quotation_id' => $quotation->id,
            'version' => $quotation->version,
            'offer_id' => $quotation->offer_id,
            'customer_id' => $quotation->customer_id,
            'vendor_id' => $quotation->vendor_id,
            'grand_total' => $quotation->grand_total,
        ]);

        // Log customer notification
        Log::info('Notifying customer of new quotation', [
            'customer_email' => $quotation->customer->email,
            'quotation_id' => $quotation->id,
            'vendor_name' => $quotation->vendor->name,
        ]);

        // TODO: Send actual email notification to customer
        // Notification::send($quotation->customer, new QuotationSentNotification($quotation));
    }
}
