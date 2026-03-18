<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\POS\Models\POSBooking;

class PosBookingCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $timeout = 120;

    public int $tries = 3;

    public array $backoff = [10, 60, 180];

    public function __construct(
        public POSBooking $booking,
        public ?User $customer = null,
        public array $hoardingTitles = [],
        public ?string $reason = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'POS Booking Cancelled - #' . ($this->booking->invoice_number ?? $this->booking->id),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pos_booking_cancelled',
            with: [
                'booking' => $this->booking,
                'customer' => $this->customer,
                'greetingName' => $this->customer?->name ?? ($this->booking->customer_name ?? 'Customer'),
                'hoardingTitles' => $this->hoardingTitles,
                'reason' => $this->reason,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
