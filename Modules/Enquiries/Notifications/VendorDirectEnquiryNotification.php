<?php

namespace Modules\Enquiries\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Enquiries\Models\DirectEnquiry;

class VendorDirectEnquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public DirectEnquiry $enquiry;

    public function __construct(DirectEnquiry $enquiry)
    {
        $this->enquiry = $enquiry;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    // public function toMail($notifiable)
    // {
    //     return (new MailMessage)
    //         ->subject('New Hoarding Enquiry in ' . ($this->enquiry->location_city ?? ''))
    //         ->markdown('emails.vendor-direct-enquiry-notification', [
    //             'enquiry' => $this->enquiry,
    //             'dashboardUrl' => route('vendor.enquiries.show', $this->enquiry->id)
    //         ]);
    // }

    public function toArray($notifiable)
    {
          \Log::info('VendorDirectEnquiryNotification toArray called', ['vendor_id' => $notifiable->id, 'enquiry_id' => $this->enquiry->id]);
        return [
            'enquiry_id' => $this->enquiry->id,
            'city' => $this->enquiry->location_city,
            'message' => 'New direct web enquiry received in ' . ($this->enquiry->location_city ?? ''),
            'action_url' => route('vendor.direct_enquiries.show', $this->enquiry->id),
        ];
    }
}
