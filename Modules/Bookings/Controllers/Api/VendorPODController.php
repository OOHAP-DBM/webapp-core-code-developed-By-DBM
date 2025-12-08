<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\BookingProof;
use App\Services\PODService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VendorPODController extends Controller
{
    protected PODService $podService;

    public function __construct(PODService $podService)
    {
        $this->podService = $podService;
    }

    /**
     * Get pending PODs for vendor
     * GET /api/v1/vendors/pod/pending
     */
    public function pendingPODs(): JsonResponse
    {
        try {
            $vendorId = auth()->id();
            $proofs = $this->podService->getPendingPODsForVendor($vendorId);

            $data = $proofs->map(function ($proof) {
                return [
                    'id' => $proof->id,
                    'booking_id' => $proof->booking_id,
                    'booking_reference' => $proof->booking->booking_reference ?? null,
                    'hoarding_name' => $proof->booking->hoarding->name ?? null,
                    'type' => $proof->type,
                    'uploaded_at' => $proof->uploaded_at?->toIso8601String(),
                    'uploaded_by' => $proof->uploader->name,
                    'distance_from_hoarding' => $proof->distance_from_hoarding,
                    'latitude' => $proof->latitude,
                    'longitude' => $proof->longitude,
                    'proof_url' => $proof->proof_url,
                    'thumbnail_url' => $proof->thumbnail_url,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => $proofs->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending PODs',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get POD statistics for vendor
     * GET /api/v1/vendors/pod/stats
     */
    public function stats(): JsonResponse
    {
        try {
            $vendorId = auth()->id();
            $stats = $this->podService->getPODStatsForVendor($vendorId);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch POD stats',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get specific POD details
     * GET /api/v1/vendors/pod/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $vendorId = auth()->id();
            
            $proof = BookingProof::with(['booking.hoarding', 'uploader', 'verifier'])
                ->whereHas('booking', function ($query) use ($vendorId) {
                    $query->where('vendor_id', $vendorId);
                })
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $proof->id,
                    'booking_id' => $proof->booking_id,
                    'booking_reference' => $proof->booking->booking_reference ?? null,
                    'hoarding' => [
                        'id' => $proof->booking->hoarding->id,
                        'name' => $proof->booking->hoarding->name,
                        'latitude' => $proof->booking->hoarding->latitude,
                        'longitude' => $proof->booking->hoarding->longitude,
                    ],
                    'type' => $proof->type,
                    'status' => $proof->status,
                    'uploaded_at' => $proof->uploaded_at?->toIso8601String(),
                    'uploaded_by' => [
                        'id' => $proof->uploader->id,
                        'name' => $proof->uploader->name,
                    ],
                    'verified_at' => $proof->verified_at?->toIso8601String(),
                    'verified_by' => $proof->verifier ? [
                        'id' => $proof->verifier->id,
                        'name' => $proof->verifier->name,
                    ] : null,
                    'verified_notes' => $proof->verified_notes,
                    'latitude' => $proof->latitude,
                    'longitude' => $proof->longitude,
                    'distance_from_hoarding' => $proof->distance_from_hoarding,
                    'proof_url' => $proof->proof_url,
                    'thumbnail_url' => $proof->thumbnail_url,
                    'metadata' => $proof->metadata,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'POD not found',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 404);
        }
    }

    /**
     * Approve POD
     * POST /api/v1/vendors/pod/{id}/approve
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $vendorId = auth()->id();
            
            $proof = BookingProof::whereHas('booking', function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })->findOrFail($id);

            $this->podService->approvePOD(
                proof: $proof,
                verifiedBy: $vendorId,
                notes: $request->input('notes')
            );

            Log::info('POD approved by vendor', [
                'proof_id' => $id,
                'vendor_id' => $vendorId,
                'booking_id' => $proof->booking_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'POD approved successfully. Booking is now active.',
                'data' => [
                    'proof_id' => $proof->id,
                    'booking_id' => $proof->booking_id,
                    'status' => 'approved',
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to approve POD', [
                'proof_id' => $id,
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : 'Failed to approve POD',
            ], 500);
        }
    }

    /**
     * Reject POD
     * POST /api/v1/vendors/pod/{id}/reject
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|min:10|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $vendorId = auth()->id();
            
            $proof = BookingProof::whereHas('booking', function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })->findOrFail($id);

            $this->podService->rejectPOD(
                proof: $proof,
                verifiedBy: $vendorId,
                notes: $request->input('notes')
            );

            Log::info('POD rejected by vendor', [
                'proof_id' => $id,
                'vendor_id' => $vendorId,
                'booking_id' => $proof->booking_id,
                'reason' => $request->input('notes'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'POD rejected. Mounter will need to upload a new proof.',
                'data' => [
                    'proof_id' => $proof->id,
                    'booking_id' => $proof->booking_id,
                    'status' => 'rejected',
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reject POD', [
                'proof_id' => $id,
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : 'Failed to reject POD',
            ], 500);
        }
    }
}
