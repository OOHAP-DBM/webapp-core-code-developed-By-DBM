<?php

namespace Modules\Enquiries\Services;

use App\Models\User;
use App\Services\NotificationEmailService;

class EnquiryNotificationService
{
    public function __construct(
        protected NotificationEmailService $notificationEmailService
    ) {}

    public function notifyAll($enquiry, array $vendorGroups): void
    {
        // 1. NOTIFY CUSTOMER (EMAIL + IN-APP NOTIFICATION)
        $customer = $enquiry->customer;
        if ($customer) {
            $allItems = $enquiry->items()->get();

            // Send Email only if enabled (service khud check karti hai)
            try {
                $this->notificationEmailService->send(
                    $customer,
                    new \Modules\Mail\CustomerEnquiryConfirmationMail($enquiry, $customer)
                );
                \Log::info('Customer enquiry confirmation email sent', [
                    'customer_id' => $customer->id,
                    'enquiry_id'  => $enquiry->id
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send customer enquiry confirmation email', [
                    'customer_id' => $customer->id,
                    'enquiry_id'  => $enquiry->id,
                    'error'       => $e->getMessage()
                ]);
            }

            // Push/in-app notification
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

                // Send Email only if enabled (service khud check karti hai)
                try {
                    $this->notificationEmailService->send(
                        $vendor,
                        new \Modules\Mail\VendorEnquiryNotificationMail($enquiry, $vendor, collect($items))
                    );
                    \Log::info('Vendor enquiry notification email sent', [
                        'vendor_id'  => $vendor->id,
                        'enquiry_id' => $enquiry->id
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send vendor enquiry notification email', [
                        'vendor_id'  => $vendor->id,
                        'enquiry_id' => $enquiry->id,
                        'error'      => $e->getMessage()
                    ]);
                }

                // Push/in-app notification
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
                        'type'       => 'vendor_enquiry',
                        'enquiry_id' => $enquiry->id
                    ]
                );
            }
        }

        // 3. NOTIFY ADMIN
        $admin = User::where('active_role', 'admin')->first();
        \Log::info('Preparing admin enquiry notification', [
            'admin_id' => $admin?->id,
            'enquiry_id' => $enquiry->id,
        ]);
        if ($admin) {
            \Log::info('Sending admin enquiry notification', [
                'admin_id' => $admin->id,
                'enquiry_id' => $enquiry->id,
            ]);
            $admin->notify(
                new \Modules\Enquiries\Notifications\AdminEnquiryNotification($enquiry)
            );
        }
    }
}