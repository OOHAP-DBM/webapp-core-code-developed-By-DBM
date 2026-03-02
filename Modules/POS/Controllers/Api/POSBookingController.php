<?php

namespace Modules\POS\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GracePeriodService;
use App\Models\Hoarding;
use Modules\POS\Services\POSBookingService;
use Modules\POS\Models\POSBooking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class POSBookingController extends Controller
{
    protected POSBookingService $posBookingService;
    protected GracePeriodService $gracePeriodService;

    public function __construct(POSBookingService $posBookingService, GracePeriodService $gracePeriodService)
    {
        $this->posBookingService = $posBookingService;
        $this->gracePeriodService = $gracePeriodService;
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

            $bookings = $this->posBookingService->getVendorBookings(Auth::id(), $filters);

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
    public function dashboard(): JsonResponse
    {
        try {
            $statistics = $this->posBookingService->getVendorStatistics(Auth::id());

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
    public function show(int $id): JsonResponse
    {
        try {
            $booking = POSBooking::with(['hoardings', 'bookingHoardings.hoarding', 'customer', 'vendor', 'approver'])
                ->forVendor(Auth::id())
                ->findOrFail($id);

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
                'id', 'title', 'location_address', 'location_city', 'location_state',
                'size', 'type', 'price_per_month', 'price_per_sqft'
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
            $booking = POSBooking::where('vendor_id', Auth::id())->findOrFail($id);

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

            $previousPaymentStatus = $booking->payment_status;

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

            // Mark as paid
            $updated = $this->posBookingService->markPaymentReceived(
                $booking,
                $validated['amount'],
                $validated['payment_date'] ?? today(),
                $validated['notes'] ?? null
            );

            if (
                $previousPaymentStatus === POSBooking::PAYMENT_STATUS_UNPAID
                && in_array($updated->payment_status, [POSBooking::PAYMENT_STATUS_PARTIAL, POSBooking::PAYMENT_STATUS_PAID], true)
            ) {
                $this->sendBookingConfirmationNotifications($updated);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment marked as received successfully',
                'data' => $updated,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to mark payment', [
                'booking_id' => $id,
                'vendor_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function sendBookingConfirmationNotifications(POSBooking $booking): void
    {
        try {
            $notification = new \App\Notifications\PosBookingConfirmedNotification($booking);

            if (!empty($booking->customer_id)) {
                $customer = \App\Models\User::find($booking->customer_id);
                if ($customer && method_exists($customer, 'notify')) {
                    $customer->notify($notification);
                }
            }

            if (!empty($booking->vendor_id)) {
                $vendor = \App\Models\User::find($booking->vendor_id);
                if ($vendor && method_exists($vendor, 'notify')) {
                    $vendor->notify($notification);
                }
            }

            $admins = \App\Models\User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'super_admin']);
            })->get();

            foreach ($admins as $admin) {
                if (method_exists($admin, 'notify')) {
                    $admin->notify($notification);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('POS booking confirmation notification failed after payment', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
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
            $booking = POSBooking::where('vendor_id', Auth::id())->findOrFail($id);

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
                'vendor_id' => Auth::id(),
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
    public function getPendingPayments(): JsonResponse
    {
        try {
            $bookings = POSBooking::where('vendor_id', Auth::id())
                ->where('payment_status', POSBooking::PAYMENT_STATUS_UNPAID)
                ->where(function ($query) {
                    $query->whereNull('hold_expiry_at')
                        ->orWhere('hold_expiry_at', '>', now());
                })
                ->with(['hoarding:id,title,location_city'])
                ->orderBy('hold_expiry_at', 'asc')
                ->get([
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
    public function sendReminder(int $id): JsonResponse
    {
        try {
            $booking = POSBooking::where('vendor_id', Auth::id())->findOrFail($id);

            if ($booking->payment_status !== POSBooking::PAYMENT_STATUS_UNPAID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only send reminders for unpaid bookings',
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

            // Queue reminder
            $booking->update([
                'reminder_count' => $booking->reminder_count + 1,
                'last_reminder_at' => now(),
            ]);

            // TODO: Queue WhatsApp notification job here
            // Notification::send($booking->customer, new PaymentReminderNotification($booking));

            return response()->json([
                'success' => true,
                'message' => 'Reminder sent successfully',
                'data' => [
                    'reminder_count' => $booking->reminder_count,
                    'last_reminder_at' => $booking->last_reminder_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminder',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
