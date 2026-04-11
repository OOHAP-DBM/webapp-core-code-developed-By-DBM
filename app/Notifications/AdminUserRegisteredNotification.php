<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AdminUserRegisteredNotification extends Notification
{
    /**
     * @param \App\Models\User $user  The newly registered user
     * @param string $role           customer | vendor
     */
    public function __construct(
        public $user,
        public string $role
    ) {}

    /**
     * Notification channels
     */
    public function via($notifiable): array
    {
        // Only send mail for vendor registration
        if ($this->role === 'vendor') {
            return ['database', 'mail'];
        }
        return ['database'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        if ($this->role !== 'vendor') {
            return null;
        }

        $message = 'New vendor registered – approval pending';
        $actionUrl = optional($this->user->vendorProfile)
            ? route('admin.vendors.show', $this->user->vendorProfile->id)
            : null;

        // Use queue for mail delivery
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject($message)
            ->mailer('smtp')
            ->view('vendor.mail.html.layout', [
                'greeting' => 'Hello Admin,',
                'message' => $message,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'actionUrl' => $actionUrl,
                'actionText' => 'View Profile',
                'footer' => 'Please review and approve the registration.'
            ])
            ->onQueue('emails');
    }
    /**
     * Send this notification via queue (implements ShouldQueue)
     */
    public $queue = 'emails';

    /**
     * Data stored in notifications table
     */
    public function toDatabase($notifiable): array
    {
        $message = match ($this->role) {
            'customer' => 'New customer registered on platform',
            'vendor'   => 'New vendor registered – approval pending',
            default    => 'New user registered on platform',
        };

        return [
            'user_id' => $this->user->id,
            'role'    => $this->role,
            'name'    => $this->user->name,
            'email'   => $this->user->email,
            'message' => $message,

            'action_url' => $this->role === 'vendor'
                ? optional($this->user->vendorProfile)
                    ? route('admin.vendors.show', $this->user->vendorProfile->id)
                    : null
                : route('admin.customers.show', $this->user->id),
        ];
    }

}
