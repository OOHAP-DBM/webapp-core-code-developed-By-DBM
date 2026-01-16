<?php

namespace Modules\Enquiries\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EnquiryNotificationService
{
    public function notifyAll($enquiry, array $vendorGroups): void
    {
        // 1. NOTIFY CUSTOMER (EMAIL + IN-APP NOTIFICATION)
        $customer = $enquiry->customer;
        if ($customer) {
            $allItems = $enquiry->items()->get();
            
            // Send Email
            try {
                Mail::to($customer->email)->send(
                    new \Modules\Mail\CustomerEnquiryConfirmationMail($enquiry, $customer)
                );
                \Log::info('Customer enquiry confirmation email sent', [
                    'customer_id' => $customer->id,
                    'enquiry_id' => $enquiry->id
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send customer enquiry confirmation email', [
                    'customer_id' => $customer->id,
                    'enquiry_id' => $enquiry->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Also send in-app notification
            $customer->notify(
                new \Modules\Enquiries\Notifications\CustomerEnquiryNotification(
                    $enquiry,
                    $allItems
                )
            );
        }

        // 2. NOTIFY VENDORS (EMAIL + IN-APP NOTIFICATION)
        foreach ($vendorGroups as $vendorId => $items) {
            $vendor = User::find($vendorId);
            if ($vendor) {
                // Send Email
                try {
                    Mail::to($vendor->email)->send(
                        new \Modules\Mail\VendorEnquiryNotificationMail($enquiry, $vendor, collect($items))
                    );
                    \Log::info('Vendor enquiry notification email sent', [
                        'vendor_id' => $vendor->id,
                        'enquiry_id' => $enquiry->id
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send vendor enquiry notification email', [
                        'vendor_id' => $vendor->id,
                        'enquiry_id' => $enquiry->id,
                        'error' => $e->getMessage()
                    ]);
                }
                
                // Also send in-app notification
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