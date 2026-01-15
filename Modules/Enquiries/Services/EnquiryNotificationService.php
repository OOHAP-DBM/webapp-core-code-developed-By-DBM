<?php

namespace Modules\Enquiries\Services;

use App\Models\User;

class EnquiryNotificationService
{
    public function notifyAll($enquiry, array $vendorGroups): void
    {
        // 1. NOTIFY CUSTOMER
        $customer = $enquiry->customer;
        if ($customer) {
            $allItems = $enquiry->items()->get();
            $customer->notify(
                new \Modules\Enquiries\Notifications\CustomerEnquiryNotification(
                    $enquiry,
                    $allItems
                )
            );
        }

        // 2. NOTIFY VENDORS
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

        // 3. NOTIFY ADMIN
        $admin = User::where('active_role', 'admin')->first();
        if ($admin) {
            $admin->notify(
                new \Modules\Enquiries\Notifications\AdminEnquiryNotification($enquiry)
            );
        }
    }
}