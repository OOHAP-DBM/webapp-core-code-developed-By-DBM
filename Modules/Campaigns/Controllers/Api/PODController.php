<?php

namespace Modules\Campaigns\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Campaigns\Services\CampaignService;
use Modules\POD\Models\PODSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;

class PODController extends Controller
{
    protected CampaignService $campaignService;

    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }

    /**
     * Submit POD (Mounter)
     * 
     * POST /api/v1/mounter/bookings/{booking_id}/pod/submit
     */
    public function submit(Request $request, int $bookingId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'files' => 'required|array|min:1|max:10',
                'files.*' => 'required|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:51200', // Max 50MB per file
                'notes' => 'nullable|string|max:1000',
            ]);

            $podSubmission = $this->campaignService->submitPOD(
                $bookingId,
                Auth::id(),
                $validated['files'],
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'POD submitted successfully. Waiting for vendor approval.',
                'data' => $podSubmission,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit POD',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get POD submissions for a booking (Customer view)
     * 
     * GET /api/v1/customer/bookings/{booking_id}/pod
     */
    public function index(int $bookingId): JsonResponse
    {
        try {
            $pods = $this->campaignService->getPODsForBooking($bookingId);

            return response()->json([
                'success' => true,
                'data' => $pods,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch POD submissions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get specific POD submission
     * 
     * GET /api/v1/customer/bookings/{booking_id}/pod/{id}
     */
    public function show(int $bookingId, int $id): JsonResponse
    {
        try {
            $pod = PODSubmission::with(['booking', 'submittedBy', 'approvedBy', 'rejectedBy'])
                ->where('booking_id', $bookingId)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $pod,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'POD not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get pending PODs for vendor
     * 
     * GET /api/v1/vendor/pod/pending
     */
    public function getPendingForVendor(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $pods = $this->campaignService->getPendingPODsForVendor(Auth::id(), $perPage);

            return response()->json([
                'success' => true,
                'data' => $pods->items(),
                'pagination' => [
                    'current_page' => $pods->currentPage(),
                    'per_page' => $pods->perPage(),
                    'total' => $pods->total(),
                    'last_page' => $pods->lastPage(),
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending PODs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve POD (Vendor)
     * 
     * POST /api/v1/vendor/pod/{id}/approve
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'approval_notes' => 'nullable|string|max:500',
            ]);

            $podSubmission = $this->campaignService->approvePOD(
                $id,
                Auth::id(),
                $validated['approval_notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'POD approved. Campaign has started!',
                'data' => $podSubmission,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve POD',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reject POD (Vendor)
     * 
     * POST /api/v1/vendor/pod/{id}/reject
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'rejection_reason' => 'required|string|max:500',
            ]);

            $podSubmission = $this->campaignService->rejectPOD(
                $id,
                Auth::id(),
                $validated['rejection_reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'POD rejected. Mounter will need to resubmit.',
                'data' => $podSubmission,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject POD',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
