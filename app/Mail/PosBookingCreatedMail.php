<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\POS\Models\POSBooking;
use Modules\POS\Models\VendorPaymentDetail;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;

class PosBookingCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public POSBooking $booking,
        public ?User $customer = null,
        public string $recipientType = 'customer'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'POS Booking Confirmation - #' . $this->booking->invoice_number,
        );
    }

    public function content(): Content
    {
        $paymentMode = $this->booking->payment_mode;
        $detailType = in_array($paymentMode, ['bank_transfer', 'cheque']) ? 'bank' : 'upi';

        $paymentDetail = VendorPaymentDetail::where('vendor_id', $this->booking->vendor_id)
            ->where('type', $detailType)
            ->first();

        $paymentQrUrl = null;
        if ($paymentDetail && !empty($paymentDetail->qr_image_path)) {
            $paymentQrUrl = Storage::disk('public')->url($paymentDetail->qr_image_path);
        }

        return new Content(
            view: 'emails.pos_booking_created',
            with: [
                'booking' => $this->booking,
                'customer' => $this->customer,
                'greetingName' => $this->customer?->name ?? ($this->booking->customer_name ?? 'Customer'),
                'recipientType' => $this->recipientType,
                'paymentDetail' => $paymentDetail,
                'paymentQrUrl' => $paymentQrUrl,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}