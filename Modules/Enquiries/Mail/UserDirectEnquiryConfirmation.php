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
        // IMPORTANT: always reload fresh model for queued mail
        $this->enquiry = DirectEnquiry::find($enquiry->id);
    }

    public function build()
    {
        return $this->subject('Your OOHAPP Enquiry Confirmation')
            ->markdown('enquiries.emails.user-direct-enquiry-confirmation')
            ->with([
                'enquiry' => $this->enquiry,
            ]);
    }
}