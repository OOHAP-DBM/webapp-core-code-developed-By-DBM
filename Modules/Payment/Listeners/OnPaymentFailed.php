<?php

namespace Modules\Payment\Listeners;

use Modules\Payment\Events\PaymentFailed;
use Modules\Bookings\Services\BookingService;
use Illuminate\Support\Facades\Log;

class OnPaymentFailed
{
    protected BookingService $bookingService;

    /**
     * Create the event listener.
     */
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentFailed $event): void
    {
        try {
            Log::info('OnPaymentFailed: Processing', [
                'payment_id' => $event->paymentId,
                'order_id' => $event->orderId,
                'error_code' => $event->errorCode
            ]);

            // Find booking by razorpay_order_id
            $booking = $this->bookingService->findByRazorpayOrderId($event->orderId);

            if (!$booking) {
                Log::error('OnPaymentFailed: Booking not found', [
                    'order_id' => $event->orderId
                ]);
                return;
            }

            // Mark payment as failed and release hold
            $this->bookingService->markPaymentFailed(
                booking: $booking,
                paymentId: $event->paymentId,
                errorCode: $event->errorCode,
                errorDescription: $event->errorDescription
            );

            Log::info('OnPaymentFailed: Booking updated successfully', [
                'booking_id' => $booking->id,
                'payment_id' => $event->paymentId,
                'status' => $booking->fresh()->status
            ]);

        } catch (\Exception $e) {
            Log::error('OnPaymentFailed: Failed to update booking', [
                'payment_id' => $event->paymentId,
                'order_id' => $event->orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

