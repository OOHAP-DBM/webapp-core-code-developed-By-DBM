<?php

namespace Modules\POS\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Modules\POS\Models\POSBooking;
use App\Services\Whatsapp\TwilioWhatsappService;

class SendPosBookingWhatsappReminderJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $bookingId;

    public function __construct(int $bookingId)
    {
        $this->bookingId = $bookingId;
    }

    public function handle(TwilioWhatsappService $whatsapp): void
    {
        DB::transaction(function () use ($whatsapp) {

            $booking = POSBooking::lockForUpdate()->find($this->bookingId);

            if (! $booking) return;

            if (! in_array($booking->status, ['confirmed', 'partial_paid'])) return;

            if ($booking->payment_status === 'paid') return;

            if ($booking->reminder_count >= 3) return;

            if (! $booking->customer_phone) return;

            $message = $this->buildMessage($booking);

            $sent = $whatsapp->send($booking->customer_phone, $message);

            if (! $sent) return;

            $booking->increment('reminder_count');
            $booking->update([
                'last_reminder_at' => now(),
            ]);
        });
    }

    protected function buildMessage(PosBooking $booking): string
    {
        return <<<MSG
        Hello {$booking->customer_name},

        This is a gentle reminder for your POS Booking #{$booking->id}.

        📍 Hoardings: {$booking->hoardings_count}
        💰 Total Amount: ₹{$booking->total_amount}
        ⏳ Pending Amount: ₹{$booking->pending_amount}
        📅 Payment Due: {$booking->payment_due_date?->format('d M Y')}

        Please complete your payment to confirm your booking.

        Thank you,
        {$booking->vendor->name}
        MSG;
    }
}
