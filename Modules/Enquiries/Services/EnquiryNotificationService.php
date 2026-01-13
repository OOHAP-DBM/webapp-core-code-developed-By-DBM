<?php

namespace Modules\Enquiries\Services;

use App\Models\User;

class EnquiryNotificationService
{
    public function notifyAll($enquiry, array $vendorGroups): void
    {
        foreach ($vendorGroups as $vendorId => $items) {
            $vendor = User::find($vendorId);
            if ($vendor) {
                $vendor->notify(
                    new \Modules\Enquiries\Notifications\VendorEnquiryNotification(
                        $enquiry,
                        $items
                    )
                );
            }
        }

        $admin = User::where('active_role', 'admin')->first();
        if ($admin) {
            $admin->notify(
                new \Modules\Enquiries\Notifications\AdminEnquiryNotification($enquiry)
            );
        }
    }
}