<?php

namespace Modules\Enquiries\Mail;


use Modules\Enquiries\Models\DirectEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminDirectEnquiryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $enquiry;

    public function __construct(DirectEnquiry $enquiry)
    {
        $this->enquiry = $enquiry;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Direct Enquiry: ' . $this->enquiry->name, // Improved subject
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'admin.enquiries.emails.direct_enquiry', // Point this to resources/views/emails/admin_enquiry.blade.php
        );
    }
}