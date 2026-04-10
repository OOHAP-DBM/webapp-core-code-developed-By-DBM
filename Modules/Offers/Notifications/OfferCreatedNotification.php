<?php

namespace Modules\Offers\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OfferCreatedNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->line('A new offer has been created.')
            ->action('View Offer', url('/offers'))
            ->line('Thank you for using our application!');
    }
}
