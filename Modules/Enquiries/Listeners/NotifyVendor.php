<?php

namespace Modules\Enquiries\Listeners;

use Modules\Enquiries\Events\EnquiryCreated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NotifyVendor
{
    /**
     * Handle the event.
     */
    public function handle(EnquiryCreated $event): void
    {
        $enquiry = $event->enquiry;
        $vendor = $enquiry->hoarding->vendor;

        // Log the enquiry creation
        Log::info('New enquiry created', [
            'enquiry_id' => $enquiry->id,
            'customer_id' => $enquiry->customer_id,
            'hoarding_id' => $enquiry->hoarding_id,
            'vendor_id' => $vendor->id,
        ]);

        // TODO: Send email notification to vendor
        // Notification::send($vendor, new NewEnquiryNotification($enquiry));

        // For now, just log that we would notify
        Log::info('Vendor notification queued', [
            'vendor_email' => $vendor->email,
            'enquiry_id' => $enquiry->id,
        ]);
    }
}

