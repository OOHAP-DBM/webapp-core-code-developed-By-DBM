<?php

namespace Modules\Offers\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Offers\Services\OfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller
{
    protected OfferService $service;

    public function __construct(OfferService $service)
    {
        $this->service = $service;
    }

    /**
     * Create a draft offer for an enquiry
     * POST /api/v1/enquiries/{enquiryId}/offers
     */
    public function createDraft(Request $request, int $enquiryId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric|min:0',
            'price_type' => 'required|in:total,monthly,weekly,daily',
            'description' => 'nullable|string|max:2000',
            'valid_until' => 'nullable|date|after:now',
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
            $data['enquiry_id'] = $enquiryId;

            $offer = $this->service->createOffer($data);

            return response()->json([
                'success' => true,
                'message' => 'Offer draft created successfully',
                'data' => $offer->load(['enquiry', 'vendor']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create offer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send an offer (draft -> sent)
     * PATCH /api/v1/offers/{id}/send
     */
    public function send(int $id): JsonResponse
    {
        try {
            $offer = $this->service->find($id);

            if (!$offer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer not found',
                ], 404);
            }

            // Check if user owns this offer
            if ($offer->vendor_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $offer = $this->service->sendOffer($id);

            return response()->json([
                'success' => true,
                'message' => 'Offer sent to customer successfully',
                'data' => $offer,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send offer',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Accept an offer (customer)
     * PATCH /api/v1/offers/{id}/accept
     */
    public function accept(int $id): JsonResponse
    {
        try {
            $offer = $this->service->find($id);

            if (!$offer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer not found',
                ], 404);
            }

            // Check if user is the customer for this enquiry
            if ($offer->enquiry->customer_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $offer = $this->service->acceptOffer($id);

            return response()->json([
                'success' => true,
                'message' => 'Offer accepted successfully',
                'data' => $offer,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept offer',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reject an offer (customer)
     * PATCH /api/v1/offers/{id}/reject
     */
    public function reject(int $id): JsonResponse
    {
        try {
            $offer = $this->service->find($id);

            if (!$offer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer not found',
                ], 404);
            }

            // Check if user is the customer
            if ($offer->enquiry->customer_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $this->service->rejectOffer($id);

            return response()->json([
                'success' => true,
                'message' => 'Offer rejected successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject offer',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get all offers for authenticated user
     * GET /api/v1/offers
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasRole('vendor')) {
            $offers = $this->service->getVendorOffers();
        } elseif ($user->hasRole('customer')) {
            $offers = $this->service->getCustomerOffers();
        } elseif ($user->hasRole('admin')) {
            $offers = $this->service->getAll($request->only(['status', 'enquiry_id', 'vendor_id']));
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $offers,
            'total' => $offers->count(),
        ]);
    }

    /**
     * Get offer by ID
     * GET /api/v1/offers/{id}
     */
    public function show(int $id): JsonResponse
    {
        $offer = $this->service->find($id);

        if (!$offer) {
            return response()->json([
                'success' => false,
                'message' => 'Offer not found',
            ], 404);
        }

        // Check if user can view this offer
        if (!$this->service->canView($offer)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $offer,
        ]);
    }

    /**
     * Get offers for a specific enquiry
     * GET /api/v1/enquiries/{enquiryId}/offers
     */
    public function getByEnquiry(int $enquiryId): JsonResponse
    {
        $offers = $this->service->getByEnquiry($enquiryId);

        return response()->json([
            'success' => true,
            'data' => $offers,
            'total' => $offers->count(),
        ]);
    }
}

