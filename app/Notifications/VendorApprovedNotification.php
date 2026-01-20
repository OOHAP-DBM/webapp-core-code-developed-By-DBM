<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class VendorApprovedNotification extends Notification
{
    /**
     * @param bool $isAdmin
     * true  → admin notification
     * false → vendor notification
     */
    public function __construct(
        public bool $isAdmin = false
    ) {}

    /**
     * Notification channels
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Data stored in notifications table
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'vendor_approved',

            'message' => $this->isAdmin
                ? 'Vendor approved successfully'
                : 'Congratulations! Your vendor account has been approved by admin.',

            'status' => 'approved',

            // Optional future navigation
            'action_url' => $this->isAdmin
                ? route('admin.vendors.index')
                : route('vendor.dashboard'),
        ];
    }
}
