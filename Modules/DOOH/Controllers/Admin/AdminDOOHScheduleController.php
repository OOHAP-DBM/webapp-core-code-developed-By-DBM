<?php

namespace Modules\DOOH\Controllers\Admin;

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
 * Admin DOOH Schedule Controller
 * PROMPT 67: Admin creative validation and schedule approval
 */
class AdminDOOHScheduleController extends Controller
{
    protected DOOHScheduleService $scheduleService;

    public function __construct(DOOHScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * List all creatives (admin view)
     */
    public function creatives(Request $request)
    {
        $creatives = DOOHCreative::with(['customer', 'doohScreen', 'validator'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->validation_status, fn($q) => $q->where('validation_status', $request->validation_status))
            ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->screen_id, fn($q) => $q->where('dooh_screen_id', $request->screen_id))
            ->when($request->search, function($q) use ($request) {
                $q->where('creative_name', 'like', '%' . $request->search . '%')
                  ->orWhere('original_filename', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(30);
        
        $stats = [
            'total' => DOOHCreative::count(),
            'pending' => DOOHCreative::pendingValidation()->count(),
            'approved' => DOOHCreative::approved()->count(),
            'rejected' => DOOHCreative::where('validation_status', DOOHCreative::VALIDATION_REJECTED)->count(),
        ];
        
        return view('admin.dooh.creatives.index', compact('creatives', 'stats'));
    }

    /**
     * Show creative for validation
     */
    public function showCreative(DOOHCreative $creative)
    {
        $creative->load(['customer', 'doohScreen', 'validator', 'schedules']);
        
        $validationResults = $creative->validation_results ?? [];
        
        return view('admin.dooh.creatives.show', compact('creative', 'validationResults'));
    }

    /**
     * Approve creative
     */
    public function approveCreative(Request $request, DOOHCreative $creative)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $creative->approve(Auth::id(), $validated['notes'] ?? null);

            Log::info('Creative approved', [
                'creative_id' => $creative->id,
                'admin_id' => Auth::id(),
            ]);

            return redirect()
                ->route('admin.dooh.creatives.show', $creative->id)
                ->with('success', 'Creative approved successfully');

        } catch (Exception $e) {
            return back()->with('error', 'Failed to approve creative: ' . $e->getMessage());
        }
    }

    /**
     * Reject creative
     */
    public function rejectCreative(Request $request, DOOHCreative $creative)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $creative->reject($validated['reason'], Auth::id());

            Log::info('Creative rejected', [
                'creative_id' => $creative->id,
                'admin_id' => Auth::id(),
                'reason' => $validated['reason'],
            ]);

            return redirect()
                ->route('admin.dooh.creatives.show', $creative->id)
                ->with('success', 'Creative rejected');

        } catch (Exception $e) {
            return back()->with('error', 'Failed to reject creative: ' . $e->getMessage());
        }
    }

    /**
     * List all schedules
     */
    public function schedules(Request $request)
    {
        $schedules = DOOHCreativeSchedule::with(['creative', 'customer', 'doohScreen', 'approver'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->validation_status, fn($q) => $q->where('validation_status', $request->validation_status))
            ->when($request->screen_id, fn($q) => $q->where('dooh_screen_id', $request->screen_id))
            ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->date_from, fn($q) => $q->where('start_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->where('end_date', '<=', $request->date_to))
            ->latest()
            ->paginate(30);
        
        $stats = [
            'total' => DOOHCreativeSchedule::count(),
            'pending' => DOOHCreativeSchedule::where('status', DOOHCreativeSchedule::STATUS_PENDING_APPROVAL)->count(),
            'approved' => DOOHCreativeSchedule::where('status', DOOHCreativeSchedule::STATUS_APPROVED)->count(),
            'active' => DOOHCreativeSchedule::where('status', DOOHCreativeSchedule::STATUS_ACTIVE)->count(),
            'with_conflicts' => DOOHCreativeSchedule::where('validation_status', DOOHCreativeSchedule::VALIDATION_CONFLICTS)->count(),
        ];
        
        $screens = DOOHScreen::active()->get();
        
        return view('admin.dooh.schedules.index', compact('schedules', 'stats', 'screens'));
    }

    /**
     * Show schedule for approval
     */
    public function showSchedule(DOOHCreativeSchedule $schedule)
    {
        $schedule->load(['creative', 'customer', 'doohScreen', 'booking', 'approver']);
        
        // Re-check availability
        $availability = $this->scheduleService->checkScheduleAvailability($schedule);
        
        $stats = $this->scheduleService->getScheduleStats($schedule);
        
        return view('admin.dooh.schedules.show', compact('schedule', 'availability', 'stats'));
    }

    /**
     * Approve schedule
     */
    public function approveSchedule(Request $request, DOOHCreativeSchedule $schedule)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->scheduleService->approveSchedule(
                $schedule->id,
                Auth::id(),
                $validated['notes'] ?? null
            );

            return redirect()
                ->route('admin.dooh.schedules.show', $schedule->id)
                ->with('success', 'Schedule approved successfully');

        } catch (Exception $e) {
            Log::error('Schedule approval failed', [
                'schedule_id' => $schedule->id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to approve schedule: ' . $e->getMessage());
        }
    }

    /**
     * Reject schedule
     */
    public function rejectSchedule(Request $request, DOOHCreativeSchedule $schedule)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $schedule->update([
                'status' => DOOHCreativeSchedule::STATUS_CANCELLED,
                'validation_status' => DOOHCreativeSchedule::VALIDATION_REJECTED,
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['reason'],
                'admin_notes' => $validated['reason'],
            ]);

            Log::info('Schedule rejected', [
                'schedule_id' => $schedule->id,
                'admin_id' => Auth::id(),
            ]);

            return redirect()
                ->route('admin.dooh.schedules.show', $schedule->id)
                ->with('success', 'Schedule rejected');

        } catch (Exception $e) {
            return back()->with('error', 'Failed to reject schedule: ' . $e->getMessage());
        }
    }

    /**
     * View screen schedule calendar
     */
    public function screenCalendar(Request $request, DOOHScreen $screen)
    {
        $startDate = $request->start_date 
            ? \Carbon\Carbon::parse($request->start_date) 
            : now()->startOfMonth();
        
        $endDate = $request->end_date 
            ? \Carbon\Carbon::parse($request->end_date) 
            : now()->endOfMonth();
        
        $schedules = DOOHCreativeSchedule::forScreen($screen->id)
            ->approved()
            ->inDateRange($startDate, $endDate)
            ->with(['creative', 'customer'])
            ->get();
        
        return view('admin.dooh.schedules.calendar', compact('screen', 'schedules', 'startDate', 'endDate'));
    }

    /**
     * View daily playback schedule
     */
    public function dailyPlayback(Request $request, DOOHScreen $screen)
    {
        $date = $request->date 
            ? \Carbon\Carbon::parse($request->date) 
            : now();
        
        $playback = $this->scheduleService->generatePlaybackSchedule($date, $screen->id);
        
        return view('admin.dooh.schedules.playback', compact('screen', 'date', 'playback'));
    }

    /**
     * Pause schedule
     */
    public function pauseSchedule(DOOHCreativeSchedule $schedule)
    {
        try {
            $schedule->pause();

            return redirect()
                ->route('admin.dooh.schedules.show', $schedule->id)
                ->with('success', 'Schedule paused');

        } catch (Exception $e) {
            return back()->with('error', 'Failed to pause schedule: ' . $e->getMessage());
        }
    }

    /**
     * Resume schedule
     */
    public function resumeSchedule(DOOHCreativeSchedule $schedule)
    {
        try {
            $schedule->resume();

            return redirect()
                ->route('admin.dooh.schedules.show', $schedule->id)
                ->with('success', 'Schedule resumed');

        } catch (Exception $e) {
            return back()->with('error', 'Failed to resume schedule: ' . $e->getMessage());
        }
    }

    /**
     * Export schedules (CSV/Excel)
     */
    public function exportSchedules(Request $request)
    {
        $schedules = DOOHCreativeSchedule::with(['creative', 'customer', 'doohScreen'])
            ->when($request->screen_id, fn($q) => $q->where('dooh_screen_id', $request->screen_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->where('start_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->where('end_date', '<=', $request->date_to))
            ->get();
        
        $csv = "ID,Schedule Name,Creative,Customer,Screen,Start Date,End Date,Status,Displays,Total Cost\n";
        
        foreach ($schedules as $schedule) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%s,%d,%.2f\n",
                $schedule->id,
                $schedule->schedule_name,
                $schedule->creative->creative_name,
                $schedule->customer->name,
                $schedule->doohScreen->name,
                $schedule->start_date->format('Y-m-d'),
                $schedule->end_date->format('Y-m-d'),
                $schedule->status,
                $schedule->total_displays,
                $schedule->total_cost
            );
        }
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="dooh_schedules_' . now()->format('Y-m-d') . '.csv"');
    }

    /**
     * Bulk approve schedules
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'schedule_ids' => 'required|array',
            'schedule_ids.*' => 'exists:dooh_creative_schedules,id',
        ]);

        $approved = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['schedule_ids'] as $scheduleId) {
            try {
                $this->scheduleService->approveSchedule($scheduleId, Auth::id());
                $approved++;
            } catch (Exception $e) {
                $failed++;
                $errors[] = "Schedule #{$scheduleId}: " . $e->getMessage();
            }
        }

        $message = "Approved {$approved} schedule(s)";
        if ($failed > 0) {
            $message .= ", {$failed} failed";
        }

        return back()->with('success', $message)->with('errors', $errors);
    }
}
