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
        $title = $this->hoarding->title ?? $this->hoarding->name ?? 'Hoarding';
        $action = ucfirst($this->action);

        // who is receiving?
        $isVendor = false;
        if (method_exists($notifiable, 'hasRole')) {
            $isVendor = $notifiable->hasRole('vendor');
        }
        \Log::info('[HoardingStatusNotification] toArray called', [
            'notifiable_id' => $notifiable->id ?? null,
            'notifiable_email' => $notifiable->email ?? null,
            'notifiable_role' => $notifiable->role ?? null,
            'hasRole_vendor' => $isVendor,
            'notifiable_class' => get_class($notifiable),
            'hoarding_id' => $this->hoarding->id ?? null,
            'action' => $this->action,
            'extra' => $this->extra,
        ]);

        $baseUrl = config('app.url');

        if ($isVendor) {
            $adminName = $this->extra['admin_name'] ?? ($this->hoarding->updated_by_name ?? 'Admin');
            $message = "Hoarding {$action} by {$adminName}";
            $relativeUrl = route('vendor.myHoardings.show', $this->hoarding->id, false);
            $url = rtrim($baseUrl, '/') . $relativeUrl;
            \Log::info('[HoardingStatusNotification] Vendor notification', [
                'message' => $message,
                'url' => $url,
            ]);
        } else {
            $message = "You {$action} hoarding '{$title}'";
            $relativeUrl = route('admin.hoardings.show', $this->hoarding->id, false);
            $url = rtrim($baseUrl, '/') . $relativeUrl;
            \Log::info('[HoardingStatusNotification] Admin notification', [
                'message' => $message,
                'url' => $url,
            ]);
        }

        return [
            'message' => $message,
            'hoarding_id' => $this->hoarding->id,
            'action' => $this->action,
            'action_url' => $url,
        ];
    }
}