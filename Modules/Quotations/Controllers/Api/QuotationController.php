<?php

namespace Modules\Quotations\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Quotations\Services\QuotationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuotationController extends Controller
{
    protected QuotationService $service;

    public function __construct(QuotationService $service)
    {
        $this->service = $service;
    }

    /**
     * Get quotations for an offer
     * GET /api/v1/offers/{offerId}/quotations
     */
    public function getByOffer(int $offerId): JsonResponse
    {
        try {
            $quotations = $this->service->getByOffer($offerId);

            return response()->json([
                'success' => true,
                'data' => $quotations,
                'total' => $quotations->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quotations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create quotation for an offer
     * POST /api/v1/offers/{offerId}/quotations
     */
    public function createForOffer(Request $request, int $offerId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();
            $data['offer_id'] = $offerId;

            $quotation = $this->service->createQuotation($data);

            return response()->json([
                'success' => true,
                'message' => 'Quotation created successfully',
                'data' => $quotation->load(['offer', 'customer', 'vendor']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create quotation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create quotation from accepted offer
     * POST /api/v1/offers/{offerId}/quotations/auto
     */
    public function createFromOffer(Request $request, int $offerId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $quotation = $this->service->createFromOffer($offerId, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Quotation generated from offer successfully',
                'data' => $quotation->load(['offer', 'customer', 'vendor']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate quotation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send quotation (draft -> sent)
     * PATCH /api/v1/quotations/{id}/send
     */
    public function send(int $id): JsonResponse
    {
        try {
            $quotation = $this->service->find($id);

            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found',
                ], 404);
            }

            // Check if user owns this quotation
            if ($quotation->vendor_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $quotation = $this->service->sendQuotation($id);

            return response()->json([
                'success' => true,
                'message' => 'Quotation sent to customer successfully',
                'data' => $quotation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send quotation',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Approve quotation (customer)
     * PATCH /api/v1/quotations/{id}/approve
     */
    public function approve(int $id): JsonResponse
    {
        try {
            $quotation = $this->service->find($id);

            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found',
                ], 404);
            }

            // Check if user is the customer
            if ($quotation->customer_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $quotation = $this->service->approveQuotation($id);

            return response()->json([
                'success' => true,
                'message' => 'Quotation approved successfully',
                'data' => $quotation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve quotation',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reject quotation (customer)
     * PATCH /api/v1/quotations/{id}/reject
     */
    public function reject(int $id): JsonResponse
    {
        try {
            $quotation = $this->service->find($id);

            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found',
                ], 404);
            }

            // Check if user is the customer
            if ($quotation->customer_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $quotation = $this->service->rejectQuotation($id);

            return response()->json([
                'success' => true,
                'message' => 'Quotation rejected successfully',
                'data' => $quotation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject quotation',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create revision of quotation
     * POST /api/v1/quotations/{id}/revise
     */
    public function revise(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'nullable|array',
            'items.*.description' => 'required_with:items|string|max:500',
            'items.*.quantity' => 'required_with:items|numeric|min:0',
            'items.*.rate' => 'required_with:items|numeric|min:0',
            'items.*.amount' => 'required_with:items|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $quotation = $this->service->find($id);

            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found',
                ], 404);
            }

            // Check if user owns this quotation
            if ($quotation->vendor_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $newQuotation = $this->service->createRevision($id, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Quotation revision created successfully',
                'data' => $newQuotation->load(['offer', 'customer', 'vendor']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create revision',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get all quotations for authenticated user
     * GET /api/v1/quotations
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasRole('vendor')) {
            $quotations = $this->service->getVendorQuotations();
        } elseif ($user->hasRole('customer')) {
            $quotations = $this->service->getCustomerQuotations();
        } elseif ($user->hasRole('admin')) {
            $quotations = $this->service->getAll($request->only(['status', 'offer_id', 'vendor_id', 'customer_id']));
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $quotations,
            'total' => $quotations->count(),
        ]);
    }

    /**
     * Get quotation by ID
     * GET /api/v1/quotations/{id}
     */
    public function show(int $id): JsonResponse
    {
        $quotation = $this->service->find($id);

        if (!$quotation) {
            return response()->json([
                'success' => false,
                'message' => 'Quotation not found',
            ], 404);
        }

        // Check if user can view this quotation
        if (!$this->service->canView($quotation)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $quotation,
        ]);
    }
}

