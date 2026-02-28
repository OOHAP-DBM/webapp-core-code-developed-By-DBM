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
            // Send Email only if enabled
            if ($customer->notification_email) {
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
            }

            // Send push/in-app notification only if enabled
            if ($customer->notification_push) {
                $customer->notify(
                    new \Modules\Enquiries\Notifications\CustomerEnquiryNotification(
                        $enquiry,
                        $allItems
                    )
                );
            }
            send(
                $customer,
                'Enquiry Submitted Successfully',
                'Your enquiry has been submitted. Vendors will contact you soon.',
                [
                    'type'       => 'customer_enquiry',
                    'enquiry_id' => $enquiry->id
                ]
            );
        }

        // 2. NOTIFY VENDORS (EMAIL + IN-APP NOTIFICATION)
        foreach ($vendorGroups as $vendorId => $items) {
            $vendor = User::find($vendorId);
            if ($vendor) {
                // Send Email only if enabled
                if ($vendor->notification_email) {
                    $vendor->notifyVendorEmails(new \Modules\Mail\VendorEnquiryNotificationMail($enquiry, $vendor, collect($items)));
                }
                // Send push/in-app notification only if enabled
                if ($vendor->notification_push) {
                    $vendor->notify(
                        new \Modules\Enquiries\Notifications\VendorEnquiryNotification(
                            $enquiry,
                            $items
                        )
                    );
                }
                send(
                    $vendor,
                    'New Enquiry Received',
                    'You have received a new enquiry. Please check the app for details.',
                    [
                        'type' => 'vendor_enquiry',
                        'enquiry_id' => $enquiry->id
                    ]
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
