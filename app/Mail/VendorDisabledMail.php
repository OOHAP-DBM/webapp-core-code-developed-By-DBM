<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorDisabledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Vendor Account Has Been Suspended - OOHAPP'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor-deactivate',
            with: [
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
