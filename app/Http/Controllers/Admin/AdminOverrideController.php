<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Booking, BookingPayment, CommissionLog, QuoteRequest, AdminOverride};
use App\Models\Offer;
use App\Services\AdminOverrideService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * PROMPT 100: Admin Override Controller
 * 
 * Handles super admin/admin override capabilities for critical system entities
 * with comprehensive audit logging and revert functionality.
 */
class AdminOverrideController extends Controller
{
    protected AdminOverrideService $overrideService;

    public function __construct(AdminOverrideService $overrideService)
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin|admin');
        $this->overrideService = $overrideService;
    }

    /**
     * Display override dashboard with statistics.
     */
    public function index(Request $request)
    {
        $statistics = $this->overrideService->getStatistics([
            'start_date' => $request->input('start_date', now()->subDays(30)),
            'end_date' => $request->input('end_date', now()),
        ]);

        $recentOverrides = AdminOverride::with(['user', 'overridable'])
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('override_type', $request->type);
            })
            ->when($request->filled('severity'), function ($query) use ($request) {
                $query->where('severity', $request->severity);
            })
            ->when($request->filled('reverted'), function ($query) use ($request) {
                if ($request->reverted === 'yes') {
                    $query->where('is_reverted', true);
                } elseif ($request->reverted === 'no') {
                    $query->where('is_reverted', false);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.overrides.index', compact('statistics', 'recentOverrides'));
    }

    /**
     * Show override details.
     */
    public function show(AdminOverride $override)
    {
        $override->load(['user', 'reverter', 'overridable']);
        
        return view('admin.overrides.show', compact('override'));
    }

    /**
     * Override booking.
     */
    public function overrideBooking(Request $request, Booking $booking)
    {
        Gate::authorize('override', $booking);

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
            'notes' => 'nullable|string|max:2000',
            'data' => 'required|array',
            'data.status' => 'nullable|in:pending_payment_hold,payment_hold,confirmed,cancelled,refunded',
            'data.payment_status' => 'nullable|string',
            'data.total_amount' => 'nullable|numeric|min:0',
            'data.start_date' => 'nullable|date',
            'data.end_date' => 'nullable|date|after_or_equal:data.start_date',
            'data.vendor_id' => 'nullable|exists:users,id',
            'data.customer_id' => 'nullable|exists:users,id',
        ]);

        try {
            $override = $this->overrideService->overrideBooking(
                booking: $booking,
                data: $validated['data'],
                admin: Auth::user(),
                reason: $validated['reason']
            );

            if (isset($validated['notes'])) {
                $override->update(['notes' => $validated['notes']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking override completed successfully',
                'override' => $override,
                'booking' => $booking->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Override failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Override payment.
     */
    public function overridePayment(Request $request, BookingPayment $payment)
    {
        Gate::authorize('override', $payment);

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
            'notes' => 'nullable|string|max:2000',
            'data' => 'required|array',
            'data.vendor_payout_status' => 'nullable|in:pending,processing,completed,failed,on_hold,pending_manual_payout',
            'data.vendor_payout_amount' => 'nullable|numeric|min:0',
            'data.admin_commission_amount' => 'nullable|numeric|min:0',
            'data.pg_fee_amount' => 'nullable|numeric|min:0',
            'data.gross_amount' => 'nullable|numeric|min:0',
            'data.payout_mode' => 'nullable|in:bank_transfer,razorpay_transfer,upi,cheque,manual',
            'data.payout_reference' => 'nullable|string|max:255',
        ]);

        try {
            $override = $this->overrideService->overridePayment(
                payment: $payment,
                data: $validated['data'],
                admin: Auth::user(),
                reason: $validated['reason']
            );

            if (isset($validated['notes'])) {
                $override->update(['notes' => $validated['notes']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment override completed successfully',
                'override' => $override,
                'payment' => $payment->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Override failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Override offer.
     */
    public function overrideOffer(Request $request, Offer $offer)
    {
        Gate::authorize('override', $offer);

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
            'notes' => 'nullable|string|max:2000',
            'data' => 'required|array',
            'data.status' => 'nullable|string',
            'data.total_amount' => 'nullable|numeric|min:0',
            'data.discount_amount' => 'nullable|numeric|min:0',
            'data.expiry_date' => 'nullable|date',
        ]);

        try {
            $override = $this->overrideService->overrideOffer(
                offer: $offer,
                data: $validated['data'],
                admin: Auth::user(),
                reason: $validated['reason']
            );

            if (isset($validated['notes'])) {
                $override->update(['notes' => $validated['notes']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Offer override completed successfully',
                'override' => $override,
                'offer' => $offer->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Override failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Override quote request.
     */
    public function overrideQuote(Request $request, QuoteRequest $quote)
    {
        Gate::authorize('override', $quote);

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
            'notes' => 'nullable|string|max:2000',
            'data' => 'required|array',
            'data.status' => 'nullable|string',
            'data.quoted_amount' => 'nullable|numeric|min:0',
            'data.vendor_id' => 'nullable|exists:users,id',
        ]);

        try {
            $override = $this->overrideService->overrideQuote(
                quote: $quote,
                data: $validated['data'],
                admin: Auth::user(),
                reason: $validated['reason']
            );

            if (isset($validated['notes'])) {
                $override->update(['notes' => $validated['notes']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Quote override completed successfully',
                'override' => $override,
                'quote' => $quote->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Override failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Override commission.
     */
    public function overrideCommission(Request $request, CommissionLog $commission)
    {
        Gate::authorize('override', $commission);

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
            'notes' => 'nullable|string|max:2000',
            'data' => 'required|array',
            'data.admin_commission' => 'nullable|numeric|min:0',
            'data.vendor_payout' => 'nullable|numeric|min:0',
            'data.commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $override = $this->overrideService->overrideCommission(
                commission: $commission,
                data: $validated['data'],
                admin: Auth::user(),
                reason: $validated['reason']
            );

            if (isset($validated['notes'])) {
                $override->update(['notes' => $validated['notes']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Commission override completed successfully (Warning: Commission logs are normally immutable)',
                'override' => $override,
                'commission' => $commission->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Override failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Revert an override.
     */
    public function revert(Request $request, AdminOverride $override)
    {
        // Only super admin can revert overrides
        if (!Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only super admin can revert overrides',
            ], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ]);

        try {
            $this->overrideService->revertOverride(
                override: $override,
                admin: Auth::user(),
                reason: $validated['reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'Override reverted successfully',
                'override' => $override->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Revert failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get override history for a specific model.
     */
    public function history(Request $request)
    {
        $validated = $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        try {
            $modelClass = $this->getModelClass($validated['model_type']);
            $model = $modelClass::findOrFail($validated['model_id']);
            
            $history = $this->overrideService->getOverrideHistory($model);

            return response()->json([
                'success' => true,
                'history' => $history,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve history: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get model class from type string.
     */
    protected function getModelClass(string $type): string
    {
        return match ($type) {
            'booking' => Booking::class,
            'payment' => BookingPayment::class,
            'offer' => Offer::class,
            'quote' => QuoteRequest::class,
            'commission' => CommissionLog::class,
            default => throw new \InvalidArgumentException('Invalid model type'),
        };
    }
}
