<?php

namespace Modules\POS\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\POS\Services\POSBookingService;
use Modules\POS\Models\POSBookingHoarding;
use Modules\POS\Models\PosCustomer;
use App\Models\Hoarding;
use App\Models\User;
use Modules\Hoardings\Models\HoardingMedia;
use Modules\Hoardings\Services\HoardingAvailabilityService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Modules\POS\Models\POSBooking;
use Modules\POS\Events\PosCustomerCreated;
class VendorPosController extends Controller
{
    protected POSBookingService $posBookingService;
    protected HoardingAvailabilityService $availabilityService;

    public function __construct(
        POSBookingService $posBookingService,
        HoardingAvailabilityService $availabilityService
    ) {
        $this->middleware(['auth', 'active_role:vendor']);
        $this->posBookingService = $posBookingService;
        $this->availabilityService = $availabilityService;
    }

    /**
     * Show POS bookings list page for vendor
     */
    public function index(Request $request)
    {
        // Blade view handles API fetch, so just render view
        return view('vendor.pos.list');
    }

    /**
     * Show create booking page
     */
    public function create(Request $request)
    {
        return view('vendor.pos.create');
    }

    /**
     * Show POS dashboard
     */
    public function dashboard(Request $request)
    {
        return view('vendor.pos.dashboard');
    }

    /**
     * Show POS booking details page for vendor
     */
    public function show($id)
    {
        $booking = POSBooking::findOrFail($id);
        \Log::info("this is".$booking);

        // The view will fetch booking details via API
        return view('vendor.pos.show', ['bookingId' => $id]);
    }

    /**
     * Get POS settings (GST rate, payment modes, etc.)
     * Web endpoint: GET /vendor/pos/api/settings
     */
    public function getSettings(): JsonResponse
    {
        try {
            $gstRate = $this->posBookingService->getGSTRate();
            $allowCash = $this->posBookingService->isCashPaymentAllowed();
            $allowCreditNote = $this->posBookingService->isCreditNoteAllowed();
            $creditNoteDays = $this->posBookingService->getCreditNoteDays();
            $autoApproval = $this->posBookingService->isAutoApprovalEnabled();
            $autoInvoice = $this->posBookingService->isAutoInvoiceEnabled();

            return response()->json([
                'success' => true,
                'data' => [
                    'gst_rate' => $gstRate,
                    'allow_cash_payment' => $allowCash,
                    'allow_credit_note' => $allowCreditNote,
                    'credit_note_days' => $creditNoteDays,
                    'auto_approval' => $autoApproval,
                    'auto_invoice' => $autoInvoice,
                    'payment_modes' => [
                        'cash' => 'Cash',
                        'credit_note' => 'Credit Note',
                        'bank_transfer' => 'Bank Transfer',
                        'cheque' => 'Cheque',
                        'online' => 'Online Payment',
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching POS settings', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get vendor's available hoardings for POS booking
     * Web endpoint: GET /vendor/pos/api/hoardings
     */

    public function getHoardings(Request $request): JsonResponse
    {
        try {
            $vendorId = Auth::id();

            // ── Base query ────────────────────────────────────────────────
            $query = Hoarding::query()
                ->where('vendor_id', $vendorId)
                ->where('status', 'active');

            // ── 1. Type filter (OOH / DOOH / ALL) ─────────────────────────
            if ($request->filled('type') && $request->type !== 'ALL') {
                $query->where('hoarding_type', strtolower($request->type));
                // strtolower because DB stores 'ooh' / 'dooh' (lowercase)
            }

            // ── 2. Text search ─────────────────────────────────────────────
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title',   'like', "%{$search}%")
                    ->orWhere('city',    'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
                });
            }

            // ── 3. Category filter (OOH only) ─────────────────────────────
            // DB column: hoardings.category  (values: billboard, unipole, gantry, pole_kiosk)
            if ($request->filled('category')) {
                $categories = array_filter(explode(',', $request->get('category')));
                if (!empty($categories)) {
                    $query->whereIn('category', $categories);
                }
            }

            // ── 4. Availability filter ─────────────────────────────────────
            // "available"  → no active booking overlapping today
            // "booked"     → has active booking overlapping today
            if ($request->filled('availability')) {
                $availabilityValues = array_filter(explode(',', $request->get('availability')));

                // Only filter if exactly one option is chosen (both = no filter needed)
                if (count($availabilityValues) === 1) {
                    $today = now()->toDateString();

                    if (in_array('available', $availabilityValues)) {
                        // Hoardings with NO active booking today
                        $query->whereDoesntHave('bookings', function ($q) use ($today) {
                            $q->where('start_date', '<=', $today)
                            ->where('end_date',   '>=', $today)
                            ->whereIn('status', ['confirmed', 'active', 'payment_hold']);
                        });
                    } elseif (in_array('booked', $availabilityValues)) {
                        // Hoardings WITH an active booking today
                        $query->whereHas('bookings', function ($q) use ($today) {
                            $q->where('start_date', '<=', $today)
                            ->where('end_date',   '>=', $today)
                            ->whereIn('status', ['confirmed', 'active', 'payment_hold']);
                        });
                    }
                }
            }

            // ── 5. Surroundings filter ─────────────────────────────────────
            // DB column: hoardings.located_at (JSON array)
            // Values: crossroad, highway, market_area, commercial_complexes, main_road
            if ($request->filled('surroundings')) {
                $surroundings = array_filter(explode(',', $request->get('surroundings')));
                if (!empty($surroundings)) {
                    $query->where(function ($q) use ($surroundings) {
                        foreach ($surroundings as $surrounding) {
                            // JSON_CONTAINS for MySQL, or use LIKE for compatibility
                            $q->orWhereRaw('JSON_CONTAINS(located_at, ?)', [json_encode($surrounding)]);
                            // Fallback if not using JSON column:
                            // $q->orWhere('located_at', 'like', "%{$surrounding}%");
                        }
                    });
                }
            }

            // ── 6. Hoarding Size filter (OOH → ooh_hoardings.width * height) ─
            // Join with ooh_hoardings to filter by size (width * height = sq.ft area)
            $hoardingSizeMin = (int) $request->get('hoarding_size_min', 0);
            $hoardingSizeMax = (int) $request->get('hoarding_size_max', 1000);

            $hasHoardingSizeFilter = $hoardingSizeMin > 0 || $hoardingSizeMax < 1000;

            if ($hasHoardingSizeFilter) {
                $query->where(function ($q) use ($hoardingSizeMin, $hoardingSizeMax) {
                    // OOH: join ooh_hoardings for width/height
                    $q->whereHas('oohHoarding', function ($oohQ) use ($hoardingSizeMin, $hoardingSizeMax) {
                        $oohQ->whereRaw('(COALESCE(width, 0) * COALESCE(height, 0)) >= ?', [$hoardingSizeMin])
                            ->whereRaw('(COALESCE(width, 0) * COALESCE(height, 0)) <= ?', [$hoardingSizeMax]);
                    })
                    // OR DOOH: join dooh_screens for width/height
                    ->orWhereHas('doohScreen', function ($doohQ) use ($hoardingSizeMin, $hoardingSizeMax) {
                        $doohQ->whereRaw('(COALESCE(width, 0) * COALESCE(height, 0)) >= ?', [$hoardingSizeMin])
                            ->whereRaw('(COALESCE(width, 0) * COALESCE(height, 0)) <= ?', [$hoardingSizeMax]);
                    });
                });
            }

            // ── 7. Screen Size filter (DOOH → dooh_screens.screen_size) ────
            // Uses dooh_screens.screen_size column (in sq.ft or inches — adjust as needed)
            $screenSizeMin = (int) $request->get('screen_size_min', 0);
            $screenSizeMax = (int) $request->get('screen_size_max', 1000);

            $hasScreenSizeFilter = $screenSizeMin > 0 || $screenSizeMax < 1000;

            if ($hasScreenSizeFilter) {
                $query->whereHas('doohScreen', function ($q) use ($screenSizeMin, $screenSizeMax) {
                    // If you have a dedicated screen_size column:
                    $q->where('screen_size', '>=', $screenSizeMin)
                    ->where('screen_size', '<=', $screenSizeMax);

                    // If you DON'T have screen_size column but have width/height:
                    // $q->whereRaw('(COALESCE(width, 0) * COALESCE(height, 0)) >= ?', [$screenSizeMin])
                    //   ->whereRaw('(COALESCE(width, 0) * COALESCE(height, 0)) <= ?', [$screenSizeMax]);
                });
            }

            // ── 8. Resolution filter (DOOH only) ──────────────────────────
            // DB column: dooh_screens.resolution  (values: led, hd, ultra_hd)
            if ($request->filled('resolution')) {
                $resolutions = array_filter(explode(',', $request->get('resolution')));
                if (!empty($resolutions)) {
                    $query->whereHas('doohScreen', function ($q) use ($resolutions) {
                        $q->whereIn('resolution', $resolutions);
                    });
                }
            }

            // ── 9. Date availability filter ────────────────────────────────
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = $request->get('start_date');
                $endDate   = $request->get('end_date');

                $query->whereDoesntHave('bookings', function ($q) use ($startDate, $endDate) {
                    $q->where(function ($inner) use ($startDate, $endDate) {
                        $inner->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function ($overlap) use ($startDate, $endDate) {
                                $overlap->where('start_date', '<=', $startDate)
                                        ->where('end_date', '>=', $endDate);
                            });
                    })
                    ->whereIn('status', ['confirmed', 'payment_hold', 'active']);
                });
            }

            // ── Execute & map ──────────────────────────────────────────────
            $hoardings = $query
                ->select([
                    'id', 'title', 'address', 'city', 'state',
                    'hoarding_type', 'category', 'located_at',
                    'base_monthly_price', 'monthly_price',
                ])
                ->orderBy('title')
                ->get()
                ->map(function ($hoarding) {
                    $imageUrl = $this->getHoardingImageUrl($hoarding);

                    $pricePerMonth = isset($hoarding->monthly_price)
                        ? (float) $hoarding->monthly_price
                        : null;

                    if (!$pricePerMonth || $pricePerMonth <= 0) {
                        $pricePerMonth = $hoarding->base_monthly_price ?? 0;
                    }

                    return [
                        'id'                  => $hoarding->id,
                        'title'               => $hoarding->title,
                        'location_address'    => $hoarding->address,
                        'location_city'       => $hoarding->city,
                        'location_state'      => $hoarding->state,
                        'type'                => $hoarding->hoarding_type,
                        'category'            => $hoarding->category,
                        'price_per_month'     => $pricePerMonth,
                        'image_url'           => $imageUrl,
                        'is_currently_booked' => $hoarding->bookings()
                            ->where('start_date', '<=', now())
                            ->where('end_date',   '>=', now())
                            ->whereIn('status', ['confirmed', 'active'])
                            ->exists(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data'    => $hoardings,
                'count'   => $hoardings->count(),
                'filters_applied' => $request->only([
                    'type', 'category', 'resolution', 'availability',
                    'surroundings', 'hoarding_size_min', 'hoarding_size_max',
                    'screen_size_min', 'screen_size_max',
                ]),
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching hoardings', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch hoardings',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: Get hoarding image URL based on type
     * 
     * OOH: Uses hoarding_media table (file_path column)
     * DOOH: Uses spatie media library on DOOHScreen child
     */
    private function getHoardingImageUrl(Hoarding $hoarding): ?string
    {
        try {
            // For OOH: Check hoarding_media table
            if ($hoarding->hoarding_type === 'ooh') {
                $media = \Modules\Hoardings\Models\HoardingMedia::where('hoarding_id', $hoarding->id)
                    ->where('is_primary', true)
                    ->orderBy('sort_order')
                    ->first();

                if ($media && $media->file_path) {
                    return asset('storage/' . ltrim($media->file_path, '/'));
                }

                // Fallback: Get first media
                $media = \Modules\Hoardings\Models\HoardingMedia::where('hoarding_id', $hoarding->id)
                    ->orderBy('sort_order')
                    ->first();

                return $media ? asset('storage/' . ltrim($media->file_path, '/')) : null;
            }

            // For DOOH: Try Spatie media library, fallback to dooh_screen_media table
            if ($hoarding->hoarding_type === 'dooh' && $hoarding->doohScreen) {
                // Try Spatie media library (if used)
                if (method_exists($hoarding->doohScreen, 'getFirstMedia')) {
                    $media = $hoarding->doohScreen->getFirstMedia('hero_image');
                    if ($media) {
                        return $media->getUrl();
                    }
                    $galleryMedia = $hoarding->doohScreen->getFirstMedia('gallery');
                    if ($galleryMedia) {
                        return $galleryMedia->getUrl();
                    }
                }
                // Fallback: Use dooh_screen_media table (primary or first)
                $media = $hoarding->doohScreen->media()
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order')
                    ->first();
                if ($media && $media->file_path) {
                    return asset('storage/' . ltrim($media->file_path, '/'));
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Error getting hoarding image URL', [
                'hoarding_id' => $hoarding->id,
                'type' => $hoarding->hoarding_type,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Search customers by name, phone, or email
     * Web endpoint: GET /vendor/pos/api/customers?search=term
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search', '');

            if (strlen($search) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Search term must be at least 2 characters',
                ]);
            }

            $customers = User::query()
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'customer');
                })
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })
                ->select(['id', 'name', 'email', 'phone', 'gstin', 'address'])
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $customers,
                'count' => $customers->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching customers', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to search customers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate total price with GST
     * Web endpoint: POST /vendor/pos/api/calculate-price
     * Body: { base_amount, discount_amount? }
     */
    public function calculatePrice(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'base_amount' => 'required|numeric|min:0',
                'discount_amount' => 'nullable|numeric|min:0',
            ]);

            $gstRate = $this->posBookingService->getGSTRate();
            $baseAmount = $validated['base_amount'];
            $discountAmount = $validated['discount_amount'] ?? 0;

            $amountAfterDiscount = max(0, $baseAmount - $discountAmount);
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error calculating price', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate price',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create POS booking with multiple hoardings
     * Web endpoint: POST /vendor/pos/api/bookings
     */
    public function createBooking(Request $request): JsonResponse
    {
        try {
            Log::info('POS create booking request', [
                'vendor_id' => Auth::id(),
                'payload' => $request->only(['hoarding_ids','start_date','end_date','base_amount','payment_mode'])
            ]);
            // $validated = $request->validate([
            //     'hoarding_ids' => 'required|string', // Comma-separated IDs
            //     'customer_id' => 'nullable|exists:users,id',
            //     'customer_name' => 'required|string|max:255',
            //     'customer_phone' => 'required|string|max:20',
            //     'customer_email' => 'nullable|email|max:255',
            //     'customer_address' => 'nullable|string|max:500',
            //     'customer_gstin' => 'nullable|string|max:15',
            //     'booking_type' => 'required|in:ooh,dooh',
            //     'start_date' => 'required|date|after_or_equal:today',
            //     'end_date' => 'required|date|after_or_equal:start_date',
            //     'base_amount' => 'required|numeric|min:0',
            //     'discount_amount' => 'nullable|numeric|min:0',
            //     'payment_mode' => 'required|in:cash,credit_note,bank_transfer,cheque,online',
            //     'payment_reference' => 'nullable|string|max:255',
            //     'payment_notes' => 'nullable|string|max:500',
            //     'notes' => 'nullable|string|max:1000',
            // ]);
              $validated = $request->validate([
                'hoarding_ids' => 'nullable', // Comma-separated IDs
                'customer_id' => 'nullable|exists:users,id',
                'customer_name' => 'nullable|string|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'customer_email' => 'nullable|email|max:255',
                'customer_address' => 'nullable|string|max:500',
                'customer_gstin' => 'nullable|string|max:15',
                'booking_type' => 'nullable|in:ooh,dooh',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'base_amount' => 'required|numeric|min:0',
                'discount_amount' => 'nullable|numeric|min:0',
                'payment_mode' => 'required|in:cash,credit_note,bank_transfer,cheque,online',
                'payment_reference' => 'nullable|string|max:255',
                'payment_notes' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000',
            ]);

            $vendorId = Auth::id();
            // $hoardingIds = array_filter(array_map('intval', explode(',', $validated['hoarding_ids'])));
            // Check if it's already an array (from JSON), otherwise explode it
            $hoardingIds = is_array($request->hoarding_ids) 
                ? $request->hoarding_ids 
                : explode(',', $request->hoarding_ids);

            if (empty($hoardingIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one hoarding must be selected',
                ], 422);
            }

            // Verify all hoardings belong to vendor
            $hoardings = Hoarding::whereIn('id', $hoardingIds)
                ->where('vendor_id', $vendorId)
                ->get();

            if ($hoardings->count() !== count($hoardingIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more hoardings not found or do not belong to you',
                ], 403);
            }

            // Check availability for all selected hoardings
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $unavailableHoardings = [];

            foreach ($hoardings as $hoarding) {
                // Get availability for this hoarding in the date range
                $availability = $this->availabilityService->checkMultipleDates(
                    $hoarding->id,
                    [
                        $startDate->format('Y-m-d'),
                        $endDate->format('Y-m-d'),
                    ]
                );

                // Verbose logging for debugging availability issues
                Log::info('POS booking availability check', [
                    'vendor_id' => $vendorId,
                    'hoarding_id' => $hoarding->id,
                    'hoarding_addr' => $hoarding->address ?? $hoarding->title ?? null,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'availability_preview' => array_slice($availability, 0, 5),
                ]);

                // Check if all dates in range are available
                if (!empty($availability)) {
                    $allDatesAvailable = true;
                    $unavailableReasons = [];

                    foreach ($availability as $dateCheck) {
                        if ($dateCheck['status'] !== 'available') {
                            $allDatesAvailable = false;
                            if (!in_array($dateCheck['status'], $unavailableReasons)) {
                                $unavailableReasons[] = $dateCheck['status'];
                            }
                        }
                    }

                    if (!$allDatesAvailable) {
                        $unavailableHoardings[] = [
                            'hoarding_id' => $hoarding->id,
                            'hoarding_name' => $hoarding->address,
                            'reasons' => $unavailableReasons,
                        ];
                    }
                }
            }

            // If any hoardings are unavailable, return error with details
            if (!empty($unavailableHoardings)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more selected hoardings are not available for the specified dates',
                    'unavailable_hoardings' => $unavailableHoardings,
                    'details' => $this->formatUnavailabilityDetails($unavailableHoardings),
                ], 422);
            }

            // Calculate pricing
            $gstRate = $this->posBookingService->getGSTRate();
            $baseAmount = (float) $validated['base_amount'];
            $discountAmount = (float) ($validated['discount_amount'] ?? 0);
            $amountAfterDiscount = max(0, $baseAmount - $discountAmount);
            $taxAmount = ($amountAfterDiscount * $gstRate) / 100;
            $totalAmount = $amountAfterDiscount + $taxAmount;

            // Create booking via service
            $bookingData = [
                'vendor_id' => $vendorId,
                'customer_id' => $validated['customer_id'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'customer_gstin' => $validated['customer_gstin']??null,
                'booking_type' => $validated['booking_type'] ?? 'ooh',
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'duration_days' => Carbon::parse($validated['end_date'])
                    ->diffInDays(Carbon::parse($validated['start_date'])) + 1,
                'base_amount' => $baseAmount,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_mode' => $validated['payment_mode'],
                'payment_reference' => $validated['payment_reference'],
                'payment_notes' => $validated['payment_notes'],
                'notes' => $validated['notes']??null,
                'status' => 'draft',
                'payment_status' => 'unpaid',
            ];

            // Ensure customer fields meet DB requirements
            if (empty($bookingData['customer_name'])) {
                $bookingData['customer_name'] = 'Walk-in Customer';
            }
            if (empty($bookingData['customer_phone'])) {
                $bookingData['customer_phone'] = 'N/A';
            }

            // Log prepared booking payload for debugging
            Log::info('POS booking data prepared', [
                'vendor_id' => $vendorId,
                'booking_data' => array_merge($bookingData, [
                    'hoarding_ids' => $hoardingIds,
                    'num_hoardings' => count($hoardingIds),
                ])
            ]);

            $booking = $this->posBookingService->createBooking($bookingData);
            Log::info('POS booking created', ['vendor_id' => $vendorId, 'booking_id' => $booking->id]);

            // Create pos_booking_hoardings records
            $durationDays = $endDate->diffInDays($startDate) + 1;
            $pricePerHoarding = $baseAmount / count($hoardingIds); // Distribute price evenly
            $discountPerHoarding = $discountAmount / count($hoardingIds);
            $taxPerHoarding = $taxAmount / count($hoardingIds);
            $totalPerHoarding = $totalAmount / count($hoardingIds);

            foreach ($hoardings as $hoarding) {
                POSBookingHoarding::create([
                    'pos_booking_id' => $booking->id,
                    'hoarding_id' => $hoarding->id,
                    'hoarding_price' => $pricePerHoarding,
                    'hoarding_discount' => $discountPerHoarding,
                    'hoarding_tax' => $taxPerHoarding,
                    'hoarding_total' => $totalPerHoarding,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'duration_days' => $durationDays,
                    'status' => 'pending',
                ]);
            }

            try {
                if (!empty($booking->customer_id)) {
                    $customer = \App\Models\User::find($booking->customer_id);
                    
                    // DB Notification
                    if ($customer && method_exists($customer, 'notify')) {
                        $customer->notify(new \App\Notifications\PosBookingCreatedNotification($booking));
                    }
                    
                    // Email — sirf tab bhejo jab valid email ho
                    if ($customer && !empty($customer->email) && filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
                        \Mail::to($customer->email)->send(new \App\Mail\PosBookingCreatedMail($booking, $customer));
                    } else {
                        \Log::info('POS email skipped - no valid email', [
                            'customer_id' => $booking->customer_id,
                            'email' => $customer->email ?? 'NULL'
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send POS booking notification/email', [
                    'error' => $e->getMessage(),
                    'booking_id' => $booking->id ?? null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => [
                    'id' => $booking->id,
                    'invoice_number' => $booking->invoice_number,
                    'total_amount' => $booking->total_amount,
                    'hoarding_count' => count($hoardingIds),
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('POS booking validation failed', [
                'vendor_id' => Auth::id(),
                'errors' => $e->errors(),
                'payload' => $request->only(['hoarding_ids','start_date','end_date','base_amount'])
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating POS booking', [
                'vendor_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get POS dashboard statistics
     * Web endpoint: GET /vendor/pos/api/dashboard
     */
    public function getDashboardStats(): JsonResponse
    {
        try {
            $vendorId = Auth::id();

            // Get statistics
            $totalBookings = \Modules\POS\Models\POSBooking::where('vendor_id', $vendorId)->count();
            $totalRevenue = \Modules\POS\Models\POSBooking::where('vendor_id', $vendorId)
                ->where('payment_status', 'paid')
                ->sum('total_amount') ?? 0;
            $pendingPayments = \Modules\POS\Models\POSBooking::where('vendor_id', $vendorId)
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->sum('total_amount') ?? 0;
            $activeCreditNotes = \Modules\POS\Models\POSBooking::where('vendor_id', $vendorId)
                ->where('credit_note_status', 'active')
                ->count();

                // 1. Registered Customers (Unique by ID)
            $regCount = POSBooking::where('vendor_id', $vendorId)
                ->whereNotNull('customer_id')
                ->distinct('customer_id')
                ->count('customer_id');

            // 2. Guest Walk-ins (Unique by Phone, excluding 'N/A')
            $guestCount = POSBooking::where('vendor_id', $vendorId)
                ->whereNull('customer_id')
                ->whereNotNull('customer_phone')
                ->where('customer_phone', '!=', 'N/A')
                ->distinct('customer_phone')
                ->count('customer_phone');

            // 3. True Anonymous (Every 'N/A' or empty phone is a new person)
            $naCount = POSBooking::where('vendor_id', $vendorId)
                ->whereNull('customer_id')
                ->where(function($q) {
                    $q->where('customer_phone', 'N/A')->orWhereNull('customer_phone');
                })
                ->count();
            return response()->json([
                'success' => true,
                'data' => [
                    'total_bookings' => $totalBookings,
                    'total_revenue' => (float) $totalRevenue,
                    'pending_payments' => (float) $pendingPayments,
                    'active_credit_notes' => $activeCreditNotes,
                    'total_customers'  => ($regCount + $guestCount + $naCount), // The combined total
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching dashboard stats', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard stats',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get vendor's recent bookings
     * Web endpoint: GET /vendor/pos/api/bookings
     */
    public function getBookingsList(Request $request): JsonResponse
    {
        try {
            $vendorId = Auth::id();
            $perPage = (int) ($request->get('per_page') ?? 10);

            $bookings = \Modules\POS\Models\POSBooking::where('vendor_id', $vendorId)
                ->with('bookingHoardings.hoarding')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $bookings->items(),
                    'current_page' => $bookings->currentPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                    'last_page' => $bookings->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching bookings list', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bookings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get bookings with active payment holds
     * Web endpoint: GET /vendor/pos/api/pending-payments
     */
    public function getPendingPayments(): JsonResponse
    {
        try {
            $vendorId = Auth::id();

            // Get bookings with unpaid/partial payments
            $pendingPayments = \Modules\POS\Models\POSBooking::where('vendor_id', $vendorId)
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->with('bookingHoardings.hoarding')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $pendingPayments,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pending payments', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending payments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format unavailability details for human-readable error messages
     * Maps availability statuses to friendly descriptions
     */
    protected function formatUnavailabilityDetails(array $unavailableHoardings): string
    {
        $messages = [];
        
        foreach ($unavailableHoardings as $item) {
            $hoardingName = $item['hoarding_name'] ?? "Hoarding #{$item['hoarding_id']}";
            $reasons = [];

            foreach ($item['reasons'] as $reason) {
                switch ($reason) {
                    case 'booked':
                        $reasons[] = 'already booked for some dates';
                        break;
                    case 'blocked':
                        $reasons[] = 'under maintenance/blocked for some dates';
                        break;
                    case 'hold':
                        $reasons[] = 'on payment hold for some dates';
                        break;
                    case 'partial':
                        $reasons[] = 'partially unavailable';
                        break;
                    default:
                        $reasons[] = $reason;
                }
            }

            $messages[] = "{$hoardingName}: " . implode(', ', $reasons);
        }

        return implode('. ', $messages) . '.';
    }


 
    // public function createCustomer(Request $request)
    // {
    //     \Log::info('Create POS customer request', [
    //         'vendor_id' => Auth::id(),
    //         'payload' => $request->all()
    //     ]);
    //     // 1. Expanded Validation
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         // 'business_name' => 'nullable|string|max:100',
    //         'email' => 'required|email|unique:users,email',
    //         'phone' => 'required|string|max:20',
    //         'password' => 'required|confirmed|min:6',
    //         'gstin' => 'nullable|string|max:15',
    //         'pincode' => 'nullable|string|max:10',
    //         'city' => 'nullable|string|max:100',
    //         'state' => 'nullable|string|max:100',
    //         'country' => 'nullable|string|max:100',
    //     ]);

    //     try {
    //         // 2. Start Transaction
    //         return DB::transaction(function () use ($request) {
    //             \Log::info('getting to create pos customer');
    //             // 3. Create the Base User
    //             $fullAddress = trim("{$request->city}, {$request->state} - {$request->pincode}, {$request->country}", ", -");

    //             $user = User::create([
    //                 'name' => $request->name,
    //                 'email' => $request->email,
    //                 'phone' => $request->phone,
    //                 'password' => Hash::make($request->password),
    //                 'active_role' => 'customer',
    //                 'pincode' => $request->pincode,
    //                 'gstin' => $request->gstin,
    //                 'city' => $request->city,
    //                 'state' => $request->state,
    //                 'address'       =>$fullAddress,
    //             ]);
    //             $user->assignRole('customer');

    //             // 4. Create the POS Customer Profile
    //             // We combine City, State, and Pincode into the 'address' field 
    //             // as per your migration schema

    //             $user->posProfile()->create([
    //                 'vendor_id'     => Auth::id(),
    //                 'created_by'    => Auth::id(),
    //                 'gstin'         => $request->gstin,
    //                 'business_name' => $request->business_name,
    //                 'address'       =>$fullAddress,
    //             ]);

    //             // 5. Return success response
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Customer created successfully',
    //                 'data' => $user
    //             ]);
    //         });

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Something went wrong: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function createCustomer(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|string|max:20|unique:users,phone',
                'password' => 'required|confirmed|min:6',
                'gstin' => 'nullable|string|max:15|unique:users,gstin',
            ]);

            $user = DB::transaction(function () use ($request) {
                $fullAddress = trim(
                    "{$request->city}, {$request->state} - {$request->pincode}, {$request->country}",
                    ", -"
                );

                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'active_role' => 'customer',
                    'gstin' => $request->gstin,
                    'address' => $fullAddress,
                ]);

                $user->assignRole('customer');

                $user->posProfile()->create([
                    'vendor_id' => Auth::id(),
                    'created_by' => Auth::id(),
                    'gstin' => $request->gstin,
                    'business_name' => $request->business_name,
                    'address' => $fullAddress,
                ]);

                return $user;
            });
            try {
                // Log customer creation
                Log::info('POS customer created', [
                    'vendor_id' => Auth::id(),
                    'customer_id' => $user->id,
                    'customer_email' => $user->email,
                ]);
                DB::afterCommit(function () use ($user) {
                    event(new PosCustomerCreated($user, Auth::user()));
                });
            } catch (\Exception $e) {
                Log::warning('Failed to log POS customer creation', [
                    'vendor_id' => Auth::id(),
                    'error' => $e->getMessage(),
                ]);
            }
           


            // // Send notification and email to Vendor
            // try {
            //     $vendor = Auth::user();
            //     // Notification (if using Laravel Notifications)
            //     if (method_exists($vendor, 'notify')) {
            //         $vendor->notify(new \App\Notifications\PosCustomerCreatedNotification($user));
            //     }
            //     // Email
            //     \Mail::to($vendor->email)->send(new \App\Mail\PosCustomerCreatedForVendor($user));
            // } catch (\Exception $e) {
            //     \Log::warning('Failed to notify vendor on POS customer create', ['error' => $e->getMessage()]);
            // }

            // // Send email and SMS to Customer
            // try {
            //     // Email
            //     \Mail::to($user->email)->send(new \App\Mail\PosCustomerWelcome($user));
            //     // SMS (if you have an SMS service)
            //     if (function_exists('send_sms')) {
            //         send_sms($user->phone, "Welcome to OOHApp! Your customer profile has been created.");
            //     }
            // } catch (\Exception $e) {
            //     \Log::warning('Failed to notify customer on POS customer create', ['error' => $e->getMessage()]);
            // }

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => $user
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Create POS customer failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer. Please try again.'
            ], 500);
        }
    }


      public function customers()
    {
        $vendorId = Auth::id();

        // 1. Get all user_ids from bookings for this vendor
        $bookingCustomers = POSBooking::where('vendor_id', $vendorId)
            ->whereNotNull('customer_id')
            ->pluck('customer_id')
            ->unique()
            ->toArray();

        // 2. Get all user_ids from pos_customers for this vendor
        $posCustomerUserIds = PosCustomer::where('vendor_id', $vendorId)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique()
            ->toArray();

        // 3. Merge and deduplicate user_ids
        $allUserIds = collect($bookingCustomers)
            ->merge($posCustomerUserIds)
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        // 4. Fetch all users
        $users = User::whereIn('id', $allUserIds)
            ->with(['posProfile' => function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            }])
            ->get();

        // 5. For each user, calculate metrics
        $customers = $users->map(function ($user) use ($vendorId) {
            // Get all bookings for this user and vendor
            $bookings = POSBooking::where('vendor_id', $vendorId)
                ->where('customer_id', $user->id)
                ->get();

            $totalBookings = $bookings->count();
            $totalSpent = $bookings->sum('total_amount');
            $lastBookingAt = $bookings->max('created_at');

            // Prefer posProfile business name if exists
            $name = $user->name;
            if ($user->posProfile && $user->posProfile->business_name) {
                $name = $user->posProfile->business_name;
            }

            return [
                'id' => $user->id, // Use user_id as customer ID
                'name' => $name,
                'phone' => $user->phone,
                'email' => $user->email,
                'total_bookings' => $totalBookings,
                'total_spent' => $totalSpent,
                'last_booking_at' => $lastBookingAt,
                'is_active' => $totalBookings > 0,
            ];
        });

        $totalCustomers = $customers->count();

        return view('vendor.pos.customers', compact('customers', 'totalCustomers'));
    }

    public function showCustomer($id)
    {
        $vendorId = Auth::id();

        // Find user by ID
        $user = User::findOrFail($id);

        // Get POS profile for this vendor (if exists)
        $posProfile = $user->posProfile()->where('vendor_id', $vendorId)->first();

        // Get all bookings for this user and vendor
        $bookings = POSBooking::where('vendor_id', $vendorId)
            ->where('customer_id', $user->id)
            ->get();

        $totalBookings = $bookings->count();
        $totalSpent = $bookings->sum('total_amount');
        $lastBookingAt = $bookings->max('created_at');

        // Prefer posProfile business name if exists
        $name = $user->name;
        if ($posProfile && $posProfile->business_name) {
            $name = $posProfile->business_name;
        }

        $customer = [
            'id' => $user->id,
            'name' => $name,
            'phone' => $user->phone,
            'email' => $user->email,
            'total_bookings' => $totalBookings,
            'total_spent' => $totalSpent,
            'last_booking_at' => $lastBookingAt,
            'is_active' => $totalBookings > 0,
            'pos_profile' => $posProfile,
            'bookings' => $bookings,
        ];

        return view('vendor.pos.customers.show', compact('customer'));
    }


}