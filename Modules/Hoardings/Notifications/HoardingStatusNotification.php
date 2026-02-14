<?php

namespace Modules\Hoardings\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class HoardingStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $hoarding;
    public $action;
    public $extra;

    public function __construct($hoarding, string $action, array $extra = [])
    {
        $this->hoarding = $hoarding;
        $this->action = $action;
        $this->extra = $extra;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Hoarding ' . ucfirst($this->action))
            ->greeting('Hello ' . ($notifiable->name ?? ''))
            ->line('Your hoarding "' . ($this->hoarding->title ?? $this->hoarding->name) . '" has been ' . $this->action . '.')
            ->action('View Hoarding', url('/vendor/hoardings/' . $this->hoarding->id))
            ->line('Thank you for using our application!');
        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'hoarding_id' => $this->hoarding->id,
            'action' => $this->action,
            'title' => $this->hoarding->title ?? $this->hoarding->name,
            'extra' => $this->extra,
        ];
    }
}
