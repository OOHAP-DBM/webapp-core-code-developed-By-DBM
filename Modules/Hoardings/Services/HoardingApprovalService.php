<?php

namespace Modules\Hoardings\Services;

use App\Models\Hoarding;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HoardingApprovalService
{
    /**
     * Submit hoarding for approval
     */
    public function submitForApproval(Hoarding $hoarding, User $vendor): array
    {
        DB::beginTransaction();
        try {
            // Create version snapshot
            $version = $this->createVersion($hoarding, 'submit');
            
            // Update hoarding status
            $hoarding->update([
                'approval_status' => 'pending',
                'submitted_at' => now(),
            ]);
            
            // Log the action
            $this->logAction($hoarding, 'submitted', null, 'pending', $vendor, 'vendor');
            
            // Check for auto-approval eligibility
            if ($this->isEligibleForAutoApproval($vendor)) {
                $this->autoApprove($hoarding, $vendor);
                $message = 'Hoarding submitted and auto-approved successfully.';
            } else {
                $message = 'Hoarding submitted for admin verification.';
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => $message,
                'version' => $version,
                'status' => $hoarding->approval_status,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Hoarding submission failed', [
                'hoarding_id' => $hoarding->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to submit hoarding: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Start verification process
     */
    public function startVerification(Hoarding $hoarding, User $admin): array
    {
        DB::beginTransaction();
        try {
            $hoarding->update([
                'approval_status' => 'under_verification',
                'verified_by' => $admin->id,
            ]);
            
            $this->logAction($hoarding, 'verification_started', 'pending', 'under_verification', $admin, 'admin');
            
            // Create checklist items
            $this->createChecklistItems($hoarding);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Verification process started.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to start verification: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Approve hoarding
     */
    public function approve(Hoarding $hoarding, User $admin, ?string $notes = null): array
    {
        DB::beginTransaction();
        try {
            $hoarding->update([
                'approval_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $admin->id,
                'verified_at' => now(),
                'admin_notes' => $notes,
                'status' => 'available', // Make it available for booking
            ]);
            
            // Update version record
            DB::table('hoarding_versions')
                ->where('hoarding_id', $hoarding->id)
                ->where('version_number', $hoarding->current_version)
                ->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => $admin->id,
                ]);
            
            $this->logAction($hoarding, 'approved', 'under_verification', 'approved', $admin, 'admin', $notes);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Hoarding approved successfully.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to approve hoarding: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Reject hoarding
     */
    public function reject(Hoarding $hoarding, User $admin, string $reason, ?array $templateIds = null): array
    {
        DB::beginTransaction();
        try {
            // Build rejection message
            $rejectionMessage = $reason;
            if ($templateIds) {
                $templates = DB::table('hoarding_rejection_templates')
                    ->whereIn('id', $templateIds)
                    ->get();
                
                $rejectionMessage .= "\n\nSpecific Issues:\n";
                foreach ($templates as $template) {
                    $rejectionMessage .= "- {$template->title}: {$template->message}\n";
                    
                    // Increment usage count
                    DB::table('hoarding_rejection_templates')
                        ->where('id', $template->id)
                        ->increment('usage_count');
                }
            }
            
            $hoarding->update([
                'approval_status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => $admin->id,
                'rejection_reason' => $rejectionMessage,
                'status' => 'inactive', // Make it unavailable
            ]);
            
            // Update version record
            DB::table('hoarding_versions')
                ->where('hoarding_id', $hoarding->id)
                ->where('version_number', $hoarding->current_version)
                ->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'rejected_by' => $admin->id,
                    'rejection_reason' => $rejectionMessage,
                ]);
            
            $this->logAction($hoarding, 'rejected', 'under_verification', 'rejected', $admin, 'admin', $rejectionMessage);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Hoarding rejected.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to reject hoarding: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle hoarding edit - trigger re-verification
     */
    public function handleEdit(Hoarding $hoarding, array $oldData, array $newData, User $vendor): array
    {
        DB::beginTransaction();
        try {
            // Detect changed fields
            $changedFields = $this->detectChanges($oldData, $newData);
            
            if (empty($changedFields)) {
                return [
                    'success' => true,
                    'message' => 'No changes detected.',
                    'requires_verification' => false,
                ];
            }
            
            // Check if changes require re-verification
            $requiresVerification = $this->requiresReVerification($changedFields, $hoarding->approval_status);
            
            if ($requiresVerification) {
                // Increment version
                $hoarding->increment('current_version');
                
                // Create new version
                $version = $this->createVersion($hoarding, 'edit', $changedFields);
                
                // Update status to pending if it was previously approved
                if ($hoarding->approval_status === 'approved') {
                    $hoarding->update([
                        'approval_status' => 'pending',
                        'submitted_at' => now(),
                        'status' => 'inactive', // Make unavailable until re-approved
                    ]);
                    
                    $this->logAction($hoarding, 'resubmitted', 'approved', 'pending', $vendor, 'vendor', 
                        'Hoarding edited. Changes: ' . implode(', ', array_keys($changedFields)));
                }
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => $requiresVerification 
                    ? 'Changes submitted for re-verification.' 
                    : 'Changes saved successfully.',
                'requires_verification' => $requiresVerification,
                'new_version' => $requiresVerification ? $hoarding->current_version : null,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to process edit: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Assign verification to admin
     */
    public function assignVerification(Hoarding $hoarding, User $admin, User $assignedBy, int $priority = 3): void
    {
        DB::table('hoarding_verification_assignments')->insert([
            'hoarding_id' => $hoarding->id,
            'admin_id' => $admin->id,
            'status' => 'assigned',
            'assigned_at' => now(),
            'assigned_by' => $assignedBy->id,
            'priority' => $priority,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->logAction($hoarding, 'assigned', null, null, $assignedBy, 'admin', 
            'Assigned to admin ID: ' . $admin->id);
    }

    /**
     * Update checklist item
     */
    public function updateChecklistItem(Hoarding $hoarding, string $item, string $status, User $admin, ?string $notes = null): void
    {
        DB::table('hoarding_approval_checklists')
            ->where('hoarding_id', $hoarding->id)
            ->where('version_number', $hoarding->current_version)
            ->where('checklist_item', $item)
            ->update([
                'status' => $status,
                'notes' => $notes,
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * Get approval statistics
     */
    public function getStatistics(?string $period = 'month'): array
    {
        $dateRange = $this->getDateRange($period);
        
        return [
            'pending' => DB::table('hoardings')
                ->where('approval_status', 'pending')
                ->count(),
            'under_verification' => DB::table('hoardings')
                ->where('approval_status', 'under_verification')
                ->count(),
            'approved_today' => DB::table('hoardings')
                ->where('approval_status', 'approved')
                ->whereDate('approved_at', today())
                ->count(),
            'rejected_today' => DB::table('hoardings')
                ->where('approval_status', 'rejected')
                ->whereDate('rejected_at', today())
                ->count(),
            'total_approved' => DB::table('hoardings')
                ->where('approval_status', 'approved')
                ->whereBetween('approved_at', $dateRange)
                ->count(),
            'total_rejected' => DB::table('hoardings')
                ->where('approval_status', 'rejected')
                ->whereBetween('rejected_at', $dateRange)
                ->count(),
            'avg_approval_time' => $this->getAverageApprovalTime($dateRange),
            'sla_breaches' => $this->getSLABreaches($dateRange),
        ];
    }

    /**
     * Get pending approvals for admin
     */
    public function getPendingApprovals(?int $adminId = null, string $status = 'all', int $limit = 50): array
    {
        $query = DB::table('hoardings')
            ->join('users', 'hoardings.vendor_id', '=', 'users.id')
            ->select(
                'hoardings.*',
                'users.name as vendor_name',
                'users.email as vendor_email'
            );
        
        if ($status !== 'all') {
            $query->where('hoardings.approval_status', $status);
        } else {
            $query->whereIn('hoardings.approval_status', ['pending', 'under_verification']);
        }
        
        if ($adminId) {
            $query->leftJoin('hoarding_verification_assignments', function($join) use ($adminId) {
                $join->on('hoardings.id', '=', 'hoarding_verification_assignments.hoarding_id')
                     ->where('hoarding_verification_assignments.admin_id', $adminId)
                     ->where('hoarding_verification_assignments.status', '!=', 'completed');
            });
        }
        
        return $query->orderBy('hoardings.submitted_at', 'asc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Create version snapshot
     */
    protected function createVersion(Hoarding $hoarding, string $changeType, ?array $changedFields = null): int
    {
        $versionData = [
            'hoarding_id' => $hoarding->id,
            'version_number' => $hoarding->current_version,
            'status' => $hoarding->approval_status,
            'location_name' => $hoarding->location_name,
            'address' => $hoarding->address,
            'city' => $hoarding->city,
            'state' => $hoarding->state,
            'pincode' => $hoarding->pincode,
            'latitude' => $hoarding->latitude,
            'longitude' => $hoarding->longitude,
            'width' => $hoarding->width,
            'height' => $hoarding->height,
            'size_category' => $hoarding->size_category,
            'board_type' => $hoarding->board_type,
            'is_lit' => $hoarding->is_lit,
            'price_per_month' => $hoarding->price_per_month,
            'images' => $hoarding->images,
            'description' => $hoarding->description,
            'amenities' => $hoarding->amenities,
            'traffic_density' => $hoarding->traffic_density,
            'visibility_rating' => $hoarding->visibility_rating,
            'target_audience' => $hoarding->target_audience,
            'change_type' => $changeType,
            'changed_fields' => $changedFields ? json_encode($changedFields) : null,
            'created_by' => $hoarding->vendor_id,
            'submitted_at' => $hoarding->submitted_at,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        DB::table('hoarding_versions')->insert($versionData);
        
        return $hoarding->current_version;
    }

    /**
     * Log approval action
     */
    protected function logAction(Hoarding $hoarding, string $action, ?string $fromStatus, ?string $toStatus, User $user, string $role, ?string $notes = null): void
    {
        DB::table('hoarding_approval_logs')->insert([
            'hoarding_id' => $hoarding->id,
            'version_number' => $hoarding->current_version,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'performed_by' => $user->id,
            'performer_role' => $role,
            'notes' => $notes,
            'metadata' => json_encode([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]),
            'performed_at' => now(),
        ]);
    }

    /**
     * Create checklist items for hoarding
     */
    protected function createChecklistItems(Hoarding $hoarding): void
    {
        $setting = DB::table('hoarding_approval_settings')
            ->where('key', 'required_checklist_items')
            ->first();
        
        $items = $setting ? json_decode($setting->value, true) : [
            'location_verified',
            'images_quality',
            'dimensions_accurate',
            'pricing_reasonable',
            'description_complete',
            'legal_compliance'
        ];
        
        foreach ($items as $item) {
            DB::table('hoarding_approval_checklists')->insert([
                'hoarding_id' => $hoarding->id,
                'version_number' => $hoarding->current_version,
                'checklist_item' => $item,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Check if vendor is eligible for auto-approval
     */
    protected function isEligibleForAutoApproval(User $vendor): bool
    {
        $autoApproveEnabled = DB::table('hoarding_approval_settings')
            ->where('key', 'auto_approve_trusted_vendors')
            ->value('value') === 'true';
        
        if (!$autoApproveEnabled) {
            return false;
        }
        
        $ratingThreshold = (float) DB::table('hoarding_approval_settings')
            ->where('key', 'trusted_vendor_rating_threshold')
            ->value('value') ?? 4.5;
        
        $minApproved = (int) DB::table('hoarding_approval_settings')
            ->where('key', 'trusted_vendor_min_approved')
            ->value('value') ?? 10;
        
        // Check vendor rating
        $avgRating = DB::table('bookings')
            ->where('vendor_id', $vendor->id)
            ->whereNotNull('customer_rating')
            ->avg('customer_rating') ?? 0;
        
        // Check approved hoardings count
        $approvedCount = DB::table('hoardings')
            ->where('vendor_id', $vendor->id)
            ->where('approval_status', 'approved')
            ->count();
        
        return $avgRating >= $ratingThreshold && $approvedCount >= $minApproved;
    }

    /**
     * Auto-approve hoarding
     */
    protected function autoApprove(Hoarding $hoarding, User $vendor): void
    {
        $hoarding->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => null, // System approval
            'verified_at' => now(),
            'status' => 'available',
        ]);
        
        DB::table('hoarding_versions')
            ->where('hoarding_id', $hoarding->id)
            ->where('version_number', $hoarding->current_version)
            ->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);
        
        $this->logAction($hoarding, 'auto_approved', 'pending', 'approved', $vendor, 'system', 
            'Auto-approved for trusted vendor');
    }

    /**
     * Detect changes between old and new data
     */
    protected function detectChanges(array $old, array $new): array
    {
        $importantFields = [
            'location_name', 'address', 'city', 'state', 'pincode',
            'latitude', 'longitude', 'width', 'height', 'board_type',
            'is_lit', 'price_per_month', 'images', 'description'
        ];
        
        $changes = [];
        foreach ($importantFields as $field) {
            if (isset($old[$field]) && isset($new[$field]) && $old[$field] != $new[$field]) {
                $changes[$field] = [
                    'old' => $old[$field],
                    'new' => $new[$field],
                ];
            }
        }
        
        return $changes;
    }

    /**
     * Check if changes require re-verification
     */
    protected function requiresReVerification(array $changedFields, string $currentStatus): bool
    {
        // Always require re-verification if hoarding was approved
        if ($currentStatus === 'approved') {
            $criticalFields = ['location_name', 'address', 'latitude', 'longitude', 'width', 'height', 'images'];
            
            foreach ($criticalFields as $field) {
                if (isset($changedFields[$field])) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Get average approval time
     */
    protected function getAverageApprovalTime(array $dateRange): ?float
    {
        $avg = DB::table('hoardings')
            ->where('approval_status', 'approved')
            ->whereBetween('approved_at', $dateRange)
            ->whereNotNull('submitted_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, submitted_at, approved_at)) as avg_hours')
            ->value('avg_hours');
        
        return $avg ? round($avg, 1) : null;
    }

    /**
     * Get SLA breaches count
     */
    protected function getSLABreaches(array $dateRange): int
    {
        $slaHours = (int) DB::table('hoarding_approval_settings')
            ->where('key', 'verification_sla_hours')
            ->value('value') ?? 48;
        
        return DB::table('hoardings')
            ->where('approval_status', 'pending')
            ->whereBetween('submitted_at', $dateRange)
            ->whereRaw('TIMESTAMPDIFF(HOUR, submitted_at, NOW()) > ?', [$slaHours])
            ->count();
    }

    /**
     * Get date range for period
     */
    protected function getDateRange(string $period): array
    {
        return match($period) {
            'today' => [Carbon::today(), Carbon::now()],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()],
            'quarter' => [Carbon::now()->startOfQuarter(), Carbon::now()],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()],
            default => [Carbon::now()->startOfMonth(), Carbon::now()],
        };
    }
}
