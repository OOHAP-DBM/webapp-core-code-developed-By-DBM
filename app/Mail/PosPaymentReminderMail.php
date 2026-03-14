<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Route;
use Modules\POS\Models\POSBooking;

class PosPaymentReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public POSBooking $booking,
        public ?User $customer = null,
        public int $reminderCount = 1
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Reminder - Invoice #' . ($this->booking->invoice_number ?? $this->booking->id),
        );
    }

    public function content(): Content
    {
        $totalAmount = (float) ($this->booking->total_amount ?? 0);
        $paidAmount = (float) ($this->booking->paid_amount ?? 0);
        $remainingAmount = max(0, $totalAmount - $paidAmount);

        return new Content(
            view: 'emails.pos_payment_reminder',
            with: [
                'booking' => $this->booking,
                'customer' => $this->customer,
                'greetingName' => $this->customer?->name ?? ($this->booking->customer_name ?? 'Customer'),
                'reminderCount' => $this->reminderCount,
                'totalAmount' => number_format($totalAmount, 2),
                'paidAmount' => number_format($paidAmount, 2),
                'remainingAmount' => number_format($remainingAmount, 2),
                'actionUrl' => $this->resolveActionUrl(),
            ]
        );
    }

    protected function resolveActionUrl(): string
    {
        if (Route::has('vendor.pos.bookings.invoice')) {
            return route('vendor.pos.bookings.invoice', ['id' => $this->booking->id]);
        }

        if (Route::has('vendor.pos.bookings.show')) {
            return route('vendor.pos.bookings.show', ['id' => $this->booking->id]);
        }

        if (Route::has('admin.pos.show')) {
            return route('admin.pos.show', ['id' => $this->booking->id]);
        }

        return url('/vendor/pos/bookings/' . $this->booking->id);
    }

    public function attachments(): array
    {
        return [];
    }
}
