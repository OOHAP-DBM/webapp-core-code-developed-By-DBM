<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class VendorApprovalPendingNotification extends Notification
{
    public function __construct(
        public $vendorUser // 👈 vendor ka User model
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        // If the notifiable is an admin, show the vendor approval page; if vendor, show dashboard
        $isAdmin = method_exists($notifiable, 'hasRole') && $notifiable->hasRole('admin');
        return [
            'title'   => 'Vendor Approval Pending',
            'message' => 'A new vendor has registered and is awaiting approval.',
            'action_url' => $isAdmin
                ? route('admin.vendors.show', $this->vendorUser->id)
                : route('vendor.dashboard'),
            'type' => 'vendor_pending',
        ];
    }
}
