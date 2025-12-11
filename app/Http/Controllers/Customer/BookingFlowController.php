<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\HoardingBookingService;
use App\Services\PaymentService;
use App\Models\BookingDraft;
use App\Models\Booking;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BookingFlowController extends Controller
{
    protected HoardingBookingService $bookingService;
    protected PaymentService $paymentService;

    public function __construct(
        HoardingBookingService $bookingService,
        PaymentService $paymentService
    ) {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
    }

    /**
     * Step 1: Get hoarding details with availability
     * GET /api/booking/hoarding/{id}
     */
    public function getHoardingDetails(int $id)
    {
        try {
            $details = $this->bookingService->getHoardingDetails($id);

            return response()->json([
                'success' => true,
                'data' => $details,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Step 2: Get available packages for hoarding
     * GET /api/booking/hoarding/{id}/packages
     */
    public function getPackages(int $id)
    {
        try {
            $packages = $this->bookingService->getAvailablePackages($id);

            return response()->json([
                'success' => true,
                'data' => $packages,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Step 3: Validate date selection
     * POST /api/booking/validate-dates
     */
    public function validateDates(Request $request)
    {
        $request->validate([
            'hoarding_id' => 'required|exists:hoardings,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'package_id' => 'nullable|exists:dooh_packages,id',
        ]);

        try {
            $result = $this->bookingService->validateDateSelection(
                $request->hoarding_id,
                $request->start_date,
                $request->end_date,
                $request->package_id
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'valid' => false,
            ], 400);
        }
    }

    /**
     * Step 4: Create/Update draft booking
     * POST /api/booking/draft
     */
    public function createOrUpdateDraft(Request $request)
    {
        $request->validate([
            'hoarding_id' => 'required|exists:hoardings,id',
            'package_id' => 'nullable|exists:dooh_packages,id',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'coupon_code' => 'nullable|string|max:50',
        ]);

        try {
            $draft = $this->bookingService->createOrUpdateDraft(
                Auth::user(),
                $request->hoarding_id,
                $request->package_id,
                $request->start_date,
                $request->end_date,
                $request->coupon_code
            );

            return response()->json([
                'success' => true,
                'message' => 'Draft saved successfully',
                'data' => [
                    'draft_id' => $draft->id,
                    'step' => $draft->step,
                    'hoarding' => [
                        'id' => $draft->hoarding->id,
                        'title' => $draft->hoarding->title,
                    ],
                    'package' => $draft->package ? [
                        'id' => $draft->package->id,
                        'name' => $draft->package->package_name,
                    ] : null,
                    'dates' => [
                        'start_date' => $draft->start_date?->format('Y-m-d'),
                        'end_date' => $draft->end_date?->format('Y-m-d'),
                        'duration_days' => $draft->duration_days,
                    ],
                    'pricing' => [
                        'base_price' => $draft->base_price,
                        'discount_amount' => $draft->discount_amount,
                        'gst_amount' => $draft->gst_amount,
                        'total_amount' => $draft->total_amount,
                    ],
                    'expires_at' => $draft->expires_at?->toIso8601String(),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Draft creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'hoarding_id' => $request->hoarding_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get existing draft
     * GET /api/booking/draft/{id}
     */
    public function getDraft(int $id)
    {
        try {
            $draft = BookingDraft::with(['hoarding', 'package', 'customer'])
                ->where('id', $id)
                ->where('customer_id', Auth::id())
                ->firstOrFail();

            if ($draft->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Draft has expired',
                    'expired' => true,
                ], 410);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'draft_id' => $draft->id,
                    'step' => $draft->step,
                    'hoarding' => [
                        'id' => $draft->hoarding->id,
                        'title' => $draft->hoarding->title,
                        'location' => $draft->hoarding->city . ', ' . $draft->hoarding->state,
                    ],
                    'package' => $draft->package ? [
                        'id' => $draft->package->id,
                        'name' => $draft->package->package_name,
                    ] : null,
                    'dates' => [
                        'start_date' => $draft->start_date?->format('Y-m-d'),
                        'end_date' => $draft->end_date?->format('Y-m-d'),
                        'duration_days' => $draft->duration_days,
                        'duration_type' => $draft->duration_type,
                    ],
                    'pricing' => [
                        'base_price' => $draft->base_price,
                        'discount_amount' => $draft->discount_amount,
                        'gst_amount' => $draft->gst_amount,
                        'total_amount' => $draft->total_amount,
                        'price_snapshot' => $draft->price_snapshot,
                    ],
                    'applied_offers' => $draft->applied_offers,
                    'expires_at' => $draft->expires_at?->toIso8601String(),
                    'last_updated' => $draft->last_updated_step_at?->toIso8601String(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Draft not found',
            ], 404);
        }
    }

    /**
     * Step 5: Get review summary
     * GET /api/booking/draft/{id}/review
     */
    public function getReviewSummary(int $id)
    {
        try {
            $draft = BookingDraft::where('id', $id)
                ->where('customer_id', Auth::id())
                ->with(['hoarding', 'package'])
                ->firstOrFail();

            if ($draft->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Draft has expired. Please start again.',
                    'expired' => true,
                ], 410);
            }

            $summary = $this->bookingService->getReviewSummary($draft);

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Step 6: Confirm booking and lock inventory
     * POST /api/booking/draft/{id}/confirm
     */
    public function confirmBooking(int $id)
    {
        try {
            $draft = BookingDraft::where('id', $id)
                ->where('customer_id', Auth::id())
                ->with(['hoarding', 'package'])
                ->firstOrFail();

            // Confirm and lock inventory
            $booking = $this->bookingService->confirmAndLockBooking($draft);

            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed and inventory locked',
                'data' => [
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                    'hold_expires_at' => $booking->hold_expiry_at->toIso8601String(),
                    'total_amount' => $booking->total_amount,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Booking confirmation failed', [
                'error' => $e->getMessage(),
                'draft_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Step 7: Create Razorpay payment session
     * POST /api/booking/{id}/create-payment
     */
    public function createPaymentSession(int $id)
    {
        try {
            $booking = Booking::where('id', $id)
                ->where('customer_id', Auth::id())
                ->with(['hoarding', 'customer'])
                ->firstOrFail();

            // Check if booking is in correct state
            if ($booking->status !== Booking::STATUS_PENDING_PAYMENT_HOLD) {
                throw new Exception('Booking is not in a valid state for payment');
            }

            // Check if hold has not expired
            if ($booking->hold_expiry_at && $booking->hold_expiry_at->isPast()) {
                throw new Exception('Booking hold has expired. Please book again.');
            }

            // Create payment order via PaymentService
            $orderResult = $this->paymentService->createOrder($booking->total_amount, 'INR', [
                'reference_type' => 'Booking',
                'reference_id' => $booking->id,
                'user_id' => $booking->customer_id,
                'receipt' => "booking_{$booking->id}_" . time(),
                'description' => "Booking #{$booking->id} - {$booking->hoarding->title}",
                'customer_name' => $booking->customer->name,
                'customer_email' => $booking->customer->email,
                'customer_phone' => $booking->customer->phone,
                'manual_capture' => true,
                'capture_expiry_minutes' => 30,
                'metadata' => [
                    'booking_id' => $booking->id,
                    'customer_id' => $booking->customer_id,
                    'hoarding_id' => $booking->hoarding_id,
                ],
            ]);

            if (!$orderResult['success']) {
                throw new Exception($orderResult['error'] ?? 'Failed to create payment order');
            }

            $orderData = $orderResult['order_data'];
            $transaction = $orderResult['transaction'];

            // Save order ID and transaction reference to booking
            $booking->update([
                'razorpay_order_id' => $orderData['id'],
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderData['id'],
                    'amount' => $orderData['amount'],
                    'currency' => $orderData['currency'],
                    'key' => config('services.razorpay.key'),
                    'booking' => [
                        'id' => $booking->id,
                        'hoarding_title' => $booking->hoarding->title,
                        'start_date' => $booking->start_date->format('d M Y'),
                        'end_date' => $booking->end_date->format('d M Y'),
                        'total_amount' => $booking->total_amount,
                    ],
                    'customer' => [
                        'name' => $booking->customer->name,
                        'email' => $booking->customer->email,
                        'phone' => $booking->customer->phone,
                    ],
                    'hold_expires_at' => $booking->hold_expiry_at->toIso8601String(),
                    'hold_remaining_seconds' => now()->diffInSeconds($booking->hold_expiry_at, false),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Payment session creation failed', [
                'error' => $e->getMessage(),
                'booking_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Step 8: Handle payment callback (success)
     * POST /api/booking/payment/callback
     */
    public function handlePaymentCallback(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            // Find booking
            $booking = Booking::where('razorpay_order_id', $request->razorpay_order_id)
                ->where('customer_id', Auth::id())
                ->firstOrFail();

            // Find payment transaction
            $transaction = PaymentTransaction::where('gateway_order_id', $request->razorpay_order_id)
                ->where('reference_type', 'Booking')
                ->where('reference_id', $booking->id)
                ->firstOrFail();

            // Update transaction with payment ID from callback
            $transaction->update([
                'gateway_payment_id' => $request->razorpay_payment_id,
            ]);

            // Update booking with payment details
            $booking->update([
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'payment_authorized_at' => now(),
                'status' => Booking::STATUS_PAYMENT_HOLD,
                'payment_status' => 'authorized',
            ]);

            // Capture payment via PaymentService
            $captureResult = $this->paymentService->capturePayment(
                $request->razorpay_payment_id,
                $booking->total_amount
            );

            if ($captureResult['success']) {
                // Payment captured successfully
                $booking->update([
                    'payment_captured_at' => now(),
                    'status' => Booking::STATUS_CONFIRMED,
                    'payment_status' => 'paid',
                    'confirmed_at' => now(),
                ]);

                // TODO: Trigger notifications
                // - Send confirmation email to customer
                // - Notify vendor
                // - Notify admin
                // - Add timeline event

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful! Your booking is confirmed.',
                    'data' => [
                        'booking_id' => $booking->id,
                        'status' => $booking->status,
                        'payment_id' => $booking->razorpay_payment_id,
                        'confirmed_at' => $booking->confirmed_at->toIso8601String(),
                    ],
                ]);
            } else {
                // Payment capture failed
                $booking->update([
                    'payment_failed_at' => now(),
                    'payment_error_code' => $captureResult['error_code'] ?? null,
                    'payment_error_description' => $captureResult['error_description'] ?? null,
                ]);

                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => 'Payment authorization successful but capture failed. Our team will assist you.',
                    'data' => [
                        'booking_id' => $booking->id,
                        'status' => $booking->status,
                    ],
                ], 400);
            }
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Payment callback handling failed', [
                'error' => $e->getMessage(),
                'order_id' => $request->razorpay_order_id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Handle payment failure
     * POST /api/booking/payment/failed
     */
    public function handlePaymentFailure(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'error_code' => 'nullable|string',
            'error_description' => 'nullable|string',
        ]);

        try {
            $booking = Booking::where('razorpay_order_id', $request->razorpay_order_id)
                ->where('customer_id', Auth::id())
                ->firstOrFail();

            $booking->update([
                'payment_failed_at' => now(),
                'payment_error_code' => $request->error_code,
                'payment_error_description' => $request->error_description,
                'payment_status' => 'failed',
            ]);

            // Don't cancel the booking yet - customer might retry
            // Hold will auto-expire if not paid within hold window

            return response()->json([
                'success' => true,
                'message' => 'Payment failure recorded. You can retry payment.',
                'data' => [
                    'booking_id' => $booking->id,
                    'can_retry' => !$booking->hold_expiry_at->isPast(),
                    'hold_expires_at' => $booking->hold_expiry_at->toIso8601String(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get customer's drafts
     * GET /api/booking/my-drafts
     */
    public function getMyDrafts()
    {
        $drafts = BookingDraft::with(['hoarding'])
            ->where('customer_id', Auth::id())
            ->where('is_converted', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($draft) => [
                'id' => $draft->id,
                'hoarding' => [
                    'id' => $draft->hoarding->id,
                    'title' => $draft->hoarding->title,
                    'location' => $draft->hoarding->city . ', ' . $draft->hoarding->state,
                ],
                'step' => $draft->step,
                'dates' => [
                    'start_date' => $draft->start_date?->format('d M Y'),
                    'end_date' => $draft->end_date?->format('d M Y'),
                ],
                'total_amount' => $draft->total_amount,
                'expires_at' => $draft->expires_at?->toIso8601String(),
                'updated_at' => $draft->updated_at->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $drafts,
        ]);
    }

    /**
     * Delete draft
     * DELETE /api/booking/draft/{id}
     */
    public function deleteDraft(int $id)
    {
        try {
            $draft = BookingDraft::where('id', $id)
                ->where('customer_id', Auth::id())
                ->firstOrFail();

            $draft->delete();

            return response()->json([
                'success' => true,
                'message' => 'Draft deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Draft not found',
            ], 404);
        }
    }
}
