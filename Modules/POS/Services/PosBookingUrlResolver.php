<?php

namespace Modules\POS\Services;

use Illuminate\Support\Facades\Route;
use Modules\POS\Models\POSBooking;

class PosBookingUrlResolver
{
    public function resolve(POSBooking $booking): string
{
    return Route::has('customer.pos.booking.show')
        ? route('customer.pos.booking.show', ['booking' => $booking->id])
        : url('/customer/pos-booking/' . $booking->id);
}
}