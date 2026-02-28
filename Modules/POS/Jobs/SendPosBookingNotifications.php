<?php

namespace Modules\POS\Jobs;

use Modules\Events\PosBookingCreated;
use App\Models\User;
use App\Notifications\AdminPosBookingNotification;
use App\Notifications\VendorPosBookingNotification;
use App\Notifications\CustomerPosBookingEmailNotification;
use App\Services\TwilioWhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPosBookingNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected PosBookingCreated $event
    ) {}

    public function handle(TwilioWhatsappService $whatsapp)
    {
        $booking = $this->event->booking->load([
            'customer',
            'vendor',
            'hoardings'
        ]);

        /** ---------------- Admin ---------------- */
        User::role('admin')
            ->each(fn ($admin) =>
                $admin->notify(new AdminPosBookingNotification($booking))
            );

        /** ---------------- Vendor ---------------- */
        $booking->vendor?->notify(
            new VendorPosBookingNotification($booking)
        );

        /** ---------------- Customer Email ---------------- */
        $booking->customer?->notify(
            new CustomerPosBookingEmailNotification($booking)
        );

        /** ---------------- WhatsApp ---------------- */
        if ($booking->customer?->phone) {
            $whatsapp->sendTemplate(
                phone: $booking->customer->phone,
                template: 'pos_booking_confirmation',
                data: [
                    'booking_id' => $booking->id,
                    'amount'     => $booking->total_amount,
                    'start_date' => $booking->start_date->format('d M Y'),
                    'end_date'   => $booking->end_date->format('d M Y'),
                ]
            );
        }
    }
}
