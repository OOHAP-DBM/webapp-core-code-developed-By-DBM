<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VendorReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $reminderData
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔔 Reminder: Enquiry ' . $this->reminderData['enquiry_id'] . ' — Response Awaited',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor-reminder',
            with: $this->reminderData,
        );
    }
}