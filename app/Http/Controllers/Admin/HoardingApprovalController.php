<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use App\Services\HoardingApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HoardingApprovalController extends Controller
{
    protected HoardingApprovalService $approvalService;

    public function __construct(HoardingApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Display approval dashboard
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $period = $request->input('period', 'month');
        
        // Get statistics
        $stats = $this->approvalService->getStatistics($period);
        
        // Get pending approvals
        $hoardings = $this->approvalService->getPendingApprovals(null, $status, 50);
        
        // Get recent activity
        $recentActivity = DB::table('hoarding_approval_logs')
            ->join('hoardings', 'hoarding_approval_logs.hoarding_id', '=', 'hoardings.id')
            ->join('users', 'hoarding_approval_logs.performed_by', '=', 'users.id')
            ->select(
                'hoarding_approval_logs.*',
                'hoardings.location_name',
                'users.name as performer_name'
            )
            ->orderBy('hoarding_approval_logs.performed_at', 'desc')
            ->limit(20)
            ->get();
        
        // Get SLA breaches
        $slaBreaches = DB::table('hoardings')
            ->join('users', 'hoardings.vendor_id', '=', 'users.id')
            ->where('hoardings.approval_status', 'pending')
            ->whereRaw('TIMESTAMPDIFF(HOUR, hoardings.submitted_at, NOW()) > 48')
            ->select(
                'hoardings.*',
                'users.name as vendor_name',
                DB::raw('TIMESTAMPDIFF(HOUR, hoardings.submitted_at, NOW()) as hours_pending')
            )
            ->orderBy('hoardings.submitted_at', 'asc')
            ->get();
        
        return view('admin.approvals.dashboard', compact(
            'stats',
            'hoardings',
            'recentActivity',
            'slaBreaches',
            'status',
            'period'
        ));
    }

    /**
     * Show hoarding details for verification
     */
    public function show($id)
    {
        $hoarding = Hoarding::with(['vendor', 'verifiedBy', 'approvedBy', 'rejectedBy'])
            ->findOrFail($id);
        
        // Get version history
        $versions = DB::table('hoarding_versions')
            ->where('hoarding_id', $id)
            ->orderBy('version_number', 'desc')
            ->get();
        
        // Get approval logs
        $logs = DB::table('hoarding_approval_logs')
            ->join('users', 'hoarding_approval_logs.performed_by', '=', 'users.id')
            ->where('hoarding_approval_logs.hoarding_id', $id)
            ->select('hoarding_approval_logs.*', 'users.name as performer_name')
            ->orderBy('hoarding_approval_logs.performed_at', 'desc')
            ->get();
        
        // Get checklist
        $checklist = DB::table('hoarding_approval_checklists')
            ->where('hoarding_id', $id)
            ->where('version_number', $hoarding->current_version)
            ->get();
        
        // Get rejection templates
        $rejectionTemplates = DB::table('hoarding_rejection_templates')
            ->where('is_active', true)
            ->orderBy('category')
            ->get()
            ->groupBy('category');
        
        // Get similar hoardings for comparison
        $similarHoardings = DB::table('hoardings')
            ->where('city', $hoarding->city)
            ->where('board_type', $hoarding->board_type)
            ->where('approval_status', 'approved')
            ->where('id', '!=', $hoarding->id)
            ->select('location_name', 'width', 'height', 'price_per_month', 'is_lit')
            ->limit(5)
            ->get();
        
        return view('admin.approvals.show', compact(
            'hoarding',
            'versions',
            'logs',
            'checklist',
            'rejectionTemplates',
            'similarHoardings'
        ));
    }

    /**
     * Start verification
     */
    public function startVerification($id)
    {
        $hoarding = Hoarding::findOrFail($id);
        $admin = auth()->user();
        
        $result = $this->approvalService->startVerification($hoarding, $admin);
        
        return redirect()->route('admin.approvals.show', $id)
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Update checklist item
     */
    public function updateChecklist(Request $request, $id)
    {
        $request->validate([
            'item' => 'required|string',
            'status' => 'required|in:pending,passed,failed,na',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $hoarding = Hoarding::findOrFail($id);
        $admin = auth()->user();
        
        $this->approvalService->updateChecklistItem(
            $hoarding,
            $request->item,
            $request->status,
            $admin,
            $request->notes
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Checklist item updated.',
        ]);
    }

    /**
     * Approve hoarding
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $hoarding = Hoarding::findOrFail($id);
        $admin = auth()->user();
        
        // Verify all checklist items are completed
        $pendingItems = DB::table('hoarding_approval_checklists')
            ->where('hoarding_id', $id)
            ->where('version_number', $hoarding->current_version)
            ->where('status', 'pending')
            ->count();
        
        if ($pendingItems > 0) {
            return redirect()->back()
                ->with('error', 'Please complete all checklist items before approving.');
        }
        
        $failedItems = DB::table('hoarding_approval_checklists')
            ->where('hoarding_id', $id)
            ->where('version_number', $hoarding->current_version)
            ->where('status', 'failed')
            ->count();
        
        if ($failedItems > 0) {
            return redirect()->back()
                ->with('error', 'Cannot approve hoarding with failed checklist items. Please reject or mark as N/A.');
        }
        
        $result = $this->approvalService->approve($hoarding, $admin, $request->notes);
        
        return redirect()->route('admin.approvals.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Reject hoarding
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:20',
            'templates' => 'nullable|array',
            'templates.*' => 'integer|exists:hoarding_rejection_templates,id',
        ]);
        
        $hoarding = Hoarding::findOrFail($id);
        $admin = auth()->user();
        
        $result = $this->approvalService->reject(
            $hoarding,
            $admin,
            $request->reason,
            $request->templates
        );
        
        return redirect()->route('admin.approvals.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * View version comparison
     */
    public function compareVersions($id, $version1, $version2)
    {
        $hoarding = Hoarding::findOrFail($id);
        
        $v1 = DB::table('hoarding_versions')
            ->where('hoarding_id', $id)
            ->where('version_number', $version1)
            ->first();
        
        $v2 = DB::table('hoarding_versions')
            ->where('hoarding_id', $id)
            ->where('version_number', $version2)
            ->first();
        
        if (!$v1 || !$v2) {
            return redirect()->back()->with('error', 'Version not found.');
        }
        
        // Compare fields
        $fields = [
            'location_name', 'address', 'city', 'state', 'pincode',
            'latitude', 'longitude', 'width', 'height', 'board_type',
            'is_lit', 'price_per_month', 'description', 'images'
        ];
        
        $differences = [];
        foreach ($fields as $field) {
            if ($v1->$field != $v2->$field) {
                $differences[$field] = [
                    'old' => $v1->$field,
                    'new' => $v2->$field,
                ];
            }
        }
        
        return view('admin.approvals.compare', compact('hoarding', 'v1', 'v2', 'differences'));
    }

    /**
     * Bulk approve
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'hoarding_ids' => 'required|array',
            'hoarding_ids.*' => 'integer|exists:hoardings,id',
        ]);
        
        $admin = auth()->user();
        $approved = 0;
        $failed = 0;
        
        foreach ($request->hoarding_ids as $id) {
            $hoarding = Hoarding::find($id);
            if ($hoarding) {
                $result = $this->approvalService->approve($hoarding, $admin, 'Bulk approved');
                if ($result['success']) {
                    $approved++;
                } else {
                    $failed++;
                }
            }
        }
        
        return redirect()->back()
            ->with('success', "Approved {$approved} hoardings. Failed: {$failed}");
    }

    /**
     * Assign to admin
     */
    public function assign(Request $request, $id)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id',
            'priority' => 'nullable|integer|min:1|max:4',
        ]);
        
        $hoarding = Hoarding::findOrFail($id);
        $admin = \App\Models\User::findOrFail($request->admin_id);
        $assignedBy = auth()->user();
        
        $this->approvalService->assignVerification(
            $hoarding,
            $admin,
            $assignedBy,
            $request->priority ?? 3
        );
        
        return redirect()->back()
            ->with('success', 'Verification assigned successfully.');
    }

    /**
     * Export pending approvals
     */
    public function export(Request $request)
    {
        $status = $request->input('status', 'all');
        
        $hoardings = $this->approvalService->getPendingApprovals(null, $status, 1000);
        
        $csv = "ID,Location,City,Vendor,Status,Submitted At,Days Pending\n";
        
        foreach ($hoardings as $hoarding) {
            $daysPending = $hoarding->submitted_at 
                ? \Carbon\Carbon::parse($hoarding->submitted_at)->diffInDays(now())
                : 0;
            
            $csv .= implode(',', [
                $hoarding->id,
                '"' . $hoarding->location_name . '"',
                $hoarding->city,
                '"' . $hoarding->vendor_name . '"',
                $hoarding->approval_status,
                $hoarding->submitted_at,
                $daysPending
            ]) . "\n";
        }
        
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pending_approvals_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Manage rejection templates
     */
    public function templates()
    {
        $templates = DB::table('hoarding_rejection_templates')
            ->orderBy('category')
            ->orderBy('title')
            ->get()
            ->groupBy('category');
        
        return view('admin.approvals.templates', compact('templates'));
    }

    /**
     * Create rejection template
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:200',
            'message' => 'required|string',
            'requires_action' => 'boolean',
            'suggested_actions' => 'nullable|array',
        ]);
        
        DB::table('hoarding_rejection_templates')->insert([
            'category' => $request->category,
            'title' => $request->title,
            'message' => $request->message,
            'requires_action' => $request->requires_action ?? true,
            'suggested_actions' => $request->suggested_actions 
                ? json_encode($request->suggested_actions) 
                : null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.approvals.templates')
            ->with('success', 'Template created successfully.');
    }

    /**
     * Update approval settings
     */
    public function settings()
    {
        $settings = DB::table('hoarding_approval_settings')
            ->get()
            ->keyBy('key');
        
        return view('admin.approvals.settings', compact('settings'));
    }

    /**
     * Save approval settings
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'auto_approve_trusted_vendors' => 'boolean',
            'trusted_vendor_rating_threshold' => 'numeric|min:0|max:5',
            'trusted_vendor_min_approved' => 'integer|min:1',
            'verification_sla_hours' => 'integer|min:1',
            'approval_sla_hours' => 'integer|min:1',
        ]);
        
        foreach ($request->all() as $key => $value) {
            if (in_array($key, ['_token', '_method'])) {
                continue;
            }
            
            DB::table('hoarding_approval_settings')
                ->where('key', $key)
                ->update([
                    'value' => is_bool($value) ? ($value ? 'true' : 'false') : $value,
                    'updated_at' => now(),
                ]);
        }
        
        return redirect()->route('admin.approvals.settings')
            ->with('success', 'Settings updated successfully.');
    }
}
