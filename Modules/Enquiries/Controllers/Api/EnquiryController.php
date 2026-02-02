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

    /**
     * Get all enquiries for authenticated user
     * GET /api/v1/enquiries
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasRole('customer')) {
            $enquiries = $this->service->getMyEnquiries();
        } elseif ($user->hasRole('vendor')) {
            $enquiries = $this->service->getVendorEnquiries();
        } elseif ($user->hasRole('admin')) {
            $enquiries = $this->service->getAll($request->only(['status', 'hoarding_id', 'customer_id']));
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $enquiries,
            'total' => $enquiries->count(),
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
            $this->service->updateStatus($id, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Enquiry status updated',
                'data' => $this->service->find($id),
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
