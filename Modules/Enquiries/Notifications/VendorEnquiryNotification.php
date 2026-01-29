<?php

namespace Modules\Enquiries\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VendorEnquiryNotification extends Notification
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

    /* ================= DATABASE ================= */

    public function toDatabase($notifiable)
    {
        return [
            'enquiry_id'   => $this->enquiry->id,
            'item_count'   => count($this->items),
            'customer_name'=> $this->enquiry->meta['customer_name'] ?? 'New Client',
            'message'      => 'New enquiry raised for ' . count($this->items) . ' hoarding(s).',
            'action_url'   => route('vendor.enquiries.index'),
            'role'         => 'vendor',
        ];
    }
}
