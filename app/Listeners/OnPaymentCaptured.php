<?php

namespace App\Listeners;

use App\Events\PaymentCaptured;
use App\Jobs\ScheduleBookingConfirmJob;
use Modules\Bookings\Services\BookingService;
use Illuminate\Support\Facades\Log;

class OnPaymentCaptured
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
    public function handle(PaymentCaptured $event): void
    {
        try {
            Log::info('OnPaymentCaptured: Processing', [
                'payment_id' => $event->paymentId,
                'order_id' => $event->orderId,
                'amount' => $event->amount
            ]);

            // Find booking by razorpay_order_id
            $booking = $this->bookingService->findByRazorpayOrderId($event->orderId);

            if (!$booking) {
                Log::error('OnPaymentCaptured: Booking not found', [
                    'order_id' => $event->orderId
                ]);
                return;
            }

            // Dispatch job to confirm booking
            ScheduleBookingConfirmJob::dispatch($booking->id, $event->paymentId)
                ->onQueue('bookings');

            Log::info('OnPaymentCaptured: Job dispatched successfully', [
                'booking_id' => $booking->id,
                'payment_id' => $event->paymentId
            ]);

        } catch (\Exception $e) {
            Log::error('OnPaymentCaptured: Failed to dispatch job', [
                'payment_id' => $event->paymentId,
                'order_id' => $event->orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
