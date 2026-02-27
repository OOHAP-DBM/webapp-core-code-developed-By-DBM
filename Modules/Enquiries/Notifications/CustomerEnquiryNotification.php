<?php

namespace Modules\Enquiries\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomerEnquiryNotification extends Notification
{
    use Queueable;

    protected $enquiry;
    protected $items;

    public function __construct($enquiry, $items)
    {
        $this->enquiry = $enquiry;
        $this->items   = $items;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'enquiry_id' => $this->enquiry->id,
            'item_count' => count($this->items),
            'status'     => 'submitted',
            'message'    => 'Your enquiry for ' . count($this->items) . ' hoarding(s) has been received.',
            'action_url' => route('customer.enquiries.show', $this->enquiry->id),
            'role'       => 'customer',
        ];
    }

}
