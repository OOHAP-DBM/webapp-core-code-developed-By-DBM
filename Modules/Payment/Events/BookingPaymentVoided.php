<?php

namespace Modules\Payment\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingPaymentVoided
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $bookingId;
    public string $paymentId;
    public array $paymentDetails;

    /**
     * Create a new event instance.
     *
     * @param int $bookingId
     * @param string $paymentId
     * @param array $paymentDetails
     */
    public function __construct(int $bookingId, string $paymentId, array $paymentDetails)
    {
        $this->bookingId = $bookingId;
        $this->paymentId = $paymentId;
        $this->paymentDetails = $paymentDetails;
    }
}

