<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingProof;
use App\Models\Hoarding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PODService
{
    /**
     * Upload POD (Proof of Delivery) with geo-validation
     * 
     * @param Booking $booking
     * @param int $uploadedBy User ID (mounter/staff)
     * @param UploadedFile $file
     * @param float|null $latitude
     * @param float|null $longitude
     * @param array $metadata
     * @return BookingProof
     * @throws Exception
     */
    public function uploadPOD(
        Booking $booking,
        int $uploadedBy,
        UploadedFile $file,
        ?float $latitude = null,
        ?float $longitude = null,
        array $metadata = []
    ): BookingProof {
        return DB::transaction(function () use ($booking, $uploadedBy, $file, $latitude, $longitude, $metadata) {
            // Validate file type
            $mimeType = $file->getMimeType();
            $type = str_contains($mimeType, 'video') ? 'video' : 'photo';

            // Get hoarding location
            $hoarding = $booking->hoarding;
            $distanceFromHoarding = null;

            // Calculate distance if GPS coordinates provided
            if ($latitude && $longitude && $hoarding->latitude && $hoarding->longitude) {
                $distanceFromHoarding = $this->calculateDistance(
                    $latitude,
                    $longitude,
                    $hoarding->latitude,
                    $hoarding->longitude
                );

                // Validate distance within radius
                $maxDistance = config('pod.max_distance_meters', 100); // Default 100 meters
                if ($distanceFromHoarding > $maxDistance) {
                    Log::warning('POD uploaded outside acceptable radius', [
                        'booking_id' => $booking->id,
                        'distance' => $distanceFromHoarding,
                        'max_distance' => $maxDistance,
                    ]);

                    throw new Exception(
                        "Location too far from hoarding. Distance: {$distanceFromHoarding}m (Max: {$maxDistance}m)"
                    );
                }
            }

            // Create BookingProof record
            $proof = BookingProof::create([
                'booking_id' => $booking->id,
                'uploaded_by' => $uploadedBy,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'distance_from_hoarding' => $distanceFromHoarding,
                'type' => $type,
                'file_size' => $file->getSize(),
                'status' => 'pending',
                'uploaded_at' => now(),
                'metadata' => array_merge($metadata, [
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'uploaded_from_device' => $metadata['device_info'] ?? 'unknown',
                ]),
            ]);

            // Upload file to Spatie Media Library
            $media = $proof->addMedia($file)
                ->usingFileName($file->hashName())
                ->toMediaCollection('proof');

            // Update media_id
            $proof->update([
                'media_id' => $media->id,
                'file_path' => $media->getPath(),
            ]);

            Log::info('POD uploaded successfully', [
                'booking_id' => $booking->id,
                'proof_id' => $proof->id,
                'uploaded_by' => $uploadedBy,
                'type' => $type,
                'distance' => $distanceFromHoarding,
            ]);

            return $proof;
        });
    }

    /**
     * Approve POD by vendor
     * 
     * @param BookingProof $proof
     * @param int $verifiedBy Vendor user ID
     * @param string|null $notes
     * @return void
     * @throws Exception
     */
    public function approvePOD(BookingProof $proof, int $verifiedBy, ?string $notes = null): void
    {
        DB::transaction(function () use ($proof, $verifiedBy, $notes) {
            if (!$proof->isPending()) {
                throw new Exception('POD has already been processed');
            }

            // Approve the proof
            $proof->approve($verifiedBy, $notes);

            // Update booking status to active/started
            $booking = $proof->booking;
            $booking->update([
                'status' => 'active',
                'pod_approved_at' => now(),
            ]);

            Log::info('POD approved and booking activated', [
                'proof_id' => $proof->id,
                'booking_id' => $booking->id,
                'verified_by' => $verifiedBy,
            ]);

            // Dispatch BookingActivated event
            event(new \App\Events\BookingActivated($booking));
        });
    }

    /**
     * Reject POD by vendor
     * 
     * @param BookingProof $proof
     * @param int $verifiedBy Vendor user ID
     * @param string $notes Reason for rejection
     * @return void
     * @throws Exception
     */
    public function rejectPOD(BookingProof $proof, int $verifiedBy, string $notes): void
    {
        if (!$proof->isPending()) {
            throw new Exception('POD has already been processed');
        }

        $proof->reject($verifiedBy, $notes);

        Log::info('POD rejected', [
            'proof_id' => $proof->id,
            'booking_id' => $proof->booking_id,
            'verified_by' => $verifiedBy,
            'reason' => $notes,
        ]);
    }

    /**
     * Calculate distance between two GPS coordinates (Haversine formula)
     * Returns distance in meters
     * 
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distance in meters
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2); // Distance in meters
    }

    /**
     * Get pending PODs for a vendor
     * 
     * @param int $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingPODsForVendor(int $vendorId)
    {
        return BookingProof::with(['booking.hoarding', 'uploader'])
            ->whereHas('booking', function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })
            ->pending()
            ->orderBy('uploaded_at', 'asc')
            ->get();
    }

    /**
     * Get POD statistics for vendor
     * 
     * @param int $vendorId
     * @return array
     */
    public function getPODStatsForVendor(int $vendorId): array
    {
        return [
            'pending' => BookingProof::whereHas('booking', function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })->pending()->count(),
            
            'approved' => BookingProof::whereHas('booking', function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })->approved()->count(),
            
            'rejected' => BookingProof::whereHas('booking', function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })->rejected()->count(),
            
            'total' => BookingProof::whereHas('booking', function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })->count(),
        ];
    }
}
