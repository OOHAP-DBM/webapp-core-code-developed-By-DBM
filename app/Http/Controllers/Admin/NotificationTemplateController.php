<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\NotificationLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationTemplateController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Display templates list
     */
    public function index(Request $request)
    {
        $query = NotificationTemplate::with(['creator', 'updater']);

        // Filters
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $templates = $query->orderBy('event_type')
            ->orderBy('channel')
            ->orderBy('priority', 'desc')
            ->paginate(20);

        $eventTypes = NotificationTemplate::getEventTypes();
        $channels = NotificationTemplate::getChannels();

        return view('admin.notifications.templates.index', compact('templates', 'eventTypes', 'channels'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $eventTypes = NotificationTemplate::getEventTypes();
        $channels = NotificationTemplate::getChannels();

        return view('admin.notifications.templates.create', compact('eventTypes', 'channels'));
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'event_type' => 'required|string|max:50',
            'channel' => 'required|string|max:20',
            'description' => 'nullable|string',
            'subject' => 'required_if:channel,email|nullable|string',
            'body' => 'required|string',
            'html_body' => 'nullable|string',
            'is_active' => 'boolean',
            'priority' => 'nullable|integer|min:0',
        ]);

        // Get available placeholders for this event type
        $placeholders = NotificationTemplate::getDefaultPlaceholders($validated['event_type']);

        $template = NotificationTemplate::create([
            ...$validated,
            'available_placeholders' => $placeholders,
            'is_active' => $request->boolean('is_active', true),
            'priority' => $validated['priority'] ?? 0,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.notifications.templates.show', $template)
            ->with('success', 'Template created successfully.');
    }

    /**
     * Show template details
     */
    public function show(NotificationTemplate $template)
    {
        $template->load(['creator', 'updater']);

        // Get recent logs for this template
        $recentLogs = NotificationLog::where('notification_template_id', $template->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get statistics
        $stats = [
            'total_sent' => NotificationLog::where('notification_template_id', $template->id)->count(),
            'success_rate' => $this->calculateSuccessRate($template->id),
            'failed_count' => NotificationLog::where('notification_template_id', $template->id)
                ->where('status', NotificationLog::STATUS_FAILED)->count(),
        ];

        return view('admin.notifications.templates.show', compact('template', 'recentLogs', 'stats'));
    }

    /**
     * Show edit form
     */
    public function edit(NotificationTemplate $template)
    {
        $eventTypes = NotificationTemplate::getEventTypes();
        $channels = NotificationTemplate::getChannels();

        return view('admin.notifications.templates.edit', compact('template', 'eventTypes', 'channels'));
    }

    /**
     * Update template
     */
    public function update(Request $request, NotificationTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'event_type' => 'required|string|max:50',
            'channel' => 'required|string|max:20',
            'description' => 'nullable|string',
            'subject' => 'required_if:channel,email|nullable|string',
            'body' => 'required|string',
            'html_body' => 'nullable|string',
            'is_active' => 'boolean',
            'priority' => 'nullable|integer|min:0',
        ]);

        // Update available placeholders if event type changed
        if ($template->event_type !== $validated['event_type']) {
            $validated['available_placeholders'] = NotificationTemplate::getDefaultPlaceholders($validated['event_type']);
        }

        $template->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
            'priority' => $validated['priority'] ?? $template->priority,
            'updated_by' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.notifications.templates.show', $template)
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Delete template
     */
    public function destroy(NotificationTemplate $template)
    {
        if ($template->is_system_default) {
            return back()->with('error', 'Cannot delete system default template.');
        }

        $template->delete();

        return redirect()
            ->route('admin.notifications.templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    /**
     * Duplicate template
     */
    public function duplicate(NotificationTemplate $template)
    {
        $copy = $template->duplicate();

        return redirect()
            ->route('admin.notifications.templates.edit', $copy)
            ->with('success', 'Template duplicated successfully. You can now customize it.');
    }

    /**
     * Toggle template status
     */
    public function toggleStatus(NotificationTemplate $template)
    {
        $template->update([
            'is_active' => !$template->is_active,
            'updated_by' => Auth::id(),
        ]);

        $status = $template->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Template {$status} successfully.");
    }

    /**
     * Test send notification
     */
    public function testSend(Request $request, NotificationTemplate $template)
    {
        $validated = $request->validate([
            'recipient' => 'required|string',
            'placeholders' => 'nullable|array',
        ]);

        try {
            // Prepare test placeholders
            $placeholders = $validated['placeholders'] ?? [];
            
            // Fill in any missing placeholders with dummy data
            foreach ($template->available_placeholders as $placeholder => $description) {
                $key = str_replace(['{{', '}}'], '', $placeholder);
                if (!isset($placeholders[$key])) {
                    $placeholders[$key] = "[Test {$description}]";
                }
            }

            $log = $this->notificationService->sendFromTemplate(
                eventType: $template->event_type,
                channel: $template->channel,
                placeholdersData: $placeholders,
                recipient: $validated['recipient'],
                relatedEntity: null
            );

            if ($log) {
                return back()->with('success', 'Test notification sent successfully.');
            }

            return back()->with('error', 'Failed to send test notification.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Show notification logs
     */
    public function logs(Request $request)
    {
        $query = NotificationLog::with(['template', 'user']);

        // Filters
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get statistics
        $stats = $this->notificationService->getStatistics($request->only(['date_from', 'date_to', 'channel', 'event_type']));

        $eventTypes = NotificationTemplate::getEventTypes();
        $channels = NotificationTemplate::getChannels();

        return view('admin.notifications.logs.index', compact('logs', 'stats', 'eventTypes', 'channels'));
    }

    /**
     * Show log details
     */
    public function logShow(NotificationLog $log)
    {
        $log->load(['template', 'user', 'related']);

        return view('admin.notifications.logs.show', compact('log'));
    }

    /**
     * Retry failed notification
     */
    public function retryLog(NotificationLog $log)
    {
        if (!$log->canRetry()) {
            return back()->with('error', 'Cannot retry this notification (max retries reached or wrong status).');
        }

        $success = $this->notificationService->retry($log);

        if ($success) {
            return back()->with('success', 'Notification queued for retry.');
        }

        return back()->with('error', 'Failed to retry notification.');
    }

    /**
     * Calculate success rate
     */
    protected function calculateSuccessRate(int $templateId): float
    {
        $total = NotificationLog::where('notification_template_id', $templateId)->count();

        if ($total === 0) {
            return 0;
        }

        $successful = NotificationLog::where('notification_template_id', $templateId)
            ->whereIn('status', [NotificationLog::STATUS_SENT, NotificationLog::STATUS_DELIVERED, NotificationLog::STATUS_READ])
            ->count();

        return round(($successful / $total) * 100, 2);
    }
}
