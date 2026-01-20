<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class UserWelcomeNotification extends Notification
{
    /**
     * @param string $role  customer | vendor
     */
    public function __construct(
        public string $role
    ) {}

    /**
     * Notification delivery channels
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
        // Default message
        $message = 'Welcome! Your account has been created successfully.';

        if ($this->role === 'vendor') {
            $message = 'Welcome! Your vendor account has been created. Please complete onboarding.';
        }

        return [
            'role'    => $this->role,
            'message' => $message,

            // Optional future use
            'action_url' => $this->role === 'vendor'
                ? route('vendor.onboarding.contact-details')
                : route('home'),
        ];
    }
}
