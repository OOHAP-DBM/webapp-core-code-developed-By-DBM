<?php

namespace Modules\POS\Notifications\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminPosBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $booking) {}

    public function via($notifiable) { return ['database']; }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->id,
            'customer'   => $this->booking->customer->name,
            'amount'     => $this->booking->total_amount,
            'payment'    => $this->booking->payment_mode,
        ];
    }
}
