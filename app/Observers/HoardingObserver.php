<?php

namespace App\Observers;

use App\Models\Hoarding;
use App\Services\HoardingApprovalService;

class HoardingObserver
{
    protected HoardingApprovalService $approvalService;

    public function __construct(HoardingApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Handle the Hoarding "creating" event.
     */
    public function creating(Hoarding $hoarding): void
    {
        // Set initial approval status for new hoardings
        if (!$hoarding->status) {
            $hoarding->status = 'draft';
        }
        
        if (!$hoarding->current_version) {
            $hoarding->current_version = 1;
        }
    }

    /**
     * Handle the Hoarding "updating" event.
     */
    public function updating(Hoarding $hoarding): void
    {
        // Get original data
        $original = $hoarding->getOriginal();
        
        // Check if hoarding was approved and is being edited
        if ($original['approval_status'] === 'approved' && $hoarding->isDirty()) {
            // Get old and new data
            $oldData = $original;
            $newData = $hoarding->getAttributes();
            
            // Check if critical fields changed
            $criticalFields = [
                'location_name', 'address', 'city', 'state', 'pincode',
                'latitude', 'longitude', 'width', 'height', 'board_type',
                'is_lit', 'price_per_month', 'images'
            ];
            
            $hasChanged = false;
            foreach ($criticalFields as $field) {
                if (isset($oldData[$field]) && isset($newData[$field])) {
                    if ($oldData[$field] != $newData[$field]) {
                        $hasChanged = true;
                        break;
                    }
                }
            }
            
            // If critical fields changed, trigger re-verification
            if ($hasChanged) {
                // This will be handled by the controller using handleEdit method
                // We just mark that verification is needed
                $hoarding->setAttribute('needs_reverification', true);
            }
        }
    }

    /**
     * Handle the Hoarding "created" event.
     */
    public function created(Hoarding $hoarding): void
    {
        // Log creation in approval logs
        \DB::table('hoarding_approval_logs')->insert([
            'hoarding_id' => $hoarding->id,
            'version_number' => 1,
            'action' => 'submitted',
            'from_status' => null,
            'to_status' => 'draft',
            'performed_by' => $hoarding->vendor_id,
            'performer_role' => 'vendor',
            'notes' => 'Hoarding created',
            'metadata' => json_encode([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]),
            'performed_at' => now(),
        ]);
    }

    /**
     * Handle the Hoarding "deleting" event.
     */
    public function deleting(Hoarding $hoarding): void
    {
        // Prevent deletion of approved hoardings with active bookings
        if ($hoarding->status === 'approved') {
            $activeBookings = \DB::table('bookings')
                ->where('hoarding_id', $hoarding->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();
            
            if ($activeBookings > 0) {
                throw new \Exception('Cannot delete hoarding with active bookings. Cancel bookings first.');
            }
        }
    }

    /**
     * Handle the Hoarding "deleted" event.
     */
    public function deleted(Hoarding $hoarding): void
    {
        // Log deletion
        \DB::table('hoarding_approval_logs')->insert([
            'hoarding_id' => $hoarding->id,
            'version_number' => $hoarding->current_version,
            'action' => 'deleted',
            'from_status' => $hoarding->approval_status,
            'to_status' => null,
            'performed_by' => auth()->id() ?? $hoarding->vendor_id,
            'performer_role' => auth()->user()->role ?? 'vendor',
            'notes' => 'Hoarding deleted',
            'metadata' => json_encode([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]),
            'performed_at' => now(),
        ]);
    }
}
