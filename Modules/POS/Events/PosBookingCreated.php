<?php
// app/Events/PosBookingCreated.php
namespace Modules\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\POS\Models\POSBooking;

class PosBookingCreated
{
    use Dispatchable, SerializesModels;

    public POSBooking $booking;

    public function __construct(POSBooking $booking)
    {
        $this->booking = $booking;
    }
}