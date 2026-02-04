<?php

namespace Modules\Enquiries\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;


class AdminEnquiryNotification extends Notification
{
    use Queueable;

    protected $enquiry;

    public function __construct($enquiry)
    {
        $this->enquiry = $enquiry;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $totalItems = $this->enquiry->items()->count();

        return (new MailMessage)
            ->subject('PLATFORM ALERT: New Lead Generated #' . $this->enquiry->id)
            ->greeting('Hello Admin,')
           ->line(
                $totalItems === 1
                    ? 'A single hoarding enquiry has been generated on the platform.'
                    : 'A multi hoarding enquiry has been generated on the platform for ' .
                    $totalItems . ' ' . Str::plural('hoarding', $totalItems) . '.'
            )

            ->line('**Client:** ' . (is_array($this->enquiry->meta) && isset($this->enquiry->meta['customer_name']) ? $this->enquiry->meta['customer_name'] : 'N/A'))
           ->line(
                '**Total ' . ucfirst(Str::plural('hoarding', $totalItems)) . ':** ' . $totalItems
            )
            ->line('**Total Potential Value:** ' . number_format($this->enquiry->items->sum(fn($i) => $i->meta['amount'] ?? 0), 2))
            ->action('Review in Admin Panel', url('/admin/enquiries/' . $this->enquiry->id))
            ->line('Ensure vendors are responding to these leads promptly.');
    }

    public function toArray($notifiable)
    {
        $totalItems = $this->enquiry->items()->count();
        return [
            'enquiry_id' => $this->enquiry->id,
            'message' => $totalItems === 1
                ? 'Single hoarding enquiry raised by customer.'
                : 'Multi hoarding enquiry raised by customer for ' .
                $totalItems . ' ' . Str::plural('hoarding', $totalItems) . '.',

            'type' => 'platform_lead'
        ];
    }
}
