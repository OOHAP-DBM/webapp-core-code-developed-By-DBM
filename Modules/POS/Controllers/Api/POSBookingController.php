<?php

namespace Modules\POS\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GracePeriodService;
use App\Services\Whatsapp\TwilioWhatsappService;
use App\Models\Hoarding;
use App\Models\User;
use Carbon\Carbon;
use Modules\POS\Services\POSBookingService;
use Modules\POS\Services\POSReminderService;
use Modules\POS\Models\POSBooking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Modules\Hoardings\Services\HoardingAvailabilityService;
use App\Notifications\PosBookingHoldExpiredNotification;
use Illuminate\Support\Facades\Notification;


/**
 * @OA\Tag(
 *     name="POS",
 *     description="POS booking and payment management APIs"
 * )
 */
class POSBookingController extends Controller
{
    protected POSBookingService $posBookingService;
    protected GracePeriodService $gracePeriodService;
    protected POSReminderService $posReminderService;
    protected HoardingAvailabilityService $availabilityService;

    public function __construct(POSBookingService $posBookingService, GracePeriodService $gracePeriodService, POSReminderService $posReminderService,HoardingAvailabilityService $availabilityService)
    {
        $this->posBookingService = $posBookingService;
        $this->gracePeriodService = $gracePeriodService;
        $this->posReminderService = $posReminderService;
         $this->availabilityService = $availabilityService;
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
     * @OA\Get(
     *     path="/pos/vendor/bookings",
     *     operationId="posListBookings",
     *     tags={"POS Bookings"},
     *     summary="List POS bookings",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="payment_status", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="booking_type", in="query", @OA\Schema(type="string", example="ooh")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="booking_scope", in="query", @OA\Schema(type="string", enum={"overall","mine","vendor"})),
     *     @OA\Parameter(name="vendor_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Bookings fetched"),
     *     @OA\Response(response=500, description="Failed to fetch bookings")
     * )
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
                            ->orWhereHas('customer', function ($q) use ($search) {
                                $q->where('phone', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                            });
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

      /* =========================================================
     *  DASHBOARD STATISTICS
     * ========================================================= */
 
    /**
     * @OA\Get(
     *     path="/pos/vendor/dashboard",
     *     operationId="posDashboard",
     *     tags={"POS Bookings"},
     *     summary="POS dashboard statistics",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="booking_scope", in="query", @OA\Schema(type="string", enum={"overall","mine","vendor"})),
     *     @OA\Parameter(name="vendor_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Dashboard fetched"),
     *     @OA\Response(response=500, description="Failed to fetch dashboard")
     * )
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $context   = $this->resolveAdminBookingScopeContext($request);
            $baseQuery = POSBooking::query();
 
            if ($context['scope'] !== 'overall') {
                $baseQuery->where('vendor_id', $context['vendor_id']);
            }
 
            $totalBookings   = (clone $baseQuery)->count();
            $totalRevenue    = (clone $baseQuery)->where('payment_status', 'paid')->sum('total_amount') ?? 0;
            $pendingPayments = (clone $baseQuery)
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount') ?? 0;
            $activeCreditNotes = (clone $baseQuery)->where('credit_note_status', 'active')->count();
 
            // Registered customers (unique by ID)
            $regCount = (clone $baseQuery)
                ->whereNotNull('customer_id')
                ->distinct('customer_id')
                ->count('customer_id');
 
            // Guest walk-ins (unique by phone, excluding 'N/A')
            $guestCount = (clone $baseQuery)
                ->whereNull('customer_id')
                ->whereNotNull('customer_phone')
                ->where('customer_phone', '!=', 'N/A')
                ->distinct('customer_phone')
                ->count('customer_phone');
 
            // Anonymous (N/A / null phone — each row is a unique person)
            $naCount = (clone $baseQuery)
                ->whereNull('customer_id')
                ->where(function ($q) {
                    $q->where('customer_phone', 'N/A')->orWhereNull('customer_phone');
                })
                ->count();
 
            return response()->json([
                'success' => true,
                'data'    => [
                    'total_bookings'       => $totalBookings,
                    'total_revenue'        => (float) $totalRevenue,
                    'pending_payments'     => (float) $pendingPayments,
                    'active_credit_notes'  => $activeCreditNotes,
                    'total_customers'      => $regCount + $guestCount + $naCount,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/pos/vendor/bookings/{id}",
     *     operationId="posShowBooking",
     *     tags={"POS Bookings"},
     *     summary="Get booking details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="booking_scope", in="query", @OA\Schema(type="string", enum={"overall","mine","vendor"})),
     *     @OA\Parameter(name="vendor_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Booking fetched"),
     *     @OA\Response(response=404, description="Booking not found"),
     *     @OA\Response(response=500, description="Failed to fetch booking")
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        \Log::info("Fetching POS booking details", [
            'booking_id' => $id,
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
        ]);
        try {
            $context = $this->resolveAdminBookingScopeContext($request);
            $query = POSBooking::with(['hoardings', 'bookingHoardings.hoarding', 'customer', 'vendor', 'approver', 'scheduledReminders', 'milestones']);

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

            // --- PATCH: Prefer customer phone/email/address from User model if available ---
            $customer = $booking->customer;
            if ($customer) {
                if (!empty($customer->email)) {
                    $bookingData['customer_email'] = $customer->email;
                }
                if (!empty($customer->phone)) {
                    $bookingData['customer_phone'] = $customer->phone;
                }
                if (!empty($customer->address)) {
                    $bookingData['customer_address'] = $customer->address;
                }
            }
            // --- END PATCH ---

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

            $bookingData['scheduled_reminders'] = $this->posReminderService->serializeScheduledReminders($booking);
            $bookingData['remaining_reminder_slots'] = $this->posReminderService->getRemainingReminderSlots($booking);

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

            $bookingQuery = POSBooking::with(['hoardings', 'bookingHoardings.hoarding', 'customer', 'vendor', 'approver', 'milestones'])
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

            // --- PATCH: Prefer customer phone/email/address from User model if available ---
            $customer = $booking->customer;
            if ($customer) {
                if (!empty($customer->email)) {
                    $bookingData['customer_email'] = $customer->email;
                }
                if (!empty($customer->phone)) {
                    $bookingData['customer_phone'] = $customer->phone;
                }
                if (!empty($customer->address)) {
                    $bookingData['customer_address'] = $customer->address;
                }
            }
            // --- END PATCH ---

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

     /* =========================================================
     *  CREATE BOOKING
     * ========================================================= */
 
    /**
     * @OA\Post(
     *     path="/pos/vendor/bookings",
     *     operationId="posCreateBooking",
     *     tags={"POS Bookings"},
     *     summary="Create a new POS booking",
     *     description="Creates a POS booking for one or more hoardings. Supports per-hoarding date ranges, milestone-based payment schedules, and multiple payment modes including credit note. Automatically checks hoarding availability, calculates GST, sets a payment hold timer, and dispatches WhatsApp notifications.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Booking creation payload. Either `hoarding_ids` (comma-separated string or array) or `hoarding_items` (array with per-hoarding detail) must be provided.",
     *         @OA\JsonContent(
     *             required={"customer_name", "customer_phone", "start_date", "end_date", "base_amount", "payment_mode"},
     *
     *             @OA\Property(
     *                 property="hoarding_ids",
     *                 description="IDs of hoardings to book. Can be a comma-separated string ('1,2,3') or a JSON array ([1,2,3]). Required unless hoarding_items is supplied.",
     *                 oneOf={
     *                     @OA\Schema(type="string", example="12,15,22"),
     *                     @OA\Schema(type="array", @OA\Items(type="integer"), example={12,15,22})
     *                 }
     *             ),
     *
     *             @OA\Property(
     *                 property="hoarding_items",
     *                 type="array",
     *                 description="Per-hoarding detail array. When supplied, each hoarding can have its own date range and monthly price. Values here override the global start_date / end_date for that hoarding.",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"hoarding_id", "start_date", "end_date"},
     *                     @OA\Property(property="hoarding_id",         type="integer", example=12,          description="ID of the hoarding (must be in hoarding_ids)."),
     *                     @OA\Property(property="start_date",          type="string",  format="date", example="2025-06-01", description="Campaign start date for this hoarding."),
     *                     @OA\Property(property="end_date",            type="string",  format="date", example="2025-08-31", description="Campaign end date for this hoarding (must be >= start_date)."),
     *                     @OA\Property(property="price_per_month",     type="number",  format="float", example=15000.00, description="Override monthly price for this hoarding. Falls back to the hoarding's stored monthly_price if omitted."),
     *                     @OA\Property(property="type",                type="string",  example="ooh",   description="Hoarding type hint (ooh / dooh). Informational only."),
     *                     @OA\Property(property="total_slots_per_day", type="integer", example=300,     description="For DOOH only: number of ad slots per day on this screen.")
     *                 )
     *             ),
     *
     *             @OA\Property(property="customer_id",      type="integer", nullable=true, example=42,                    description="ID of an existing registered user (customer role). When provided, customer_name / phone / email are pulled from the user record if not supplied."),
     *             @OA\Property(property="customer_name",    type="string",  maxLength=255, example="Rajesh Kumar",        description="Customer display name. Defaults to 'Walk-in Customer' if omitted."),
     *             @OA\Property(property="customer_phone",   type="string",  maxLength=20,  example="9876543210",          description="Customer mobile number (10-digit Indian number without country code). Defaults to 'N/A' if omitted. Used for WhatsApp notifications."),
     *             @OA\Property(property="customer_email",   type="string",  format="email", nullable=true, example="rajesh@example.com", description="Customer email address. Used for email notifications."),
     *             @OA\Property(property="customer_address", type="string",  nullable=true, maxLength=500, example="123, MG Road, Bangalore", description="Customer billing / correspondence address."),
     *             @OA\Property(property="customer_gstin",   type="string",  nullable=true, maxLength=15,  example="29ABCDE1234F1Z5",         description="Customer GST Identification Number (GSTIN). Printed on the invoice."),
     *
     *             @OA\Property(property="booking_type", type="string", enum={"ooh","dooh"}, example="ooh", description="Booking category. 'ooh' = Out-of-Home (static billboards, hoardings). 'dooh' = Digital Out-of-Home (LED / digital screens). Defaults to 'ooh'."),
     *
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-06-01", description="Global campaign start date (YYYY-MM-DD). Used as the fallback start date for hoardings not listed in hoarding_items."),
     *             @OA\Property(property="end_date",   type="string", format="date", example="2025-08-31", description="Global campaign end date (YYYY-MM-DD). Must be >= start_date. Used as the fallback end date for hoardings not listed in hoarding_items."),
     *
     *             @OA\Property(property="base_amount",     type="number", format="float", example=90000.00, description="Total base amount (sum of all hoarding charges, before discount and GST). Must be >= 0."),
     *             @OA\Property(property="discount_amount", type="number", format="float", nullable=true, example=5000.00, description="Flat discount to apply on the base amount. Must be >= 0 and <= base_amount. Distributed proportionally across hoardings. Defaults to 0."),
     *
     *             @OA\Property(
     *                 property="payment_mode",
     *                 type="string",
     *                 enum={"cash", "credit_note", "bank_transfer", "cheque", "online"},
     *                 example="bank_transfer",
     *                 description="Payment method selected for this booking. 'credit_note' auto-confirms the booking and generates a credit note with a due date. All other modes place the booking in 'pending_payment' state until payment is received."
     *             ),
     *             @OA\Property(property="payment_reference", type="string", nullable=true, maxLength=255, example="UTR123456789",       description="Payment transaction reference (UTR, cheque number, etc.). Stored for audit."),
     *             @OA\Property(property="payment_notes",     type="string", nullable=true, maxLength=500, example="Advance payment",    description="Free-text notes about the payment."),
     *             @OA\Property(property="notes",             type="string", nullable=true, maxLength=1000, example="Preferred morning slot", description="General booking notes visible to vendor and admin."),
     *
     *             @OA\Property(property="payment_details_type", type="string", nullable=true, enum={"bank_transfer","online","credit_note"}, description="Hint for which vendor payment detail record to attach to the WhatsApp message (bank / UPI / credit note). Purely informational; does not affect booking logic."),
     *
     *             @OA\Property(
     *                 property="hold_minutes",
     *                 type="integer",
     *                 nullable=true,
     *                 minimum=0,
     *                 example=60,
     *                 description="Number of minutes to hold the hoarding(s) while awaiting payment. After this period the booking is auto-cancelled and the hoarding is released. Set to 0 for no time limit. Defaults to 30. Ignored for credit_note bookings (no hold timer)."
     *             ),
     *
     *             @OA\Property(property="is_milestone", type="boolean", example=true, description="Set to true to enable milestone-based payment schedule. When true, milestone_data is required."),
     *
     *             @OA\Property(
     *                 property="milestone_data",
     *                 type="array",
     *                 description="Required when is_milestone=true. Defines the instalment schedule. All milestone amounts must sum to 100% (for percentage type) or exactly equal total_amount (for fixed type). The first milestone is automatically set to 'due'; subsequent ones start as 'pending'.",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"title", "amount_type", "amount"},
     *                     @OA\Property(property="title",        type="string",  maxLength=100, example="Advance",             description="Label for this milestone, e.g. 'Advance', 'Mid-campaign', 'Final'."),
     *                     @OA\Property(property="amount_type",  type="string",  enum={"percentage","fixed"}, example="percentage", description="Whether amount is a percentage of total_amount or a fixed rupee value."),
     *                     @OA\Property(property="amount",       type="number",  format="float", minimum=0.01, example=30,   description="Amount value. If amount_type='percentage', this is the % (e.g. 30 = 30%). If 'fixed', this is the rupee amount."),
     *                     @OA\Property(property="due_date",     type="string",  format="date", nullable=true, example="2025-06-01", description="Expected payment date for this milestone."),
     *                     @OA\Property(property="vendor_notes", type="string",  nullable=true, maxLength=500, example="Include GST receipt", description="Internal notes from the vendor about this milestone.")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Booking created successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string",  example="POS booking created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id",             type="integer", example=101,                        description="Newly created POS booking ID."),
     *                 @OA\Property(property="invoice_number", type="string",  example="INV-2025-1042",             description="Auto-generated invoice number (null if auto-invoice is disabled in settings)."),
     *                 @OA\Property(property="total_amount",   type="number",  format="float", example=99940.00,   description="Final payable amount after discount and GST."),
     *                 @OA\Property(property="hoarding_count", type="integer", example=2,                          description="Number of hoardings attached to this booking."),
     *                 @OA\Property(property="hold_expiry_at", type="string",  format="date-time", nullable=true, example="2025-05-10T11:30:00.000000Z", description="UTC timestamp when the payment hold expires and the booking is auto-cancelled. Null if hold_minutes=0 or payment_mode=credit_note."),
     *                 @OA\Property(property="hold_minutes",   type="integer", example=60,                         description="Hold duration that was applied.")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed or one/more hoardings are unavailable for the requested dates.",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     description="Laravel validation error",
     *                     @OA\Property(property="success", type="boolean", example=false),
     *                     @OA\Property(property="message", type="string",  example="Validation failed"),
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Field-level validation errors.",
     *                         example={"start_date":{"The start date field is required."},"payment_mode":{"The selected payment mode is invalid."}}
     *                     )
     *                 ),
     *                 @OA\Schema(
     *                     description="Hoarding availability conflict",
     *                     @OA\Property(property="success", type="boolean", example=false),
     *                     @OA\Property(property="message", type="string",  example="One or more selected hoardings are not available for the specified dates"),
     *                     @OA\Property(
     *                         property="unavailable_hoardings",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="hoarding_id",   type="integer", example=12),
     *                             @OA\Property(property="hoarding_name", type="string",  example="MG Road Billboard"),
     *                             @OA\Property(property="reasons",       type="array",   @OA\Items(type="string"), example={"booked","hold"})
     *                         )
     *                     ),
     *                     @OA\Property(property="details", type="string", example="MG Road Billboard: already booked for some dates, on payment hold for some dates.")
     *                 )
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="One or more hoarding IDs do not belong to the authenticated vendor.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string",  example="One or more hoardings not found or do not belong to you")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected server error while creating the booking.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string",  example="Failed to create booking"),
     *             @OA\Property(property="error",   type="string",  example="SQLSTATE[23000]: Integrity constraint violation ...")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $vendorId = $this->resolveEffectiveVendorId($request);

            Log::info('POS API create booking request', [
                'vendor_id'    => $vendorId,
                'payload_keys' => array_keys($request->all()),
            ]);

            $validated = $request->validate([
                'hoarding_ids'                         => 'nullable',
                'hoarding_items'                       => 'nullable|array',
                'hoarding_items.*.hoarding_id'         => 'required_with:hoarding_items|integer',
                'hoarding_items.*.start_date'          => 'required_with:hoarding_items|date',
                'hoarding_items.*.end_date'            => 'required_with:hoarding_items|date|after_or_equal:hoarding_items.*.start_date',
                'hoarding_items.*.price_per_month'     => 'nullable|numeric',
                'hoarding_items.*.type'                => 'nullable|string',
                'hoarding_items.*.total_slots_per_day' => 'nullable|integer',
                'customer_id'                          => 'nullable|exists:users,id',
                'customer_name'                        => 'nullable|string|max:255',
                'customer_phone'                       => 'nullable|string|max:20',
                'customer_email'                       => 'nullable|email|max:255',
                'customer_address'                     => 'nullable|string|max:500',
                'customer_gstin'                       => 'nullable|string|max:15',
                'booking_type'                         => 'nullable|in:ooh,dooh',
                'start_date'                           => 'required|date',
                'end_date'                             => 'required|date|after_or_equal:start_date',
                'base_amount'                          => 'required|numeric|min:0',
                'discount_amount'                      => 'nullable|numeric|min:0',
                'payment_mode'                         => 'required|in:cash,credit_note,bank_transfer,cheque,online',
                'payment_reference'                    => 'nullable|string|max:255',
                'payment_notes'                        => 'nullable|string|max:500',
                'notes'                                => 'nullable|string|max:1000',
                'hold_minutes'                         => 'nullable|integer|min:0',
                'payment_details_type'                 => 'nullable|string|in:bank_transfer,online,credit_note',
                'is_milestone'                         => 'nullable|boolean',
                'milestone_data'                       => 'required_if:is_milestone,true|array|min:1',
                'milestone_data.*.title'               => 'required_if:is_milestone,true|string|max:100',
                'milestone_data.*.amount_type'         => 'required_if:is_milestone,true|in:percentage,fixed',
                'milestone_data.*.amount'              => 'required_if:is_milestone,true|numeric|min:0.01',
                'milestone_data.*.due_date'            => 'nullable|date',
                'milestone_data.*.vendor_notes'        => 'nullable|string|max:500',
                 'po_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240'
            ]);

            // ── Resolve hoarding IDs ─────────────────────────────────────
            $hoardingIds = is_array($request->hoarding_ids)
                ? $request->hoarding_ids
                : explode(',', $request->hoarding_ids ?? '');
            $hoardingIds = array_values(array_filter(array_map('intval', $hoardingIds)));

            if (empty($hoardingIds)) {
                return response()->json(['success' => false, 'message' => 'At least one hoarding must be selected'], 422);
            }

            // ── Per-hoarding metadata map ────────────────────────────────
            $hoardingItemsMap = [];
            foreach ($validated['hoarding_items'] ?? [] as $item) {
                $hoardingItemsMap[(int) $item['hoarding_id']] = $item;
            }

            // ── Verify hoardings belong to vendor ────────────────────────
            $hoardings = Hoarding::whereIn('id', $hoardingIds)
                ->where('vendor_id', $vendorId)
                ->get();

            if ($hoardings->count() !== count($hoardingIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more hoardings not found or do not belong to you',
                ], 403);
            }

            // ── Per-hoarding availability check ──────────────────────────
            $unavailableHoardings = [];
            $skipStatuses         = ['available', 'blocked'];

            foreach ($hoardings as $hoarding) {
                $item      = $hoardingItemsMap[$hoarding->id] ?? null;
                $itemStart = $item ? Carbon::parse($item['start_date']) : Carbon::parse($validated['start_date']);
                $itemEnd   = $item ? Carbon::parse($item['end_date'])   : Carbon::parse($validated['end_date']);

                $availability = $this->availabilityService->checkMultipleDates(
                    $hoarding->id,
                    [$itemStart->format('Y-m-d'), $itemEnd->format('Y-m-d')]
                );

                if (!empty($availability)) {
                    $unavailableReasons = [];
                    foreach ($availability as $dateCheck) {
                        if (
                            !in_array($dateCheck['status'], $skipStatuses) &&
                            !in_array($dateCheck['status'], $unavailableReasons)
                        ) {
                            $unavailableReasons[] = $dateCheck['status'];
                        }
                    }
                    if (!empty($unavailableReasons)) {
                        $unavailableHoardings[] = [
                            'hoarding_id'   => $hoarding->id,
                            'hoarding_name' => $hoarding->address ?? $hoarding->title,
                            'reasons'       => $unavailableReasons,
                        ];
                    }
                }
            }

            if (!empty($unavailableHoardings)) {
                return response()->json([
                    'success'               => false,
                    'message'               => 'One or more selected hoardings are not available for the specified dates',
                    'unavailable_hoardings' => $unavailableHoardings,
                    'details'               => $this->formatUnavailabilityDetails($unavailableHoardings),
                ], 422);
            }

            // ── Build booking data ───────────────────────────────────────
            $isMilestone   = (bool) ($validated['is_milestone'] ?? false);
            $milestoneData = $isMilestone ? ($validated['milestone_data'] ?? []) : [];
            $holdMinutes   = (int) ($validated['hold_minutes'] ?? 30);
            $holdExpiryAt  = $holdMinutes > 0 ? now()->addMinutes($holdMinutes) : null;

            $gstRate             = $this->posBookingService->getGSTRate();
            $baseAmount          = (float) $validated['base_amount'];
            $discountAmount      = (float) ($validated['discount_amount'] ?? 0);
            $amountAfterDiscount = max(0, $baseAmount - $discountAmount);
            $taxAmount           = ($amountAfterDiscount * $gstRate) / 100;
            $totalAmount         = $amountAfterDiscount + $taxAmount;

            $bookingData = [
                'vendor_id'         => $vendorId,
                'hoarding_ids'      => $hoardingIds,
                'hoarding_items'    => $validated['hoarding_items'] ?? [],
                'customer_id'       => $validated['customer_id']      ?? null,
                'customer_name'     => $validated['customer_name']    ?? 'Walk-in Customer',
                'customer_email'    => $validated['customer_email']   ?? null,
                'customer_phone'    => $validated['customer_phone']   ?? 'N/A',
                'customer_address'  => $validated['customer_address'] ?? null,
                'customer_gstin'    => $validated['customer_gstin']   ?? null,
                'booking_type'      => $validated['booking_type']     ?? 'ooh',
                'start_date'        => $validated['start_date'],
                'end_date'          => $validated['end_date'],
                'duration_days'     => Carbon::parse($validated['end_date'])
                    ->diffInDays(Carbon::parse($validated['start_date'])) + 1,
                'base_amount'       => $baseAmount,
                'discount_amount'   => $discountAmount,
                'tax_amount'        => round($taxAmount, 2),
                'total_amount'      => round($totalAmount, 2),
                'payment_mode'      => $validated['payment_mode'],
                'payment_reference' => $validated['payment_reference'] ?? null,
                'payment_notes'     => $validated['payment_notes']     ?? null,
                'notes'             => $validated['notes']             ?? null,
                'status'            => 'draft',
                'po_file_path'      => $request->hasFile('po_file') ? $request->file('po_file')->store('po_files') : null,
                'payment_status'    => 'unpaid',
                'hold_minutes'      => $holdMinutes,
                'hold_expiry_at'    => $holdExpiryAt,
                'is_milestone'      => $isMilestone,
                'milestone_data'    => $milestoneData,
            ];

            $booking = $this->posBookingService->createBooking($bookingData);
            $booking->load('bookingHoardings.hoarding');

            // ── WhatsApp notification ────────────────────────────────────
            try {
                $phone = $booking->customer_phone
                    ?? ($booking->customer_id ? optional(User::find($booking->customer_id))->phone : null);

                if ($phone && $phone !== 'N/A') {
                    $this->sendWhatsAppNotification($booking, $phone);
                }
            } catch (\Exception $e) {
                Log::warning('POS API WhatsApp notification failed', [
                    'booking_id' => $booking->id,
                    'error'      => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'POS booking created successfully',
                'data'    => [
                    'id'             => $booking->id,
                    'invoice_number' => $booking->invoice_number,
                    'total_amount'   => round($totalAmount, 2),
                    'hoarding_count' => count($hoardingIds),
                    'hold_expiry_at' => $holdExpiryAt?->toISOString(),
                    'hold_minutes'   => $holdMinutes,
                ],
            ], 201);
        } catch (ValidationException $e) {
            Log::warning('POS API booking validation failed', ['vendor_id' => Auth::id(), 'errors' => $e->errors()]);
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('POS API error creating booking', ['vendor_id' => Auth::id(), 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to create booking', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * @OA\Put(
     *     path="/pos/vendor/bookings/{id}",
     *     operationId="posUpdateBooking",
     *     tags={"POS Bookings"},
     *     summary="Update POS booking",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="customer_name", type="string"),
     *             @OA\Property(property="customer_email", type="string", format="email", nullable=true),
     *             @OA\Property(property="customer_phone", type="string"),
     *             @OA\Property(property="customer_address", type="string", nullable=true),
     *             @OA\Property(property="customer_gstin", type="string", nullable=true),
     *             @OA\Property(property="start_date", type="string", format="date"),
     *             @OA\Property(property="end_date", type="string", format="date"),
     *             @OA\Property(property="base_amount", type="number", format="float"),
     *             @OA\Property(property="discount_amount", type="number", format="float", nullable=true),
     *             @OA\Property(property="payment_reference", type="string", nullable=true),
     *             @OA\Property(property="payment_notes", type="string", nullable=true),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Booking updated"),
     *     @OA\Response(response=400, description="Invalid state"),
     *     @OA\Response(response=500, description="Failed to update booking")
     * )
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
     * @OA\Post(
     *     path="/pos/vendor/bookings/{id}/mark-cash-collected",
     *     operationId="posMarkCashCollected",
     *     tags={"POS Bookings"},
     *     summary="Mark booking cash collected",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", format="float"),
     *             @OA\Property(property="reference", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment marked"),
     *     @OA\Response(response=500, description="Failed to mark payment")
     * )
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
     * @OA\Post(
     *     path="/pos/vendor/bookings/{id}/convert-to-credit-note",
     *     operationId="posConvertToCreditNote",
     *     tags={"POS Bookings"},
     *     summary="Convert booking to credit note",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="validity_days", type="integer", minimum=1, maximum=365)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Converted to credit note"),
     *     @OA\Response(response=500, description="Failed to convert")
     * )
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
     * @OA\Post(
     *     path="/pos/vendor/bookings/{id}/cancel-credit-note",
     *     operationId="posCancelCreditNote",
     *     tags={"POS Bookings"},
     *      summary="Cancel debit note (credit-note payment)",
     *     description="Cancels debit note for a booking only when booking is not cancelled, payment mode is credit_note, payment status is credit, and debit note is still active.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(required={"reason"}, @OA\Property(property="reason", type="string"))
     *     ),
     *     @OA\Response(response=200, description="Debit note cancelled successfully"),
     *     @OA\Response(response=422, description="Invalid state: booking cancelled, not credit note, not on credit, or debit note already cancelled"),
     *     @OA\Response(response=404, description="Booking not found"),
     *     @OA\Response(response=500, description="Failed to cancel debit note")
     * )
     */
    public function cancelCreditNote(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $booking = POSBooking::forVendor(Auth::id())->findOrFail($id);
            
            if ($booking->isCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel debit note because booking is already cancelled',
                ], 422);
            }

            if (!$booking->isCreditNote()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking is not a credit note',
                ], 422);
            }

            if ($booking->payment_status !== POSBooking::PAYMENT_STATUS_CREDIT) {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only cancel debit note when booking is on credit',
                ], 422);
            }

            if ($booking->credit_note_status === POSBooking::CREDIT_NOTE_STATUS_CANCELLED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debit note is already cancelled',
                ], 422);
            }
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
     * @OA\Post(
     *     path="/pos/vendor/bookings/{id}/cancel",
     *     operationId="posCancelBooking",
     *     tags={"POS Bookings"},
     *     summary="Cancel booking",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(required={"reason"}, @OA\Property(property="reason", type="string"))
     *     ),
     *     @OA\Response(response=200, description="Booking cancelled"),
     *     @OA\Response(response=400, description="Already cancelled"),
     *     @OA\Response(response=500, description="Failed to cancel booking")
     * )
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string|max:500',
            ]);

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
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors();
            return response()->json([
                'success' => false,
                'message' => $errors->first('reason') ?: 'Reason is required to cancel booking.',
                'errors' => $errors,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/pos/vendor/search-hoardings",
     *     operationId="posSearchHoardings",
     *     tags={"POS Bookings"},
     *     summary="Search hoardings for POS booking",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="start_date", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="end_date", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="Hoardings fetched"),
     *     @OA\Response(response=500, description="Failed to search hoardings")
     * )
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
     * @OA\Post(
     *     path="/pos/vendor/calculate-price",
     *     operationId="posCalculatePrice",
     *     tags={"POS Bookings"},
     *     summary="Calculate booking price",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"base_amount"},
     *             @OA\Property(property="base_amount", type="number", format="float", example=100000),
     *             @OA\Property(property="discount_amount", type="number", format="float", nullable=true, example=5000)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Price calculated"),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Failed to calculate price")
     * )
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
                'milestone_ids' => 'nullable|array',
                'milestone_ids.*' => 'integer|distinct',
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
                isset($validated['payment_date']) ? Carbon::parse((string) $validated['payment_date']) : today(),
                $validated['notes'] ?? null,
                $validated['milestone_ids'] ?? []
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

        $booking = null;

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

   /* =========================================================
     *  SEND REMINDER
     * ========================================================= */
 
    /**
     * @OA\Post(
     * path="/pos/vendor/bookings/{id}/send-reminder",
     * operationId="posSendReminder",
     * tags={"POS Bookings"},
     * summary="Send or schedule payment reminders",
     * description="Handles immediate delivery, single scheduling, or bulk replacement of reminders.",
     * security={{"sanctum":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="The ID of the POS Booking",
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=false,
     * @OA\JsonContent(
     * @OA\Property(
     * property="scheduled_at",
     * type="string",
     * format="date-time",
     * example="2026-04-10 14:00:00",
     * description="Schedule a single reminder. If null or past, reminder is sent immediately."
     * ),
     * @OA\Property(
     * property="scheduled_reminders",
     * type="array",
     * description="Bulk schedule/replace reminders (min: 1, max: 3)",
     * @OA\Items(
     * @OA\Property(
     * property="scheduled_at",
     * type="string",
     * format="date-time",
     * example="2026-04-12 09:00:00"
     * )
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Reminder scheduled successfully"),
     * @OA\Property(property="data", type="object", nullable=true)
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error (e.g., more than 3 reminders or invalid dates)",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="The scheduled reminders field is required.")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Booking not found"
     * ),
     * @OA\Response(
     * response=500,
     * description="Server error"
     * )
     * )
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

            if ($request->has('scheduled_reminders')) {
                $validated = $request->validate([
                    'scheduled_reminders' => ['required', 'array', 'min:1', 'max:3'],
                    'scheduled_reminders.*.scheduled_at' => ['required', 'date'],
                ]);

                $result = $this->posReminderService->replacePendingSchedules(
                    $booking,
                    $validated['scheduled_reminders'],
                    Auth::id()
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Reminder scheduled successfully',
                    'data' => $result,
                ]);
            }

            if ($request->filled('scheduled_at')) {
                $scheduledAt = Carbon::parse((string) $request->input('scheduled_at'));

                if ($scheduledAt->greaterThan(now()->addMinute())) {
                    $result = $this->posReminderService->scheduleSingleReminder($booking, $scheduledAt, Auth::id());

                    return response()->json([
                        'success' => true,
                        'message' => 'Reminder scheduled successfully',
                        'data' => $result,
                    ]);
                }
            }

            $result = $this->posReminderService->sendImmediateReminder($booking, null, Auth::id());

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null,
            ], $result['status'] ?? 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminder',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


      /**
     * Send WhatsApp notification on booking creation.
     */
    protected function sendWhatsAppNotification(POSBooking $booking, string $phone): void
    {
        if (!$booking->relationLoaded('bookingHoardings')) {
            $booking->load('bookingHoardings.hoarding');
        }
 
        $vendor      = User::find($booking->vendor_id);
        $vendorName  = $vendor?->name ?? 'Vendor';
        $totalAmount = number_format((float) $booking->total_amount, 2);
        $holdMins    = $booking->hold_minutes ?? 0;
        $holdText    = $holdMins > 0
            ? "⏳ *Payment Due Within:* " . ($holdMins >= 1440
                ? round($holdMins / 1440) . ' day(s)'
                : ($holdMins >= 60 ? round($holdMins / 60) . ' hour(s)' : "{$holdMins} minutes"))
            : "ℹ️ No payment time limit.";
 
        $milestones     = \App\Models\QuotationMilestone::where('pos_booking_id', $booking->id)->orderBy('sequence_no')->get();
        $milestoneBlock = '';
        if ($milestones->isNotEmpty()) {
            $lines = $milestones->values()->map(function ($ms, $idx) {
                $seq     = $ms->sequence_no ?? ($idx + 1);
                $title   = $ms->title ?? ('Milestone ' . $seq);
                $amount  = number_format((float) ($ms->calculated_amount ?? $ms->amount ?? 0), 2);
                $dueDate = $ms->due_date ? Carbon::parse($ms->due_date)->format('d M Y') : 'N/A';
                return "{$seq}. {$title} - ₹{$amount} (Due: {$dueDate})";
            })->implode("\n");
            $milestoneBlock = "\n🧩 *Milestones:*\n{$lines}\n";
        }
 
        $hoardingLines = $booking->bookingHoardings->map(function ($bh) {
            $h    = $bh->hoarding;
            $url  = $h?->id ? config('app.url') . '/h/' . $h->id : null;
            $link = $url ? "🔗 {$h->title}\n   {$url}" : "• " . ($h->title ?? 'Hoarding');
            return $link . " ({$bh->start_date} → {$bh->end_date})";
        })->implode("\n\n");
 
        $paymentBlock  = '';
        $paymentDetail = null;
        if (in_array($booking->payment_mode, ['bank_transfer', 'cheque', 'online', 'upi'], true)) {
            $detailType    = in_array($booking->payment_mode, ['bank_transfer', 'cheque'], true) ? 'bank' : 'upi';
            $paymentDetail = \Modules\POS\Models\VendorPaymentDetail::where('vendor_id', $booking->vendor_id)->where('type', $detailType)->first();
        }
 
        if (in_array($booking->payment_mode, ['bank_transfer', 'cheque'], true) && $paymentDetail) {
            $paymentBlock = "\n🏦 *Bank Transfer Details:*\nBank: {$paymentDetail->bank_name}\nA/C No: {$paymentDetail->account_number}\nHolder: {$paymentDetail->account_holder}\nIFSC: {$paymentDetail->ifsc_code}\nReference: {$booking->invoice_number}";
        } elseif (in_array($booking->payment_mode, ['online', 'upi'], true) && $paymentDetail) {
            $paymentBlock = "\n📱 *UPI Payment:*\nUPI ID: {$paymentDetail->upi_id}";
        } elseif ($booking->payment_mode === 'cash') {
            $paymentBlock = "\n💵 *Payment Mode:* Cash (collect at office)";
        }
 
        $message = "🎯 *POS Booking created!*\n\nHello *{$booking->customer_name}*,\n\nYour booking has been created by *{$vendorName}*.\n\n📋 *Booking Details:*\nInvoice: #{$booking->invoice_number}\nTotal Amount: ₹{$totalAmount}\n\n🏛️ *Hoardings Booked:*\n{$hoardingLines}\n\n{$milestoneBlock}{$holdText}\n{$paymentBlock}\n\nThank you for your business!";
 
        $normalizedPhone = preg_replace('/\D+/', '', $phone);
 
        if (empty($normalizedPhone) || strlen($normalizedPhone) < 10) {
            Log::warning('POS API WhatsApp skipped - invalid phone', ['booking_id' => $booking->id, 'phone' => $phone]);
            return;
        }
 
        if (!str_starts_with($normalizedPhone, '91')) {
            $normalizedPhone = '91' . ltrim($normalizedPhone, '0');
        }
        $normalizedPhone = '+' . $normalizedPhone;
 
        try {
            $sent = app(TwilioWhatsappService::class)->send($normalizedPhone, $message);
            Log::info('POS API WhatsApp dispatched', ['booking_id' => $booking->id, 'phone' => $normalizedPhone, 'sent' => $sent]);
        } catch (\Throwable $e) {
            Log::error('POS API WhatsApp failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
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
