<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EnquiryReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $url,
        public array  $meta = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'type'    => 'reminder',
            'url'     => $this->url,
            'meta'    => $this->meta,
        ];
    }
}