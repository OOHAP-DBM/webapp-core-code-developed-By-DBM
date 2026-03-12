<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class HoardingRejectedNotification extends Notification
{
    public function __construct(public $hoarding, public $rejectionReason = null) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'        => 'hoarding_rejected',
            'title'       => 'Hoarding Rejected',
            'message'     => 'Your hoarding has been rejected by admin.' .
                ($this->rejectionReason ? ' Reason: ' . $this->rejectionReason : ''),
            'hoarding_id' => $this->hoarding->id,
            'reason'      => $this->rejectionReason,

            // 👇 redirect vendor to My Hoardings page
            'action_url' => route('vendor.myHoardings.show', $this->hoarding->id),
        ];
    }
}
