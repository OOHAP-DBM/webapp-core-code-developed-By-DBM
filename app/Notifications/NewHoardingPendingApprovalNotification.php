<?php

namespace App\Notifications;

use App\Models\Hoarding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Hoarding Pending Approval')
            ->greeting('Hello Admin!')
            ->line('A new hoarding has been submitted and is pending your approval.')
            ->line('Title: ' . $this->hoarding->title)
            ->line('Location: ' . $this->hoarding->address)
            ->action('Review Hoarding', url('/admin/hoardings/' . $this->hoarding->id))
            ->line('Please review and approve or reject the listing.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'new_hoarding_pending_approval',
            'hoarding_id' => $this->hoarding->id,
            'title' => $this->hoarding->title,
            'address' => $this->hoarding->address,
            'message' => 'A new hoarding is pending approval.',
        ];
    }
}
