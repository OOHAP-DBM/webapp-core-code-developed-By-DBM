<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Display audit logs list
     */
    public function index(Request $request)
    {
        $query = AuditLog::query()->with(['user', 'auditable']);

        // Apply filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest('created_at')->paginate(50);
        
        // Get filter options
        $actions = AuditLog::distinct()->pluck('action');
        $modules = AuditLog::distinct()->whereNotNull('module')->pluck('module');
        
        // Get statistics
        $statistics = $this->auditService->getStatistics([
            'from' => $request->from_date,
            'to' => $request->to_date,
            'module' => $request->module,
        ]);

        return view('admin.audit-logs.index', compact('logs', 'actions', 'modules', 'statistics'));
    }

    /**
     * Display specific audit log
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load(['user', 'auditable']);
        
        // Get related logs (same entity)
        $relatedLogs = [];
        if ($auditLog->auditable_type && $auditLog->auditable_id) {
            $relatedLogs = AuditLog::forModel($auditLog->auditable_type, $auditLog->auditable_id)
                                   ->where('id', '!=', $auditLog->id)
                                   ->with('user')
                                   ->latest('created_at')
                                   ->limit(10)
                                   ->get();
        }

        return view('admin.audit-logs.show', compact('auditLog', 'relatedLogs'));
    }

    /**
     * Get audit logs for specific model (API)
     */
    public function forModel(Request $request)
    {
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $logs = AuditLog::forModel($request->model_type, $request->model_id)
                       ->with('user')
                       ->latest('created_at')
                       ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    /**
     * Get user activity (API)
     */
    public function userActivity(Request $request, int $userId)
    {
        $limit = $request->get('limit', 100);
        $logs = $this->auditService->getUserActivity($userId, $limit);

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    /**
     * Get recent activity (API)
     */
    public function recent(Request $request)
    {
        $limit = $request->get('limit', 50);
        $logs = $this->auditService->getRecentActivity($limit);

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    /**
     * Get statistics (API)
     */
    public function statistics(Request $request)
    {
        $filters = [
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'module' => $request->get('module'),
        ];

        $statistics = $this->auditService->getStatistics($filters);

        return response()->json([
            'success' => true,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Search audit logs (API)
     */
    public function search(Request $request)
    {
        $filters = [
            'action' => $request->get('action'),
            'module' => $request->get('module'),
            'user_id' => $request->get('user_id'),
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'model_type' => $request->get('model_type'),
            'model_id' => $request->get('model_id'),
            'search' => $request->get('search'),
        ];

        $query = $this->auditService->search($filters);
        $logs = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    /**
     * Export audit logs
     */
    public function export(Request $request)
    {
        $filters = [
            'action' => $request->get('action'),
            'module' => $request->get('module'),
            'user_id' => $request->get('user_id'),
            'from' => $request->get('from'),
            'to' => $request->get('to'),
        ];

        $query = $this->auditService->search($filters);
        $logs = $query->get();

        $filename = 'audit_logs_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Date/Time',
                'User',
                'Action',
                'Module',
                'Model',
                'Description',
                'IP Address',
                'Changes',
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user_name ?? 'System',
                    $log->action_label,
                    $log->module ?? '-',
                    $log->model_name,
                    $log->description ?? '-',
                    $log->ip_address ?? '-',
                    count($log->changed_fields ?? []) . ' fields changed',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get audit timeline for entity
     */
    public function timeline(Request $request)
    {
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $logs = AuditLog::forModel($request->model_type, $request->model_id)
                       ->with('user')
                       ->latest('created_at')
                       ->get();

        // Group by date
        $timeline = $logs->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });

        return response()->json([
            'success' => true,
            'timeline' => $timeline,
        ]);
    }
}
