<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\DOOH\Models\DOOHSlot;
use App\Models\Hoarding;
use App\Models\Booking;
use App\Services\DOOHSlotService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DOOHSlotController extends Controller
{
    protected $slotService;

    public function __construct(DOOHSlotService $slotService)
    {
        $this->slotService = $slotService;
    }

    /**
     * Display DOOH slots for a hoarding
     */
    public function index(Hoarding $hoarding)
    {
        $slots = $hoarding->doohSlots()->with('booking')->paginate(20);
        $stats = $hoarding->getDOOHStats();

        return view('admin.dooh-slots.index', compact('hoarding', 'slots', 'stats'));
    }

    /**
     * Show create slot form
     */
    public function create(Hoarding $hoarding)
    {
        return view('admin.dooh-slots.create', compact('hoarding'));
    }

    /**
     * Store new DOOH slot
     */
    public function store(Request $request, Hoarding $hoarding)
    {
        $validated = $request->validate([
            'slot_name' => 'nullable|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'duration_seconds' => 'required|integer|min:5|max:60',
            'frequency_per_hour' => 'required|integer|min:1|max:60',
            'price_per_display' => 'required|numeric|min:0',
            'is_prime_time' => 'boolean',
            'ads_in_loop' => 'nullable|integer|min:1',
            'loop_position' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $validated['hoarding_id'] = $hoarding->id;
        $validated['start_time'] = $validated['start_time'] . ':00';
        $validated['end_time'] = $validated['end_time'] . ':00';

        $slot = $this->slotService->createSlot($validated);

        return redirect()
            ->route('admin.dooh-slots.show', $slot)
            ->with('success', 'DOOH slot created successfully!');
    }

    /**
     * Show slot details
     */
    public function show(DOOHSlot $slot)
    {
        $slot->load(['hoarding', 'booking']);
        
        // Generate schedule for today
        $todaySchedule = $this->slotService->generateDailySchedule($slot, now());
        
        // Calculate metrics
        $metrics = null;
        if ($slot->start_date && $slot->end_date) {
            $metrics = $this->slotService->calculateMetrics(
                $slot,
                Carbon::parse($slot->start_date),
                Carbon::parse($slot->end_date)
            );
        }

        return view('admin.dooh-slots.show', compact('slot', 'todaySchedule', 'metrics'));
    }

    /**
     * Show edit form
     */
    public function edit(DOOHSlot $slot)
    {
        return view('admin.dooh-slots.edit', compact('slot'));
    }

    /**
     * Update slot
     */
    public function update(Request $request, DOOHSlot $slot)
    {
        $validated = $request->validate([
            'slot_name' => 'nullable|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'duration_seconds' => 'required|integer|min:5|max:60',
            'frequency_per_hour' => 'required|integer|min:1|max:60',
            'price_per_display' => 'required|numeric|min:0',
            'is_prime_time' => 'boolean',
            'ads_in_loop' => 'nullable|integer|min:1',
            'loop_position' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['start_time'] = $validated['start_time'] . ':00';
        $validated['end_time'] = $validated['end_time'] . ':00';

        $slot = $this->slotService->updateSlot($slot, $validated);

        return redirect()
            ->route('admin.dooh-slots.show', $slot)
            ->with('success', 'DOOH slot updated successfully!');
    }

    /**
     * Delete slot
     */
    public function destroy(DOOHSlot $slot)
    {
        if ($slot->status === 'booked') {
            return back()->with('error', 'Cannot delete booked slot. Release booking first.');
        }

        $hoardingId = $slot->hoarding_id;
        $slot->delete();

        return redirect()
            ->route('admin.hoarding.dooh-slots', $hoardingId)
            ->with('success', 'DOOH slot deleted successfully!');
    }

    /**
     * API: Get slot availability
     */
    public function checkAvailability(Request $request, Hoarding $hoarding)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
        ]);

        $startTime = isset($validated['start_time']) ? $validated['start_time'] . ':00' : null;
        $endTime = isset($validated['end_time']) ? $validated['end_time'] . ':00' : null;

        $availability = $this->slotService->checkAvailability(
            $hoarding->id,
            Carbon::parse($validated['start_date']),
            Carbon::parse($validated['end_date']),
            $startTime,
            $endTime
        );

        return response()->json([
            'success' => true,
            'availability' => $availability,
        ]);
    }

    /**
     * API: Calculate booking cost
     */
    public function calculateCost(Request $request)
    {
        $validated = $request->validate([
            'slot_ids' => 'required|array',
            'slot_ids.*' => 'exists:dooh_slots,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $totalCost = 0;
        $totalDisplays = 0;
        $slotDetails = [];

        foreach ($validated['slot_ids'] as $slotId) {
            $slot = DOOHSlot::find($slotId);
            
            if ($slot) {
                $cost = $this->slotService->calculateBookingCost(
                    $slot,
                    Carbon::parse($validated['start_date']),
                    Carbon::parse($validated['end_date'])
                );
                
                $totalCost += $cost['total_cost'];
                $totalDisplays += $cost['total_displays'];
                
                $slotDetails[] = [
                    'slot_id' => $slot->id,
                    'slot_name' => $slot->slot_name,
                    'time_range' => $slot->time_range,
                    'cost_details' => $cost,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'total_cost' => round($totalCost, 2),
            'total_displays' => $totalDisplays,
            'cost_per_display' => $totalDisplays > 0 ? round($totalCost / $totalDisplays, 4) : 0,
            'cpm' => $totalDisplays > 0 ? round(($totalCost / $totalDisplays) * 1000, 2) : 0,
            'slot_details' => $slotDetails,
        ]);
    }

    /**
     * API: Get daily schedule
     */
    public function getDailySchedule(Request $request, DOOHSlot $slot)
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $schedule = $this->slotService->generateDailySchedule(
            $slot,
            Carbon::parse($validated['date'])
        );

        return response()->json([
            'success' => true,
            'schedule' => $schedule,
        ]);
    }

    /**
     * Book slot(s)
     */
    public function book(Request $request)
    {
        $validated = $request->validate([
            'slot_ids' => 'required|array',
            'slot_ids.*' => 'exists:dooh_slots,id',
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        $result = $this->slotService->bookSlots($validated['slot_ids'], $booking);

        if ($result['total_failed'] > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Some slots could not be booked',
                'result' => $result,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Slots booked successfully',
            'result' => $result,
        ]);
    }

    /**
     * Release slot
     */
    public function release(DOOHSlot $slot)
    {
        if ($slot->status !== 'booked') {
            return back()->with('error', 'Slot is not booked.');
        }

        $slot->release();

        return back()->with('success', 'Slot released successfully!');
    }

    /**
     * Block slot
     */
    public function block(Request $request, DOOHSlot $slot)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $slot->block($validated['reason'] ?? null);

        return back()->with('success', 'Slot blocked successfully!');
    }

    /**
     * Mark slot for maintenance
     */
    public function maintenance(Request $request, DOOHSlot $slot)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $slot->markForMaintenance($validated['reason'] ?? null);

        return back()->with('success', 'Slot marked for maintenance!');
    }

    /**
     * API: Calculate optimal frequency
     */
    public function calculateFrequency(Request $request)
    {
        $validated = $request->validate([
            'desired_daily_displays' => 'required|integer|min:1',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ]);

        $result = $this->slotService->calculateOptimalFrequency(
            $validated['desired_daily_displays'],
            $validated['start_time'] . ':00',
            $validated['end_time'] . ':00'
        );

        return response()->json([
            'success' => true,
            'recommendation' => $result,
        ]);
    }

    /**
     * API: Optimize for budget
     */
    public function optimizeForBudget(Request $request)
    {
        $validated = $request->validate([
            'monthly_budget' => 'required|numeric|min:0',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'price_per_display' => 'required|numeric|min:0',
        ]);

        $result = $this->slotService->optimizeForBudget(
            $validated['monthly_budget'],
            $validated['start_time'] . ':00',
            $validated['end_time'] . ':00',
            $validated['price_per_display']
        );

        return response()->json([
            'success' => true,
            'optimization' => $result,
        ]);
    }

    /**
     * API: Get slot metrics
     */
    public function getMetrics(Request $request, DOOHSlot $slot)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $metrics = $this->slotService->calculateMetrics(
            $slot,
            Carbon::parse($validated['start_date']),
            Carbon::parse($validated['end_date'])
        );

        return response()->json([
            'success' => true,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Setup default slots for hoarding
     */
    public function setupDefaults(Hoarding $hoarding)
    {
        if (!$hoarding->isDOOH()) {
            return back()->with('error', 'This hoarding is not a DOOH hoarding.');
        }

        $slots = $hoarding->setupDefaultSlots();

        return back()->with('success', count($slots) . ' default slots created successfully!');
    }

    /**
     * View booking interface
     */
    public function bookingView(Hoarding $hoarding)
    {
        $slots = $hoarding->doohSlots()
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        $stats = $hoarding->getDOOHStats();

        return view('admin.dooh-slots.booking', compact('hoarding', 'slots', 'stats'));
    }

    /**
     * Get ROI calculator view
     */
    public function roiCalculator()
    {
        return view('admin.dooh-slots.roi-calculator');
    }
}
