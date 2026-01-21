<?php

namespace App\Mail;

namespace App\Mail;

use App\Models\DirectEnquiry; // Add this import
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminDirectEnquiryMail extends Mailable
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
            view: 'admin.emails.admin_enquiry', // Point this to resources/views/emails/admin_enquiry.blade.php
        );
    }
}