<?php

namespace Modules\Enquiries\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Modules\Enquiries\Models\DirectEnquiry;

class AdminDirectEnquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public DirectEnquiry $enquiry;
    public Collection $vendors;

    public function __construct(DirectEnquiry $enquiry, Collection $vendors)
    {
        $this->enquiry = $enquiry;
        $this->vendors = $vendors;
    }

    public function build()
    {
        return $this->subject("New Enquiry: {$enquiry->location_city} - {$this->enquiry->name}")
            ->markdown('emails.admin-enquiry-notification')
            ->with([
                'enquiry' => $this->enquiry,
                'vendors' => $this->vendors,
                'adminUrl' => route('admin.enquiries.show', $this->enquiry->id)
            ]);
    }
}