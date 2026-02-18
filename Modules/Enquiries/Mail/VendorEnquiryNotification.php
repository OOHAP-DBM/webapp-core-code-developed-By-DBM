<?php

namespace Modules\Enquiries\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Enquiries\Models\DirectEnquiry;
use App\Models\User;

class VendorEnquiryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public DirectEnquiry $enquiry;
    public User $vendor;

    public function __construct(DirectEnquiry $enquiry, User $vendor)
    {
        $this->enquiry = $enquiry;
        $this->vendor = $vendor;
    }

    public function build()
    {
        return $this->subject("New Hoarding Enquiry in {$this->enquiry->location_city}")
            ->markdown('enquiries.emails.vendor-enquiry-notification')
            ->with([
                'enquiry' => $this->enquiry,
                'vendor' => $this->vendor,
                'action_url' => route('vendor.enquiries.show', $this->enquiry->id)
            ]);
    }
}