<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\QuotationMilestone;
use App\Models\Booking;
use App\Services\MilestoneService;
use App\Services\MilestoneInvoiceService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * MilestonePaymentController
 * 
 * Handles milestone payment operations WITHOUT modifying existing payment flow.
 * Only activates when booking has milestone payment mode.
 */
class MilestonePaymentController extends Controller
{
    protected MilestoneService $milestoneService;
    protected MilestoneInvoiceService $invoiceService;
    protected PaymentService $paymentService;

    public function __construct(
        MilestoneService $milestoneService,
        MilestoneInvoiceService $invoiceService,
        PaymentService $paymentService
    ) {
        $this->milestoneService = $milestoneService;
        $this->invoiceService = $invoiceService;
        $this->paymentService = $paymentService;
    }

    /**
     * Get all milestones for a booking
     * GET /api/v1/bookings/{bookingId}/milestones
     */
    public function index(int $bookingId): JsonResponse
    {
        try {
            $booking = Booking::with(['quotation.milestones'])->findOrFail($bookingId);

            // Authorization check
            if ($booking->customer_id !== Auth::id() && !Auth::user()->hasAnyRole(['admin', 'vendor'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Check if booking has milestones
            if (!$booking->hasMilestones()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking does not use milestone payments',
                ], 400);
            }

            $milestones = $booking->getMilestones()->map(function ($milestone) {
                return [
                    'id' => $milestone->id,
                    'sequence_no' => $milestone->sequence_no,
                    'title' => $milestone->title,
                    'description' => $milestone->description,
                    'amount' => $milestone->calculated_amount,
                    'formatted_amount' => $milestone->getFormattedCalculatedAmount(),
                    'status' => $milestone->status,
                    'status_label' => $milestone->getStatusLabel(),
                    'status_color' => $milestone->getStatusColor(),
                    'due_date' => $milestone->due_date?->format('Y-m-d'),
                    'paid_at' => $milestone->paid_at?->format('Y-m-d H:i:s'),
                    'invoice_number' => $milestone->invoice_number,
                    'is_paid' => $milestone->isPaid(),
                    'is_overdue' => $milestone->isOverdue(),
                    'is_due' => $milestone->isDue(),
                    'days_until_due' => $milestone->getDaysUntilDue(),
                    'days_overdue' => $milestone->getDaysOverdue(),
                ];
            });

            $summary = $this->milestoneService->getMilestoneSummary($booking->quotation);

            return response()->json([
                'success' => true,
                'data' => [
                    'milestones' => $milestones,
                    'summary' => $summary,
                    'booking' => [
                        'id' => $booking->id,
                        'milestone_total' => $booking->milestone_total,
                        'milestone_paid' => $booking->milestone_paid,
                        'milestone_amount_paid' => $booking->milestone_amount_paid,
                        'milestone_amount_remaining' => $booking->milestone_amount_remaining,
                        'progress_percentage' => $booking->getMilestoneProgressPercentage(),
                        'all_milestones_paid' => $booking->allMilestonesPaid(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch milestones', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch milestones',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get milestone details
     * GET /api/v1/milestones/{milestoneId}
     */
    public function show(int $milestoneId): JsonResponse
    {
        try {
            $milestone = QuotationMilestone::with(['quotation.customer', 'quotation.vendor', 'paymentTransaction'])
                ->findOrFail($milestoneId);

            $booking = Booking::where('quotation_id', $milestone->quotation_id)->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found for this milestone',
                ], 404);
            }

            // Authorization check
            if ($booking->customer_id !== Auth::id() && !Auth::user()->hasAnyRole(['admin', 'vendor'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'milestone' => [
                        'id' => $milestone->id,
                        'sequence_no' => $milestone->sequence_no,
                        'title' => $milestone->title,
                        'description' => $milestone->description,
                        'amount_type' => $milestone->amount_type,
                        'amount' => $milestone->amount,
                        'calculated_amount' => $milestone->calculated_amount,
                        'formatted_amount' => $milestone->getFormattedCalculatedAmount(),
                        'status' => $milestone->status,
                        'status_label' => $milestone->getStatusLabel(),
                        'due_date' => $milestone->due_date?->format('Y-m-d'),
                        'paid_at' => $milestone->paid_at?->format('Y-m-d H:i:s'),
                        'invoice_number' => $milestone->invoice_number,
                        'vendor_notes' => $milestone->vendor_notes,
                        'payment_details' => $milestone->payment_details,
                        'is_paid' => $milestone->isPaid(),
                    ],
                    'booking' => [
                        'id' => $booking->id,
                        'status' => $booking->status,
                        'customer_name' => $booking->customer->name,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch milestone details', [
                'milestone_id' => $milestoneId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch milestone details',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create payment order for milestone
     * POST /api/v1/milestones/{milestoneId}/create-payment
     */
    public function createPayment(Request $request, int $milestoneId): JsonResponse
    {
        try {
            $milestone = QuotationMilestone::with(['quotation.customer'])->findOrFail($milestoneId);
            
            $booking = Booking::where('quotation_id', $milestone->quotation_id)->firstOrFail();

            // Authorization check
            if ($booking->customer_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Validate milestone can be paid
            if ($milestone->isPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This milestone has already been paid',
                ], 400);
            }

            if (!$milestone->isDue() && !$milestone->isOverdue()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This milestone is not yet due for payment',
                ], 400);
            }

            // Create payment order using PaymentService (from PROMPT 69)
            $orderData = $this->paymentService->createOrder([
                'amount' => $milestone->calculated_amount,
                'currency' => 'INR',
                'receipt' => 'milestone_' . $milestone->id . '_' . now()->timestamp,
                'notes' => [
                    'booking_id' => $booking->id,
                    'quotation_id' => $milestone->quotation_id,
                    'milestone_id' => $milestone->id,
                    'milestone_title' => $milestone->title,
                    'milestone_sequence' => $milestone->sequence_no,
                    'payment_type' => 'milestone',
                ],
            ]);

            // Store order ID in milestone
            $milestone->update([
                'razorpay_order_id' => $orderData['order_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment order created successfully',
                'data' => [
                    'order_id' => $orderData['order_id'],
                    'amount' => $orderData['amount'],
                    'currency' => $orderData['currency'],
                    'key_id' => $orderData['key_id'],
                    'milestone' => [
                        'id' => $milestone->id,
                        'title' => $milestone->title,
                        'sequence_no' => $milestone->sequence_no,
                        'amount' => $milestone->calculated_amount,
                    ],
                    'customer' => [
                        'name' => $milestone->quotation->customer->name,
                        'email' => $milestone->quotation->customer->email,
                        'contact' => $milestone->quotation->customer->phone,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create milestone payment order', [
                'milestone_id' => $milestoneId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Handle payment callback
     * POST /api/v1/milestones/{milestoneId}/payment-callback
     */
    public function paymentCallback(Request $request, int $milestoneId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
                'razorpay_signature' => 'required|string',
            ]);

            $milestone = QuotationMilestone::findOrFail($milestoneId);
            $booking = Booking::where('quotation_id', $milestone->quotation_id)->firstOrFail();

            // Authorization check
            if ($booking->customer_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Verify payment signature using PaymentService
            $verified = $this->paymentService->verifyPaymentSignature(
                $validated['razorpay_order_id'],
                $validated['razorpay_payment_id'],
                $validated['razorpay_signature']
            );

            if (!$verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed',
                ], 400);
            }

            // Capture payment
            $payment = $this->paymentService->capturePayment($validated['razorpay_payment_id']);

            // Create payment transaction record
            $paymentTransaction = \App\Models\PaymentTransaction::create([
                'booking_id' => $booking->id,
                'quotation_milestone_id' => $milestone->id,
                'transaction_type' => 'milestone_payment',
                'gateway' => 'razorpay',
                'gateway_order_id' => $validated['razorpay_order_id'],
                'gateway_payment_id' => $validated['razorpay_payment_id'],
                'amount' => $milestone->calculated_amount,
                'currency' => 'INR',
                'status' => 'completed',
                'payment_method' => $payment['method'] ?? null,
                'metadata' => [
                    'milestone_id' => $milestone->id,
                    'milestone_sequence' => $milestone->sequence_no,
                    'milestone_title' => $milestone->title,
                    'payment_details' => $payment,
                ],
            ]);

            // Process milestone payment (marks paid, updates booking, generates invoice, adds timeline)
            $this->milestoneService->processMilestonePayment($milestone, $paymentTransaction);

            return response()->json([
                'success' => true,
                'message' => 'Milestone payment completed successfully',
                'data' => [
                    'milestone' => [
                        'id' => $milestone->id,
                        'status' => $milestone->fresh()->status,
                        'paid_at' => $milestone->fresh()->paid_at?->format('Y-m-d H:i:s'),
                        'invoice_number' => $milestone->fresh()->invoice_number,
                    ],
                    'booking' => [
                        'id' => $booking->id,
                        'milestone_paid' => $booking->fresh()->milestone_paid,
                        'progress_percentage' => $booking->fresh()->getMilestoneProgressPercentage(),
                        'all_milestones_paid' => $booking->fresh()->allMilestonesPaid(),
                        'status' => $booking->fresh()->status,
                    ],
                    'payment' => [
                        'transaction_id' => $paymentTransaction->id,
                        'amount' => $paymentTransaction->amount,
                        'payment_id' => $validated['razorpay_payment_id'],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process milestone payment callback', [
                'milestone_id' => $milestoneId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get milestone invoices summary for booking
     * GET /api/v1/bookings/{bookingId}/milestone-invoices
     */
    public function getInvoicesSummary(int $bookingId): JsonResponse
    {
        try {
            $booking = Booking::with(['quotation.milestones'])->findOrFail($bookingId);

            // Authorization check
            if ($booking->customer_id !== Auth::id() && !Auth::user()->hasAnyRole(['admin', 'vendor'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            if (!$booking->hasMilestones()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking does not use milestone payments',
                ], 400);
            }

            $invoices = $this->invoiceService->getMilestoneInvoicesSummary($booking);

            return response()->json([
                'success' => true,
                'data' => [
                    'booking_id' => $booking->id,
                    'invoices' => $invoices,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch milestone invoices', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch invoices',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
