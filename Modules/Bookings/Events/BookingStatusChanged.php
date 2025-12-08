<?php

namespace Modules\Bookings\Events;

use Modules\Bookings\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class BookingStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Booking $booking;
    public string $fromStatus;
    public string $toStatus;

    public function __construct(Booking $booking, string $fromStatus, string $toStatus)
    {
        $this->booking = $booking;
        $this->fromStatus = $fromStatus;
        $this->toStatus = $toStatus;
    }
}

