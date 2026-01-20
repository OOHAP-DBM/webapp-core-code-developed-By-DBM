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
        return ['database'];
    }

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
