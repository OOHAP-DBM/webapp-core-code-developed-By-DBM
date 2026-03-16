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
use Illuminate\Contracts\Queue\ShouldQueue;

class PosBookingCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Queue worker timeout safety for SMTP delays.
     */
    public int $timeout = 120;

    public int $tries = 3;

    public array $backoff = [10, 60, 180];

    public function __construct(
        public POSBooking $booking,
        public ?User $customer = null,
        public string $recipientType = 'customer'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'POS Booking Created - #' . $this->booking->invoice_number,
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
        $paymentQrAbsolutePath = null;
        if ($paymentDetail && !empty($paymentDetail->qr_image_path)) {
            $paymentQrUrl = $paymentDetail->qrImageUrl(true);
            $normalizedPath = $paymentDetail->normalizedQrImagePath();
            if ($normalizedPath) {
                $paymentQrAbsolutePath = storage_path('app/public/' . $normalizedPath);
            }
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
                'paymentQrAbsolutePath' => $paymentQrAbsolutePath,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
