<?php

namespace Modules\POS\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GracePeriodService;
use App\Services\Whatsapp\TwilioWhatsappService;
use App\Models\Hoarding;
use App\Mail\PosPaymentReminderMail;
use App\Notifications\PosPaymentReminderInAppNotification;
use Modules\POS\Services\POSBookingService;
use Modules\POS\Models\POSBooking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class POSBookingController extends Controller
{
    protected POSBookingService $posBookingService;
    protected GracePeriodService $gracePeriodService;

    public function __construct(POSBookingService $posBookingService, GracePeriodService $gracePeriodService)
    {
        $this->posBookingService = $posBookingService;
        $this->gracePeriodService = $gracePeriodService;
    }

    private function resolveEffectiveVendorId(Request $request): int
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $isAdminContext = method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['admin', 'superadmin', 'super_admin']);

        if (!$isAdminContext) {
            return (int) $user->id;
        }

        $sessionKey = 'pos.selected_vendor_id';
        $requestedVendorId = $request->input('vendor_id') ?? $request->query('vendor_id');

        if (!empty($requestedVendorId)) {
            $vendor = User::query()
                ->whereKey((int) $requestedVendorId)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'vendor');
                })
                ->first();

            if (!$vendor) {
                abort(422, 'Invalid vendor selected for POS context.');
            }

            if ($request->hasSession()) {
                $request->session()->put($sessionKey, (int) $vendor->id);
            }

            return (int) $vendor->id;
        }

        $sessionVendorId = $request->hasSession() ? $request->session()->get($sessionKey) : null;
        if (!empty($sessionVendorId)) {
            $exists = User::query()
                ->whereKey((int) $sessionVendorId)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'vendor');
                })
                ->exists();

            if ($exists) {
                return (int) $sessionVendorId;
            }
        }

        $fallbackVendorId = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'vendor');
            })
            ->orderBy('id')
            ->value('id');

        if (!$fallbackVendorId) {
            abort(422, 'No vendor available for POS context.');
        }

        if ($request->hasSession()) {
            $request->session()->put($sessionKey, (int) $fallbackVendorId);
        }

        return (int) $fallbackVendorId;
    }

    private function resolveAdminBookingScopeContext(Request $request): array
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $isAdminContext = method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['admin', 'superadmin', 'super_admin']);

        if (!$isAdminContext) {
            return [
                'scope' => 'mine',
                'vendor_id' => (int) $user->id,
                'is_admin' => false,
            ];
        }

        $scope = strtolower((string) ($request->input('booking_scope')
            ?? $request->query('booking_scope')
            ?? ($request->hasSession() ? $request->session()->get('pos.admin_booking_scope', 'vendor') : 'vendor')));

        if (!in_array($scope, ['overall', 'mine', 'vendor'], true)) {
            $scope = 'vendor';
        }

        if ($scope === 'overall') {
            return [
                'scope' => 'overall',
                'vendor_id' => null,
                'is_admin' => true,
            ];
        }

        if ($scope === 'mine') {
            return [
                'scope' => 'mine',
                'vendor_id' => (int) $user->id,
                'is_admin' => true,
            ];
        }

        return [
            'scope' => 'vendor',
            'vendor_id' => $this->resolveEffectiveVendorId($request),
            'is_admin' => true,
        ];
    }

    /**
     * Get vendor's POS bookings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'status' => $request->get('status'),
                'payment_status' => $request->get('payment_status'),
                'booking_type' => $request->get('booking_type'),
                'search' => $request->get('search'),
                'per_page' => $request->get('per_page', 15),
            ];

            $context = $this->resolveAdminBookingScopeContext($request);

            if ($context['scope'] === 'overall') {
                $query = POSBooking::query()->orderBy('created_at', 'desc');

                if (!empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }
                if (!empty($filters['payment_status'])) {
                    $query->where('payment_status', $filters['payment_status']);
                }
                if (!empty($filters['booking_type'])) {
                    $query->where('booking_type', $filters['booking_type']);
                }
                if (!empty($filters['search'])) {
                    $search = trim((string) $filters['search']);
                    $query->where(function ($builder) use ($search) {
                        $builder->where('invoice_number', 'like', "%{$search}%")
                            ->orWhere('customer_name', 'like', "%{$search}%")
                            ->orWhere('customer_phone', 'like', "%{$search}%")
                            ->orWhere('customer_email', 'like', "%{$search}%");
                    });
                }

                $bookings = $query->paginate((int) ($filters['per_page'] ?? 15));
            } else {
                $bookings = $this->posBookingService->getVendorBookings((int) $context['vendor_id'], $filters);
            }

            return response()->json([
                'success' => true,
                'data' => $bookings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bookings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get POS dashboard statistics
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $context = $this->resolveAdminBookingScopeContext($request);

            if ($context['scope'] === 'overall') {
                $statistics = [
                    'total_bookings' => POSBooking::count(),
                    'total_revenue' => (float) POSBooking::where('payment_status', 'paid')->sum('total_amount'),
                    'pending_payments' => (float) POSBooking::whereIn('payment_status', ['unpaid', 'partial'])->sum('total_amount'),
                    'active_credit_notes' => POSBooking::where('credit_note_status', 'active')->count(),
                ];
            } else {
                $statistics = $this->posBookingService->getVendorStatistics((int) $context['vendor_id']);
            }

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single booking details this is also being used in web view, so it has some additional data formatting for invoice URL and hoarding details
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $context = $this->resolveAdminBookingScopeContext($request);
            $query = POSBooking::with(['hoardings', 'bookingHoardings.hoarding', 'customer', 'vendor', 'approver']);

            if ($context['scope'] !== 'overall') {
                $query->where('vendor_id', $context['vendor_id']);
            }

            $booking = $query->findOrFail($id);

            $bookingData = $booking->toArray();
            $bookingData['has_invoice'] = !empty($booking->invoice_path);
            $bookingData['invoice_url'] = null;

            if (!empty($booking->invoice_path) && Route::has('vendor.pos.bookings.invoice')) {
                $bookingData['invoice_url'] = route('vendor.pos.bookings.invoice', ['id' => $booking->id]);
            }

            // Add all booked hoardings and their campaign durations
            $bookingData['hoardings'] = [];
            foreach ($booking->bookingHoardings as $bh) {
                $hoarding = $bh->hoarding;
                $bookingData['hoardings'][] = [
                    'id' => $hoarding->id ?? null,
                    'title' => $hoarding->title ?? null,
                    'location_address' => $hoarding->location_address ?? null,
                    'location_city' => $hoarding->location_city ?? null,
                    'location_state' => $hoarding->location_state ?? null,
                    'size' => $hoarding->size ?? null,
                    'type' => $hoarding->type ?? null,
                    'price_per_month' => $hoarding->price_per_month ?? null,
                    'price_per_sqft' => $hoarding->price_per_sqft ?? null,
                    'status' => $hoarding->status ?? null,
                    'campaign_start_date' => $bh->start_date ? \Carbon\Carbon::parse($bh->start_date)->toDateString() : null,
                    'campaign_end_date' => $bh->end_date ? \Carbon\Carbon::parse($bh->end_date)->toDateString() : null,
                    'campaign_duration_days' => ($bh->start_date && $bh->end_date) ? (\Carbon\Carbon::parse($bh->start_date)->diffInDays(\Carbon\Carbon::parse($bh->end_date)) + 1) : null,
                    'image_url' => $hoarding->heroImage(),
                    'hoarding_price' => $bh->hoarding_price,
                    'hoarding_discount' => $bh->hoarding_discount,
                    'hoarding_tax' => $bh->hoarding_tax,
                    'hoarding_total' => $bh->hoarding_total,
                    'pivot_status' => $bh->status,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $bookingData,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch booking details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single POS booking details for authenticated customer.
     */
    public function showForCustomer(Request $request, int $id): JsonResponse
    {
        try {
            $customer = Auth::user();
            $customerId = (int) ($customer->id ?? 0);
            $customerEmail = strtolower(trim((string) ($customer->email ?? '')));

            $bookingQuery = POSBooking::with(['hoardings', 'bookingHoardings.hoarding', 'customer', 'vendor', 'approver'])
                ->where(function ($query) use ($customerId, $customerEmail) {
                    if ($customerId > 0) {
                        $query->where('customer_id', $customerId);
                    }

                    if ($customerEmail !== '') {
                        $query->orWhereRaw('LOWER(customer_email) = ?', [$customerEmail]);
                    }
                });

            $booking = $bookingQuery->findOrFail($id);

            $bookingData = $booking->toArray();
            $bookingData['has_invoice'] = !empty($booking->invoice_path);
            $bookingData['invoice_url'] = null;

            // Keep invoice hidden for customer POS view unless a dedicated route exists.
            if (!empty($booking->invoice_path) && Route::has('customer.pos.invoice')) {
                $bookingData['invoice_url'] = route('customer.pos.invoice', ['id' => $booking->id]);
            }

            $bookingData['hoardings'] = [];
            foreach ($booking->bookingHoardings as $bh) {
                $hoarding = $bh->hoarding;
                $bookingData['hoardings'][] = [
                    'id' => $hoarding->id ?? null,
                    'title' => $hoarding->title ?? null,
                    'location_address' => $hoarding->location_address ?? null,
                    'location_city' => $hoarding->location_city ?? null,
                    'location_state' => $hoarding->location_state ?? null,
                    'size' => $hoarding->size ?? null,
                    'type' => $hoarding->type ?? null,
                    'price_per_month' => $hoarding->price_per_month ?? null,
                    'price_per_sqft' => $hoarding->price_per_sqft ?? null,
                    'status' => $hoarding->status ?? null,
                    'campaign_start_date' => $bh->start_date ? \Carbon\Carbon::parse($bh->start_date)->toDateString() : null,
                    'campaign_end_date' => $bh->end_date ? \Carbon\Carbon::parse($bh->end_date)->toDateString() : null,
                    'campaign_duration_days' => ($bh->start_date && $bh->end_date) ? (\Carbon\Carbon::parse($bh->start_date)->diffInDays(\Carbon\Carbon::parse($bh->end_date)) + 1) : null,
                    'image_url' => $hoarding->heroImage(),
                    'hoarding_price' => $bh->hoarding_price,
                    'hoarding_discount' => $bh->hoarding_discount,
                    'hoarding_tax' => $bh->hoarding_tax,
                    'hoarding_total' => $bh->hoarding_total,
                    'pivot_status' => $bh->status,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $bookingData,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch booking details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create new POS booking
     */
    public function store(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'nullable|string',
            'customer_gstin' => 'nullable|string|max:15',
            'booking_type' => 'required|in:ooh,dooh',
            'hoarding_id' => 'required_if:booking_type,ooh|exists:hoardings,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'duration_type' => 'nullable|in:days,weeks,months',
            'base_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_mode' => 'required|in:cash,credit_note,online,bank_transfer,cheque',
            'payment_reference' => 'nullable|string|max:255',
            'payment_notes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Add grace period validation if hoarding_id is present
        if ($request->hoarding_id) {
            $hoarding = Hoarding::findOrFail($request->hoarding_id);
            $this->gracePeriodService->addValidationRule($validator, 'start_date', $hoarding);
        }

        $validated = $validator->validate();

        try {
            $booking = $this->posBookingService->createBooking($validated);

            return response()->json([
                'success' => true,
                'message' => 'POS booking created successfully',
                'data' => $booking->load(['hoarding', 'customer']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update POS booking
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'sometimes|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'sometimes|string|max:20',
            'customer_address' => 'nullable|string',
            'customer_gstin' => 'nullable|string|max:15',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'base_amount' => 'sometimes|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_reference' => 'nullable|string|max:255',
            'payment_notes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $booking = POSBooking::forVendor(Auth::id())->findOrFail($id);

            if ($booking->status === POSBooking::STATUS_CANCELLED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update cancelled booking',
                ], 400);
            }

            $updatedBooking = $this->posBookingService->updateBooking($booking, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully',
                'data' => $updatedBooking->load(['hoarding', 'customer']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark payment as cash collected
     */
    public function markCashCollected(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'reference' => 'nullable|string|max:255',
        ]);

        try {
            $booking = POSBooking::forVendor(Auth::id())->findOrFail($id);

            $updatedBooking = $this->posBookingService->markAsCashCollected(
                $booking,
                $validated['amount'],
                $validated['reference'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment marked as cash collected',
                'data' => $updatedBooking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Convert to credit note
     */
    public function convertToCreditNote(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'validity_days' => 'nullable|integer|min:1|max:365',
        ]);

        try {
            $booking = POSBooking::forVendor(Auth::id())->findOrFail($id);

            $updatedBooking = $this->posBookingService->markAsCreditNote(
                $booking,
                $validated['validity_days'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking converted to credit note',
                'data' => $updatedBooking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to convert to credit note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel credit note
     */
    public function cancelCreditNote(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $booking = POSBooking::forVendor(Auth::id())->findOrFail($id);

            $updatedBooking = $this->posBookingService->cancelCreditNote(
                $booking,
                $validated['reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'Credit note cancelled successfully',
                'data' => $updatedBooking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel credit note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel booking
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $booking = POSBooking::forVendor(Auth::id())->findOrFail($id);

            if ($booking->isCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking is already cancelled',
                ], 400);
            }

            $updatedBooking = $this->posBookingService->cancelBooking(
                $booking,
                $validated['reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'data' => $updatedBooking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available hoardings for POS booking
     */
    public function searchHoardings(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $query = Hoarding::query()
                ->where('vendor_id', Auth::id())
                ->where('status', 'approved');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('location_address', 'like', "%{$search}%")
                        ->orWhere('location_city', 'like', "%{$search}%");
                });
            }

            // Check availability if dates provided
            if ($startDate && $endDate) {
                $query->whereDoesntHave('bookings', function ($q) use ($startDate, $endDate) {
                    $q->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function ($q) use ($startDate, $endDate) {
                                $q->where('start_date', '<=', $startDate)
                                    ->where('end_date', '>=', $endDate);
                            });
                    })
                        ->whereIn('status', ['confirmed', 'payment_hold']);
                });
            }

            $hoardings = $query->select([
                'id',
                'title',
                'location_address',
                'location_city',
                'location_state',
                'size',
                'type',
                'price_per_month',
                'price_per_sqft'
            ])->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $hoardings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search hoardings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate pricing for booking
     */
    public function calculatePrice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'base_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $gstRate = $this->posBookingService->getGSTRate();
            $baseAmount = $validated['base_amount'];
            $discountAmount = $validated['discount_amount'] ?? 0;

            $amountAfterDiscount = $baseAmount - $discountAmount;
            $taxAmount = ($amountAfterDiscount * $gstRate) / 100;
            $totalAmount = $amountAfterDiscount + $taxAmount;

            return response()->json([
                'success' => true,
                'data' => [
                    'base_amount' => round($baseAmount, 2),
                    'discount_amount' => round($discountAmount, 2),
                    'amount_after_discount' => round($amountAfterDiscount, 2),
                    'gst_rate' => $gstRate,
                    'tax_amount' => round($taxAmount, 2),
                    'total_amount' => round($totalAmount, 2),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate price',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function markAsPaid(Request $request, int $id): JsonResponse
    {

        try {
            $context = $this->resolveAdminBookingScopeContext($request);
            $bookingQuery = POSBooking::query();
            if ($context['scope'] !== 'overall') {
                $bookingQuery->where('vendor_id', $context['vendor_id']);
            }
            $booking = $bookingQuery->findOrFail($id);

            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'payment_date' => 'nullable|date|before_or_equal:today',
                'notes' => 'nullable|string|max:500',
            ]);

            // Validate current state
            if (!in_array($booking->payment_status, ['unpaid', 'partial'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking is not in a payable state (status: ' . $booking->payment_status . ')',
                ], 422);
            }

            if ($booking->status === POSBooking::STATUS_CANCELLED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot mark payment for cancelled booking',
                ], 422);
            }

            $remainingPayableAmount = max(0, (float) $booking->total_amount - (float) ($booking->paid_amount ?? 0));
            if ((float) $validated['amount'] > $remainingPayableAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Entered amount cannot be greater than payable amount',
                    'errors' => [
                        'amount' => [
                            'Amount cannot be greater than remaining payable amount of ₹' . number_format($remainingPayableAmount, 2),
                        ],
                    ],
                    'payable_amount' => $remainingPayableAmount,
                ], 422);
            }

            // Store previous payment status for WhatsApp message
            $previousStatus = $booking->payment_status;
            $totalAmount = (float) $booking->total_amount;
            $previousAmount = (float) ($booking->paid_amount ?? 0);
            $newAmount = $previousAmount + (float) $validated['amount'];
            $isFullPayment = $newAmount >= $totalAmount;

            // Mark as paid
            $updated = $this->posBookingService->markPaymentReceived(
                $booking,
                $validated['amount'],
                $validated['payment_date'] ?? today(),
                $validated['notes'] ?? null
            );

            // Send WhatsApp notification for payment received
            try {
                $this->sendPaymentConfirmationWhatsApp(
                    $updated,
                    $validated['amount'],
                    $isFullPayment,
                    $newAmount,
                    $totalAmount
                );
            } catch (\Exception $e) {
                Log::warning('Failed to send payment WhatsApp notification', [
                    'booking_id' => $updated->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment marked as received successfully',
                'data' => $updated,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to mark payment', [
                'booking_id' => $id,
                'vendor_id' => $booking->vendor_id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * CRITICAL: Release booking hold (cancel pending payment, free hoarding)
     * Useful for: Order cancellations, customer rejections
     * Transitions: unpaid → released, allows rebooking
     */
    public function releaseBooking(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $context = $this->resolveAdminBookingScopeContext($request);
            $bookingQuery = POSBooking::query();
            if ($context['scope'] !== 'overall') {
                $bookingQuery->where('vendor_id', $context['vendor_id']);
            }
            $booking = $bookingQuery->findOrFail($id);

            // Can only release if pending payment
            if ($booking->payment_status !== POSBooking::PAYMENT_STATUS_UNPAID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only release unpaid bookings (current status: ' . $booking->payment_status . ')',
                ], 422);
            }

            if (!in_array($booking->status, ['draft', 'confirmed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking already started, cannot release',
                ], 422);
            }

            $released = $this->posBookingService->releaseBooking(
                $booking,
                $validated['reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking released successfully. Hoarding is now available.',
                'data' => $released,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to release booking', [
                'booking_id' => $id,
                'vendor_id' => $booking->vendor_id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to release booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all bookings with pending payments (hold status)
     * Used for dashboard pending orders section
     */
    public function getPendingPayments(Request $request): JsonResponse
    {
        try {
            $context = $this->resolveAdminBookingScopeContext($request);
            $query = POSBooking::where('payment_status', POSBooking::PAYMENT_STATUS_UNPAID)
                ->where(function ($query) {
                    $query->whereNull('hold_expiry_at')
                        ->orWhere('hold_expiry_at', '>', now());
                })
                ->with(['hoarding:id,title,location_city'])
                ->orderBy('hold_expiry_at', 'asc');

            if ($context['scope'] !== 'overall') {
                $query->where('vendor_id', $context['vendor_id']);
            }

            $bookings = $query->get([
                'id',
                'customer_name',
                'customer_phone',
                'hoarding_id',
                'total_amount',
                'paid_amount',
                'start_date',
                'hold_expiry_at',
                'reminder_count',
                'created_at',
            ]);

            return response()->json([
                'success' => true,
                'data' => $bookings,
                'count' => $bookings->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending payments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send reminder for pending payment
     * Limit to max 3 reminders per booking
     */
    public function sendReminder(Request $request, int $id): JsonResponse
    {

        try {
            $context = $this->resolveAdminBookingScopeContext($request);
            $bookingQuery = POSBooking::query();
            if ($context['scope'] !== 'overall') {
                $bookingQuery->where('vendor_id', $context['vendor_id']);
            }
            $booking = $bookingQuery->findOrFail($id);

            if (!in_array($booking->payment_status, [POSBooking::PAYMENT_STATUS_UNPAID, POSBooking::PAYMENT_STATUS_PARTIAL])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only send reminders for unpaid or partial paid bookings',
                ], 422);
            }

            if ($booking->reminder_count >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum reminders already sent (3 limit)',
                ], 422);
            }

            // Rate limit: at least 12 hours between reminders
            if ($booking->last_reminder_at && now()->diffInHours($booking->last_reminder_at) < 12) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please wait before sending another reminder',
                ], 429);
            }

            $nextReminderCount = ((int) $booking->reminder_count) + 1;
            $reminderSentAt = now();

            $booking->update([
                'reminder_count' => $nextReminderCount,
                'last_reminder_at' => $reminderSentAt,
            ]);

            if (!$booking->relationLoaded('customer')) {
                $booking->load('customer:id,email');
            }

            $bookingCustomerEmail = trim((string) ($booking->customer_email ?? ''));
            $customerProfileEmail = trim((string) ($booking->customer?->email ?? ''));

            $emailRecipient = null;
            if (!empty($bookingCustomerEmail) && filter_var($bookingCustomerEmail, FILTER_VALIDATE_EMAIL)) {
                $emailRecipient = $bookingCustomerEmail;
            } elseif (!empty($customerProfileEmail) && filter_var($customerProfileEmail, FILTER_VALIDATE_EMAIL)) {
                $emailRecipient = $customerProfileEmail;
            }

            $emailSent = false;
            $emailError = null;

            // Send email notification
            try {
                if (!empty($emailRecipient)) {
                    Mail::to($emailRecipient)
                        ->queue(new PosPaymentReminderMail($booking, $booking->customer, $nextReminderCount));

                    Log::info('Payment reminder email queued', [
                        'booking_id' => $booking->id,
                        'email' => $emailRecipient,
                        'reminder_count' => $nextReminderCount,
                    ]);

                    $emailSent = true;
                } else {
                    $emailError = 'No valid customer email found on booking or customer profile';
                    Log::warning('Payment reminder email skipped - no valid recipient email', [
                        'booking_id' => $booking->id,
                        'booking_customer_email' => $booking->customer_email,
                        'customer_id' => $booking->customer_id,
                        'customer_profile_email' => $booking->customer?->email,
                    ]);
                }
            } catch (\Exception $e) {
                $emailError = $e->getMessage();
                Log::warning('Failed to send payment reminder email', [
                    'booking_id' => $booking->id,
                    'email' => $emailRecipient,
                    'error' => $e->getMessage(),
                ]);
            }

            $inAppSent = false;
            $inAppError = null;

            try {
                if (!empty($booking->customer_id) && $booking->customer) {
                    $booking->customer->notify(new PosPaymentReminderInAppNotification($booking, $nextReminderCount));
                    $inAppSent = true;
                } else {
                    $inAppError = 'Customer account not found for in-app notification';
                    Log::warning('Payment reminder in-app skipped - customer not found', [
                        'booking_id' => $booking->id,
                        'customer_id' => $booking->customer_id,
                    ]);
                }
            } catch (\Throwable $e) {
                $inAppError = $e->getMessage();
                Log::warning('Failed to send payment reminder in-app notification', [
                    'booking_id' => $booking->id,
                    'customer_id' => $booking->customer_id,
                    'error' => $e->getMessage(),
                ]);
            }

            $whatsappSent = $this->sendReminderWhatsAppMessage($booking);

            $deliverySuccess = $emailSent || $whatsappSent || $inAppSent;

            if ($emailSent && $whatsappSent && $inAppSent) {
                $message = 'Reminder sent successfully via email, WhatsApp, and in-app notification';
            } elseif ($deliverySuccess) {
                $successfulChannels = [];
                if ($emailSent) {
                    $successfulChannels[] = 'email';
                }
                if ($whatsappSent) {
                    $successfulChannels[] = 'WhatsApp';
                }
                if ($inAppSent) {
                    $successfulChannels[] = 'in-app notification';
                }
                $message = 'Reminder sent via ' . implode(', ', $successfulChannels);
            } else {
                $message = 'Reminder could not be delivered via email, WhatsApp, or in-app notification';
            }

            return response()->json([
                'success' => $deliverySuccess,
                'message' => $message,
                'data' => [
                    'reminder_count' => $nextReminderCount,
                    'last_reminder_at' => $reminderSentAt,
                    'channels' => [
                        'email' => [
                            'sent' => $emailSent,
                            'recipient' => $emailRecipient,
                            'error' => $emailError,
                        ],
                        'whatsapp' => [
                            'sent' => $whatsappSent,
                        ],
                        'in_app' => [
                            'sent' => $inAppSent,
                            'error' => $inAppError,
                        ],
                    ],
                ],
            ], $deliverySuccess ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminder',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send WhatsApp reminder message for pending payment
     */
    private function sendReminderWhatsAppMessage(POSBooking $booking): bool
    {
        try {
            // Ensure relationships are loaded
            if (!$booking->relationLoaded('bookingHoardings')) {
                $booking->load('bookingHoardings.hoarding');
            }

            // Get customer phone
            $phone = $booking->customer_phone;
            if (empty($phone) || $phone === 'N/A') {
                Log::warning('Reminder WhatsApp skipped - no valid phone', [
                    'booking_id' => $booking->id,
                    'phone' => $phone,
                ]);
                return false;
            }

            // Build hoarding list
            $hoardingLines = $booking->bookingHoardings->map(function ($bh) {
                $h = $bh->hoarding;
                return "• " . ($h->title ?? 'Hoarding') . " ({$bh->start_date} → {$bh->end_date})";
            })->implode("\n");

            // Calculate payment details
            $totalAmount = (float) $booking->total_amount;
            $paidAmount = (float) ($booking->paid_amount ?? 0);
            $remainingAmount = max(0, $totalAmount - $paidAmount);
            $paidFormatted = number_format($paidAmount, 2);
            $totalFormatted = number_format($totalAmount, 2);
            $remainingFormatted = number_format($remainingAmount, 2);

            // Build WhatsApp message
            $message = "⏰ *Payment Reminder - Invoice #{$booking->invoice_number}*\n\n"
                . "Hello *{$booking->customer_name}*,\n\n"
                . "This is a friendly payment reminder for your POS booking.\n\n"
                . "📋 *Booking Details:*\n"
                . "Status: {$booking->status}\n"
                . "Reminder Count: {$booking->reminder_count}/3\n\n"
                . "💰 *Payment Status:*\n"
                . "Total Amount: ₹{$totalFormatted}\n"
                . "Paid Amount: ₹{$paidFormatted}\n"
                . "Outstanding Balance: ₹{$remainingFormatted}\n\n"
                . "🏛️ *Hoardings Booked:*\n{$hoardingLines}\n\n"
                . "Please clear the outstanding balance at your earliest convenience.\n\n"
                . "Thank you!";

            // Normalize phone number
            $normalizedPhone = preg_replace('/\D+/', '', $phone);

            if (empty($normalizedPhone) || strlen($normalizedPhone) < 10) {
                Log::warning('Reminder WhatsApp skipped - invalid phone', [
                    'booking_id' => $booking->id,
                    'original_phone' => $phone,
                    'normalized_phone' => $normalizedPhone,
                ]);
                return false;
            }

            // Add country code if needed
            if (!str_starts_with($normalizedPhone, '91')) {
                $normalizedPhone = '91' . ltrim($normalizedPhone, '0');
            }

            $normalizedPhone = '+' . $normalizedPhone;

            Log::info('Reminder WhatsApp attempting to send', [
                'booking_id' => $booking->id,
                'phone' => $normalizedPhone,
                'reminder_count' => $booking->reminder_count,
            ]);

            // Send via WhatsApp
            $whatsapp = app(TwilioWhatsappService::class);
            $sent = $whatsapp->send($normalizedPhone, $message);

            Log::info('Reminder WhatsApp notification dispatched', [
                'booking_id' => $booking->id,
                'phone' => $normalizedPhone,
                'sent' => $sent,
                'reminder_count' => $booking->reminder_count,
                'message_preview' => substr($message, 0, 100),
            ]);

            return (bool) $sent;
        } catch (\Throwable $e) {
            Log::error('Reminder WhatsApp notification failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Send WhatsApp notification when payment is received (full or partial)
     */
    private function sendPaymentConfirmationWhatsApp(
        POSBooking $booking,
        float $amountReceived,
        bool $isFullPayment,
        float $totalPaidAmount,
        float $totalAmount
    ): void {
        try {
            // Ensure relationships are loaded
            if (!$booking->relationLoaded('bookingHoardings')) {
                $booking->load('bookingHoardings.hoarding');
            }

            $vendor = User::find($booking->vendor_id);
            $vendorName = $vendor?->name ?? 'Vendor';

            // Get customer phone
            $phone = $booking->customer_phone;
            if (empty($phone) || $phone === 'N/A') {
                Log::warning('Payment WhatsApp skipped - no valid phone', [
                    'booking_id' => $booking->id,
                    'phone' => $phone,
                ]);
                return;
            }

            // Build hoarding list
            $hoardingLines = $booking->bookingHoardings->map(function ($bh) {
                $h = $bh->hoarding;
                return "• " . ($h->title ?? 'Hoarding') . " ({$bh->start_date} → {$bh->end_date})";
            })->implode("\n");

            // Build WhatsApp message
            $amountReceivedFormatted = number_format($amountReceived, 2);
            $totalPaidFormatted = number_format($totalPaidAmount, 2);
            $totalAmountFormatted = number_format($totalAmount, 2);
            $remainingAmount = max(0, $totalAmount - $totalPaidAmount);
            $remainingFormatted = number_format($remainingAmount, 2);

            if ($isFullPayment) {
                // Full payment message
                $message = "✅ *Payment Received - Booking Confirmed!*\n\n"
                    . "Hello *{$booking->customer_name}*,\n\n"
                    . "Thank you! Your full payment has been received.\n\n"
                    . "📋 *Booking Details:*\n"
                    . "Invoice: #{$booking->invoice_number}\n"
                    . "Booking Status: ✅ *CONFIRMED*\n"
                    . "Payment Status: ✅ *PAID*\n\n"
                    . "💰 *Payment Summary:*\n"
                    . "Amount Received: ₹{$amountReceivedFormatted}\n"
                    . "Total Amount: ₹{$totalAmountFormatted}\n\n"
                    . "🏛️ *Hoardings Booked:*\n{$hoardingLines}\n\n"
                    . "Your booking is now confirmed by *{$vendorName}*. Looking forward to serving you!\n\n"
                    . "Thank you for your business!";
            } else {
                // Partial payment message
                $message = "📝 *Partial Payment Received - Booking Confirmed!*\n\n"
                    . "Hello *{$booking->customer_name}*,\n\n"
                    . "Thank you! We have received your partial payment. Your booking is now confirmed.\n\n"
                    . "📋 *Booking Details:*\n"
                    . "Invoice: #{$booking->invoice_number}\n"
                    . "Booking Status: ✅ *CONFIRMED*\n\n"
                    . "💰 *Payment Summary:*\n"
                    . "Amount Just Received: ₹{$amountReceivedFormatted}\n"
                    . "Total Paid So Far: ₹{$totalPaidFormatted}\n"
                    . "Total Amount: ₹{$totalAmountFormatted}\n"
                    . "Remaining Balance: ₹{$remainingFormatted}\n\n"
                    . "🏛️ *Hoardings Booked:*\n{$hoardingLines}\n\n"
                    . "Your booking is confirmed by *{$vendorName}*. Please clear the remaining balance of ₹{$remainingFormatted} soon.\n\n"
                    . "Thank you for your business!";
            }

            // Normalize phone number
            $normalizedPhone = preg_replace('/\D+/', '', $phone);

            if (empty($normalizedPhone) || strlen($normalizedPhone) < 10) {
                Log::warning('Payment WhatsApp skipped - invalid phone', [
                    'booking_id' => $booking->id,
                    'original_phone' => $phone,
                    'normalized_phone' => $normalizedPhone,
                ]);
                return;
            }

            // Add country code if needed
            if (!str_starts_with($normalizedPhone, '91')) {
                $normalizedPhone = '91' . ltrim($normalizedPhone, '0');
            }

            $normalizedPhone = '+' . $normalizedPhone;

            Log::info('Payment WhatsApp attempting to send', [
                'booking_id' => $booking->id,
                'phone' => $normalizedPhone,
                'is_full_payment' => $isFullPayment,
                'amount_received' => $amountReceived,
            ]);

            // Send via WhatsApp
            $whatsapp = app(TwilioWhatsappService::class);
            $sent = $whatsapp->send($normalizedPhone, $message);

            Log::info('Payment WhatsApp notification dispatched', [
                'booking_id' => $booking->id,
                'phone' => $normalizedPhone,
                'sent' => $sent,
                'is_full_payment' => $isFullPayment,
                'amount_received' => $amountReceived,
                'message_preview' => substr($message, 0, 100),
            ]);
        } catch (\Throwable $e) {
            Log::error('Payment WhatsApp notification failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
