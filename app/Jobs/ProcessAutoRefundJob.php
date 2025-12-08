<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\RazorpayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Bookings\Services\BookingService;
use Carbon\Carbon;
use Exception;

class ProcessAutoRefundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $bookingId;
    public int $userId;
    public string $cancellationReason;

    /**
     * Create a new job instance.
     */
    public function __construct(int $bookingId, int $userId, string $cancellationReason)
    {
        $this->bookingId = $bookingId;
        $this->userId = $userId;
        $this->cancellationReason = $cancellationReason;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $booking = Booking::with(['customer', 'vendor'])->findOrFail($this->bookingId);

            // Verify booking is eligible for auto-refund
            if ($booking->status !== Booking::STATUS_CONFIRMED) {
                Log::warning('Auto-refund: Booking not in confirmed status', [
                    'booking_id' => $this->bookingId,
                    'status' => $booking->status,
                ]);
                return;
            }

            // Check if payment was captured
            if (!$booking->razorpay_payment_id || !$booking->payment_captured_at) {
                Log::warning('Auto-refund: No captured payment found', [
                    'booking_id' => $this->bookingId,
                ]);
                return;
            }

            // Check if within 30 minutes of payment capture
            $paymentCapturedAt = Carbon::parse($booking->payment_captured_at);
            $thirtyMinutesAgo = Carbon::now()->subMinutes(30);

            if ($paymentCapturedAt->lt($thirtyMinutesAgo)) {
                Log::warning('Auto-refund: Payment captured more than 30 minutes ago', [
                    'booking_id' => $this->bookingId,
                    'captured_at' => $paymentCapturedAt->toIso8601String(),
                ]);
                
                // Regular cancellation process
                $bookingService = app(BookingService::class);
                $bookingService->cancelBooking(
                    $this->bookingId,
                    $this->userId,
                    'Cancellation requested after 30-minute window - ' . $this->cancellationReason
                );
                
                return;
            }

            // Process auto-refund via Razorpay
            $razorpayService = app(RazorpayService::class);

            try {
                $refundResponse = $razorpayService->createRefund(
                    $booking->razorpay_payment_id,
                    (float) $booking->total_amount,
                    'Auto-refund: Customer cancelled within 30 minutes'
                );

                // Update booking status to refunded
                $booking->update([
                    'status' => Booking::STATUS_REFUNDED,
                    'payment_status' => 'refunded',
                    'cancellation_reason' => $this->cancellationReason,
                    'cancelled_at' => now(),
                    'refund_id' => $refundResponse['id'] ?? null,
                    'refund_amount' => $refundResponse['amount'] / 100 ?? $booking->total_amount,
                    'refunded_at' => now(),
                ]);

                // Log status
                \App\Models\BookingStatusLog::create([
                    'booking_id' => $booking->id,
                    'from_status' => Booking::STATUS_CONFIRMED,
                    'to_status' => Booking::STATUS_REFUNDED,
                    'changed_by' => $this->userId,
                    'notes' => 'Auto-refund processed - Cancelled within 30 minutes',
                ]);

                // Notify customer about refund
                // $booking->customer->notify(new RefundProcessedNotification($booking, $refundResponse));

                Log::info('Auto-refund processed successfully', [
                    'booking_id' => $this->bookingId,
                    'refund_id' => $refundResponse['id'] ?? null,
                    'amount' => $refundResponse['amount'] / 100 ?? $booking->total_amount,
                ]);

            } catch (Exception $razorpayException) {
                Log::error('Razorpay refund failed for auto-refund', [
                    'booking_id' => $this->bookingId,
                    'error' => $razorpayException->getMessage(),
                ]);

                // Mark booking as cancelled but flag refund as pending/failed
                $booking->update([
                    'status' => Booking::STATUS_CANCELLED,
                    'payment_status' => 'refund_pending',
                    'cancellation_reason' => $this->cancellationReason,
                    'cancelled_at' => now(),
                    'refund_error' => $razorpayException->getMessage(),
                ]);

                // Log status
                \App\Models\BookingStatusLog::create([
                    'booking_id' => $booking->id,
                    'from_status' => Booking::STATUS_CONFIRMED,
                    'to_status' => Booking::STATUS_CANCELLED,
                    'changed_by' => $this->userId,
                    'notes' => 'Booking cancelled but refund failed - Manual intervention required',
                ]);

                throw $razorpayException; // Re-throw to trigger retry
            }

        } catch (Exception $e) {
            Log::error('Auto-refund job failed', [
                'booking_id' => $this->bookingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Let Laravel queue handle retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Exception $exception): void
    {
        Log::error('Auto-refund job failed permanently', [
            'booking_id' => $this->bookingId,
            'user_id' => $this->userId,
            'error' => $exception?->getMessage(),
        ]);

        // Notify admin about manual intervention needed
        // Admin::notify(new RefundFailedNotification($this->bookingId, $exception));
    }
}
