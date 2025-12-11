<?php

namespace Modules\DOOH\Services;

use Modules\DOOH\Models\DOOHCreative;
use Modules\DOOH\Models\DOOHCreativeSchedule;
use Modules\DOOH\Models\DOOHScreen;
use App\Models\Booking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * DOOH Schedule Service
 * PROMPT 67: Handles creative scheduling with availability validation
 */
class DOOHScheduleService
{
    /**
     * Upload creative file
     */
    public function uploadCreative(
        int $customerId,
        array $fileData,
        ?int $bookingId = null,
        ?int $screenId = null
    ): DOOHCreative {
        try {
            DB::beginTransaction();
            
            $file = $fileData['file'];
            $type = $this->detectCreativeType($file);
            
            // Store file
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('dooh_creatives/' . $customerId, $fileName, 'public');
            
            // Extract metadata
            $metadata = $this->extractMediaMetadata($file, $type);
            
            // Create creative record
            $creative = DOOHCreative::create([
                'customer_id' => $customerId,
                'booking_id' => $bookingId,
                'dooh_screen_id' => $screenId,
                'creative_name' => $fileData['name'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'description' => $fileData['description'] ?? null,
                'creative_type' => $type,
                'file_path' => $filePath,
                'file_url' => Storage::url($filePath),
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size_bytes' => $file->getSize(),
                'resolution' => $metadata['resolution'] ?? null,
                'width_pixels' => $metadata['width'] ?? null,
                'height_pixels' => $metadata['height'] ?? null,
                'duration_seconds' => $metadata['duration'] ?? null,
                'fps' => $metadata['fps'] ?? null,
                'codec' => $metadata['codec'] ?? null,
                'bitrate_kbps' => $metadata['bitrate'] ?? null,
                'metadata' => $metadata,
                'tags' => $fileData['tags'] ?? [],
                'uploaded_at' => now(),
                'status' => DOOHCreative::STATUS_DRAFT,
                'validation_status' => DOOHCreative::VALIDATION_PENDING,
            ]);
            
            // Run automatic validations
            $this->validateCreative($creative, $screenId);
            
            // Generate thumbnail for videos
            if ($creative->isVideo()) {
                $this->generateThumbnail($creative);
            }
            
            DB::commit();
            
            Log::info('DOOH creative uploaded', [
                'creative_id' => $creative->id,
                'customer_id' => $customerId,
                'type' => $type,
                'size' => $creative->file_size,
            ]);
            
            return $creative->fresh();
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to upload DOOH creative', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate creative against technical requirements
     */
    public function validateCreative(DOOHCreative $creative, ?int $screenId = null): array
    {
        $screen = $screenId ? DOOHScreen::find($screenId) : $creative->doohScreen;
        
        $validationResults = $creative->runValidations($screen);
        
        // Update creative with validation results
        $creative->update([
            'format_valid' => $validationResults['format_valid'],
            'file_size_valid' => $validationResults['file_size_valid'],
            'duration_valid' => $validationResults['duration_valid'],
            'resolution_valid' => $validationResults['resolution_valid'],
            'validation_results' => $validationResults,
            'validation_status' => $validationResults['overall_valid'] 
                ? DOOHCreative::VALIDATION_PENDING 
                : DOOHCreative::VALIDATION_REJECTED,
            'rejection_reason' => $validationResults['overall_valid'] 
                ? null 
                : implode('; ', $validationResults['errors']),
        ]);
        
        return $validationResults;
    }

    /**
     * Create a new schedule
     */
    public function createSchedule(array $data): DOOHCreativeSchedule
    {
        try {
            DB::beginTransaction();
            
            $creative = DOOHCreative::findOrFail($data['creative_id']);
            $screen = DOOHScreen::findOrFail($data['dooh_screen_id']);
            
            // Validate creative is approved
            if (!$creative->canBeScheduled()) {
                throw new Exception('Creative must be approved before scheduling');
            }
            
            // Parse dates
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);
            
            // Validate date range
            if ($endDate->lt($startDate)) {
                throw new Exception('End date must be after start date');
            }
            
            if ($startDate->lt(now()->startOfDay())) {
                throw new Exception('Start date cannot be in the past');
            }
            
            // Calculate displays and costs
            $displaysPerHour = $data['displays_per_hour'] ?? $this->calculateDefaultDisplaysPerHour($screen);
            $costPerDisplay = $data['cost_per_display'] ?? $screen->price_per_slot;
            
            // Create schedule
            $schedule = DOOHCreativeSchedule::create([
                'creative_id' => $creative->id,
                'dooh_screen_id' => $screen->id,
                'booking_id' => $data['booking_id'] ?? $creative->booking_id,
                'customer_id' => $creative->customer_id,
                'schedule_name' => $data['schedule_name'] ?? $creative->creative_name . ' Schedule',
                'description' => $data['description'] ?? null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'time_slots' => $data['time_slots'] ?? null,
                'daily_start_time' => $data['daily_start_time'] ?? null,
                'daily_end_time' => $data['daily_end_time'] ?? null,
                'slots_per_loop' => $data['slots_per_loop'] ?? $screen->slots_per_loop ?? 1,
                'loop_frequency' => $data['loop_frequency'] ?? 1,
                'displays_per_hour' => $displaysPerHour,
                'priority' => $data['priority'] ?? 5,
                'position_in_loop' => $data['position_in_loop'] ?? null,
                'active_days' => $data['active_days'] ?? null,
                'is_recurring' => $data['is_recurring'] ?? false,
                'cost_per_display' => $costPerDisplay,
                'customer_notes' => $data['customer_notes'] ?? null,
                'status' => DOOHCreativeSchedule::STATUS_DRAFT,
                'validation_status' => DOOHCreativeSchedule::VALIDATION_PENDING,
            ]);
            
            // Check availability
            $availabilityCheck = $this->checkScheduleAvailability($schedule);
            
            if (!$availabilityCheck['available']) {
                $schedule->update([
                    'validation_status' => DOOHCreativeSchedule::VALIDATION_CONFLICTS,
                    'validation_errors' => json_encode($availabilityCheck['conflicts']),
                    'conflict_warnings' => $availabilityCheck['conflicts'],
                ]);
            } else {
                $schedule->update([
                    'availability_confirmed' => true,
                    'availability_checked_at' => now(),
                    'status' => DOOHCreativeSchedule::STATUS_PENDING_APPROVAL,
                ]);
            }
            
            // Update creative schedule count
            $creative->updateScheduleCount();
            
            DB::commit();
            
            Log::info('DOOH schedule created', [
                'schedule_id' => $schedule->id,
                'creative_id' => $creative->id,
                'screen_id' => $screen->id,
                'available' => $availabilityCheck['available'],
            ]);
            
            return $schedule->fresh();
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create DOOH schedule', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check schedule availability
     */
    public function checkScheduleAvailability(DOOHCreativeSchedule $schedule): array
    {
        $conflicts = [];
        $warnings = [];
        
        // Get overlapping schedules
        $overlappingSchedules = DOOHCreativeSchedule::overlapping(
            $schedule->start_date,
            $schedule->end_date,
            $schedule->dooh_screen_id,
            $schedule->id
        )->get();
        
        if ($overlappingSchedules->isEmpty()) {
            return [
                'available' => true,
                'conflicts' => [],
                'warnings' => [],
                'message' => 'Schedule slot is available',
            ];
        }
        
        // Analyze conflicts
        foreach ($overlappingSchedules as $existing) {
            $conflict = [
                'schedule_id' => $existing->id,
                'schedule_name' => $existing->schedule_name,
                'customer' => $existing->customer->name,
                'start_date' => $existing->start_date->format('Y-m-d'),
                'end_date' => $existing->end_date->format('Y-m-d'),
                'priority' => $existing->priority,
                'displays_per_day' => $existing->displays_per_day,
            ];
            
            // Check if time slots overlap
            if ($this->timeSlotsOverlap($schedule, $existing)) {
                $conflict['type'] = 'time_conflict';
                $conflict['severity'] = 'high';
                $conflicts[] = $conflict;
            } else {
                $conflict['type'] = 'date_overlap';
                $conflict['severity'] = 'low';
                $warnings[] = $conflict;
            }
        }
        
        // Check screen capacity
        $screenCapacity = $this->checkScreenCapacity($schedule, $overlappingSchedules);
        
        if (!$screenCapacity['has_capacity']) {
            $conflicts[] = [
                'type' => 'capacity_exceeded',
                'severity' => 'high',
                'message' => $screenCapacity['message'],
                'current_load' => $screenCapacity['current_load'],
                'max_capacity' => $screenCapacity['max_capacity'],
            ];
        }
        
        return [
            'available' => empty($conflicts),
            'conflicts' => $conflicts,
            'warnings' => $warnings,
            'message' => empty($conflicts) 
                ? 'Schedule is available with ' . count($warnings) . ' warnings' 
                : count($conflicts) . ' conflicts found',
            'capacity_info' => $screenCapacity,
        ];
    }

    /**
     * Check if time slots overlap
     */
    protected function timeSlotsOverlap(
        DOOHCreativeSchedule $schedule1,
        DOOHCreativeSchedule $schedule2
    ): bool {
        // If either has no specific time slots, they overlap
        if (empty($schedule1->time_slots) || empty($schedule2->time_slots)) {
            return true;
        }
        
        // Check each time slot combination
        foreach ($schedule1->time_slots as $slot1) {
            foreach ($schedule2->time_slots as $slot2) {
                $start1 = Carbon::parse($slot1['start_time']);
                $end1 = Carbon::parse($slot1['end_time']);
                $start2 = Carbon::parse($slot2['start_time']);
                $end2 = Carbon::parse($slot2['end_time']);
                
                // Check for overlap
                if (!($end1->lt($start2) || $start1->gt($end2))) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check screen capacity
     */
    protected function checkScreenCapacity(
        DOOHCreativeSchedule $newSchedule,
        $existingSchedules
    ): array {
        $screen = $newSchedule->doohScreen;
        $maxSlotsPerDay = $screen->total_slots_per_day ?? 288; // Default: 5-minute slots in 24 hours
        
        // Calculate current load
        $currentLoad = $existingSchedules->sum('displays_per_day');
        $newLoad = $newSchedule->displays_per_day;
        $totalLoad = $currentLoad + $newLoad;
        
        $utilizationPercent = ($totalLoad / $maxSlotsPerDay) * 100;
        
        return [
            'has_capacity' => $totalLoad <= $maxSlotsPerDay,
            'current_load' => $currentLoad,
            'new_load' => $newLoad,
            'total_load' => $totalLoad,
            'max_capacity' => $maxSlotsPerDay,
            'available_slots' => max(0, $maxSlotsPerDay - $currentLoad),
            'utilization_percent' => round($utilizationPercent, 2),
            'message' => $totalLoad > $maxSlotsPerDay 
                ? "Exceeds screen capacity by " . ($totalLoad - $maxSlotsPerDay) . " slots/day"
                : "Capacity available: " . ($maxSlotsPerDay - $totalLoad) . " slots/day remaining",
        ];
    }

    /**
     * Approve schedule
     */
    public function approveSchedule(
        int $scheduleId,
        int $approverId,
        ?string $notes = null
    ): DOOHCreativeSchedule {
        try {
            DB::beginTransaction();
            
            $schedule = DOOHCreativeSchedule::findOrFail($scheduleId);
            
            // Re-check availability
            $availability = $this->checkScheduleAvailability($schedule);
            
            if (!$availability['available']) {
                throw new Exception('Schedule has conflicts: ' . $availability['message']);
            }
            
            $schedule->approve($approverId, $notes);
            
            DB::commit();
            
            Log::info('DOOH schedule approved', [
                'schedule_id' => $schedule->id,
                'approved_by' => $approverId,
            ]);
            
            return $schedule->fresh();
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate playback schedule for a date
     */
    public function generatePlaybackSchedule(Carbon $date, int $screenId): array
    {
        $activeSchedules = DOOHCreativeSchedule::forScreen($screenId)
            ->where('status', DOOHCreativeSchedule::STATUS_ACTIVE)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->with('creative')
            ->orderBy('priority', 'desc')
            ->get();
        
        if ($activeSchedules->isEmpty()) {
            return [
                'date' => $date->format('Y-m-d'),
                'screen_id' => $screenId,
                'total_slots' => 0,
                'schedule' => [],
            ];
        }
        
        $playbackSchedule = [];
        $currentTime = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        $slotNumber = 0;
        
        while ($currentTime->lt($endOfDay)) {
            $currentTimeStr = $currentTime->format('H:i:s');
            
            // Find which schedules are active at this time
            $activeNow = $activeSchedules->filter(function($schedule) use ($date, $currentTimeStr) {
                // Check day of week
                if (!empty($schedule->active_days) && !in_array($date->dayOfWeek, $schedule->active_days)) {
                    return false;
                }
                
                // Check time slots
                if (!empty($schedule->time_slots)) {
                    foreach ($schedule->time_slots as $slot) {
                        if ($currentTimeStr >= $slot['start_time'] && $currentTimeStr <= $slot['end_time']) {
                            return true;
                        }
                    }
                    return false;
                }
                
                // Check daily range
                if ($schedule->daily_start_time && $schedule->daily_end_time) {
                    return $currentTimeStr >= $schedule->daily_start_time->format('H:i:s')
                        && $currentTimeStr <= $schedule->daily_end_time->format('H:i:s');
                }
                
                return true; // 24/7 schedule
            });
            
            if ($activeNow->isNotEmpty()) {
                // Distribute slots based on priority and frequency
                foreach ($activeNow as $schedule) {
                    $playbackSchedule[] = [
                        'slot_number' => ++$slotNumber,
                        'time' => $currentTime->format('H:i:s'),
                        'datetime' => $currentTime->toDateTimeString(),
                        'schedule_id' => $schedule->id,
                        'schedule_name' => $schedule->schedule_name,
                        'creative_id' => $schedule->creative_id,
                        'creative_name' => $schedule->creative->creative_name,
                        'creative_type' => $schedule->creative->creative_type,
                        'file_url' => $schedule->creative->full_url,
                        'duration' => $schedule->creative->duration_seconds ?? 10,
                        'priority' => $schedule->priority,
                    ];
                }
            }
            
            // Move to next slot (default 5 minutes)
            $currentTime->addMinutes(5);
            
            // Safety limit
            if ($slotNumber > 1000) {
                break;
            }
        }
        
        return [
            'date' => $date->format('Y-m-d'),
            'screen_id' => $screenId,
            'total_slots' => count($playbackSchedule),
            'active_schedules' => $activeSchedules->count(),
            'schedule' => $playbackSchedule,
        ];
    }

    /**
     * Detect creative type from file
     */
    protected function detectCreativeType($file): string
    {
        $mimeType = $file->getMimeType();
        
        if (str_starts_with($mimeType, 'video/')) {
            return DOOHCreative::TYPE_VIDEO;
        } elseif (str_starts_with($mimeType, 'image/')) {
            if (str_contains($mimeType, 'gif')) {
                return DOOHCreative::TYPE_GIF;
            }
            return DOOHCreative::TYPE_IMAGE;
        }
        
        return DOOHCreative::TYPE_IMAGE;
    }

    /**
     * Extract media metadata
     */
    protected function extractMediaMetadata($file, string $type): array
    {
        $metadata = [
            'file_type' => $type,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ];
        
        try {
            if ($type === DOOHCreative::TYPE_IMAGE || $type === DOOHCreative::TYPE_GIF) {
                $imageInfo = getimagesize($file->getRealPath());
                if ($imageInfo) {
                    $metadata['width'] = $imageInfo[0];
                    $metadata['height'] = $imageInfo[1];
                    $metadata['resolution'] = $imageInfo[0] . 'x' . $imageInfo[1];
                }
            } elseif ($type === DOOHCreative::TYPE_VIDEO) {
                // For video, you'd typically use FFMpeg or similar
                // For now, store basic info
                $metadata['codec'] = 'h264'; // Default assumption
                $metadata['duration'] = 30; // Would need FFMpeg to extract
            }
        } catch (Exception $e) {
            Log::warning('Failed to extract media metadata', ['error' => $e->getMessage()]);
        }
        
        return $metadata;
    }

    /**
     * Generate thumbnail for video
     */
    protected function generateThumbnail(DOOHCreative $creative): void
    {
        // This would typically use FFMpeg to generate a thumbnail
        // For now, just log
        Log::info('Thumbnail generation queued', ['creative_id' => $creative->id]);
        
        // TODO: Implement with FFMpeg or queue job
        // $creative->update(['thumbnail_path' => $thumbnailPath]);
    }

    /**
     * Calculate default displays per hour based on screen config
     */
    protected function calculateDefaultDisplaysPerHour(DOOHScreen $screen): int
    {
        $loopDurationMinutes = ($screen->loop_duration_seconds ?? 300) / 60;
        $loopsPerHour = 60 / $loopDurationMinutes;
        
        return (int) ceil($loopsPerHour);
    }

    /**
     * Get schedule statistics
     */
    public function getScheduleStats(DOOHCreativeSchedule $schedule): array
    {
        return [
            'schedule_id' => $schedule->id,
            'status' => $schedule->status,
            'total_days' => $schedule->total_days,
            'days_elapsed' => $schedule->isActive() 
                ? $schedule->start_date->diffInDays(now()) + 1 
                : 0,
            'days_remaining' => $schedule->days_remaining,
            'progress_percent' => $schedule->progress_percentage,
            'total_displays_planned' => $schedule->total_displays,
            'actual_displays' => $schedule->actual_displays,
            'completion_rate' => $schedule->calculateCompletionRate(),
            'total_cost' => $schedule->total_cost,
            'cost_per_day' => $schedule->daily_cost,
            'is_currently_running' => $schedule->isCurrentlyRunning(),
            'is_in_time_slot' => $schedule->isInCurrentTimeSlot(),
        ];
    }
}
