<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\POS\Models\POSBooking;

class PosBookingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $booking;

    public function __construct(POSBooking $booking)
    {
        $this->booking = $booking;
    }

    // Sirf database channel
    public function via($notifiable)
    {
        return ['database'];
    }

    // Sirf DB mein save hoga
    public function toDatabase($notifiable)
    {
        return [
            'booking_id'     => $this->booking->id,
            'invoice_number' => $this->booking->invoice_number,
            'total_amount'   => $this->booking->total_amount,
            'start_date'     => $this->booking->start_date,
            'end_date'       => $this->booking->end_date,
            'customer_name'  => $this->booking->customer_name,
            'url'            => url('/vendor/pos/bookings/' . $this->booking->id),
        ];
    }
}