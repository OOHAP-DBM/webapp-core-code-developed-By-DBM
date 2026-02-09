<?php

namespace Modules\Enquiries\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

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
            ->line('A new multi-item enquiry has been generated on the platform.')
            ->line('**Client:** ' . (is_array($this->enquiry->meta) && isset($this->enquiry->meta['customer_name']) ? $this->enquiry->meta['customer_name'] : 'N/A'))
            ->line('**Total Hoardings:** ' . $totalItems)
            ->line('**Total Potential Value:** ' . number_format($this->enquiry->items->sum(fn($i) => $i->meta['amount'] ?? 0), 2))
            ->action('Review in Admin Panel', url('/admin/enquiries/' . $this->enquiry->id))
            ->line('Ensure vendors are responding to these leads promptly.');
    }

    public function toDatabase($notifiable)
    {
        $items = method_exists($this->enquiry, 'items') ? $this->enquiry->items : ($this->enquiry->items ?? []);
        $itemCount = is_callable([$items, 'count']) ? $items->count() : (is_array($items) ? count($items) : 0);
        $customerName = is_array($this->enquiry->meta ?? null) && isset($this->enquiry->meta['customer_name'])
            ? $this->enquiry->meta['customer_name']
            : 'New Client';
        return [
            'enquiry_id'   => $this->enquiry->id,
            'item_count'   => $itemCount,
            'customer_name'=> $customerName,
            'message'      => 'New enquiry raised for ' . $itemCount . ' hoarding(s).',
            'action_url'   => route('admin.enquiries.show', $this->enquiry->id),
            'role'         => 'admin',
        ];
    }
}
