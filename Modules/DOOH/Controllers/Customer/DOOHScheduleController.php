<?php

namespace Modules\DOOH\Controllers\Customer;

use App\Http\Controllers\Controller;
use Modules\DOOH\Services\DOOHScheduleService;
use Modules\DOOH\Models\DOOHCreative;
use Modules\DOOH\Models\DOOHCreativeSchedule;
use Modules\DOOH\Models\DOOHScreen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * DOOH Schedule Controller (Customer)
 * PROMPT 67: Customer-facing creative and schedule management
 */
class DOOHScheduleController extends Controller
{
    protected DOOHScheduleService $scheduleService;

    public function __construct(DOOHScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
        $this->middleware('auth');
    }

    /**
     * List customer's creatives
     */
    public function creatives(Request $request)
    {
        $creatives = DOOHCreative::byCustomer(Auth::id())
            ->with(['doohScreen', 'booking', 'schedules'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->validation_status, fn($q) => $q->where('validation_status', $request->validation_status))
            ->latest()
            ->paginate(20);
        
        return view('customer.dooh.creatives.index', compact('creatives'));
    }

    /**
     * Show creative upload form
     */
    public function createCreative()
    {
        $screens = DOOHScreen::active()->get();
        
        return view('customer.dooh.creatives.create', compact('screens'));
    }

    /**
     * Upload new creative
     */
    public function storeCreative(Request $request)
    {
        $validated = $request->validate([
            'creative_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'creative_file' => 'required|file|mimes:mp4,mov,avi,webm,jpg,jpeg,png,webp,gif|max:512000', // 500MB
            'screen_id' => 'nullable|exists:dooh_screens,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'tags' => 'nullable|array',
        ]);

        try {
            $creative = $this->scheduleService->uploadCreative(
                Auth::id(),
                [
                    'file' => $request->file('creative_file'),
                    'name' => $validated['creative_name'],
                    'description' => $validated['description'] ?? null,
                    'tags' => $validated['tags'] ?? [],
                ],
                $validated['booking_id'] ?? null,
                $validated['screen_id'] ?? null
            );

            return redirect()
                ->route('customer.dooh.creatives.show', $creative->id)
                ->with('success', 'Creative uploaded successfully! Validation in progress.');

        } catch (Exception $e) {
            Log::error('Creative upload failed', [
                'customer_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Show creative details
     */
    public function showCreative(DOOHCreative $creative)
    {
        // Verify ownership
        if ($creative->customer_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        $creative->load(['schedules', 'doohScreen', 'booking']);
        
        $validationResults = $creative->validation_results ?? [];
        
        return view('customer.dooh.creatives.show', compact('creative', 'validationResults'));
    }

    /**
     * Delete creative
     */
    public function destroyCreative(DOOHCreative $creative)
    {
        // Verify ownership
        if ($creative->customer_id !== Auth::id()) {
            abort(403);
        }

        // Check if creative has active schedules
        if ($creative->activeSchedules()->count() > 0) {
            return back()->with('error', 'Cannot delete creative with active schedules');
        }

        $creative->delete();

        return redirect()
            ->route('customer.dooh.creatives.index')
            ->with('success', 'Creative deleted successfully');
    }

    /**
     * List customer's schedules
     */
    public function schedules(Request $request)
    {
        $schedules = DOOHCreativeSchedule::forCustomer(Auth::id())
            ->with(['creative', 'doohScreen', 'booking'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->screen_id, fn($q) => $q->where('dooh_screen_id', $request->screen_id))
            ->latest()
            ->paginate(20);
        
        $screens = DOOHScreen::active()->get();
        
        return view('customer.dooh.schedules.index', compact('schedules', 'screens'));
    }

    /**
     * Show schedule creation form
     */
    public function createSchedule(Request $request)
    {
        $creativeId = $request->creative_id;
        $screenId = $request->screen_id;
        
        $creative = $creativeId 
            ? DOOHCreative::where('customer_id', Auth::id())->findOrFail($creativeId)
            : null;
        
        $screen = $screenId 
            ? DOOHScreen::findOrFail($screenId)
            : null;
        
        $creatives = DOOHCreative::byCustomer(Auth::id())
            ->approved()
            ->active()
            ->get();
        
        $screens = DOOHScreen::active()->get();
        
        return view('customer.dooh.schedules.create', compact('creative', 'screen', 'creatives', 'screens'));
    }

    /**
     * Store new schedule
     */
    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'creative_id' => 'required|exists:dooh_creatives,id',
            'dooh_screen_id' => 'required|exists:dooh_screens,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'schedule_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'time_slots' => 'nullable|array',
            'time_slots.*.start_time' => 'required_with:time_slots|date_format:H:i',
            'time_slots.*.end_time' => 'required_with:time_slots|date_format:H:i|after:time_slots.*.start_time',
            'daily_start_time' => 'nullable|date_format:H:i',
            'daily_end_time' => 'nullable|date_format:H:i|after:daily_start_time',
            'displays_per_hour' => 'nullable|integer|min:1|max:60',
            'priority' => 'nullable|integer|min:1|max:10',
            'active_days' => 'nullable|array',
            'active_days.*' => 'integer|min:0|max:6',
            'customer_notes' => 'nullable|string',
        ]);

        try {
            // Verify creative ownership
            $creative = DOOHCreative::findOrFail($validated['creative_id']);
            if ($creative->customer_id !== Auth::id()) {
                abort(403, 'Unauthorized access to creative');
            }

            $schedule = $this->scheduleService->createSchedule($validated);

            return redirect()
                ->route('customer.dooh.schedules.show', $schedule->id)
                ->with('success', $schedule->availability_confirmed 
                    ? 'Schedule created successfully! Pending admin approval.' 
                    : 'Schedule created with conflicts. Please review.');

        } catch (Exception $e) {
            Log::error('Schedule creation failed', [
                'customer_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create schedule: ' . $e->getMessage());
        }
    }

    /**
     * Show schedule details
     */
    public function showSchedule(DOOHCreativeSchedule $schedule)
    {
        // Verify ownership
        if ($schedule->customer_id !== Auth::id()) {
            abort(403);
        }

        $schedule->load(['creative', 'doohScreen', 'booking', 'approver']);
        
        $stats = $this->scheduleService->getScheduleStats($schedule);
        
        return view('customer.dooh.schedules.show', compact('schedule', 'stats'));
    }

    /**
     * Cancel schedule
     */
    public function cancelSchedule(Request $request, DOOHCreativeSchedule $schedule)
    {
        // Verify ownership
        if ($schedule->customer_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $schedule->cancel($validated['reason']);

            return redirect()
                ->route('customer.dooh.schedules.show', $schedule->id)
                ->with('success', 'Schedule cancelled successfully');

        } catch (Exception $e) {
            return back()->with('error', 'Failed to cancel schedule: ' . $e->getMessage());
        }
    }

    /**
     * Check availability (AJAX)
     */
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'creative_id' => 'required|exists:dooh_creatives,id',
            'dooh_screen_id' => 'required|exists:dooh_screens,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'time_slots' => 'nullable|array',
            'displays_per_hour' => 'nullable|integer',
        ]);

        try {
            // Create temporary schedule for checking
            $tempSchedule = new DOOHCreativeSchedule($validated);
            $tempSchedule->customer_id = Auth::id();
            $tempSchedule->displays_per_day = $validated['displays_per_hour'] ?? 12;
            
            $availability = $this->scheduleService->checkScheduleAvailability($tempSchedule);

            return response()->json([
                'success' => true,
                'availability' => $availability,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get playback preview (AJAX)
     */
    public function playbackPreview(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'screen_id' => 'required|exists:dooh_screens,id',
        ]);

        try {
            $playback = $this->scheduleService->generatePlaybackSchedule(
                \Carbon\Carbon::parse($validated['date']),
                $validated['screen_id']
            );

            return response()->json([
                'success' => true,
                'playback' => $playback,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
