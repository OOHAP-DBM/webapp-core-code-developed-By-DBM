<?php

namespace Modules\Campaigns\Services;

use App\Models\Booking;
use App\Models\User;
use Modules\POD\Models\PODSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Exception;

/**
 * Campaign Service
 * Handles campaign start approval workflow
 * Campaign starts when: Mounter uploads POD + Vendor approves
 */
class CampaignService
{
    /**
     * Submit POD (Proof of Display) by mounter
     * Campaign can only start after this is approved by vendor
     *
     * @param int $bookingId
     * @param int $mounterId
     * @param array $files (photos/videos)
     * @param string|null $notes
     * @return PODSubmission
     * @throws Exception
     */
    public function submitPOD(int $bookingId, int $mounterId, array $files, ?string $notes = null): PODSubmission
    {
        return DB::transaction(function () use ($bookingId, $mounterId, $files, $notes) {
            // Validate booking
            $booking = Booking::with(['vendor', 'customer', 'hoarding'])->findOrFail($bookingId);

            if ($booking->status !== Booking::STATUS_CONFIRMED) {
                throw new Exception('Can only submit POD for confirmed bookings');
            }

            // Check if booking has started
            $today = Carbon::today();
            $startDate = Carbon::parse($booking->start_date);

            if ($today->lt($startDate)) {
                throw new Exception('Cannot submit POD before booking start date');
            }

            // Check if POD already exists
            $existingPOD = PODSubmission::where('booking_id', $bookingId)
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($existingPOD) {
                throw new Exception('POD already submitted for this booking');
            }

            // Upload files
            $uploadedFiles = [];
            foreach ($files as $file) {
                $path = $file->store('pod/' . $bookingId, 'public');
                $uploadedFiles[] = [
                    'path' => $path,
                    'type' => str_starts_with($file->getMimeType(), 'image') ? 'photo' : 'video',
                    'size' => $file->getSize(),
                    'uploaded_at' => now()->toIso8601String(),
                ];
            }

            // Create POD submission
            $podSubmission = PODSubmission::create([
                'booking_id' => $bookingId,
                'submitted_by' => $mounterId,
                'submission_date' => now(),
                'files' => $uploadedFiles,
                'notes' => $notes,
                'status' => 'pending',
            ]);

            // Notify vendor for approval
            // $booking->vendor->notify(new PODSubmittedNotification($podSubmission));

            Log::info('POD submitted', [
                'booking_id' => $bookingId,
                'pod_id' => $podSubmission->id,
                'mounter_id' => $mounterId,
                'files_count' => count($uploadedFiles),
            ]);

            return $podSubmission;
        });
    }

    /**
     * Approve POD by vendor
     * This triggers campaign start
     *
     * @param int $podId
     * @param int $vendorId
     * @param string|null $approvalNotes
     * @return PODSubmission
     * @throws Exception
     */
    public function approvePOD(int $podId, int $vendorId, ?string $approvalNotes = null): PODSubmission
    {
        return DB::transaction(function () use ($podId, $vendorId, $approvalNotes) {
            $podSubmission = PODSubmission::with('booking')->findOrFail($podId);
            $booking = $podSubmission->booking;

            // Verify vendor ownership
            if ($booking->vendor_id !== $vendorId) {
                throw new Exception('You are not authorized to approve this POD');
            }

            if ($podSubmission->status !== 'pending') {
                throw new Exception('POD is not in pending status');
            }

            // Approve POD
            $podSubmission->update([
                'status' => 'approved',
                'approved_by' => $vendorId,
                'approved_at' => now(),
                'approval_notes' => $approvalNotes,
            ]);

            // Mark campaign as started
            $booking->update([
                'campaign_started_at' => now(),
                'pod_approved_at' => now(),
            ]);

            // Log status
            \App\Models\BookingStatusLog::create([
                'booking_id' => $booking->id,
                'from_status' => $booking->status,
                'to_status' => $booking->status,
                'changed_by' => $vendorId,
                'notes' => 'Campaign started - POD approved by vendor',
            ]);

            // Notify customer that campaign has started
            // $booking->customer->notify(new CampaignStartedNotification($booking, $podSubmission));

            Log::info('POD approved and campaign started', [
                'booking_id' => $booking->id,
                'pod_id' => $podSubmission->id,
                'vendor_id' => $vendorId,
                'campaign_started_at' => $booking->campaign_started_at,
            ]);

            return $podSubmission->fresh(['booking']);
        });
    }

    /**
     * Reject POD by vendor
     *
     * @param int $podId
     * @param int $vendorId
     * @param string $rejectionReason
     * @return PODSubmission
     * @throws Exception
     */
    public function rejectPOD(int $podId, int $vendorId, string $rejectionReason): PODSubmission
    {
        return DB::transaction(function () use ($podId, $vendorId, $rejectionReason) {
            $podSubmission = PODSubmission::with('booking')->findOrFail($podId);
            $booking = $podSubmission->booking;

            // Verify vendor ownership
            if ($booking->vendor_id !== $vendorId) {
                throw new Exception('You are not authorized to reject this POD');
            }

            if ($podSubmission->status !== 'pending') {
                throw new Exception('POD is not in pending status');
            }

            // Reject POD
            $podSubmission->update([
                'status' => 'rejected',
                'rejected_by' => $vendorId,
                'rejected_at' => now(),
                'rejection_reason' => $rejectionReason,
            ]);

            // Log status
            \App\Models\BookingStatusLog::create([
                'booking_id' => $booking->id,
                'from_status' => $booking->status,
                'to_status' => $booking->status,
                'changed_by' => $vendorId,
                'notes' => 'POD rejected by vendor: ' . $rejectionReason,
            ]);

            // Notify mounter to resubmit
            // $podSubmission->submittedBy->notify(new PODRejectedNotification($podSubmission, $rejectionReason));

            Log::info('POD rejected', [
                'booking_id' => $booking->id,
                'pod_id' => $podSubmission->id,
                'vendor_id' => $vendorId,
                'reason' => $rejectionReason,
            ]);

            return $podSubmission->fresh(['booking']);
        });
    }

    /**
     * Get pending PODs for vendor
     *
     * @param int $vendorId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPendingPODsForVendor(int $vendorId, int $perPage = 15)
    {
        return PODSubmission::with(['booking.hoarding', 'submittedBy'])
            ->whereHas('booking', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->where('status', 'pending')
            ->orderBy('submission_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all PODs for a booking
     *
     * @param int $bookingId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPODsForBooking(int $bookingId)
    {
        return PODSubmission::with(['submittedBy', 'approvedBy', 'rejectedBy'])
            ->where('booking_id', $bookingId)
            ->orderBy('submission_date', 'desc')
            ->get();
    }

    /**
     * Check if campaign has started for a booking
     *
     * @param Booking $booking
     * @return bool
     */
    public function hasCampaignStarted(Booking $booking): bool
    {
        return $booking->campaign_started_at !== null;
    }

    /**
     * Get campaign status
     *
     * @param Booking $booking
     * @return array
     */
    public function getCampaignStatus(Booking $booking): array
    {
        $today = Carbon::today();
        $startDate = Carbon::parse($booking->start_date);
        $endDate = Carbon::parse($booking->end_date);
        $campaignStarted = $this->hasCampaignStarted($booking);

        if ($campaignStarted) {
            $campaignStartedAt = Carbon::parse($booking->campaign_started_at);
            
            if ($today->gt($endDate)) {
                return [
                    'status' => 'completed',
                    'message' => 'Campaign has completed',
                    'started_at' => $campaignStartedAt->toIso8601String(),
                    'duration_completed' => $campaignStartedAt->diffInDays($endDate) + 1,
                ];
            }

            return [
                'status' => 'active',
                'message' => 'Campaign is currently running',
                'started_at' => $campaignStartedAt->toIso8601String(),
                'days_running' => $campaignStartedAt->diffInDays($today) + 1,
                'days_remaining' => $today->diffInDays($endDate),
            ];
        }

        // Campaign not started yet
        if ($today->lt($startDate)) {
            return [
                'status' => 'scheduled',
                'message' => 'Campaign is scheduled to start',
                'scheduled_start_date' => $startDate->format('Y-m-d'),
                'days_until_start' => $today->diffInDays($startDate),
            ];
        }

        // Today is within booking period but campaign not started
        return [
            'status' => 'awaiting_pod',
            'message' => 'Waiting for mounter to upload POD and vendor approval',
            'booking_started_on' => $startDate->format('Y-m-d'),
            'days_since_start' => $startDate->diffInDays($today),
        ];
    }
}
