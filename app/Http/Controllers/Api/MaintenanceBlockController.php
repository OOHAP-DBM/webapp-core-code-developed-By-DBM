<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateMaintenanceBlockRequest;
use App\Http\Requests\UpdateMaintenanceBlockRequest;
use App\Models\MaintenanceBlock;
use App\Models\Hoarding;
use App\Services\MaintenanceBlockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * PROMPT 102: Admin Blocking Periods (Maintenance/Repairs)
 * 
 * API Controller for managing maintenance blocks
 * Admin and Vendors can create, update, delete blocks
 */
class MaintenanceBlockController extends Controller
{
    protected MaintenanceBlockService $service;

    public function __construct(MaintenanceBlockService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all maintenance blocks for a hoarding
     * 
     * GET /api/v1/maintenance-blocks?hoarding_id=1&status=active
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'hoarding_id' => 'required|integer|exists:hoardings,id',
            'status' => 'nullable|in:active,completed,cancelled',
            'block_type' => 'nullable|in:maintenance,repair,inspection,other',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $filters = $request->only(['status', 'block_type', 'start_date', 'end_date']);
            $blocks = $this->service->getBlocksForHoarding($request->hoarding_id, $filters);

            return response()->json([
                'success' => true,
                'data' => $blocks,
                'count' => $blocks->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve maintenance blocks.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific maintenance block
     * 
     * GET /api/v1/maintenance-blocks/{id}
     */
    public function show(MaintenanceBlock $maintenanceBlock): JsonResponse
    {
        // Check authorization
        $user = Auth::user();
        $hoarding = $maintenanceBlock->hoarding;

        if (!$this->canManageBlock($user, $hoarding)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to view this block.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $maintenanceBlock->load('hoarding', 'creator'),
        ]);
    }

    /**
     * Create a new maintenance block
     * 
     * POST /api/v1/maintenance-blocks
     */
    public function store(CreateMaintenanceBlockRequest $request): JsonResponse
    {
        try {
            // Check authorization
            $hoarding = Hoarding::findOrFail($request->hoarding_id);
            $user = Auth::user();

            if (!$this->canManageBlock($user, $hoarding)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You do not have permission to create blocks for this hoarding.',
                ], 403);
            }

            // Create with conflict check
            $forceCreate = $request->boolean('force_create', false);
            $result = $this->service->createWithConflictCheck(
                $request->validated(),
                $user->id,
                $forceCreate
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create maintenance block due to conflicts.',
                    'warnings' => $result['warnings'],
                    'conflicting_bookings' => $result['conflicting_bookings'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Maintenance block created successfully.',
                'data' => $result['block'],
                'warnings' => $result['warnings'],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create maintenance block.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing maintenance block
     * 
     * PUT /api/v1/maintenance-blocks/{id}
     */
    public function update(UpdateMaintenanceBlockRequest $request, MaintenanceBlock $maintenanceBlock): JsonResponse
    {
        try {
            // Check authorization
            $user = Auth::user();
            $hoarding = $maintenanceBlock->hoarding;

            if (!$this->canManageBlock($user, $hoarding)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You do not have permission to update this block.',
                ], 403);
            }

            $block = $this->service->update($maintenanceBlock, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Maintenance block updated successfully.',
                'data' => $block,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update maintenance block.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a maintenance block
     * 
     * DELETE /api/v1/maintenance-blocks/{id}
     */
    public function destroy(MaintenanceBlock $maintenanceBlock): JsonResponse
    {
        try {
            // Check authorization
            $user = Auth::user();
            $hoarding = $maintenanceBlock->hoarding;

            if (!$this->canManageBlock($user, $hoarding)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You do not have permission to delete this block.',
                ], 403);
            }

            $this->service->delete($maintenanceBlock);

            return response()->json([
                'success' => true,
                'message' => 'Maintenance block deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete maintenance block.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark block as completed
     * 
     * POST /api/v1/maintenance-blocks/{id}/complete
     */
    public function markCompleted(MaintenanceBlock $maintenanceBlock): JsonResponse
    {
        try {
            // Check authorization
            $user = Auth::user();
            $hoarding = $maintenanceBlock->hoarding;

            if (!$this->canManageBlock($user, $hoarding)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 403);
            }

            $block = $this->service->markCompleted($maintenanceBlock);

            return response()->json([
                'success' => true,
                'message' => 'Maintenance block marked as completed.',
                'data' => $block,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark block as completed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark block as cancelled
     * 
     * POST /api/v1/maintenance-blocks/{id}/cancel
     */
    public function markCancelled(MaintenanceBlock $maintenanceBlock): JsonResponse
    {
        try {
            // Check authorization
            $user = Auth::user();
            $hoarding = $maintenanceBlock->hoarding;

            if (!$this->canManageBlock($user, $hoarding)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 403);
            }

            $block = $this->service->markCancelled($maintenanceBlock);

            return response()->json([
                'success' => true,
                'message' => 'Maintenance block cancelled.',
                'data' => $block,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel block.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check availability (no active maintenance blocks)
     * 
     * GET /api/v1/maintenance-blocks/check-availability
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'hoarding_id' => 'required|integer|exists:hoardings,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $result = $this->service->checkAvailability(
                $request->hoarding_id,
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get blocked dates for calendar display
     * 
     * GET /api/v1/maintenance-blocks/blocked-dates
     */
    public function getBlockedDates(Request $request): JsonResponse
    {
        $request->validate([
            'hoarding_id' => 'required|integer|exists:hoardings,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $blockedDates = $this->service->getBlockedDates(
                $request->hoarding_id,
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'success' => true,
                'data' => $blockedDates,
                'count' => count($blockedDates),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve blocked dates.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get statistics for a hoarding's maintenance blocks
     * 
     * GET /api/v1/maintenance-blocks/statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $request->validate([
            'hoarding_id' => 'required|integer|exists:hoardings,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $statistics = $this->service->getStatistics(
                $request->hoarding_id,
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get conflicting bookings for a date range
     * Used to warn before creating a block
     * 
     * GET /api/v1/maintenance-blocks/conflicting-bookings
     */
    public function getConflictingBookings(Request $request): JsonResponse
    {
        $request->validate([
            'hoarding_id' => 'required|integer|exists:hoardings,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $bookings = $this->service->getConflictingBookings(
                $request->hoarding_id,
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'success' => true,
                'data' => $bookings,
                'count' => $bookings->count(),
                'has_conflicts' => $bookings->isNotEmpty(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check for conflicts.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: Check if user can manage blocks for this hoarding
     * Admin can manage all, Vendor can only manage their own hoardings
     */
    protected function canManageBlock($user, Hoarding $hoarding): bool
    {
        // Admin can manage all blocks
        if ($user->hasRole(['admin', 'super_admin'])) {
            return true;
        }

        // Vendor can only manage blocks for their own hoardings
        if ($user->hasRole(['vendor', 'subvendor'])) {
            return $hoarding->vendor_id === $user->id;
        }

        return false;
    }
}
