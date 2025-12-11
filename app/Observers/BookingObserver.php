<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\FraudDetectionService;
use App\Services\FraudEventLogger;
use Illuminate\Support\Facades\Log;

class BookingObserver
{
    public function __construct(
        private FraudDetectionService $fraudService,
        private FraudEventLogger $eventLogger
    ) {}

    /**
     * Handle the Booking "creating" event.
     */
    public function creating(Booking $booking): void
    {
        $user = $booking->customer;
        if (!$user) return;

        // Log booking attempt
        $this->eventLogger->logBookingAttempt($user, $booking);

        // Run fraud checks
        try {
            $alerts = $this->fraudService->checkBooking($booking);

            if (count($alerts) > 0) {
                Log::info('Fraud alerts triggered during booking creation', [
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'alerts_count' => count($alerts),
                ]);
            }

        } catch (\Exception $e) {
            // Don't block booking on fraud check failure, just log
            Log::error('Fraud check failed during booking creation', [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        $user = $booking->customer;
        if (!$user) return;

        // Update risk profile
        $this->fraudService->updateRiskProfileFromBooking($user, $booking);
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        // Check for status changes
        if ($booking->wasChanged('status')) {
            $user = $booking->customer;
            if (!$user) return;

            // Log cancellation
            if ($booking->status === 'cancelled') {
                $this->eventLogger->logCancellation($user, $booking, [
                    'previous_status' => $booking->getOriginal('status'),
                ]);

                // Update risk profile
                $riskProfile = $this->fraudService->getOrCreateRiskProfile($user);
                $riskProfile->increment('cancelled_bookings');
                $riskProfile->recalculateRiskScore();
            }
        }
    }
}
