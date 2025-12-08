<?php

namespace Modules\Payment\Jobs;

use Modules\Bookings\Models\Booking;
use Modules\Payment\Services\RazorpayService;
use Modules\Bookings\Services\BookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CaptureExpiredHoldsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300; // 5 minutes

    /**
     * Execute the job.
     */
    public function handle(RazorpayService $razorpayService, BookingService $bookingService): void
    {
        Log::info('CaptureExpiredHoldsJob: Starting execution');

        $startTime = now();
        $processedCount = 0;
        $capturedCount = 0;
        $failedCount = 0;
        $skippedCount = 0;

        try {
            // Find all bookings with expired holds that need capture
            $expiredHolds = Booking::where('status', 'payment_hold')
                ->where('payment_status', 'authorized')
                ->where('hold_expiry_at', '<=', now())
                ->whereNotNull('razorpay_payment_id')
                ->whereNull('capture_attempted_at') // Idempotency - not already attempted
                ->orderBy('hold_expiry_at', 'asc')
                ->get();

            Log::info('CaptureExpiredHoldsJob: Found expired holds', [
                'count' => $expiredHolds->count()
            ]);

            foreach ($expiredHolds as $booking) {
                $processedCount++;

                try {
                    // Use DB transaction with row locking for idempotency
                    DB::transaction(function () use (
                        $booking,
                        $razorpayService,
                        $bookingService,
                        &$capturedCount,
                        &$failedCount,
                        &$skippedCount
                    ) {
                        // Lock the booking row
                        $lockedBooking = Booking::where('id', $booking->id)
                            ->whereNull('capture_attempted_at')
                            ->lockForUpdate()
                            ->first();

                        if (!$lockedBooking) {
                            Log::info('CaptureExpiredHoldsJob: Booking already processed', [
                                'booking_id' => $booking->id
                            ]);
                            $skippedCount++;
                            return;
                        }

                        // Mark capture attempt immediately for idempotency
                        $lockedBooking->capture_attempted_at = now();
                        $lockedBooking->save();

                        Log::info('CaptureExpiredHoldsJob: Attempting capture', [
                            'booking_id' => $lockedBooking->id,
                            'payment_id' => $lockedBooking->razorpay_payment_id,
                            'amount' => $lockedBooking->total_amount
                        ]);

                        try {
                            // Capture payment via Razorpay
                            $captureResponse = $razorpayService->capturePayment(
                                paymentId: $lockedBooking->razorpay_payment_id,
                                amount: $lockedBooking->total_amount,
                                currency: 'INR'
                            );

                            // Capture successful - confirm booking
                            $bookingService->confirmBookingAfterCapture(
                                booking: $lockedBooking,
                                paymentId: $lockedBooking->razorpay_payment_id
                            );

                            $capturedCount++;

                            Log::info('CaptureExpiredHoldsJob: Capture successful', [
                                'booking_id' => $lockedBooking->id,
                                'payment_id' => $lockedBooking->razorpay_payment_id,
                                'capture_response' => $captureResponse
                            ]);

                        } catch (\Exception $captureException) {
                            // Capture failed - mark payment failed and release hold
                            Log::error('CaptureExpiredHoldsJob: Capture failed', [
                                'booking_id' => $lockedBooking->id,
                                'payment_id' => $lockedBooking->razorpay_payment_id,
                                'error' => $captureException->getMessage()
                            ]);

                            $bookingService->markPaymentFailed(
                                booking: $lockedBooking,
                                paymentId: $lockedBooking->razorpay_payment_id,
                                errorCode: 'CAPTURE_FAILED',
                                errorDescription: 'Automatic capture failed: ' . $captureException->getMessage()
                            );

                            $failedCount++;
                        }
                    });

                } catch (\Exception $e) {
                    Log::error('CaptureExpiredHoldsJob: Transaction failed', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $failedCount++;
                }
            }

            $duration = now()->diffInSeconds($startTime);

            Log::info('CaptureExpiredHoldsJob: Execution completed', [
                'duration_seconds' => $duration,
                'processed' => $processedCount,
                'captured' => $capturedCount,
                'failed' => $failedCount,
                'skipped' => $skippedCount
            ]);

        } catch (\Exception $e) {
            Log::error('CaptureExpiredHoldsJob: Job execution failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e; // Re-throw to mark job as failed
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('CaptureExpiredHoldsJob: Job failed permanently', [
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
}

