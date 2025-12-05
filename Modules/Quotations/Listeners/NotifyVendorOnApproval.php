<?php

namespace Modules\Quotations\Listeners;

use Modules\Quotations\Events\QuotationApproved;
use Illuminate\Support\Facades\Log;

class NotifyVendorOnApproval
{
    public function handle(QuotationApproved $event): void
    {
        $quotation = $event->quotation;

        // Log quotation approval
        Log::info('Quotation approved', [
            'quotation_id' => $quotation->id,
            'version' => $quotation->version,
            'offer_id' => $quotation->offer_id,
            'customer_id' => $quotation->customer_id,
            'vendor_id' => $quotation->vendor_id,
            'grand_total' => $quotation->grand_total,
            'approved_at' => $quotation->approved_at,
        ]);

        // Log vendor notification
        Log::info('Notifying vendor of quotation approval', [
            'vendor_email' => $quotation->vendor->email,
            'quotation_id' => $quotation->id,
            'customer_name' => $quotation->customer->name,
        ]);

        // TODO: Send actual email notification to vendor
        // Notification::send($quotation->vendor, new QuotationApprovedNotification($quotation));
    }
}
