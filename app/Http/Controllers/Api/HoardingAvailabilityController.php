<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetAvailabilityCalendarRequest;
use App\Http\Requests\CheckMultipleDatesRequest;
use App\Models\Hoarding;
use App\Services\HoardingAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PROMPT 104: Hoarding Availability API for Frontend Calendar
 * 
 * Controller for availability calendar endpoints
 * Returns date availability status for calendar heatmap UI
 */
class HoardingAvailabilityController extends Controller
{
    protected HoardingAvailabilityService $availabilityService;

    public function __construct(HoardingAvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Get availability calendar for a hoarding
     * 
     * GET /api/v1/hoardings/{hoarding}/availability/calendar
     * 
     * Query Parameters:
     * - start_date (required): YYYY-MM-DD
     * - end_date (required): YYYY-MM-DD
     * - include_details (optional): true/false, default false
     * 
     * Response Statuses:
     * - available: No conflicts, can be booked
     * - booked: Has confirmed booking or POS booking
     * - blocked: Maintenance block active
     * - hold: Active payment hold
     * - partial: Multiple statuses on same date
     * 
     * @param GetAvailabilityCalendarRequest $request
     * @param Hoarding $hoarding
     * @return JsonResponse
     */
    public function getCalendar(GetAvailabilityCalendarRequest $request, Hoarding $hoarding): JsonResponse
    {
        $data = $this->availabilityService->getAvailabilityCalendar(
            $hoarding->id,
            $request->input('start_date'),
            $request->input('end_date'),
            $request->boolean('include_details', false)
        );

        return response()->json([
            'success' => true,
            'message' => 'Availability calendar retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get availability summary (counts only)
     * 
     * GET /api/v1/hoardings/{hoarding}/availability/summary
     * 
     * @param GetAvailabilityCalendarRequest $request
     * @param Hoarding $hoarding
     * @return JsonResponse
     */
    public function getSummary(GetAvailabilityCalendarRequest $request, Hoarding $hoarding): JsonResponse
    {
        $summary = $this->availabilityService->getAvailabilitySummary(
            $hoarding->id,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json([
            'success' => true,
            'message' => 'Availability summary retrieved successfully',
            'data' => [
                'hoarding_id' => $hoarding->id,
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'summary' => $summary,
            ],
        ]);
    }

    /**
     * Get month calendar (optimized for monthly view)
     * 
     * GET /api/v1/hoardings/{hoarding}/availability/month/{year}/{month}
     * 
     * @param Request $request
     * @param Hoarding $hoarding
     * @param int $year
     * @param int $month
     * @return JsonResponse
     */
    public function getMonthCalendar(Request $request, Hoarding $hoarding, int $year, int $month): JsonResponse
    {
        // Validate year and month
        if ($year < 2020 || $year > 2100) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid year. Must be between 2020 and 2100.',
            ], 422);
        }

        if ($month < 1 || $month > 12) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid month. Must be between 1 and 12.',
            ], 422);
        }

        $data = $this->availabilityService->getMonthCalendar($hoarding->id, $year, $month);

        return response()->json([
            'success' => true,
            'message' => 'Month calendar retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Check availability for specific dates (batch check)
     * 
     * POST /api/v1/hoardings/{hoarding}/availability/check-dates
     * 
     * Body:
     * {
     *   "dates": ["2025-12-20", "2025-12-21", "2025-12-22"]
     * }
     * 
     * @param CheckMultipleDatesRequest $request
     * @param Hoarding $hoarding
     * @return JsonResponse
     */
    public function checkMultipleDates(CheckMultipleDatesRequest $request, Hoarding $hoarding): JsonResponse
    {
        $results = $this->availabilityService->checkMultipleDates(
            $hoarding->id,
            $request->input('dates')
        );

        return response()->json([
            'success' => true,
            'message' => 'Date availability checked successfully',
            'data' => [
                'hoarding_id' => $hoarding->id,
                'requested_dates' => $request->input('dates'),
                'results' => $results,
            ],
        ]);
    }

    /**
     * Get next N available dates
     * 
     * GET /api/v1/hoardings/{hoarding}/availability/next-available
     * 
     * Query Parameters:
     * - count (optional): Number of dates to find, default 10
     * - start_from (optional): Start date, default today
     * - max_search_days (optional): Max days to search, default 365
     * 
     * @param Request $request
     * @param Hoarding $hoarding
     * @return JsonResponse
     */
    public function getNextAvailable(Request $request, Hoarding $hoarding): JsonResponse
    {
        $request->validate([
            'count' => 'nullable|integer|min:1|max:100',
            'start_from' => 'nullable|date|after_or_equal:today',
            'max_search_days' => 'nullable|integer|min:1|max:730',
        ]);

        $data = $this->availabilityService->getNextAvailableDates(
            $hoarding->id,
            $request->integer('count', 10),
            $request->input('start_from'),
            $request->integer('max_search_days', 365)
        );

        return response()->json([
            'success' => true,
            'message' => 'Next available dates retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get availability heatmap data (for visualization)
     * 
     * GET /api/v1/hoardings/{hoarding}/availability/heatmap
     * 
     * Returns color-coded data for calendar heatmap:
     * - available: green (#22c55e)
     * - booked: red (#ef4444)
     * - blocked: gray (#6b7280)
     * - hold: yellow (#eab308)
     * - partial: orange (#f97316)
     * 
     * @param GetAvailabilityCalendarRequest $request
     * @param Hoarding $hoarding
     * @return JsonResponse
     */
    public function getHeatmap(GetAvailabilityCalendarRequest $request, Hoarding $hoarding): JsonResponse
    {
        $calendar = $this->availabilityService->getAvailabilityCalendar(
            $hoarding->id,
            $request->input('start_date'),
            $request->input('end_date'),
            false
        );

        // Transform to heatmap format
        $heatmapData = collect($calendar['calendar'])->map(function ($day) {
            $colors = [
                'available' => '#22c55e',
                'booked' => '#ef4444',
                'blocked' => '#6b7280',
                'hold' => '#eab308',
                'partial' => '#f97316',
            ];

            $labels = [
                'available' => 'Available',
                'booked' => 'Booked',
                'blocked' => 'Blocked (Maintenance)',
                'hold' => 'On Hold',
                'partial' => 'Multiple Statuses',
            ];

            return [
                'date' => $day['date'],
                'status' => $day['status'],
                'color' => $colors[$day['status']] ?? '#9ca3af',
                'label' => $labels[$day['status']] ?? 'Unknown',
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Heatmap data retrieved successfully',
            'data' => [
                'hoarding_id' => $hoarding->id,
                'start_date' => $calendar['start_date'],
                'end_date' => $calendar['end_date'],
                'summary' => $calendar['summary'],
                'heatmap' => $heatmapData,
            ],
        ]);
    }

    /**
     * Get quick status check (lightweight, single date)
     * 
     * GET /api/v1/hoardings/{hoarding}/availability/quick-check
     * 
     * Query Parameters:
     * - date (required): YYYY-MM-DD
     * 
     * @param Request $request
     * @param Hoarding $hoarding
     * @return JsonResponse
     */
    public function quickCheck(Request $request, Hoarding $hoarding): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->input('date');
        $results = $this->availabilityService->checkMultipleDates($hoarding->id, [$date]);

        return response()->json([
            'success' => true,
            'message' => 'Quick availability check completed',
            'data' => [
                'hoarding_id' => $hoarding->id,
                'date' => $date,
                'status' => $results[0]['status'] ?? 'unknown',
                'details' => $results[0]['details'] ?? null,
            ],
        ]);
    }
}
