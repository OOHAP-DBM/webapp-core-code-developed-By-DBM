<?php

namespace Modules\Enquiries\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Modules\Enquiries\Models\DirectEnquiry;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminDirectEnquiryMail extends Mailable implements ShouldQueue
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
        return $this->subject("New Enquiry: {$this->enquiry->location_city} - {$this->enquiry->name}")
            ->markdown('admin.emails.direct-enquiry-notification')
            ->with([
                'enquiry' => $this->enquiry,
                'vendors' => $this->vendors,
                'adminUrl' => route('admin.direct-enquiries.index', $this->enquiry->id)
            ]);
    }
}