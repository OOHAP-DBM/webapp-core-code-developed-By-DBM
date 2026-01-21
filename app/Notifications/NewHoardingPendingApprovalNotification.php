<?php

namespace App\Notifications;

use App\Models\Hoarding;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewHoardingPendingApprovalNotification extends Notification
{
    use Queueable;

    protected $hoarding;

    public function __construct(Hoarding $hoarding)
    {
        $this->hoarding = $hoarding;
    }

    public function via($notifiable)
    {
        return ['database']; // ✅ ONLY DB
    }

    public function toDatabase($notifiable)
    {
        return [
            'type'        => 'new_hoarding_pending_approval',
            'title'       => 'New Hoarding Pending Approval',
            'message'     => 'A new vendor hoarding is pending approval.',
            'hoarding_id' => $this->hoarding->id,
            'address'     => $this->hoarding->address,
            'action_url'  => route('admin.vendor-hoardings.index'),
        ];
    }
}
