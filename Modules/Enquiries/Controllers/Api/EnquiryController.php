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
    public function store(Request $request): JsonResponse
    {
        $hoarding = Hoarding::findOrFail($request->hoarding_id);
        
        $validator = Validator::make($request->all(), [
            'hoarding_id' => 'required|exists:hoardings,id',
            'preferred_start_date' => 'required|date|after_or_equal:today',
            'preferred_end_date' => 'required|date|after:preferred_start_date',
            'duration_type' => 'required|in:days,weeks,months',
            'message' => 'nullable|string|max:1000',
        ]);

        // Add grace period validation
        $this->gracePeriodService->addValidationRule($validator, 'preferred_start_date', $hoarding);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $enquiry = $this->service->createEnquiry($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Enquiry submitted successfully',
                'data' => $enquiry->load(['customer', 'hoarding']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create enquiry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get enquiry by ID
     * GET /api/v1/enquiries/{id}
     */
    public function show(int $id): JsonResponse
    {
        $enquiry = $this->service->find($id);

        if (!$enquiry) {
            return response()->json([
                'success' => false,
                'message' => 'Enquiry not found',
            ], 404);
        }

        // Check if user can view this enquiry
        if (!$this->service->canView($enquiry)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $enquiry,
        ]);
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
