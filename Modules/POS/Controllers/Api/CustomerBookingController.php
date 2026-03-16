<?php

namespace Modules\POS\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\POS\Models\POSBooking;


/**
 * @OA\Tag(
 *     name="POS",
 *     description="Customer-facing POS booking management APIs"
 * )
 */
class CustomerBookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

     /**
     * @OA\Get(
     *     path="/pos/customer/",
     *     operationId="customerListPOSBookings",
     *     tags={"Customer POS Bookings"},
     *     summary="List authenticated customer's POS bookings",
     *     description="Returns paginated list of POS bookings for the authenticated customer with stats and filter options.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by booking ID (integer) or invoice number",
     *         @OA\Schema(type="string", example="101")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by booking status",
     *         @OA\Schema(
     *             type="string",
     *             enum={"draft","pending_payment","confirmed","active","completed","cancelled"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="payment_status",
     *         in="query",
     *         description="Filter by payment status",
     *         @OA\Schema(type="string", enum={"paid","unpaid","partial","credit"})
     *     ),
     *     @OA\Parameter(
     *         name="from_date",
     *         in="query",
     *         description="Filter bookings where start_date >= from_date (Y-m-d)",
     *         @OA\Schema(type="string", format="date", example="2026-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         in="query",
     *         description="Filter bookings where end_date <= to_date (Y-m-d)",
     *         @OA\Schema(type="string", format="date", example="2026-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Column to sort by",
     *         @OA\Schema(type="string", enum={"created_at","start_date","end_date","total_amount"}, default="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_dir",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(type="string", enum={"asc","desc"}, default="desc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page (1–100)",
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bookings fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="bookings",
     *                     type="object",
     *                     @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="total", type="integer", example=25),
     *                     @OA\Property(property="last_page", type="integer", example=3)
     *                 ),
     *                 @OA\Property(
     *                     property="stats",
     *                     type="object",
     *                     @OA\Property(property="total_bookings", type="integer", example=25),
     *                     @OA\Property(property="active", type="integer", example=5),
     *                     @OA\Property(property="pending_payment", type="integer", example=3),
     *                     @OA\Property(property="total_spent", type="number", format="float", example=250000)
     *                 ),
     *                 @OA\Property(
     *                     property="filters",
     *                     type="object",
     *                     @OA\Property(property="status_options", type="object"),
     *                     @OA\Property(property="payment_status_options", type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch bookings",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch bookings. Please try again.")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $customerId = (int) Auth::id();

            $query = POSBooking::with(['hoardings', 'bookingHoardings.hoarding'])
                ->where('customer_id', $customerId);

            // ── Search by booking ID ───────────────────────────────────────
            if ($search = $request->input('search')) {
                // Accept either a numeric ID or an invoice number string
                if (is_numeric($search)) {
                    $query->where('id', (int) $search);
                } else {
                    $query->where('invoice_number', 'like', "%{$search}%");
                }
            }

            // ── Status filter ──────────────────────────────────────────────
            $allowedStatuses = [
                'draft', 'pending_payment', 'confirmed',
                'active', 'completed', 'cancelled',
            ];
            if ($status = $request->input('status')) {
                if (in_array($status, $allowedStatuses, true)) {
                    $query->where('status', $status);
                }
            }

            // ── Payment status filter ──────────────────────────────────────
            $allowedPaymentStatuses = ['paid', 'unpaid', 'partial', 'credit'];
            if ($paymentStatus = $request->input('payment_status')) {
                if (in_array($paymentStatus, $allowedPaymentStatuses, true)) {
                    $query->where('payment_status', $paymentStatus);
                }
            }

            // ── Date range filter ──────────────────────────────────────────
            if ($from = $request->input('from_date')) {
                $query->whereDate('start_date', '>=', $from);
            }
            if ($to = $request->input('to_date')) {
                $query->whereDate('end_date', '<=', $to);
            }

            // ── Sorting ────────────────────────────────────────────────────
            $allowedSortColumns = ['created_at', 'start_date', 'end_date', 'total_amount'];
            $sortBy  = $request->input('sort_by', 'created_at');
            $sortDir = $request->input('sort_dir', 'desc');

            if (in_array($sortBy, $allowedSortColumns, true)) {
                $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // ── Pagination ─────────────────────────────────────────────────
            $perPage  = max(1, min((int) $request->input('per_page', 10), 100));
            $paginated = $query->paginate($perPage)->withQueryString();

            // ── Customer stats ─────────────────────────────────────────────
            $base = POSBooking::where('customer_id', $customerId);

            $stats = [
                'total_bookings'  => (clone $base)->count(),
                'active'          => (clone $base)->where('status', 'confirmed')->count(),
                'pending_payment' => (clone $base)->where('payment_status', 'unpaid')->count(),
                'total_spent'     => (float) (clone $base)->where('payment_status', 'paid')->sum('total_amount'),
            ];

            return response()->json([
                'success' => true,
                'data'    => [
                    'bookings' => [
                        'data'         => $paginated->items(),
                        'current_page' => $paginated->currentPage(),
                        'per_page'     => $paginated->perPage(),
                        'total'        => $paginated->total(),
                        'last_page'    => $paginated->lastPage(),
                    ],
                    'stats'    => $stats,
                    'filters'  => [
                        'status_options' => [
                            ''                => 'All Status',
                            'confirmed'       => 'Confirmed',
                            'pending_payment' => 'Pending Payment',
                            'cancelled'       => 'Cancelled',
                        ],
                        'payment_status_options' => [
                            ''       => 'All Payments',
                            'paid'   => 'Paid',
                            'unpaid' => 'Unpaid',
                        ],
                    ],
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('Customer POS booking list failed', [
                'customer_id' => Auth::id(),
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bookings. Please try again.',
            ], 500);
        }
    }

     /**
     * @OA\Get(
     *     path="/pos/customer/{bookingId}",
     *     operationId="customerShowPOSBooking",
     *     tags={"Customer POS Bookings"},
     *     summary="Get a single POS booking for the authenticated customer",
     *     description="Returns full booking detail including vendor info, hoardings with media, pricing, invoice URL, payment summary, hold expiry, and milestone info.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="bookingId",
     *         in="path",
     *         required=true,
     *         description="ID of the POS booking",
     *         @OA\Schema(type="integer", example=101)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=101),
     *                 @OA\Property(property="invoice_number", type="string", example="INV-2026-0001"),
     *                 @OA\Property(property="booking_type", type="string", enum={"ooh","dooh"}, example="ooh"),
     *                 @OA\Property(property="status", type="string", example="confirmed"),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2026-04-01"),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2026-04-30"),
     *                 @OA\Property(property="duration_days", type="integer", example=30),
     *                 @OA\Property(property="notes", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="customer_name", type="string", example="John Doe"),
     *                 @OA\Property(property="customer_email", type="string", format="email", nullable=true),
     *                 @OA\Property(property="customer_phone", type="string", example="+919876543210"),
     *                 @OA\Property(property="customer_address", type="string", nullable=true),
     *                 @OA\Property(property="customer_gstin", type="string", nullable=true),
     *                 @OA\Property(
     *                     property="vendor",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="name", type="string", example="Vendor Name"),
     *                     @OA\Property(property="email", type="string", format="email"),
     *                     @OA\Property(property="phone", type="string", nullable=true),
     *                     @OA\Property(property="company_name", type="string", nullable=true),
     *                     @OA\Property(property="address", type="string", nullable=true),
     *                     @OA\Property(property="logo_url", type="string", nullable=true)
     *                 ),
     *                 @OA\Property(
     *                     property="hoardings",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=10),
     *                         @OA\Property(property="title", type="string", example="Main Road Billboard"),
     *                         @OA\Property(property="address", type="string", nullable=true),
     *                         @OA\Property(property="city", type="string", nullable=true),
     *                         @OA\Property(property="state", type="string", nullable=true),
     *                         @OA\Property(property="type", type="string", nullable=true),
     *                         @OA\Property(property="category", type="string", nullable=true),
     *                         @OA\Property(property="size", type="string", nullable=true),
     *                         @OA\Property(property="status", type="string", nullable=true),
     *                         @OA\Property(property="image_url", type="string", nullable=true),
     *                         @OA\Property(property="gallery", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="campaign_start_date", type="string", format="date", nullable=true),
     *                         @OA\Property(property="campaign_end_date", type="string", format="date", nullable=true),
     *                         @OA\Property(property="campaign_duration_days", type="integer", nullable=true),
     *                         @OA\Property(property="hoarding_price", type="number", format="float"),
     *                         @OA\Property(property="hoarding_discount", type="number", format="float"),
     *                         @OA\Property(property="hoarding_tax", type="number", format="float"),
     *                         @OA\Property(property="hoarding_total", type="number", format="float"),
     *                         @OA\Property(property="pivot_status", type="string", nullable=true)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="payment",
     *                     type="object",
     *                     @OA\Property(property="base_amount", type="number", format="float", example=100000),
     *                     @OA\Property(property="discount_amount", type="number", format="float", example=5000),
     *                     @OA\Property(property="tax_amount", type="number", format="float", example=17100),
     *                     @OA\Property(property="total_amount", type="number", format="float", example=112100),
     *                     @OA\Property(property="paid_amount", type="number", format="float", example=50000),
     *                     @OA\Property(property="remaining_amount", type="number", format="float", example=62100),
     *                     @OA\Property(property="payment_mode", type="string", example="cash"),
     *                     @OA\Property(property="payment_status", type="string", example="partial"),
     *                     @OA\Property(property="payment_reference", type="string", nullable=true)
     *                 ),
     *                 @OA\Property(
     *                     property="hold",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="hold_expiry_at", type="string", format="date-time"),
     *                     @OA\Property(property="seconds_remaining", type="integer", example=3600),
     *                     @OA\Property(property="is_expired", type="boolean", example=false)
     *                 ),
     *                 @OA\Property(property="has_invoice", type="boolean", example=true),
     *                 @OA\Property(property="invoice_url", type="string", nullable=true),
     *                 @OA\Property(property="is_milestone", type="boolean", example=false),
     *                 @OA\Property(property="milestone_total", type="integer", example=0),
     *                 @OA\Property(property="milestone_paid", type="integer", example=0),
     *                 @OA\Property(property="milestone_amount_paid", type="number", format="float", example=0),
     *                 @OA\Property(property="milestone_amount_remaining", type="number", format="float", example=0)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Booking not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch booking details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch booking details. Please try again.")
     *         )
     *     )
     * )
     */
    public function show(int $bookingId): JsonResponse
    {
        try {
            $customerId = (int) Auth::id();

            $booking = POSBooking::where('id', $bookingId)
                ->where('customer_id', $customerId)
                ->with([
                    'vendor.vendorProfile',
                    'bookingHoardings.hoarding.hoardingMedia',
                    'hoardings',
                ])
                ->firstOrFail();

            // ── Vendor info ────────────────────────────────────────────────
            $vendor        = $booking->vendor;
            $vendorProfile = $vendor?->vendorProfile;

            $vendorData = [
                'id'           => $vendor?->id,
                'name'         => $vendor?->name,
                'email'        => $vendor?->email,
                'phone'        => $vendor?->phone,
                'company_name' => $vendorProfile?->company_name ?? $vendor?->name,
                'address'      => $vendorProfile?->address ?? null,
                'logo_url'     => $vendorProfile?->logo_url ?? null,
            ];

            // ── Hoardings with full details ────────────────────────────────
            $hoardings = $booking->bookingHoardings->map(function ($bh) {
                $hoarding = $bh->hoarding;

                // Primary media image
                $primaryMedia = $hoarding?->hoardingMedia
                    ?->sortBy('sort_order')
                    ->first();
                $imageUrl = $primaryMedia
                    ? asset('storage/' . ltrim($primaryMedia->file_path, '/'))
                    : null;

                // All media for gallery
                $gallery = $hoarding?->hoardingMedia?->map(fn ($m) => [
                    'id'        => $m->id,
                    'url'       => asset('storage/' . ltrim($m->file_path, '/')),
                    'is_primary'=> (bool) $m->is_primary,
                    'sort_order'=> $m->sort_order,
                ]) ?? [];

                $startDate = $bh->start_date
                    ? \Carbon\Carbon::parse($bh->start_date)->toDateString()
                    : null;
                $endDate = $bh->end_date
                    ? \Carbon\Carbon::parse($bh->end_date)->toDateString()
                    : null;
                $durationDays = ($startDate && $endDate)
                    ? (\Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1)
                    : null;

                return [
                    'id'                   => $hoarding?->id,
                    'title'                => $hoarding?->title,
                    'address'              => $hoarding?->address,
                    'city'                 => $hoarding?->city,
                    'state'                => $hoarding?->state,
                    'type'                 => $hoarding?->hoarding_type,
                    'category'             => $hoarding?->category,
                    'size'                 => $hoarding?->size ?? null,
                    'status'               => $hoarding?->status,
                    'image_url'            => $imageUrl,
                    'gallery'              => $gallery,
                    // Campaign dates for this specific hoarding
                    'campaign_start_date'  => $startDate,
                    'campaign_end_date'    => $endDate,
                    'campaign_duration_days' => $durationDays,
                    // Per-hoarding pricing
                    'hoarding_price'       => (float) ($bh->hoarding_price ?? 0),
                    'hoarding_discount'    => (float) ($bh->hoarding_discount ?? 0),
                    'hoarding_tax'         => (float) ($bh->hoarding_tax ?? 0),
                    'hoarding_total'       => (float) ($bh->hoarding_total ?? 0),
                    'pivot_status'         => $bh->status,
                ];
            })->values();

            // ── Invoice URL (read-only, vendor-served PDF) ─────────────────
            $invoiceUrl = null;
            if (!empty($booking->invoice_path)) {
                // Use the vendor-facing route — customer gets read-only access
                if (\Illuminate\Support\Facades\Route::has('vendor.pos.bookings.invoice')) {
                    $invoiceUrl = route('vendor.pos.bookings.invoice', ['id' => $booking->id]);
                }
            }

            // ── Payment summary ────────────────────────────────────────────
            $totalAmount     = (float) $booking->total_amount;
            $paidAmount      = (float) ($booking->paid_amount ?? 0);
            $remainingAmount = max(0, $totalAmount - $paidAmount);

            $paymentSummary = [
                'base_amount'      => (float) $booking->base_amount,
                'discount_amount'  => (float) $booking->discount_amount,
                'tax_amount'       => (float) $booking->tax_amount,
                'total_amount'     => $totalAmount,
                'paid_amount'      => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'payment_mode'     => $booking->payment_mode,
                'payment_status'   => $booking->payment_status,
                'payment_reference'=> $booking->payment_reference,
            ];

            // ── Hold expiry countdown ──────────────────────────────────────
            $holdInfo = null;
            if ($booking->hold_expiry_at && $booking->payment_status === 'unpaid') {
                $expiresAt     = \Carbon\Carbon::parse($booking->hold_expiry_at);
                $secondsLeft   = max(0, (int) now()->diffInSeconds($expiresAt, false));
                $holdInfo = [
                    'hold_expiry_at'    => $expiresAt->toISOString(),
                    'seconds_remaining' => $secondsLeft,
                    'is_expired'        => $secondsLeft === 0,
                ];
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    // Core booking fields
                    'id'               => $booking->id,
                    'invoice_number'   => $booking->invoice_number,
                    'booking_type'     => $booking->booking_type,
                    'status'           => $booking->status,
                    'start_date'       => $booking->start_date,
                    'end_date'         => $booking->end_date,
                    'duration_days'    => $booking->duration_days,
                    'notes'            => $booking->notes,
                    'created_at'       => $booking->created_at,
                    'updated_at'       => $booking->updated_at,
                    // Customer fields (own data)
                    'customer_name'    => $booking->customer_name,
                    'customer_email'   => $booking->customer_email,
                    'customer_phone'   => $booking->customer_phone,
                    'customer_address' => $booking->customer_address,
                    'customer_gstin'   => $booking->customer_gstin,
                    // Nested objects
                    'vendor'           => $vendorData,
                    'hoardings'        => $hoardings,
                    'payment'          => $paymentSummary,
                    'hold'             => $holdInfo,
                    // Invoice
                    'has_invoice'      => !empty($booking->invoice_path),
                    'invoice_url'      => $invoiceUrl,
                    // Milestone
                    'is_milestone'             => (bool) $booking->is_milestone,
                    'milestone_total'          => (int) ($booking->milestone_total ?? 0),
                    'milestone_paid'           => (int) ($booking->milestone_paid ?? 0),
                    'milestone_amount_paid'    => (float) ($booking->milestone_amount_paid ?? 0),
                    'milestone_amount_remaining' => (float) ($booking->milestone_amount_remaining ?? 0),
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.',
            ], 404);
        } catch (\Throwable $e) {
            Log::error('Customer POS booking show failed', [
                'customer_id' => Auth::id(),
                'booking_id'  => $bookingId,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch booking details. Please try again.',
            ], 500);
        }
    }
}