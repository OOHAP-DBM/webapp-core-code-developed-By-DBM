<?php

namespace App\Listeners;

use App\Events\PaymentCaptured;
use App\Jobs\ScheduleBookingConfirmJob;
use App\Services\CommissionService;
use Modules\Bookings\Services\BookingService;
use Illuminate\Support\Facades\Log;

class OnPaymentCaptured
{
    protected BookingService $bookingService;
    protected CommissionService $commissionService;

    /**
     * Create the event listener.
     */
    public function __construct(BookingService $bookingService, CommissionService $commissionService)
    {
        $this->bookingService = $bookingService;
        $this->commissionService = $commissionService;
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

            // Calculate and record commission
            [$bookingPayment, $commissionLog] = $this->commissionService->calculateAndRecord(
                $booking,
                $event->paymentId,
                $event->orderId
            );

            Log::info('OnPaymentCaptured: Commission recorded', [
                'booking_id' => $booking->id,
                'booking_payment_id' => $bookingPayment->id,
                'commission_log_id' => $commissionLog->id,
                'vendor_payout_amount' => $bookingPayment->vendor_payout_amount,
            ]);

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
