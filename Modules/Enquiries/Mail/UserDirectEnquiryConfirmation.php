<?php

namespace Modules\Enquiries\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Enquiries\Models\DirectEnquiry;

class UserDirectEnquiryConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public DirectEnquiry $enquiry;

    public function __construct(DirectEnquiry $enquiry)
    {
        $this->enquiry = $enquiry;
    }

    public function build()
    {
        return $this->subject('Your Hoarding Enquiry Has Been Received')
            ->markdown('enquiries.emails.user-direct-enquiry-confirmation')
            ->with([
                'enquiry' => $this->enquiry,
                 'action_url' => route('vendor.enquiries.show', $this->enquiry->id)
            ]);
    }
}