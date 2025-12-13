<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckBookingOverlapRequest;
use App\Http\Requests\BatchOverlapCheckRequest;
use App\Http\Requests\AvailabilityReportRequest;
use App\Services\BookingOverlapValidator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PROMPT 101: Booking Overlap Validation API Controller
 * 
 * Endpoints for checking booking date conflicts and availability
 */
class BookingOverlapController extends Controller
{
    protected BookingOverlapValidator $validator;

    public function __construct(BookingOverlapValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Check if dates are available (single date range)
     * 
     * POST /api/v1/bookings/check-overlap
     * 
     * @param CheckBookingOverlapRequest $request
     * @return JsonResponse
     */
    public function checkOverlap(CheckBookingOverlapRequest $request): JsonResponse
    {
        try {
            $validated = $request->validatedWithDefaults();

            $result = $this->validator->validateAvailability(
                $validated['hoarding_id'],
                $validated['start_date'],
                $validated['end_date'],
                $validated['exclude_booking_id'],
                $validated['include_grace_period']
            );

            // Return detailed or simple response based on request
            if ($validated['detailed']) {
                return response()->json([
                    'success' => true,
                    'data' => $result,
                ]);
            }

            return response()->json([
                'success' => true,
                'available' => $result['available'],
                'message' => $result['message'],
                'conflicts_count' => $result['available'] ? 0 : $result['conflicts']->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Quick availability check (returns only boolean)
     * 
     * GET /api/v1/bookings/is-available
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function isAvailable(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'hoarding_id' => 'required|integer|exists:hoardings,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after:start_date',
                'exclude_booking_id' => 'nullable|integer|exists:bookings,id',
            ]);

            $available = $this->validator->isAvailable(
                $validated['hoarding_id'],
                $validated['start_date'],
                $validated['end_date'],
                $validated['exclude_booking_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'available' => $available,
                'hoarding_id' => $validated['hoarding_id'],
                'dates' => [
                    'start' => $validated['start_date'],
                    'end' => $validated['end_date'],
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batch check multiple date ranges
     * 
     * POST /api/v1/bookings/batch-check-overlap
     * 
     * @param BatchOverlapCheckRequest $request
     * @return JsonResponse
     */
    public function batchCheck(BatchOverlapCheckRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $result = $this->validator->validateMultipleDateRanges(
                $validated['hoarding_id'],
                $validated['date_ranges']
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process batch check',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get occupied dates within a date range
     * 
     * GET /api/v1/bookings/occupied-dates
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getOccupiedDates(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'hoarding_id' => 'required|integer|exists:hoardings,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            $start = Carbon::parse($validated['start_date']);
            $end = Carbon::parse($validated['end_date']);

            $occupiedDates = $this->validator->getOccupiedDates(
                $validated['hoarding_id'],
                $start,
                $end
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'hoarding_id' => $validated['hoarding_id'],
                    'period' => [
                        'start' => $start->format('Y-m-d'),
                        'end' => $end->format('Y-m-d'),
                        'days' => $start->diffInDays($end) + 1,
                    ],
                    'occupied_dates' => $occupiedDates,
                    'total_occupied_days' => count($occupiedDates),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get occupied dates',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Find next available slot for given duration
     * 
     * GET /api/v1/bookings/find-next-slot
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function findNextSlot(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'hoarding_id' => 'required|integer|exists:hoardings,id',
                'duration_days' => 'required|integer|min:1|max:365',
                'search_from' => 'nullable|date|after_or_equal:today',
                'max_search_days' => 'nullable|integer|min:7|max:365',
            ]);

            $searchFrom = isset($validated['search_from']) 
                ? Carbon::parse($validated['search_from']) 
                : null;

            $slot = $this->validator->findNextAvailableSlot(
                $validated['hoarding_id'],
                $validated['duration_days'],
                $searchFrom,
                $validated['max_search_days'] ?? 90
            );

            if ($slot) {
                return response()->json([
                    'success' => true,
                    'slot_found' => true,
                    'data' => [
                        'start_date' => $slot['start_date']->format('Y-m-d'),
                        'end_date' => $slot['end_date']->format('Y-m-d'),
                        'duration_days' => $slot['duration_days'],
                    ],
                    'message' => 'Available slot found',
                ]);
            }

            return response()->json([
                'success' => true,
                'slot_found' => false,
                'message' => 'No available slot found within search range',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to find next slot',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get comprehensive availability report
     * 
     * GET /api/v1/bookings/availability-report
     * 
     * @param AvailabilityReportRequest $request
     * @return JsonResponse
     */
    public function getAvailabilityReport(AvailabilityReportRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $start = Carbon::parse($validated['start_date']);
            $end = Carbon::parse($validated['end_date']);

            $report = $this->validator->getAvailabilityReport(
                $validated['hoarding_id'],
                $start,
                $end
            );

            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate availability report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get conflicts for specific date range (detailed)
     * 
     * GET /api/v1/bookings/get-conflicts
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getConflicts(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'hoarding_id' => 'required|integer|exists:hoardings,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'exclude_booking_id' => 'nullable|integer|exists:bookings,id',
            ]);

            $result = $this->validator->validateAvailability(
                $validated['hoarding_id'],
                $validated['start_date'],
                $validated['end_date'],
                $validated['exclude_booking_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'has_conflicts' => !$result['available'],
                'conflicts' => $result['available'] ? [] : $result['conflicts']->values()->toArray(),
                'conflict_details' => $result['available'] ? null : $result['conflict_details'],
                'message' => $result['message'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get conflicts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
