<?php

namespace Modules\Enquiries\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Enquiries\Models\DirectEnquiry;

/**
 * Customer Direct Enquiry Notification
 * 
 * Sent to customer when their direct enquiry is submitted successfully
 * Shows in in-app notification center on website and mobile app
 */
class CustomerDirectEnquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public DirectEnquiry $enquiry;

    public function __construct(DirectEnquiry $enquiry)
    {
        $this->enquiry = $enquiry;
    }

    /**
     * Get the notification's delivery channels
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage
     */
    public function toArray($notifiable)
    {
        $hoardingTypes = implode(', ', array_map('strtoupper', explode(',', $this->enquiry->hoarding_type)));

        return [
            'type' => 'customer_direct_enquiry_submitted',
            'enquiry_id' => $this->enquiry->id,
            'title' => 'Enquiry Submitted Successfully',
            'message' => "Your {$hoardingTypes} hoarding enquiry for {$this->enquiry->location_city} has been submitted. Vendors will contact you within 24-48 hours.",
            'hoarding_type' => $this->enquiry->hoarding_type,
            'city' => $this->enquiry->location_city,
            'status' => $this->enquiry->status,
            'action_url' => route('customer.enquiries.show', $this->enquiry->id),
            'created_at' => now(),
        ];
    }
}
