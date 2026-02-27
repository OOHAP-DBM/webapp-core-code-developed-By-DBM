<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class HoardingApproved extends Notification
{
    public function __construct(public $hoarding) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'        => 'hoarding_approved',
            'title'       => 'Hoarding Approved 🎉',
            'message'     => 'Your hoarding has been approved by admin.',
            'hoarding_id' => $this->hoarding->id,

            // 👇 redirect vendor to My Hoardings page
            'action_url' => route('vendor.myHoardings.show', $this->hoarding->id),
        ];
    }
}
