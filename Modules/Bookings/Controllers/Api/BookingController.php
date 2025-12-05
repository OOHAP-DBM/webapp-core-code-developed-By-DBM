<?php

namespace Modules\Bookings\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Bookings\Services\BookingService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    protected BookingService $service;
    protected RazorpayService $razorpayService;

    public function __construct(BookingService $service, RazorpayService $razorpayService)
    {
        $this->service = $service;
        $this->razorpayService = $razorpayService;
    }

    /**
     * Create booking from quotation
     * POST /api/v1/quotations/{quotationId}/book
     */
    public function createFromQuotation(Request $request, int $quotationId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $booking = $this->service->createFromQuotation($quotationId, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully with 30-minute payment hold',
                'data' => $booking->load(['quotation', 'customer', 'vendor', 'hoarding']),
                'hold_expiry_at' => $booking->hold_expiry_at?->toIso8601String(),
                'hold_minutes_remaining' => $booking->getHoldMinutesRemaining(),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create Razorpay order for booking
     * POST /api/v1/bookings/{id}/create-order
     */
    public function createOrder(int $id): JsonResponse
    {
        try {
            $booking = $this->service->find($id);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found',
                ], 404);
            }

            // Check authorization
            if ($booking->customer_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Check if booking is in valid state
            if (!in_array($booking->status, ['pending', 'payment_hold'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot create order for booking with status: {$booking->status}",
                ], 400);
            }

            // Check if order already exists
            if ($booking->razorpay_order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Razorpay order already exists for this booking',
                    'existing_order_id' => $booking->razorpay_order_id,
                ], 400);
            }

            // Check if hold is expired
            if ($booking->hold_expiry_at && $booking->hold_expiry_at->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking hold has expired',
                ], 400);
            }

            // Create Razorpay order with manual capture
            $receipt = "BOOKING_{$booking->id}_" . time();
            $orderData = $this->razorpayService->createOrder(
                amount: $booking->total_amount,
                currency: 'INR',
                receipt: $receipt,
                captureMethod: 'manual'
            );

            // Update booking with Razorpay order ID
            $booking->razorpay_order_id = $orderData['id'];
            $booking->save();

            // Move to payment hold if not already there
            if ($booking->status === 'pending') {
                $this->service->moveToPaymentHold($booking->id, $orderData['id']);
                $booking->refresh();
            }

            Log::info('Razorpay order created successfully', [
                'booking_id' => $booking->id,
                'order_id' => $orderData['id'],
                'amount' => $booking->total_amount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Razorpay order created successfully',
                'data' => [
                    'booking_id' => $booking->id,
                    'order_id' => $orderData['id'],
                    'amount' => $orderData['amount'], // Amount in paise
                    'amount_inr' => $booking->total_amount,
                    'currency' => $orderData['currency'],
                    'receipt' => $orderData['receipt'],
                    'status' => $orderData['status'],
                    'created_at' => $orderData['created_at'],
                    'hold_expiry_at' => $booking->hold_expiry_at?->toIso8601String(),
                    'hold_minutes_remaining' => $booking->getHoldMinutesRemaining(),
                ],
                'razorpay_key' => config('services.razorpay.key_id'),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create Razorpay order', [
                'booking_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create Razorpay order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Move booking to payment hold (when Razorpay order created)
     * PATCH /api/v1/bookings/{id}/payment-hold
     */
    public function moveToPaymentHold(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'razorpay_order_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $booking = $this->service->find($id);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found',
                ], 404);
            }

            // Check authorization
            if ($booking->customer_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $booking = $this->service->moveToPaymentHold($id, $validator->validated()['razorpay_order_id']);

            return response()->json([
                'success' => true,
                'message' => 'Booking moved to payment hold',
                'data' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Confirm booking after payment
     * PATCH /api/v1/bookings/{id}/confirm
     */
    public function confirm(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'razorpay_payment_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $booking = $this->service->find($id);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found',
                ], 404);
            }

            // Check authorization
            if ($booking->customer_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $booking = $this->service->confirmBooking($id, $validator->validated()['razorpay_payment_id']);

            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed successfully',
                'data' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm booking',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel booking
     * PATCH /api/v1/bookings/{id}/cancel
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $booking = $this->service->find($id);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found',
                ], 404);
            }

            // Check authorization (customer or vendor can cancel)
            if ($booking->customer_id !== Auth::id() 
                && $booking->vendor_id !== Auth::id() 
                && !Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $booking = $this->service->cancelBooking($id, $request->input('reason'));

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'data' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get all bookings for authenticated user
     * GET /api/v1/bookings
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasRole('customer')) {
            $bookings = $this->service->getCustomerBookings();
        } elseif ($user->hasRole('vendor')) {
            $bookings = $this->service->getVendorBookings();
        } elseif ($user->hasRole('admin')) {
            $bookings = $this->service->getAll($request->only(['status', 'customer_id', 'vendor_id', 'hoarding_id']));
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $bookings,
            'total' => $bookings->count(),
        ]);
    }

    /**
     * Get booking by ID
     * GET /api/v1/bookings/{id}
     */
    public function show(int $id): JsonResponse
    {
        $booking = $this->service->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check authorization
        if (!$this->service->canView($booking)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $booking,
            'hold_minutes_remaining' => $booking->getHoldMinutesRemaining(),
        ]);
    }

    /**
     * Release expired holds (cron endpoint)
     * POST /api/v1/bookings/release-expired-holds
     */
    public function releaseExpiredHolds(): JsonResponse
    {
        try {
            $count = $this->service->releaseExpiredHolds();

            return response()->json([
                'success' => true,
                'message' => "Released {$count} expired booking hold(s)",
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to release expired holds',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
