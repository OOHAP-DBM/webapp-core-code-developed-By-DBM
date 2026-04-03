<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

class VendorApprovalPendingNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public function __construct(
        public $vendorUser
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
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
               'action_url'  => optional($this->vendorUser->vendorProfile)
                    ? route('admin.vendors.show', $this->vendorUser->vendorProfile->id)
                    : null,
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

     /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $message = 'New vendor registered – approval pending';

        $actionUrl = optional($this->vendorUser->vendorProfile)
            ? route('admin.vendors.show', $this->vendorUser->vendorProfile->id)
            : null;

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject($message)
            ->mailer('smtp')
            ->view('admin.emails.vendor-approval', [   // ✅ CHANGE HERE
                'name' => $this->vendorUser->name,
                'email' => $this->vendorUser->email,
                'actionUrl' => $actionUrl,
                'actionText' => 'View Profile',
                'footer' => 'Please review and approve the registration.'
            ]);
    }
}
