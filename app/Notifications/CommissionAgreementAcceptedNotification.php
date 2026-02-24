<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommissionAgreementAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $vendorName,
        protected int $vendorId,
        protected ?float $commission,
        protected string $acceptedAt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Vendor Accepted Commission Agreement')
            ->greeting('Hello Admin,')
            ->line("{$this->vendorName} has accepted the commission agreement.")
            ->line('Accepted At: ' . $this->acceptedAt)
            ->line('Commission: ' . ($this->commission !== null ? number_format($this->commission, 2) . '%' : 'N/A'))
            ->action('View Vendor Commission', url('/admin/commission'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'vendor_commission_agreement_accepted',
            'title' => 'Commission Agreement Accepted',
            'message' => "{$this->vendorName} has accepted the commission agreement.",
            'vendor_id' => $this->vendorId,
            'vendor_name' => $this->vendorName,
            'commission' => $this->commission,
            'accepted_at' => $this->acceptedAt,
            'action_url' => url('/admin/commission'),
        ];
    }
}
