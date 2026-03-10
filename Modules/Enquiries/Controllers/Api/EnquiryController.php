<?php

namespace Modules\Enquiries\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GracePeriodService;
use App\Models\Hoarding;
use Modules\Enquiries\Services\EnquiryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Enquiries\Models\Enquiry;
use Modules\Enquiries\Models\EnquiryItem;
use Modules\Enquiries\Http\Resources\Api\EnquiryResource;
use Modules\Enquiries\Http\Resources\Api\EnquiryItemResource;
use Symfony\Component\HttpFoundation\Response;

class EnquiryController extends Controller
{
    protected EnquiryService $service;
    protected GracePeriodService $gracePeriodService;

    public function __construct(EnquiryService $service, GracePeriodService $gracePeriodService)
    {
        $this->service = $service;
        $this->gracePeriodService = $gracePeriodService;
    }

    /**
     * Store a new enquiry
     * POST /api/v1/enquiries
     */
    // public function store(Request $request): JsonResponse
    // {
    //     $hoarding = Hoarding::findOrFail($request->hoarding_id);
        
    //     $validator = Validator::make($request->all(), [
    //         'hoarding_id' => 'required|exists:hoardings,id',
    //         'preferred_start_date' => 'required|date|after_or_equal:today',
    //         'preferred_end_date' => 'required|date|after:preferred_start_date',
    //         'duration_type' => 'required|in:days,weeks,months',
    //         'message' => 'nullable|string|max:1000',
    //     ]);

    //     // Add grace period validation
    //     $this->gracePeriodService->addValidationRule($validator, 'preferred_start_date', $hoarding);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     try {
    //         $enquiry = $this->service->createEnquiry($validator->validated());

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Enquiry submitted successfully',
    //             'data' => $enquiry->load(['customer', 'hoarding']),
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to create enquiry',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
    {
        // 1. Basic Validation
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_mobile' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'message' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.hoarding_id' => 'required|exists:hoardings,id',
            'items.*.preferred_start_date' => 'required|date|after_or_equal:today',
            'items.*.duration_unit' => 'required|in:days,weeks,months',
            'items.*.duration_value' => 'required|integer|min:1',
        ]);

        // 2. Custom Validation (Grace Period Check)
        $validator->after(function ($validator) use ($request) {
            foreach ($request->items as $index => $item) {
                $hoarding = Hoarding::find($item['hoarding_id']);
                if (!$hoarding) {
                    $validator->errors()->add("items.$index.hoarding_id", "The selected hoarding no longer exists.");
                    continue;
                }

                // Optional: Check if the hoarding is marked as active in your DB
                if (isset($hoarding->status) && $hoarding->status !== 'active') {
                    $validator->errors()->add("items.$index.hoarding_id", "Hoarding #{$hoarding->id} is currently unavailable for booking.");
                }
               $this->gracePeriodService->addValidationRule(
                $validator, 
                    "items.$index.preferred_start_date", 
                    $hoarding
                );
            }
        });

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            return DB::transaction(function () use ($request) {
                $user = Auth::user();
                $itemsData = $request->items;

                // Restrict vendors from creating enquiries for their own hoardings
                if ($user->hasRole('vendor')) {
                    foreach ($itemsData as $item) {
                        $hoarding = Hoarding::find($item['hoarding_id']);
                        if ($hoarding && $hoarding->vendor_id == $user->id) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Vendors cannot create enquiries for their own hoardings.'
                            ], 403);
                        }
                    }
                }

                // 3. Create Enquiry Header
                $enquiry = Enquiry::create([
                    'customer_id'    => $user->id,
                    'enquiry_type'   => count($itemsData) > 1 ? 'multiple' : 'single',
                    'source'         => $user->role ?? 'user',
                    'status'         => 'submitted',
                    'customer_note'  => $request->message,
                    'contact_number' => $request->customer_mobile,
                ]);

                foreach ($itemsData as $item) {
                    $hoarding = Hoarding::with('doohScreen')->findOrFail($item['hoarding_id']);
                    $startDate = Carbon::parse($item['preferred_start_date']);
                    // 4. Resolve Duration and Package
                    $unit = $item['duration_unit']; // days, weeks, months
                    $value = (int) $item['duration_value'];
                    $packageId = $item['package_id'] ?? null;
                    $package = null;
                    $packageType = 'base';

                    if ($packageId) {
                        $packageType = 'package';
                        if ($hoarding->hoarding_type === 'dooh') {
                            $package = \Modules\DOOH\Models\DOOHPackage::find($packageId);
                        } else {
                            $package = \Modules\Hoardings\Models\HoardingPackage::find($packageId);
                        }
                        // If package exists, it might override the duration value (assuming packages are months)
                        if ($package && isset($package->min_booking_duration)) {
                            $value = $package->min_booking_duration;
                            $unit = 'months'; 
                        }
                    }

                    // Dynamic End Date Calculation
                    $endDate = (clone $startDate);
                    switch ($unit) {
                        case 'days':
                            $endDate->addDays($value);
                            break;
                        case 'weeks':
                            $endDate->addWeeks($value);
                            break;
                        case 'months':
                        default:
                            $endDate->addMonths($value);
                            break;
                    }

                    // 5. Build Meta Data
                    $meta = [
                        'customer_name'   => $request->customer_name,
                        'customer_mobile' => $request->customer_mobile,
                        'duration_unit'   => $unit,
                        'duration_value'  => $value,
                        'package_label'   => $package ? $package->package_name : 'Base Price',
                    ];

                    if ($hoarding->hoarding_type === 'dooh') {
                        $meta['dooh_specs'] = [
                            'video_duration' => $item['video_duration'] ?? 15,
                            'slots_per_day'  => $item['slots_count'] ?? 120,
                            'loop_interval'  => $item['slot'] ?? 'Standard',
                        ];
                    }

                    // 6. Create Enquiry Item
                    EnquiryItem::create([
                        'enquiry_id'           => $enquiry->id,
                        'hoarding_id'          => $hoarding->id,
                        'hoarding_type'        => str_contains($hoarding->hoarding_type, 'dooh') ? 'dooh' : 'ooh',
                        'package_id'           => $packageId,
                        'package_type'         => $packageType,
                        'preferred_start_date' => $startDate,
                        'preferred_end_date'   => $endDate,
                        'expected_duration'    => "$value-$unit",
                        'meta'                 => $meta,
                        'status'               => 'new',
                    ]);

                    // 7. Cleanup Cart
                    DB::table('carts')
                        ->where('user_id', $user->id)
                        ->where('hoarding_id', $hoarding->id)
                        ->delete();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Enquiry submitted successfully',
                    'enquiry_id' => $enquiry->id
                ], 201);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Submission failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   /* =====================================================
     | INDEX — with search + filters (mirrors web controller)
     ===================================================== */

    /**
     * @OA\Get(
     *     path="/api/v1/enquiries",
     *     summary="List enquiries for authenticated customer",
     *     description="Returns paginated list of enquiries with optional filters for status, search by ID, date range, and custom date range.",
     *     tags={"Enquiries"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by enquiry status",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"submitted","pending","accepted","rejected","cancelled"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by enquiry ID (digits only are extracted)",
     *         required=false,
     *         @OA\Schema(type="string", example="ENQ-101")
     *     ),
     *     @OA\Parameter(
     *         name="date_filter",
     *         in="query",
     *         description="Preset date range filter",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"last_week","last_month","last_year","custom"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="from_date",
     *         in="query",
     *         description="Start date for custom range (required when date_filter=custom)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         in="query",
     *         description="End date for custom range (required when date_filter=custom)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-03-31")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page (default: 10)",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of enquiries",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/EnquiryResource")
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=48),
     *                 @OA\Property(property="search_id", type="integer", nullable=true, example=101)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error (e.g. invalid date format)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // ── Validate query params ──────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'status'      => 'nullable|string|in:submitted,pending,accepted,rejected,cancelled',
            'search'      => 'nullable|string|max:50',
            'date_filter' => 'nullable|string|in:last_week,last_month,last_year,custom',
            'from_date'   => 'nullable|date|required_if:date_filter,custom',
            'to_date'     => 'nullable|date|required_if:date_filter,custom|after_or_equal:from_date',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // ── Build query (mirrors web controller exactly) ───────────────────
        $query = Enquiry::where('customer_id', Auth::id())
            ->with(['items.hoarding']);

        // Filter: status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: search by enquiry ID
        $searchId = null;
        if ($request->filled('search')) {
            $raw      = trim($request->search);
            $searchId = preg_replace('/\D/', '', $raw); // strip non-digits

            if ($searchId !== '') {
                $query->where('id', (int) $searchId);
                $query->orderByRaw(
                    'CASE WHEN id = ? THEN 0 ELSE 1 END',
                    [(int) $searchId]
                );
            }
        }

        // Filter: date range
        if ($request->filled('date_filter')) {
            switch ($request->date_filter) {
                case 'last_week':
                    $query->where('created_at', '>=', Carbon::now()->subWeek());
                    break;

                case 'last_month':
                    $query->where('created_at', '>=', Carbon::now()->subMonth());
                    break;

                case 'last_year':
                    $query->where('created_at', '>=', Carbon::now()->subYear());
                    break;

                case 'custom':
                    if ($request->filled('from_date') && $request->filled('to_date')) {
                        $query->whereBetween('created_at', [
                            Carbon::parse($request->from_date)->startOfDay(),
                            Carbon::parse($request->to_date)->endOfDay(),
                        ]);
                    }
                    break;
            }
        }

        // Default sort
        $query->orderBy('created_at', 'desc');

        $perPage    = (int) $request->input('per_page', 10);
        $enquiries  = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'success' => true,
            'data'    => EnquiryResource::collection($enquiries),
            'meta'    => [
                'current_page' => $enquiries->currentPage(),
                'last_page'    => $enquiries->lastPage(),
                'per_page'     => $enquiries->perPage(),
                'total'        => $enquiries->total(),
                'search_id'    => $searchId !== '' ? (int) $searchId : null,
            ],
        ]);
    }


    // public function show(int $id)
    // {
    //     $enquiry = Enquiry::with([
    //         'customer',
    //         'items.hoarding.vendor',
    //         'offers'
    //     ])->findOrFail($id);

    //     return response()->json([
    //         'success' => true,
    //         'data'    => new EnquiryResource($enquiry),
    //     ]);
    // }

   public function show(int $id)
{
    $user = Auth::user();

    // Must be authenticated
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated'
        ], Response::HTTP_UNAUTHORIZED);
    }

    // First check ownership + existence
    $enquiry = Enquiry::with([
            'customer',
            'items.hoarding.vendor',
            'items.hoarding.ooh',
            'items.hoarding.doohScreen',
            'items.hoarding.vendor.vendorProfile',
            'offers',
            'items.package'
        ])
        ->where('id', $id)
        ->where('customer_id', $user->id)
        ->withVendorCount()
        ->first();

    if (! $enquiry) {
        return response()->json([
            'success' => false,
            'message' => 'Enquiry not found or access denied'
        ], Response::HTTP_NOT_FOUND);
    }

    // Then display
    return response()->json([
        'success' => true,
        'data' => new EnquiryItemResource($enquiry),
    ]);
}

    /**
     * Update enquiry status
     * PATCH /api/v1/enquiries/{id}/status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,accepted,rejected,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $enquiry = $this->service->find($id);

        if (!$enquiry) {
            return response()->json([
                'success' => false,
                'message' => 'Enquiry not found',
            ], 404);
        }

        // Check permissions
        $user = Auth::user();
        if (!$user->hasRole('admin') && !$user->hasRole('vendor')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Vendor can only update their own hoarding enquiries
        if ($user->hasRole('vendor') && $enquiry->hoarding->vendor_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $enquiry = $this->service
                ->find($id)
                ->load(['customer', 'items.hoarding.vendor']);
            // $this->service->updateStatus($id, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Enquiry status updated',
                'data'    => new EnquiryResource($enquiry),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
