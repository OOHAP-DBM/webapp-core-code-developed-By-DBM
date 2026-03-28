<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetAvailabilityCalendarRequest;
use App\Http\Requests\CheckMultipleDatesRequest;
use App\Models\Hoarding;
use Modules\Hoardings\Services\HoardingAvailabilityService;
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

    /**
     * @OA\Get(
     *     path="/hoardings/{hoarding}/availability/calendar",
     *     summary="Get availability calendar for a hoarding",
     *     tags={"Hoarding Availability"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="hoarding", in="path", required=true, @OA\Schema(type="integer")),
     *    @OA\Parameter(
                name="start_date",
                in="query",
                required=true,
                description="Start date in YYYY-MM-DD format",
                @OA\Schema(
                    type="string",
                    format="date",
                    example="2026-03-28",
                    pattern="^\d{4}-\d{2}-\d{2}$"
                )
            ),
     *     @OA\Parameter(
                name="end_date",
                in="query",
                required=true,
                description="End date in YYYY-MM-DD format",
                @OA\Schema(
                    type="string",
                    format="date",
                    example="2026-04-05",
                    pattern="^\d{4}-\d{2}-\d{2}$"
                )
            ),
     *     @OA\Parameter(name="include_details", in="query", required=false, @OA\Schema(type="boolean")),
     *     @OA\Response(response=200, description="Availability calendar retrieved successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
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

    /**
     * @OA\Get(
     *     path="/hoardings/{hoarding}/availability/summary",
     *     summary="Get availability summary (counts only)",
     *     tags={"Hoarding Availability"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="hoarding", in="path", required=true, @OA\Schema(type="integer")),
     *    @OA\Parameter(
                name="start_date",
                in="query",
                required=true,
                description="Start date in YYYY-MM-DD format",
                @OA\Schema(
                    type="string",
                    format="date",
                    example="2026-03-28",
                    pattern="^\d{4}-\d{2}-\d{2}$"
                )
            ),
     *    @OA\Parameter(
                name="end_date",
                in="query",
                required=true,
                description="End date in YYYY-MM-DD format",
                @OA\Schema(
                    type="string",
                    format="date",
                    example="2026-04-05",
                    pattern="^\d{4}-\d{2}-\d{2}$"
                )
            ),
     *     @OA\Response(response=200, description="Availability summary retrieved successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
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

    /**
     * @OA\Get(
     *     path="/hoardings/{hoarding}/availability/month/{year}/{month}",
     *     summary="Get month calendar (optimized for monthly view)",
     *     tags={"Hoarding Availability"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="hoarding", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="year", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="month", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Month calendar retrieved successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    // public function getMonthCalendar(Request $request, Hoarding $hoarding, int $year, int $month): JsonResponse
    // {
    //     if ($year < 2020 || $year > 2100) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid year. Must be between 2020 and 2100.',
    //         ], 422);
    //     }

    //     if ($month < 1 || $month > 12) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid month. Must be between 1 and 12.',
    //         ], 422);
    //     }

    //     $data = $this->availabilityService->getMonthCalendar($hoarding->id, $year, $month);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Month calendar retrieved successfully',
    //         'data' => $data,
    //     ]);
    // }

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

    /**
     * @OA\Post(
     *     path="/hoardings/{hoarding}/availability/check-dates",
     *     summary="Check availability for specific dates (batch check)",
     *     tags={"Hoarding Availability"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="hoarding", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="application/json",
     *             @OA\Schema(type="object",
     *                 @OA\Property(property="dates", type="array", @OA\Items(type="string", format="date"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Date availability checked successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    // public function checkMultipleDates(CheckMultipleDatesRequest $request, Hoarding $hoarding): JsonResponse
    // {
    //     $results = $this->availabilityService->checkMultipleDates(
    //         $hoarding->id,
    //         $request->input('dates')
    //     );

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Date availability checked successfully',
    //         'data' => [
    //             'hoarding_id' => $hoarding->id,
    //             'requested_dates' => $request->input('dates'),
    //             'results' => $results,
    //         ],
    //     ]);
    // }

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

    /**
     * @OA\Get(
     *     path="/hoardings/{hoarding}/availability/next-available",
     *     summary="Get next N available dates",
     *     tags={"Hoarding Availability"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="hoarding", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="count", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="start_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="max_search_days", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Next available dates retrieved successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
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

    /**
     * @OA\Get(
     *     path="/hoardings/{hoarding}/availability/heatmap",
     *     summary="Get availability heatmap data (for visualization)",
     *     tags={"Hoarding Availability"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="hoarding", in="path", required=true, @OA\Schema(type="integer")),
     *    @OA\Parameter(
                name="start_date",
                in="query",
                required=true,
                description="Start date in YYYY-MM-DD format",
                @OA\Schema(
                    type="string",
                    format="date",
                    example="2026-03-28",
                    pattern="^\d{4}-\d{2}-\d{2}$"
                )
            ),
     *     @OA\Parameter(
                name="end_date",
                in="query",
                required=true,
                description="End date in YYYY-MM-DD format",
                @OA\Schema(
                    type="string",
                    format="date",
                    example="2026-04-05",
                    pattern="^\d{4}-\d{2}-\d{2}$"
                )
            ),
     *     @OA\Response(response=200, description="Heatmap data retrieved successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function getHeatmap(GetAvailabilityCalendarRequest $request, Hoarding $hoarding): JsonResponse
    {
        try {
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
        } catch (\Throwable $e) {
            \Log::error('Heatmap error for hoarding ' . $hoarding->id . ': ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Internal server error retrieving heatmap',
            ], 500);
        }
    }

    /**
     * Get quick status check (lightweight, single date)
     * 
     * GET /hoardings/{hoarding}/availability/quick-check
     * 
     * Query Parameters:
     * - date (required): YYYY-MM-DD
     * 
     * @param Request $request
     * @param Hoarding $hoarding
     * @return JsonResponse
     */

    /**
     * @OA\Get(
     *     path="/hoardings/{hoarding}/availability/quick-check",
     *     summary="Get quick status check (lightweight, single date)",
     *     tags={"Hoarding Availability"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="hoarding", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="date", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="Quick availability check completed"),
     *     @OA\Response(response=422, description="Validation error")
     * )
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