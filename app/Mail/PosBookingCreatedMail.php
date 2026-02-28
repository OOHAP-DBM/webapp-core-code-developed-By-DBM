<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\POS\Models\POSBooking;
use App\Models\User;

class PosBookingCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public POSBooking $booking,
        public User $customer
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'POS Booking Confirmation - #' . $this->booking->invoice_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pos_booking_created',
            with: [
                'booking' => $this->booking,
                'customer' => $this->customer,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}