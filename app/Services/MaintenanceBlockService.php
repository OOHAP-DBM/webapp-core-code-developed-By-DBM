<?php

namespace App\Services;

use App\Models\MaintenanceBlock;
use App\Models\Hoarding;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * PROMPT 102: Admin Blocking Periods (Maintenance/Repairs)
 * 
 * Service for managing maintenance blocks on hoardings
 * Handles creation, validation, conflict detection, and calendar integration
 */
class MaintenanceBlockService
{
    /**
     * Create a new maintenance block
     * 
     * @param array $data
     * @param int $creatorId User ID (admin or vendor)
     * @return MaintenanceBlock
     * @throws ValidationException
     */
    public function create(array $data, int $creatorId): MaintenanceBlock
    {
        // Validate dates
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($endDate->lt($startDate)) {
            throw ValidationException::withMessages([
                'end_date' => ['End date must be after or equal to start date.']
            ]);
        }

        // Check for overlapping active blocks
        $existingBlocks = MaintenanceBlock::forHoarding($data['hoarding_id'])
            ->active()
            ->overlapping($startDate, $endDate)
            ->count();

        if ($existingBlocks > 0) {
            throw ValidationException::withMessages([
                'dates' => ['Another active maintenance block already exists for these dates.']
            ]);
        }

        // Create the block
        $block = MaintenanceBlock::create([
            'hoarding_id' => $data['hoarding_id'],
            'created_by' => $creatorId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $data['status'] ?? MaintenanceBlock::STATUS_ACTIVE,
            'block_type' => $data['block_type'] ?? MaintenanceBlock::TYPE_MAINTENANCE,
            'affected_by' => $data['affected_by'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $block->load('hoarding', 'creator');
    }

    /**
     * Update an existing maintenance block
     * 
     * @param MaintenanceBlock $block
     * @param array $data
     * @return MaintenanceBlock
     * @throws ValidationException
     */
    public function update(MaintenanceBlock $block, array $data): MaintenanceBlock
    {
        // If dates are being changed, validate
        if (isset($data['start_date']) || isset($data['end_date'])) {
            $startDate = isset($data['start_date']) 
                ? Carbon::parse($data['start_date']) 
                : $block->start_date;
            
            $endDate = isset($data['end_date']) 
                ? Carbon::parse($data['end_date']) 
                : $block->end_date;

            if ($endDate->lt($startDate)) {
                throw ValidationException::withMessages([
                    'end_date' => ['End date must be after or equal to start date.']
                ]);
            }

            // Check for overlapping blocks (excluding this one)
            $existingBlocks = MaintenanceBlock::forHoarding($block->hoarding_id)
                ->active()
                ->where('id', '!=', $block->id)
                ->overlapping($startDate, $endDate)
                ->count();

            if ($existingBlocks > 0) {
                throw ValidationException::withMessages([
                    'dates' => ['Another active maintenance block already exists for these dates.']
                ]);
            }

            $data['start_date'] = $startDate;
            $data['end_date'] = $endDate;
        }

        $block->update($data);

        return $block->load('hoarding', 'creator');
    }

    /**
     * Delete a maintenance block
     * 
     * @param MaintenanceBlock $block
     * @return bool
     */
    public function delete(MaintenanceBlock $block): bool
    {
        return $block->delete();
    }

    /**
     * Mark block as completed
     * 
     * @param MaintenanceBlock $block
     * @return MaintenanceBlock
     */
    public function markCompleted(MaintenanceBlock $block): MaintenanceBlock
    {
        $block->markCompleted();
        return $block->load('hoarding', 'creator');
    }

    /**
     * Mark block as cancelled
     * 
     * @param MaintenanceBlock $block
     * @return MaintenanceBlock
     */
    public function markCancelled(MaintenanceBlock $block): MaintenanceBlock
    {
        $block->markCancelled();
        return $block->load('hoarding', 'creator');
    }

    /**
     * Get all blocks for a hoarding
     * 
     * @param int $hoardingId
     * @param array $filters ['status', 'block_type', 'start_date', 'end_date']
     * @return Collection
     */
    public function getBlocksForHoarding(int $hoardingId, array $filters = []): Collection
    {
        $query = MaintenanceBlock::forHoarding($hoardingId)
            ->with('creator:id,name,email');

        // Filter by status
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Filter by block type
        if (isset($filters['block_type'])) {
            $query->byType($filters['block_type']);
        }

        // Filter by date range
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->overlapping($filters['start_date'], $filters['end_date']);
        }

        return $query->orderBy('start_date')->get();
    }

    /**
     * Check if hoarding is available (no active maintenance blocks)
     * 
     * @param int $hoardingId
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return array ['available' => bool, 'blocks' => Collection, 'message' => string]
     */
    public function checkAvailability(int $hoardingId, $startDate, $endDate): array
    {
        $blocks = MaintenanceBlock::getActiveBlocks($hoardingId, $startDate, $endDate);

        if ($blocks->isEmpty()) {
            return [
                'available' => true,
                'blocks' => collect([]),
                'message' => 'Hoarding is available for the selected dates.',
            ];
        }

        return [
            'available' => false,
            'blocks' => $blocks,
            'message' => sprintf(
                'Hoarding has %d active maintenance block(s) during this period: %s',
                $blocks->count(),
                $blocks->pluck('title')->join(', ')
            ),
        ];
    }

    /**
     * Get all blocked dates for a hoarding (for calendar display)
     * 
     * @param int $hoardingId
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return array Array of dates with block information
     */
    public function getBlockedDates(int $hoardingId, $startDate, $endDate): array
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        $blocks = MaintenanceBlock::forHoarding($hoardingId)
            ->active()
            ->overlapping($start, $end)
            ->get();

        $blockedDates = [];

        foreach ($blocks as $block) {
            $blockStart = $block->start_date->greaterThan($start) ? $block->start_date : $start;
            $blockEnd = $block->end_date->lessThan($end) ? $block->end_date : $end;

            $currentDate = $blockStart->copy();
            while ($currentDate->lessThanOrEqualTo($blockEnd)) {
                $dateKey = $currentDate->format('Y-m-d');
                
                if (!isset($blockedDates[$dateKey])) {
                    $blockedDates[$dateKey] = [
                        'date' => $dateKey,
                        'blocks' => [],
                    ];
                }

                $blockedDates[$dateKey]['blocks'][] = [
                    'id' => $block->id,
                    'title' => $block->title,
                    'block_type' => $block->block_type,
                    'description' => $block->description,
                ];

                $currentDate->addDay();
            }
        }

        return array_values($blockedDates);
    }

    /**
     * Get statistics for a hoarding's maintenance blocks
     * 
     * @param int $hoardingId
     * @param string|Carbon|null $startDate Optional date range
     * @param string|Carbon|null $endDate
     * @return array
     */
    public function getStatistics(int $hoardingId, $startDate = null, $endDate = null): array
    {
        $query = MaintenanceBlock::forHoarding($hoardingId);

        if ($startDate && $endDate) {
            $query->overlapping($startDate, $endDate);
        }

        $allBlocks = $query->get();

        return [
            'total_blocks' => $allBlocks->count(),
            'active_blocks' => $allBlocks->where('status', MaintenanceBlock::STATUS_ACTIVE)->count(),
            'completed_blocks' => $allBlocks->where('status', MaintenanceBlock::STATUS_COMPLETED)->count(),
            'cancelled_blocks' => $allBlocks->where('status', MaintenanceBlock::STATUS_CANCELLED)->count(),
            'by_type' => [
                'maintenance' => $allBlocks->where('block_type', MaintenanceBlock::TYPE_MAINTENANCE)->count(),
                'repair' => $allBlocks->where('block_type', MaintenanceBlock::TYPE_REPAIR)->count(),
                'inspection' => $allBlocks->where('block_type', MaintenanceBlock::TYPE_INSPECTION)->count(),
                'other' => $allBlocks->where('block_type', MaintenanceBlock::TYPE_OTHER)->count(),
            ],
            'total_blocked_days' => $allBlocks->sum(fn($block) => $block->getDurationDays()),
            'current_blocks' => MaintenanceBlock::forHoarding($hoardingId)
                ->active()
                ->current()
                ->count(),
            'future_blocks' => MaintenanceBlock::forHoarding($hoardingId)
                ->active()
                ->future()
                ->where('start_date', '>', Carbon::today())
                ->count(),
        ];
    }

    /**
     * Get blocks that conflict with existing bookings
     * Used to warn admin/vendor before creating a block
     * 
     * @param int $hoardingId
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return Collection Collection of conflicting bookings
     */
    public function getConflictingBookings(int $hoardingId, $startDate, $endDate): Collection
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        return Booking::where('hoarding_id', $hoardingId)
            ->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_PAYMENT_HOLD,
                Booking::STATUS_PENDING_PAYMENT_HOLD,
            ])
            ->where(function ($q) use ($start, $end) {
                $q->where('start_date', '<=', $end->format('Y-m-d'))
                  ->where('end_date', '>=', $start->format('Y-m-d'));
            })
            ->with('customer:id,name,email')
            ->orderBy('start_date')
            ->get();
    }

    /**
     * Create maintenance block with booking conflict check
     * Returns warnings if bookings will be affected
     * 
     * @param array $data
     * @param int $creatorId
     * @param bool $forceCreate If true, create even with conflicts
     * @return array ['success' => bool, 'block' => MaintenanceBlock|null, 'warnings' => array]
     */
    public function createWithConflictCheck(
        array $data,
        int $creatorId,
        bool $forceCreate = false
    ): array {
        $conflictingBookings = $this->getConflictingBookings(
            $data['hoarding_id'],
            $data['start_date'],
            $data['end_date']
        );

        $warnings = [];
        if ($conflictingBookings->isNotEmpty()) {
            $warnings[] = sprintf(
                'WARNING: %d existing booking(s) will be affected by this maintenance block.',
                $conflictingBookings->count()
            );
            
            foreach ($conflictingBookings as $booking) {
                $warnings[] = sprintf(
                    'Booking #%d (%s to %s) for %s',
                    $booking->id,
                    $booking->start_date->format('Y-m-d'),
                    $booking->end_date->format('Y-m-d'),
                    $booking->customer->name ?? 'Unknown'
                );
            }

            if (!$forceCreate) {
                return [
                    'success' => false,
                    'block' => null,
                    'warnings' => $warnings,
                    'conflicting_bookings' => $conflictingBookings,
                ];
            }
        }

        $block = $this->create($data, $creatorId);

        return [
            'success' => true,
            'block' => $block,
            'warnings' => $warnings,
            'conflicting_bookings' => $conflictingBookings,
        ];
    }

    /**
     * Batch create multiple blocks (e.g., recurring maintenance)
     * 
     * @param array $blocks Array of block data
     * @param int $creatorId
     * @return array ['created' => int, 'blocks' => Collection, 'errors' => array]
     */
    public function batchCreate(array $blocks, int $creatorId): array
    {
        $created = [];
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($blocks as $index => $blockData) {
                try {
                    $block = $this->create($blockData, $creatorId);
                    $created[] = $block;
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'data' => $blockData,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            if (empty($errors)) {
                DB::commit();
                return [
                    'created' => count($created),
                    'blocks' => collect($created),
                    'errors' => [],
                ];
            } else {
                DB::rollBack();
                return [
                    'created' => 0,
                    'blocks' => collect([]),
                    'errors' => $errors,
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
