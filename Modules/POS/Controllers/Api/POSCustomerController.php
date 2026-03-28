<?php

namespace Modules\POS\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\POS\Events\PosCustomerCreated;
use Modules\POS\Models\POSBooking;
use Modules\POS\Models\PosCustomer;

/**
 * @OA\Tag(
 *     name="POS",
 *     description="POS customer management APIs"
 * )
 */
class POSCustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:vendor|admin|superadmin']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers (mirrors VendorPosController logic)
    // ──────────────────────────────────────────────────────────────────────────

    private function resolveEffectiveVendorId(Request $request): int
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $isAdmin = method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['admin', 'superadmin', 'super_admin']);

        if (!$isAdmin) {
            return (int) $user->id;
        }

        // Admin: honour explicit vendor_id in request body / query string
        $requestedVendorId = $request->input('vendor_id') ?? $request->query('vendor_id');

        if (!empty($requestedVendorId)) {
            $vendor = User::query()
                ->whereKey((int) $requestedVendorId)
                ->whereHas('roles', fn ($q) => $q->where('name', 'vendor'))
                ->first();

            if (!$vendor) {
                abort(422, 'Invalid vendor selected for POS context.');
            }

            return (int) $vendor->id;
        }

        // Fallback: first vendor in DB
        $fallback = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'vendor'))
            ->orderBy('id')
            ->value('id');

        if (!$fallback) {
            abort(422, 'No vendor available for POS context.');
        }

        return (int) $fallback;
    }

   /**
     * @OA\Post(
     *     path="/pos/vendor/customers",
     *     operationId="storePosCustomer",
     *     tags={"POS Customers"},
     *     summary="Create POS customer",
     *     description="Creates a new customer under POS context.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","phone","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Aviral Pandit"),
     *             @OA\Property(property="email", type="string", format="email", example="aviralpandit90@gmail.com"),
     *             @OA\Property(property="phone", type="string", example="9876543210"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123"),
     *             @OA\Property(property="gstin", type="string", example="07ABCDE1234F1Z5"),
     *             @OA\Property(property="business_name", type="string", example="ABC Ads Pvt Ltd"),
     *             @OA\Property(property="city", type="string", example="Noida"),
     *             @OA\Property(property="state", type="string", example="Uttar Pradesh"),
     *             @OA\Property(property="pincode", type="string", example="201301"),
     *             @OA\Property(property="country", type="string", example="India"),
     *             @OA\Property(property="vendor_id", type="integer", example=12)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Customer created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Customer created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=101),
     *                 @OA\Property(property="name", type="string", example="Aviral Pandit"),
     *                 @OA\Property(property="email", type="string", example="aviralpandit90@gmail.com"),
     *                 @OA\Property(property="phone", type="string", example="9876543210"),
     *                 @OA\Property(property="gstin", type="string", nullable=true, example="07ABCDE1234F1Z5"),
     *                 @OA\Property(property="address", type="string", nullable=true, example="Noida, Uttar Pradesh, - 201301, India"),
     *                 @OA\Property(property="status", type="string", example="active")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create customer"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name'                  => 'required|string|max:255',
                'email'                 => 'required|email|max:255|unique:users,email',
                'phone'                 => 'required|string|max:20|unique:users,phone',
                'password'              => 'required|string|min:6|confirmed',   // needs password_confirmation
                'gstin'                 => 'nullable|string|max:15|unique:users,gstin',
                'business_name'         => 'nullable|string|max:255',
                'city'                  => 'nullable|string|max:100',
                'state'                 => 'nullable|string|max:100',
                'pincode'               => 'nullable|string|max:10',
                'country'               => 'nullable|string|max:100',
                // Admin-only: optionally scope the customer to a specific vendor
                'vendor_id'             => 'nullable|integer|exists:users,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            $vendorId = $this->resolveEffectiveVendorId($request);

            $user = DB::transaction(function () use ($request, $vendorId) {
                $fullAddress = trim(
                    implode(', ', array_filter([
                        $request->city,
                        $request->state,
                        $request->pincode ? '- ' . $request->pincode : null,
                        $request->country,
                    ])),
                    ', '
                );

                /** @var User $user */
                $user = User::create([
                    'name'        => $request->name,
                    'email'       => $request->email,
                    'phone'       => $request->phone,
                    'password'    => Hash::make($request->password),
                    'active_role' => 'customer',
                    'gstin'       => $request->gstin,
                    'address'     => $fullAddress ?: null,
                    'status'      => 'active',
                ]);

                $user->assignRole('customer');

                $user->posProfile()->create([
                    'vendor_id'     => $vendorId,
                    'created_by'    => Auth::id(),
                    'gstin'         => $request->gstin,
                    'business_name' => $request->business_name,
                    'address'       => $fullAddress ?: null,
                ]);

                return $user;
            });

            if (!$user || !$user->id) {
                throw new \RuntimeException('Customer record could not be created.');
            }

            Log::info('POS API: customer created', [
                'vendor_id'      => $vendorId,
                'customer_id'    => $user->id,
                'customer_email' => $user->email,
            ]);

            // Post-commit: events, notifications, emails (non-fatal)
            DB::afterCommit(function () use ($user) {
                try {
                    event(new PosCustomerCreated($user, Auth::user()));
                } catch (\Throwable $e) {
                    Log::warning('POS API: PosCustomerCreated event failed', [
                        'customer_id' => $user->id,
                        'error'       => $e->getMessage(),
                    ]);
                }

                // In-app notification to customer
                try {
                    if (method_exists($user, 'notify')) {
                        $user->notify(new \App\Notifications\PosCustomerCreatedNotification($user));
                    }
                } catch (\Throwable $e) {
                    Log::warning('POS API: in-app notification failed', [
                        'customer_id' => $user->id,
                        'error'       => $e->getMessage(),
                    ]);
                }

                // Welcome email to customer
                try {
                    \Mail::to($user->email)->send(new \App\Mail\PosCustomerWelcome($user));
                } catch (\Throwable $e) {
                    Log::warning('POS API: welcome email failed', [
                        'customer_id' => $user->id,
                        'error'       => $e->getMessage(),
                    ]);
                }

                // Push notification to customer
                try {
                    send(
                        $user,
                        'Account Created Successfully',
                        'Welcome to OOHApp! Your customer account has been created.',
                        [
                            'type'          => 'pos_customer_account_created',
                            'customer_id'   => $user->id,
                            'customer_name' => $user->name,
                            'source'        => 'pos_system',
                        ]
                    );
                } catch (\Throwable $e) {
                    Log::warning('POS API: push notification to customer failed', [
                        'customer_id' => $user->id,
                        'error'       => $e->getMessage(),
                    ]);
                }

                // Push notification to vendor
                try {
                    $vendor = Auth::user();
                    send(
                        $vendor,
                        'New POS Customer Created',
                        "Customer {$user->name} has been successfully created",
                        [
                            'type'           => 'pos_customer_created',
                            'customer_id'    => $user->id,
                            'customer_name'  => $user->name,
                            'customer_email' => $user->email,
                            'customer_phone' => $user->phone,
                            'source'         => 'pos_system',
                        ]
                    );
                } catch (\Throwable $e) {
                    Log::warning('POS API: push notification to vendor failed', [
                        'vendor_id'   => Auth::id(),
                        'customer_id' => $user->id,
                        'error'       => $e->getMessage(),
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data'    => $user->only([
                    'id', 'name', 'email', 'phone', 'gstin', 'address', 'status',
                ]),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Catch late-stage unique constraint violations (race conditions)
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('POS API: create customer failed', [
                'vendor_id' => Auth::id(),
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer. Please try again.',
            ], 500);
        }
    }

   /**
     * @OA\Get(
     *     path="/pos/vendor/customers",
     *     operationId="listPosCustomers",
     *     tags={"POS Customers"},
     *     summary="List POS customers",
     *     description="Returns paginated list of POS customers for resolved vendor context.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Search by name/email/phone"
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="vendor_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Admin can scope results to a specific vendor"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customers fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=136),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=101),
     *                         @OA\Property(property="name", type="string", example="ABC Ads Pvt Ltd"),
     *                         @OA\Property(property="email", type="string", example="aviralpandit90@gmail.com"),
     *                         @OA\Property(property="phone", type="string", example="9876543210"),
     *                         @OA\Property(property="gstin", type="string", nullable=true, example="07ABCDE1234F1Z5"),
     *                         @OA\Property(property="total_bookings", type="integer", example=8),
     *                         @OA\Property(property="total_spent", type="number", format="float", example=1250000),
     *                         @OA\Property(property="last_booking_at", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="is_active", type="boolean", example=true)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Failed to fetch customers")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $vendorId = $this->resolveEffectiveVendorId($request);

            // Collect all user IDs that have ever interacted with this vendor
            $bookingCustomerIds = POSBooking::where('vendor_id', $vendorId)
                ->whereNotNull('customer_id')
                ->pluck('customer_id');

            $posProfileUserIds = PosCustomer::where('vendor_id', $vendorId)
                ->whereNotNull('user_id')
                ->pluck('user_id');

            $allUserIds = $bookingCustomerIds
                ->merge($posProfileUserIds)
                ->unique()
                ->filter()
                ->values();

            $query = User::whereIn('id', $allUserIds)
                ->with(['posProfile' => fn ($q) => $q->where('vendor_id', $vendorId)]);

            if ($request->filled('search')) {
                $term = $request->get('search');
                $query->where(function ($q) use ($term) {
                    $q->where('name',  'like', "%{$term}%")
                      ->orWhere('email', 'like', "%{$term}%")
                      ->orWhere('phone', 'like', "%{$term}%");
                });
            }

            $perPage  = max(1, min((int) $request->get('per_page', 15), 100));
            $paginated = $query->orderBy('name')->paginate($perPage);

            $items = collect($paginated->items())->map(function (User $user) use ($vendorId) {
                $bookings       = POSBooking::where('vendor_id', $vendorId)
                                            ->where('customer_id', $user->id)
                                            ->get(['total_amount', 'created_at']);
                $totalBookings  = $bookings->count();
                $totalSpent     = $bookings->sum('total_amount');
                $lastBookingAt  = $bookings->max('created_at');
                $displayName    = $user->posProfile?->business_name ?? $user->name;

                return [
                    'id'              => $user->id,
                    'name'            => $displayName,
                    'email'           => $user->email,
                    'phone'           => $user->phone,
                    'gstin'           => $user->gstin,
                    'total_bookings'  => $totalBookings,
                    'total_spent'     => (float) $totalSpent,
                    'last_booking_at' => $lastBookingAt,
                    'is_active'       => $totalBookings > 0,
                    'account_status'  => $user->status ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'data'    => [
                    'data'         => $items,
                    'current_page' => $paginated->currentPage(),
                    'per_page'     => $paginated->perPage(),
                    'total'        => $paginated->total(),
                    'last_page'    => $paginated->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('POS API: list customers failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customers.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/pos/vendor/customers/{id}",
     *     operationId="showPosCustomer",
     *     tags={"POS Customers"},
     *     summary="Get POS customer details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="vendor_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Admin can scope to a specific vendor"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=101),
     *                 @OA\Property(property="name", type="string", example="ABC Ads Pvt Ltd"),
     *                 @OA\Property(property="email", type="string", example="aviralpandit90@gmail.com"),
     *                 @OA\Property(property="phone", type="string", example="9876543210"),
     *                 @OA\Property(property="gstin", type="string", nullable=true),
     *                 @OA\Property(property="address", type="string", nullable=true),
     *                 @OA\Property(property="total_bookings", type="integer", example=8),
     *                 @OA\Property(property="total_spent", type="number", format="float", example=1250000),
     *                 @OA\Property(property="last_booking_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="pos_profile", type="object", nullable=true),
     *                 @OA\Property(property="bookings", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Customer not found"),
     *     @OA\Response(response=500, description="Failed to fetch customer")
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $vendorId = $this->resolveEffectiveVendorId($request);

            $user = User::with(['posProfile' => fn ($q) => $q->where('vendor_id', $vendorId)])
                        ->findOrFail($id);

            $bookings      = POSBooking::where('vendor_id', $vendorId)
                                       ->where('customer_id', $user->id)
                                       ->get();
            $totalBookings = $bookings->count();
            $totalSpent    = $bookings->sum('total_amount');
            $lastBookingAt = $bookings->max('created_at');
            $displayName   = $user->posProfile?->business_name ?? $user->name;

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'              => $user->id,
                    'name'            => $displayName,
                    'email'           => $user->email,
                    'phone'           => $user->phone,
                    'gstin'           => $user->gstin,
                    'address'         => $user->address,
                    'total_bookings'  => $totalBookings,
                    'total_spent'     => (float) $totalSpent,
                    'last_booking_at' => $lastBookingAt,
                    'is_active'       => $totalBookings > 0,
                    'pos_profile'     => $user->posProfile,
                    'bookings'        => $bookings,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        } catch (\Throwable $e) {
            Log::error('POS API: show customer failed', ['customer_id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customer.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/pos/vendor/customers/search",
     *     operationId="searchPosCustomers",
     *     tags={"POS Customers"},
     *     summary="Search POS customers",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", minLength=2),
     *         description="Search term (name/email/phone)"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="count", type="integer", example=3),
     *             @OA\Property(property="message", type="string", nullable=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=101),
     *                     @OA\Property(property="name", type="string", example="Aviral Pandit"),
     *                     @OA\Property(property="email", type="string", example="aviralpandit90@gmail.com"),
     *                     @OA\Property(property="phone", type="string", example="9876543210"),
     *                     @OA\Property(property="gstin", type="string", nullable=true),
     *                     @OA\Property(property="address", type="string", nullable=true),
     *                     @OA\Property(property="billing_address", type="string", nullable=true),
     *                     @OA\Property(property="billing_city", type="string", nullable=true),
     *                     @OA\Property(property="billing_state", type="string", nullable=true),
     *                     @OA\Property(property="billing_pincode", type="string", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Failed to search customers")
     * )
    */
    public function search(Request $request): JsonResponse
    {
        try {
            $term = trim((string) $request->get('search', ''));

            if (strlen($term) < 2) {
                return response()->json([
                    'success' => true,
                    'data'    => [],
                    'message' => 'Search term must be at least 2 characters.',
                ]);
            }

            $customers = User::whereHas('roles', fn ($q) => $q->where('name', 'customer'))
                ->where(function ($q) use ($term) {
                    $q->where('name',  'like', "%{$term}%")
                      ->orWhere('email', 'like', "%{$term}%")
                      ->orWhere('phone', 'like', "%{$term}%");
                })
                ->select([
                    'id', 'name', 'email', 'phone', 'gstin',
                    'address', 'billing_address', 'billing_city',
                    'billing_state', 'billing_pincode',
                ])
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $customers,
                'count'   => $customers->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error('POS API: customer search failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to search customers.',
            ], 500);
        }
    }
}