<?php

namespace App\Notifications;

use App\Models\Hoarding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewHoardingPendingApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $hoarding;

    public function __construct(Hoarding $hoarding)
    {
        $this->hoarding = $hoarding;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }
    public function toMail($notifiable)
    {
        $status = $this->hoarding->status;
        $isActive = ($status === 'active');
        $isAdmin = $notifiable->hasRole('admin');

        if ($isActive) {
            if ($isAdmin) {
                $subject = 'Hoarding Auto Approved';
                $line1 = 'A hoarding was auto-approved and is now live.';
                $actionUrl = route('admin.hoardings.show', $this->hoarding->id);
            } else {
                $subject = 'Hoarding Activated';
                $line1 = 'Your hoarding is now active and published.';
                $actionUrl = route('vendor.hoardings.show', $this->hoarding->id);
            }
        } else {
            if ($isAdmin) {
                $subject = 'New Hoarding Pending Approval';
                $line1 = 'A new hoarding has been submitted and its approval is pending.';
                $actionUrl = route('admin.hoardings.show', $this->hoarding->id);
            } else {
                $subject = 'Hoarding Pending Approval';
                $line1 = 'Your hoarding has been submitted and is pending admin approval.';
                $actionUrl = route('vendor.hoardings.show', $this->hoarding->id);
            }
        }

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject($subject)
            ->greeting('Hello!')
            ->line($line1)
            ->action('View Hoarding', $actionUrl)
            ->line('Thank you for using our platform!');
    }

    public function toDatabase($notifiable)
    {
        $status = $this->hoarding->status;
        $isActive = ($status === 'active');
        $isAdmin = $notifiable->hasRole('admin');

        if ($isActive) {
            // Auto-approved: Vendor gets "Activated", Admin gets "Auto Approved"
            if ($isAdmin) {
                $type = 'hoarding_auto_approved';
                $title = 'Hoarding Auto Approved';
                $message = 'A hoarding was auto-approved and is now live.';
                $actionUrl = route('admin.hoardings.show', $this->hoarding->id);
            } else {
                $type = 'hoarding_activated';
                $title = 'Hoarding Activated';
                $message = 'Your hoarding is now active and published.';
                $actionUrl = route('vendor.hoardings.show', $this->hoarding->id);
            }
        } else {
            // Pending approval: Vendor gets "Pending", Admin gets "New Pending Approval"
            if ($isAdmin) {
                $type = 'new_hoarding_pending_approval';
                $title = 'New Hoarding Pending Approval';
                $message = 'A new hoarding has been submitted and its approval is pending.';
                $actionUrl = route('admin.hoardings.show', $this->hoarding->id);
            } else {
                $type = 'pending_approval';
                $title = 'Hoarding Pending Approval';
                $message = 'Your hoarding has been submitted and is pending admin approval.';
                $actionUrl = route('vendor.hoardings.show', $this->hoarding->id);
            }
        }
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
