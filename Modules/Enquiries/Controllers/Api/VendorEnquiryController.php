<?php
namespace Modules\Enquiries\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Enquiries\Models\Enquiry;
use Modules\Enquiries\Http\Resources\Api\EnquiryResource;
use Modules\Enquiries\Services\EnquiryService;
use Modules\Enquiries\Http\Resources\Api\EnquiryItemResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
class VendorEnquiryController extends Controller
{
    protected EnquiryService $service;
    protected GracePeriodService $gracePeriodService;

    public function __construct(EnquiryService $service)
    {
        $this->service = $service;
       
    }
    /* =====================================================
     | INDEX — paginated + filtered
     ===================================================== */

    /**
     * @OA\Get(
     *     path="/enquiries/vendor/all",
     *     summary="List enquiries for vendor's hoardings",
     *     description="Returns paginated enquiries that contain at least one item belonging to the authenticated vendor's hoardings. Supports filtering by status, search by enquiry ID, hoarding ID, customer name/mobile, date presets, and custom date range.",
     *     tags={"Vendor Enquiries"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by enquiry status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"submitted","pending","accepted","rejected","cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by enquiry ID (digits extracted automatically)",
     *         required=false,
     *         @OA\Schema(type="string", example="ENQ-101")
     *     ),
     *     @OA\Parameter(
     *         name="hoarding_id",
     *         in="query",
     *         description="Filter by a specific hoarding ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Parameter(
     *         name="customer_name",
     *         in="query",
     *         description="Filter by customer name (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="John")
     *     ),
     *     @OA\Parameter(
     *         name="customer_mobile",
     *         in="query",
     *         description="Filter by customer mobile number (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="98765")
     *     ),
     *     @OA\Parameter(
     *         name="date_filter",
     *         in="query",
     *         description="Preset date range filter",
     *         required=false,
     *         @OA\Schema(type="string", enum={"last_week","last_month","last_year","custom"})
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
     *         description="Results per page (default: 10, max: 100)",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of vendor enquiries",
     *         @OA\JsonContent(
     *             @OA\Property(property="success",     type="boolean", example=true),
     *             @OA\Property(property="viewer_type", type="string",  example="owner"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/EnquiryResource")
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page",    type="integer", example=4),
     *                 @OA\Property(property="per_page",     type="integer", example=10),
     *                 @OA\Property(property="total",        type="integer", example=36),
     *                 @OA\Property(property="search_id",    type="integer", nullable=true, example=101)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string",  example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden — not a vendor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string",  example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors",  type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->hasRole('vendor')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        // ── Validate query params ──────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'status'          => 'nullable|string|in:submitted,pending,accepted,rejected,cancelled',
            'search'          => 'nullable|string|max:50',
            'hoarding_id'     => 'nullable|integer|exists:hoardings,id',
            'customer_name'   => 'nullable|string|max:100',
            'customer_mobile' => 'nullable|string|max:20',
            'date_filter'     => 'nullable|string|in:last_week,last_month,last_year,custom',
            'from_date'       => 'nullable|date|required_if:date_filter,custom',
            'to_date'         => 'nullable|date|required_if:date_filter,custom|after_or_equal:from_date',
            'per_page'        => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // ── Base query — only enquiries that touch this vendor's hoardings ──
        $query = Enquiry::whereHas('items.hoarding', function ($q) use ($user) {
                $q->where('vendor_id', $user->id);
            })
            ->with([
                'customer',
                'items' => function ($q) use ($user) {
                    $q->whereHas('hoarding', fn($h) => $h->where('vendor_id', $user->id));
                },
                'items.hoarding',
            ]);

        // ── Filter: status ─────────────────────────────────────────────────
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ── Filter: search by enquiry ID ───────────────────────────────────
        $searchId = null;
        if ($request->filled('search')) {
            $searchId = preg_replace('/\D/', '', trim($request->search));
            if ($searchId !== '') {
                $query->where('id', (int) $searchId);
                $query->orderByRaw(
                    'CASE WHEN id = ? THEN 0 ELSE 1 END',
                    [(int) $searchId]
                );
            }
        }

        // ── Filter: specific hoarding ──────────────────────────────────────
        if ($request->filled('hoarding_id')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('hoarding_id', $request->hoarding_id);
            });
        }

        // ── Filter: customer name (stored in enquiry_items.meta->customer_name) ──
        if ($request->filled('customer_name')) {
            $name = $request->customer_name;
            $query->whereHas('items', function ($q) use ($name) {
                $q->whereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(meta, '$.customer_name')) LIKE ?",
                    ["%{$name}%"]
                );
            });
        }

        // ── Filter: customer mobile ────────────────────────────────────────
        if ($request->filled('customer_mobile')) {
            $mobile = $request->customer_mobile;
            $query->where(function ($q) use ($mobile) {
                // Check top-level contact_number column
                $q->where('contact_number', 'LIKE', "%{$mobile}%")
                  // Also check meta in items
                  ->orWhereHas('items', function ($iq) use ($mobile) {
                      $iq->whereRaw(
                          "JSON_UNQUOTE(JSON_EXTRACT(meta, '$.customer_mobile')) LIKE ?",
                          ["%{$mobile}%"]
                      );
                  });
            });
        }

        // ── Filter: date range ─────────────────────────────────────────────
        // ── Filter: date range ─────────────────────────────────────────────
        if ($request->filled('date_filter')) {

            $now = Carbon::now();

            switch ($request->date_filter) {

                case 'last_week':
                    $query->whereBetween('created_at', [
                        $now->copy()->subWeek()->startOfWeek(),
                        $now->copy()->subWeek()->endOfWeek(),
                    ]);
                    break;

                case 'last_month':
                    $query->whereBetween('created_at', [
                        $now->copy()->subMonth()->startOfMonth(),
                        $now->copy()->subMonth()->endOfMonth(),
                    ]);
                    break;

                case 'last_year':
                    $query->whereBetween('created_at', [
                        $now->copy()->subYear()->startOfYear(),
                        $now->copy()->subYear()->endOfYear(),
                    ]);
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

        // ── Default sort ───────────────────────────────────────────────────
        $query->orderBy('created_at', 'desc');

        $perPage   = (int) $request->input('per_page', 10);
        $enquiries = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'success'     => true,
            'viewer_type' => 'owner',
            'data'        => EnquiryResource::collection($enquiries),
            'meta'        => [
                'current_page' => $enquiries->currentPage(),
                'last_page'    => $enquiries->lastPage(),
                'per_page'     => $enquiries->perPage(),
                'total'        => $enquiries->total(),
                'search_id'    => ($searchId !== null && $searchId !== '') ? (int) $searchId : null,
            ],
        ]);
    }
    /**
     * @OA\Get(
     *     path="/enquiries/vendor/{id}",
     *     summary="Get a single vendor enquiry by ID",
     *     description="Returns a single enquiry if it belongs to the authenticated vendor. Loads only vendor-specific data.",
     *     tags={"Vendor Enquiries"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Enquiry ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=101)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Enquiry found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/EnquiryItemResource"),
     *             @OA\Property(property="viewer_type", type="string", example="owner")
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
     *         response=404,
     *         description="Enquiry not found or access denied",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Enquiry not found or access denied")
     *         )
     *     )
     * )
     */
    public function show(int $id)
    {
        $user = Auth::user();

        // Must be authenticated
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // First check: enquiry exists AND has at least one item for this vendor
        $enquiry = Enquiry::where('id', $id)
            ->whereHas('items.hoarding', function ($q) use ($user) {
                $q->where('vendor_id', $user->id);
            })
            ->first();

        if (! $enquiry) {
            return response()->json([
                'success' => false,
                'message' => 'Enquiry not found or access denied'
            ], Response::HTTP_NOT_FOUND);
        }

        // Load ONLY vendor-specific data
        $enquiry->load([
            'customer',
            'items' => function ($q) use ($user) {
                $q->whereHas('hoarding', function ($h) use ($user) {
                    $h->where('vendor_id', $user->id);
                });
            },
            'items.hoarding.vendor',
            'items.hoarding.ooh',
            'items.hoarding.doohScreen',
            'items.hoarding.vendor.vendorProfile',
            'items.package',
        ]);

        return response()->json([
            'success' => true,
            'data' => (new EnquiryItemResource($enquiry))
                ->additional(['viewer_type' => 'owner']),
        ]);
    }
}
