<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use Modules\Bookings\Models\Booking;
use App\Services\PODService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PODController extends Controller
{
    protected PODService $podService;

    public function __construct(PODService $podService)
    {
        $this->podService = $podService;
    }

    /**
     * Upload POD for a booking
     * POST /api/v1/staff/bookings/{id}/upload-pod
     */
    public function uploadPOD(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4,mov|max:51200', // Max 50MB
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'device_info' => 'nullable|string',
            'gps_accuracy' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $booking = Booking::findOrFail($id);

            // Check if booking is confirmed
            if ($booking->status !== 'confirmed') {
                return response()->json([
                    'success' => false,
                    'message' => 'POD can only be uploaded for confirmed bookings',
                ], 400);
            }

            $file = $request->file('file');
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');

            $metadata = [
                'device_info' => $request->input('device_info'),
                'gps_accuracy' => $request->input('gps_accuracy'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];

            $proof = $this->podService->uploadPOD(
                booking: $booking,
                uploadedBy: auth()->id(),
                file: $file,
                latitude: $latitude,
                longitude: $longitude,
                metadata: $metadata
            );

            return response()->json([
                'success' => true,
                'message' => 'POD uploaded successfully. Awaiting vendor approval.',
                'data' => [
                    'proof_id' => $proof->id,
                    'booking_id' => $booking->id,
                    'status' => $proof->status,
                    'uploaded_at' => $proof->uploaded_at->toIso8601String(),
                    'distance_from_hoarding' => $proof->distance_from_hoarding,
                    'proof_url' => $proof->proof_url,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to upload POD', [
                'booking_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : 'Failed to upload POD',
            ], 500);
        }
    }

    /**
     * Get POD status for a booking
     * GET /api/v1/staff/bookings/{id}/pod-status
     */
    public function getPODStatus(int $id): JsonResponse
    {
        try {
            $booking = Booking::with(['bookingProofs.uploader', 'bookingProofs.verifier'])
                ->findOrFail($id);

            $proofs = $booking->bookingProofs->map(function ($proof) {
                return [
                    'id' => $proof->id,
                    'type' => $proof->type,
                    'status' => $proof->status,
                    'uploaded_at' => $proof->uploaded_at?->toIso8601String(),
                    'uploaded_by' => $proof->uploader->name,
                    'verified_at' => $proof->verified_at?->toIso8601String(),
                    'verified_by' => $proof->verifier?->name,
                    'verified_notes' => $proof->verified_notes,
                    'distance_from_hoarding' => $proof->distance_from_hoarding,
                    'proof_url' => $proof->proof_url,
                    'thumbnail_url' => $proof->thumbnail_url,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'booking_id' => $booking->id,
                    'booking_status' => $booking->status,
                    'proofs' => $proofs,
                    'has_approved_pod' => $booking->approvedProof !== null,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch POD status',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}

