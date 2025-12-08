<?php

namespace Modules\Payment\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Payment\Models\BookingPayment;
use Modules\Payment\Services\CommissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class FinanceController extends Controller
{
    protected CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Display bookings payments ledger
     * GET /admin/finance/bookings-payments
     */
    public function bookingsPaymentsLedger(Request $request): View
    {
        $query = BookingPayment::with(['booking.vendor', 'booking.customer', 'commissionLog'])
            ->orderBy('created_at', 'desc');

        // Filter by payout status
        if ($request->filled('payout_status')) {
            $query->where('vendor_payout_status', $request->payout_status);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $bookingPayments = $query->paginate(50);

        // Calculate statistics
        $stats = [
            'total_gross' => BookingPayment::sum('gross_amount'),
            'total_commission' => BookingPayment::sum('admin_commission_amount'),
            'pending_payout' => BookingPayment::where('vendor_payout_status', 'pending')->sum('vendor_payout_amount'),
            'completed_payout' => BookingPayment::where('vendor_payout_status', 'completed')->sum('vendor_payout_amount'),
        ];

        return view('admin.finance.bookings_payments', compact('bookingPayments', 'stats'));
    }

    /**
     * Get payment details (API)
     * GET /api/v1/admin/booking-payments/{id}
     */
    public function getPaymentDetails(int $id): JsonResponse
    {
        try {
            $payment = BookingPayment::with(['booking', 'commissionLog'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $payment->append(['commission_percentage', 'payment_summary']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }
    }

    /**
     * Mark payout as paid (API)
     * POST /api/v1/admin/booking-payments/{id}/mark-paid
     */
    public function markPayoutPaid(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payout_mode' => 'required|string|max:50',
            'payout_reference' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payment = BookingPayment::findOrFail($id);

            if ($payment->vendor_payout_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending payouts can be marked as paid',
                ], 400);
            }

            $payment->markPayoutCompleted(
                $validator->validated()['payout_mode'],
                $validator->validated()['payout_reference'],
                ['notes' => $validator->validated()['notes'] ?? null]
            );

            Log::info('Payout marked as paid', [
                'payment_id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'payout_mode' => $validator->validated()['payout_mode'],
                'admin_user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payout marked as paid successfully',
                'data' => $payment->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark payout as paid', [
                'payment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark payout as paid',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hold payout (API)
     * POST /api/v1/admin/booking-payments/{id}/hold
     */
    public function holdPayout(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payment = BookingPayment::findOrFail($id);

            if ($payment->vendor_payout_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending payouts can be put on hold',
                ], 400);
            }

            $payment->markPayoutOnHold($validator->validated()['reason']);

            Log::info('Payout put on hold', [
                'payment_id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'reason' => $validator->validated()['reason'],
                'admin_user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payout put on hold successfully',
                'data' => $payment->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to hold payout', [
                'payment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to hold payout',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get commission statistics (API)
     * GET /api/v1/admin/commission-stats
     */
    public function getCommissionStats(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        try {
            $stats = $this->commissionService->getCommissionStats($startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => $stats,
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch commission statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending payouts list (API)
     * GET /api/v1/admin/pending-payouts
     */
    public function getPendingPayouts(): JsonResponse
    {
        try {
            $pendingPayouts = $this->commissionService->getPendingPayouts();

            return response()->json([
                'success' => true,
                'data' => $pendingPayouts,
                'total_pending_amount' => $pendingPayouts->sum('vendor_payout_amount'),
                'count' => $pendingPayouts->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending payouts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get vendor payout summary (API)
     * GET /api/v1/admin/vendors/{vendorId}/payout-summary
     */
    public function getVendorPayoutSummary(int $vendorId): JsonResponse
    {
        try {
            $summary = $this->commissionService->getVendorPayoutSummary($vendorId);

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch vendor payout summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display pending manual payouts
     * GET /admin/finance/pending-manual-payouts
     */
    public function pendingManualPayouts(Request $request): View
    {
        $query = BookingPayment::with(['booking.vendor.vendorKYC', 'booking.customer', 'commissionLog'])
            ->where('vendor_payout_status', 'pending_manual_payout')
            ->orderBy('created_at', 'desc');

        // Search by vendor or booking
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('booking', function($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function($vendorQ) use ($search) {
                      $vendorQ->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $manualPayouts = $query->paginate(30)->withQueryString();

        // Statistics
        $stats = [
            'total_pending' => BookingPayment::where('vendor_payout_status', 'pending_manual_payout')->count(),
            'total_amount' => BookingPayment::where('vendor_payout_status', 'pending_manual_payout')
                ->sum('vendor_payout_amount'),
            'auto_paid_count' => BookingPayment::where('vendor_payout_status', 'auto_paid')->count(),
            'auto_paid_amount' => BookingPayment::where('vendor_payout_status', 'auto_paid')
                ->sum('vendor_payout_amount'),
        ];

        return view('admin.finance.pending_manual_payouts', compact('manualPayouts', 'stats'));
    }

    /**
     * Process manual payout (API)
     * POST /api/v1/admin/booking-payments/{id}/process-manual-payout
     */
    public function processManualPayout(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payout_mode' => 'required|in:bank_transfer,razorpay_transfer,upi,cheque,manual',
            'payout_reference' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $bookingPayment = BookingPayment::findOrFail($id);

            if ($bookingPayment->vendor_payout_status !== 'pending_manual_payout') {
                return response()->json([
                    'success' => false,
                    'message' => 'This payout is not pending manual processing',
                ], 400);
            }

            DB::beginTransaction();

            $bookingPayment->update([
                'vendor_payout_status' => 'completed',
                'payout_mode' => $request->payout_mode,
                'payout_reference' => $request->payout_reference,
                'paid_at' => now(),
                'metadata' => array_merge($bookingPayment->metadata ?? [], [
                    'manual_payout_processed_by' => auth()->user()->name,
                    'manual_payout_processed_at' => now()->toIso8601String(),
                    'manual_payout_notes' => $request->notes,
                ]),
            ]);

            DB::commit();

            Log::info('Manual payout processed', [
                'booking_payment_id' => $id,
                'payout_mode' => $request->payout_mode,
                'payout_reference' => $request->payout_reference,
                'processed_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Manual payout processed successfully',
                'data' => $bookingPayment,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process manual payout', [
                'booking_payment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process manual payout',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}


