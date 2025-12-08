<?php

namespace Modules\Bookings\Jobs;

use Modules\Bookings\Services\BookingService;
use Modules\Bookings\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScheduleBookingConfirmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $bookingId;
    protected string $paymentId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $bookingId, string $paymentId)
    {
        $this->bookingId = $bookingId;
        $this->paymentId = $paymentId;
    }

    /**
     * Execute the job.
     */
    public function handle(BookingService $bookingService): void
    {
        try {
            Log::info('ScheduleBookingConfirmJob: Processing', [
                'booking_id' => $this->bookingId,
                'payment_id' => $this->paymentId
            ]);

            $booking = $bookingService->find($this->bookingId);

            if (!$booking) {
                Log::error('ScheduleBookingConfirmJob: Booking not found', [
                    'booking_id' => $this->bookingId
                ]);
                return;
            }

            // Confirm booking after payment capture
            $bookingService->confirmBookingAfterCapture($booking, $this->paymentId);

            Log::info('ScheduleBookingConfirmJob: Booking confirmed successfully', [
                'booking_id' => $this->bookingId,
                'payment_id' => $this->paymentId,
                'status' => $booking->fresh()->status
            ]);

        } catch (\Exception $e) {
            Log::error('ScheduleBookingConfirmJob: Failed to confirm booking', [
                'booking_id' => $this->bookingId,
                'payment_id' => $this->paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ScheduleBookingConfirmJob: Job failed', [
            'booking_id' => $this->bookingId,
            'payment_id' => $this->paymentId,
            'error' => $exception->getMessage()
        ]);
    }
}

