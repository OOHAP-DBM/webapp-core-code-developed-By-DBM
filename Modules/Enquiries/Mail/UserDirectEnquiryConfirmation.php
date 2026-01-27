<?php

namespace Modules\Enquiries\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Enquiries\Models\DirectEnquiry;

class UserDirectEnquiryConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public DirectEnquiry $enquiry;

    /**
     * Create a new message instance.
     */
    public function __construct(DirectEnquiry $enquiry)
    {
        $this->enquiry = $enquiry;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('We have received your enquiry | OOHAPP')
            ->view('enquiries.emails.user-direct-enquiry-confirmation')
            ->with([
                'enquiry' => $this->enquiry,
            ]);
    }
}
