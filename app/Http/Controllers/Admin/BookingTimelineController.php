<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTimelineEvent;
use App\Services\BookingTimelineService;
use Illuminate\Http\Request;

class BookingTimelineController extends Controller
{
    protected $timelineService;

    public function __construct(BookingTimelineService $timelineService)
    {
        $this->timelineService = $timelineService;
    }

    /**
     * Get timeline for booking
     */
    public function index(Booking $booking)
    {
        $timeline = $this->timelineService->getTimeline($booking);
        $progress = $this->timelineService->getProgress($booking);
        $currentStage = $this->timelineService->getCurrentStage($booking);
        $nextEvent = $this->timelineService->getNextEvent($booking);

        return view('admin.bookings.timeline', compact('booking', 'timeline', 'progress', 'currentStage', 'nextEvent'));
    }

    /**
     * Get timeline API
     */
    public function getTimeline(Booking $booking)
    {
        $timeline = $this->timelineService->getTimeline($booking);
        $progress = $this->timelineService->getProgress($booking);
        $currentStage = $this->timelineService->getCurrentStage($booking);

        return response()->json([
            'success' => true,
            'timeline' => $timeline,
            'progress' => $progress,
            'current_stage' => $currentStage,
        ]);
    }

    /**
     * Start production stage
     */
    public function startStage(Request $request, Booking $booking)
    {
        $request->validate([
            'stage' => 'required|string|in:graphics,printing,mounting,proof',
        ]);

        $event = $this->timelineService->startProductionEvent($booking, $request->stage);

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->stage) . ' stage started',
            'event' => $event,
        ]);
    }

    /**
     * Complete production stage
     */
    public function completeStage(Request $request, Booking $booking)
    {
        $request->validate([
            'stage' => 'required|string|in:graphics,printing,mounting,proof',
        ]);

        $event = $this->timelineService->completeProductionEvent($booking, $request->stage);

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->stage) . ' stage completed',
            'event' => $event,
        ]);
    }

    /**
     * Update event status
     */
    public function updateEvent(Request $request, BookingTimelineEvent $event)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,failed,cancelled',
        ]);

        $event->update(['status' => $request->status]);

        if ($request->status === 'completed') {
            $event->markAsCompleted();
        } elseif ($request->status === 'in_progress') {
            $event->markAsStarted();
        }

        return response()->json([
            'success' => true,
            'message' => 'Event status updated',
            'event' => $event,
        ]);
    }

    /**
     * Add custom event
     */
    public function addEvent(Request $request, Booking $booking)
    {
        $request->validate([
            'event_type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,completed,failed,cancelled',
            'scheduled_at' => 'nullable|date',
        ]);

        $event = $this->timelineService->createEvent(
            $booking,
            $request->event_type,
            $request->title,
            [
                'description' => $request->description,
                'status' => $request->status ?? 'pending',
                'scheduled_at' => $request->scheduled_at,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Event added to timeline',
            'event' => $event,
        ]);
    }

    /**
     * Rebuild timeline
     */
    public function rebuild(Booking $booking)
    {
        $this->timelineService->rebuildTimeline($booking);

        return response()->json([
            'success' => true,
            'message' => 'Timeline rebuilt successfully',
        ]);
    }

    /**
     * Get progress
     */
    public function progress(Booking $booking)
    {
        $progress = $this->timelineService->getProgress($booking);

        return response()->json([
            'success' => true,
            'progress' => $progress,
        ]);
    }

    /**
     * Get current stage
     */
    public function currentStage(Booking $booking)
    {
        $currentStage = $this->timelineService->getCurrentStage($booking);

        return response()->json([
            'success' => true,
            'current_stage' => $currentStage,
        ]);
    }
}
