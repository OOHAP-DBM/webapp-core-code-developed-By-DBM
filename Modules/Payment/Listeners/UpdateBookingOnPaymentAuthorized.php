<?php

namespace Modules\Payment\Listeners;

use Modules\Payment\Events\PaymentAuthorized;
use Modules\Bookings\Services\BookingService;
use Modules\Settings\Services\SettingsService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateBookingOnPaymentAuthorized
{
    protected BookingService $bookingService;
    protected SettingsService $settingsService;

    /**
     * Create the event listener.
     */
    public function __construct(BookingService $bookingService, SettingsService $settingsService)
    {
        $this->bookingService = $bookingService;
        $this->settingsService = $settingsService;
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentAuthorized $event): void
    {
        try {
            Log::info('UpdateBookingOnPaymentAuthorized: Processing', [
                'payment_id' => $event->paymentId,
                'order_id' => $event->orderId
            ]);

            // Find booking by razorpay_order_id
            $booking = $this->bookingService->findByRazorpayOrderId($event->orderId);

            if (!$booking) {
                Log::error('UpdateBookingOnPaymentAuthorized: Booking not found', [
                    'order_id' => $event->orderId
                ]);
                return;
            }

            // Get booking hold minutes from settings (default 30)
            $holdMinutes = $this->settingsService->get('booking_hold_minutes', 30);

            // Update booking with payment authorization details
            $this->bookingService->updatePaymentAuthorized(
                booking: $booking,
                paymentId: $event->paymentId,
                holdMinutes: (int) $holdMinutes
            );

            Log::info('UpdateBookingOnPaymentAuthorized: Booking updated successfully', [
                'booking_id' => $booking->id,
                'payment_id' => $event->paymentId,
                'hold_expiry_at' => $booking->fresh()->hold_expiry_at,
            ]);

        } catch (\Exception $e) {
            Log::error('UpdateBookingOnPaymentAuthorized: Failed to update booking', [
                'payment_id' => $event->paymentId,
                'order_id' => $event->orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

