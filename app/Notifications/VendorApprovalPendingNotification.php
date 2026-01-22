<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class VendorApprovalPendingNotification extends Notification
{
    public function __construct(
        public $vendorUser
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $isAdmin = method_exists($notifiable, 'hasRole')
            && $notifiable->hasRole('admin');

        if ($isAdmin) {
            // ✅ ADMIN MESSAGE
            return [
                'title'       => 'Vendor Approval Pending',
                'message'     => 'A new vendor has registered and is awaiting approval.',
                'action_url'  => route('admin.vendors.show', $this->vendorUser->id),
                'type'        => 'vendor_pending_admin',
            ];
        }

        // ✅ VENDOR MESSAGE
        return [
            'title'       => 'Welcome to OOHAPP',
            'message'     => 'Your vendor profile has been submitted successfully. It is currently under review. Please wait for admin approval.',
            'action_url'  => route('vendor.dashboard'),
            'type'        => 'vendor_pending_vendor',
        ];
    }
}
