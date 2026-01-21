<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorHoardingSubmitted extends Notification
{
    public function __construct(public $hoarding) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Hoarding Submitted',
            'message' => 'A vendor has Added a new hoarding.',
            'hoarding_id' => $this->hoarding->id,
        ];
    }
}

