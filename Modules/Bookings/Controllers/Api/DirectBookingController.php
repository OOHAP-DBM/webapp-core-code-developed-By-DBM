<?php

namespace Modules\Bookings\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GracePeriodService;
use App\Models\Hoarding;
use Modules\Bookings\Services\DirectBookingService;
use Modules\Bookings\Services\BookingService;
use App\Services\RazorpayService;
use App\Services\CommissionService;
use App\Jobs\ProcessAutoRefundJob;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * Direct Booking API Controller
 * Handles customer direct bookings without quotation
 */
class DirectBookingController extends Controller
{
    protected DirectBookingService $directBookingService;
    protected BookingService $bookingService;
    protected RazorpayService $razorpayService;
    protected CommissionService $commissionService;
    protected GracePeriodService $gracePeriodService;

    public function __construct(
        DirectBookingService $directBookingService,
        BookingService $bookingService,
        RazorpayService $razorpayService,
        CommissionService $commissionService,
        GracePeriodService $gracePeriodService
    ) {
        $this->directBookingService = $directBookingService;
        $this->bookingService = $bookingService;
        $this->razorpayService = $razorpayService;
        $this->commissionService = $commissionService;
        $this->gracePeriodService = $gracePeriodService;
    }

    /**
     * Get available hoardings for booking
     * 
     * GET /api/v1/customer/direct-bookings/available-hoardings
     */
    public function getAvailableHoardings(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'location' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'type' => 'nullable|string|in:ooh,dooh',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'min_width' => 'nullable|numeric|min:0',
                'min_height' => 'nullable|numeric|min:0',
                'start_date' => 'nullable|date|after_or_equal:today',
                'end_date' => 'nullable|date|after:start_date',
                'sort_by' => 'nullable|string|in:price_per_day,width,height,created_at',
                'sort_order' => 'nullable|string|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $hoardings = $this->directBookingService->getAvailableHoardings($filters);

            return response()->json([
                'success' => true,
                'data' => $hoardings->items(),
                'pagination' => [
                    'current_page' => $hoardings->currentPage(),
                    'per_page' => $hoardings->perPage(),
                    'total' => $hoardings->total(),
                    'last_page' => $hoardings->lastPage(),
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch available hoardings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check hoarding availability for specific dates
     * 
     * POST /api/v1/customer/direct-bookings/check-availability
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        try {
            $hoarding = Hoarding::findOrFail($request->hoarding_id);
            
            $validator = \Validator::make($request->all(), [
                'hoarding_id' => 'required|integer|exists:hoardings,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after:start_date',
            ]);

            // Add grace period validation
            $this->gracePeriodService->addValidationRule($validator, 'start_date', $hoarding);
            $validated = $validator->validate();

            $result = $this->directBookingService->checkHoardingAvailability(
                $validated['hoarding_id'],
                $validated['start_date'],
                $validated['end_date']
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a direct booking
     * 
     * POST /api/v1/customer/direct-bookings
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $hoarding = Hoarding::findOrFail($request->hoarding_id);
            
            $validator = \Validator::make($request->all(), [
                'hoarding_id' => 'required|integer|exists:hoardings,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after:start_date',
                'customer_notes' => 'nullable|string|max:1000',
            ]);

            // Add grace period validation
            $this->gracePeriodService->addValidationRule($validator, 'start_date', $hoarding);
            $validated = $validator->validate();

            $validated['customer_id'] = Auth::id();

            $booking = $this->directBookingService->createDirectBooking($validated);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully. Please complete payment within 30 minutes.',
                'data' => [
                    'booking' => $booking,
                    'hold_expiry_at' => $booking->hold_expiry_at,
                    'payment_required' => true,
                ],
            ], 201);

        } catch (Exception $e) {
            Log::error('Direct booking creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Initiate payment for a booking
     * Creates Razorpay order
     * 
     * POST /api/v1/customer/direct-bookings/{id}/initiate-payment
     */
    public function initiatePayment(int $id): JsonResponse
    {
        try {
            $booking = Booking::with(['hoarding', 'customer'])->findOrFail($id);

            // Verify ownership
            if ($booking->customer_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this booking',
                ], 403);
            }

            // Check if booking is in correct status
            if ($booking->status !== Booking::STATUS_PENDING_PAYMENT_HOLD) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking is not eligible for payment',
                ], 422);
            }

            // Check if hold hasn't expired
            if (Carbon::parse($booking->hold_expiry_at)->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking hold has expired',
                ], 422);
            }

            // Create Razorpay order
            $razorpayOrder = $this->razorpayService->createOrder(
                (float) $booking->total_amount,
                'INR',
                'BOOKING-' . $booking->id,
                'manual'
            );

            // Update booking with Razorpay order ID
            $booking = $this->bookingService->moveToPaymentHold(
                $booking->id,
                $razorpayOrder['id']
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'booking_id' => $booking->id,
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'razorpay_key_id' => config('services.razorpay.key_id'),
                    'amount' => $booking->total_amount,
                    'currency' => 'INR',
                    'hold_expiry_at' => $booking->hold_expiry_at,
                    'customer' => [
                        'name' => $booking->customer->name,
                        'email' => $booking->customer->email,
                        'phone' => $booking->customer->phone,
                    ],
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Payment initiation failed', [
                'booking_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm payment after Razorpay success
     * 
     * POST /api/v1/customer/direct-bookings/{id}/confirm-payment
     */
    public function confirmPayment(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
                'razorpay_signature' => 'required|string',
            ]);

            $booking = Booking::findOrFail($id);

            // Verify ownership
            if ($booking->customer_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this booking',
                ], 403);
            }

            // Verify signature
            $signatureValid = $this->razorpayService->verifySignature(
                $validated['razorpay_order_id'],
                $validated['razorpay_payment_id'],
                $validated['razorpay_signature']
            );

            if (!$signatureValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment signature',
                ], 422);
            }

            // Capture payment
            $captureResponse = $this->razorpayService->capturePayment(
                $validated['razorpay_payment_id'],
                (float) $booking->total_amount
            );

            // Confirm booking
            $booking = $this->bookingService->confirmBooking(
                $booking->id,
                $validated['razorpay_payment_id']
            );

            // Calculate and record commission
            [$bookingPayment, $commissionLog] = $this->commissionService->calculateAndRecord(
                $booking,
                $validated['razorpay_payment_id'],
                $validated['razorpay_order_id']
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed successfully. Booking is now active.',
                'data' => [
                    'booking' => $booking->fresh(['hoarding', 'customer', 'vendor']),
                    'payment' => $bookingPayment,
                    'can_cancel_with_refund' => true, // Within 30 minutes
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Payment confirmation failed', [
                'booking_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel booking within 30 minutes for auto-refund
     * 
     * POST /api/v1/customer/direct-bookings/{id}/cancel
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cancellation_reason' => 'required|string|max:500',
            ]);

            $booking = Booking::findOrFail($id);

            // Verify ownership
            if ($booking->customer_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this booking',
                ], 403);
            }

            // Check if booking can be cancelled
            if (!in_array($booking->status, [Booking::STATUS_CONFIRMED, Booking::STATUS_PAYMENT_HOLD])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking cannot be cancelled in current status',
                ], 422);
            }

            // Check if within 30 minutes of payment capture for auto-refund
            $paymentCapturedAt = $booking->payment_captured_at ? Carbon::parse($booking->payment_captured_at) : null;
            $isWithin30Minutes = $paymentCapturedAt && $paymentCapturedAt->gt(Carbon::now()->subMinutes(30));

            if ($isWithin30Minutes) {
                // Dispatch auto-refund job
                ProcessAutoRefundJob::dispatch($booking->id, Auth::id(), $validated['cancellation_reason']);

                return response()->json([
                    'success' => true,
                    'message' => 'Booking cancelled. Refund will be processed automatically within 5-7 business days.',
                    'data' => [
                        'booking_id' => $booking->id,
                        'refund_eligible' => true,
                        'refund_amount' => $booking->total_amount,
                        'refund_processing' => true,
                    ],
                ]);
            }

            // Regular cancellation (no auto-refund)
            $booking = $this->bookingService->cancelBooking(
                $booking->id,
                Auth::id(),
                $validated['cancellation_reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled. Refund is not applicable as 30-minute window has passed.',
                'data' => [
                    'booking_id' => $booking->id,
                    'refund_eligible' => false,
                    'cancelled_at' => $booking->cancelled_at,
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Booking cancellation failed', [
                'booking_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get booking details
     * 
     * GET /api/v1/customer/direct-bookings/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $booking = Booking::with([
                'hoarding.vendor',
                'customer',
                'bookingPayment.commissionLog',
                'statusLogs',
            ])->findOrFail($id);

            // Verify ownership (customer can view their own, admin can view all)
            $user = Auth::user();
            $isAdmin = in_array($user->role, ['admin', 'super_admin']);
            
            if ($booking->customer_id !== Auth::id() && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this booking',
                ], 403);
            }

            // Calculate if within refund window
            $paymentCapturedAt = $booking->payment_captured_at ? Carbon::parse($booking->payment_captured_at) : null;
            $canCancelWithRefund = $paymentCapturedAt && $paymentCapturedAt->gt(Carbon::now()->subMinutes(30));

            // Calculate time remaining for hold/refund
            $holdExpiryAt = $booking->hold_expiry_at ? Carbon::parse($booking->hold_expiry_at) : null;
            $minutesRemaining = $holdExpiryAt && $holdExpiryAt->isFuture() ? Carbon::now()->diffInMinutes($holdExpiryAt) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'booking' => $booking,
                    'can_cancel_with_refund' => $canCancelWithRefund,
                    'hold_minutes_remaining' => $minutesRemaining,
                    'hold_expired' => $holdExpiryAt && $holdExpiryAt->isPast(),
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch booking details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
