<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $reminderData
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔔 Multi-Vendor Reminder: Enquiry ' . $this->reminderData['enquiry_id'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-reminder',
            with: $this->reminderData,
        );
    }
}