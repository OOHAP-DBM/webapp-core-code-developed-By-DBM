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

        $hoardingTypes = implode(', ', array_map('strtoupper', explode(',', $this->enquiry->hoarding_type)));

        return [
            'type' => 'vendor_direct_enquiry_received',
            'enquiry_id' => $this->enquiry->id,
            'title' => 'New Hoarding Enquiry Received',
            'message' => "New {$hoardingTypes} enquiry from {$this->enquiry->name} in {$this->enquiry->location_city}.",
            'customer_name' => $this->enquiry->name,
            'customer_phone' => $this->enquiry->phone,
            'customer_email' => $this->enquiry->email,
            'hoarding_type' => $this->enquiry->hoarding_type,
            'city' => $this->enquiry->location_city,
            'locations' => $this->enquiry->preferred_locations,
            'status' => $this->enquiry->status,
            'source' => $this->enquiry->source,
            'action_url' => route('vendor.direct-enquiries.index'),
            'created_at' => now(),
        ];
    }
}
