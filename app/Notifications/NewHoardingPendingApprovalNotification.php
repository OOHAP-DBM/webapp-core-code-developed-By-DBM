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
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $status = $this->hoarding->status;
        $isActive = ($status === 'active');
        $type = $isActive ? 'hoarding_active' : 'new_hoarding_pending_approval';
        $title = $isActive ? 'Hoarding Activated' : 'New Hoarding Pending Approval';
        $message = $isActive
            ? 'Your hoarding is now active and published.'
            : 'A new vendor hoarding is pending approval.';
        $actionUrl = $isActive
            ? route('vendor.hoardings.myHoardings')
            : route('admin.vendor-hoardings.index');
        return [
            'type'        => $type,
            'title'       => $title,
            'message'     => $message,
            'hoarding_id' => $this->hoarding->id,
            'address'     => $this->hoarding->address,
            'action_url'  => $actionUrl,
        ];
    }
}
