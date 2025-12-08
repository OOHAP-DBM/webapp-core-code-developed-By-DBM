<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Bookings\Models\Booking;
use App\Services\RazorpayService;
use Modules\Bookings\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BookingHoldController extends Controller
{
    protected RazorpayService $razorpayService;
    protected BookingService $bookingService;

    public function __construct(RazorpayService $razorpayService, BookingService $bookingService)
    {
        $this->razorpayService = $razorpayService;
        $this->bookingService = $bookingService;
    }

    /**
     * Display booking price snapshot
     * GET /admin/bookings/{id}/price-snapshot
     */
    public function showPriceSnapshot(int $id): View
    {
        $booking = Booking::with(['priceSnapshot', 'customer', 'vendor', 'hoarding'])->findOrFail($id);
        $priceSnapshot = $booking->priceSnapshot;

        if (!$priceSnapshot) {
            abort(404, 'Price snapshot not found for this booking');
        }

        return view('admin.bookings.price_snapshot', compact('booking', 'priceSnapshot'));
    }

    /**
     * Display payment holds management page
     * GET /admin/bookings/holds
     */
    public function index(): View
    {
        // Active holds (not expired yet)
        $activeHolds = Booking::where('status', 'payment_hold')
            ->where('payment_status', 'authorized')
            ->where('hold_expiry_at', '>', now())
            ->with(['customer', 'vendor', 'hoarding'])
            ->orderBy('hold_expiry_at', 'asc')
            ->get();

        // Expiring soon (within next 10 minutes)
        $expiringSoon = Booking::where('status', 'payment_hold')
            ->where('payment_status', 'authorized')
            ->whereBetween('hold_expiry_at', [now(), now()->addMinutes(10)])
            ->with(['customer', 'vendor', 'hoarding'])
            ->orderBy('hold_expiry_at', 'asc')
            ->get();

        // Expired holds pending capture
        $expired = Booking::where('status', 'payment_hold')
            ->where('payment_status', 'authorized')
            ->where('hold_expiry_at', '<=', now())
            ->with(['customer', 'vendor', 'hoarding'])
            ->orderBy('hold_expiry_at', 'asc')
            ->limit(50) // Limit to recent 50
            ->get();

        // Calculate total hold value
        $totalHoldValue = $activeHolds->sum('total_amount');

        return view('admin.bookings.holds', compact(
            'activeHolds',
            'expiringSoon',
            'expired',
            'totalHoldValue'
        ));
    }

    /**
     * Manual capture payment for a booking
     * POST /api/v1/admin/bookings/{id}/manual-capture
     */
    public function manualCapture(int $id): JsonResponse
    {
        try {
            Log::info('Manual capture requested', [
                'booking_id' => $id,
                'admin_user_id' => auth()->id()
            ]);

            $booking = Booking::findOrFail($id);

            // Validate booking state
            if ($booking->status !== 'payment_hold') {
                return response()->json([
                    'success' => false,
                    'message' => "Booking status must be 'payment_hold', current: {$booking->status}"
                ], 400);
            }

            if ($booking->payment_status !== 'authorized') {
                return response()->json([
                    'success' => false,
                    'message' => "Payment status must be 'authorized', current: {$booking->payment_status}"
                ], 400);
            }

            if (!$booking->razorpay_payment_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Razorpay payment ID missing'
                ], 400);
            }

            // Check if already attempted
            if ($booking->capture_attempted_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Capture already attempted for this booking'
                ], 400);
            }

            // Use transaction for idempotency
            DB::transaction(function () use ($booking) {
                // Lock and mark as attempted
                $lockedBooking = Booking::where('id', $booking->id)
                    ->whereNull('capture_attempted_at')
                    ->lockForUpdate()
                    ->first();

                if (!$lockedBooking) {
                    throw new \Exception('Booking capture already in progress');
                }

                $lockedBooking->capture_attempted_at = now();
                $lockedBooking->save();

                // Attempt capture
                try {
                    $this->razorpayService->capturePayment(
                        paymentId: $lockedBooking->razorpay_payment_id,
                        amount: $lockedBooking->total_amount,
                        currency: 'INR'
                    );

                    // Confirm booking
                    $this->bookingService->confirmBookingAfterCapture(
                        booking: $lockedBooking,
                        paymentId: $lockedBooking->razorpay_payment_id
                    );

                    Log::info('Manual capture successful', [
                        'booking_id' => $lockedBooking->id,
                        'payment_id' => $lockedBooking->razorpay_payment_id,
                        'admin_user_id' => auth()->id()
                    ]);

                } catch (\Exception $captureException) {
                    // Capture failed
                    Log::error('Manual capture failed', [
                        'booking_id' => $lockedBooking->id,
                        'error' => $captureException->getMessage()
                    ]);

                    $this->bookingService->markPaymentFailed(
                        booking: $lockedBooking,
                        paymentId: $lockedBooking->razorpay_payment_id,
                        errorCode: 'MANUAL_CAPTURE_FAILED',
                        errorDescription: 'Manual capture failed: ' . $captureException->getMessage()
                    );

                    throw $captureException;
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment captured successfully',
                'booking_id' => $id
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Manual capture error', [
                'booking_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to capture payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run capture job manually (for testing)
     * POST /api/v1/admin/bookings/run-capture-job
     */
    public function runCaptureJob(): JsonResponse
    {
        try {
            Log::info('Manual capture job triggered', [
                'admin_user_id' => auth()->id()
            ]);

            // Dispatch job immediately
            \App\Jobs\CaptureExpiredHoldsJob::dispatch();

            return response()->json([
                'success' => true,
                'message' => 'Capture job dispatched successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to dispatch job: ' . $e->getMessage()
            ], 500);
        }
    }
}

