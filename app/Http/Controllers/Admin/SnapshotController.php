<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Snapshot;
use App\Services\SnapshotService;
use Illuminate\Http\Request;

class SnapshotController extends Controller
{
    protected SnapshotService $snapshotService;
    
    public function __construct(SnapshotService $snapshotService)
    {
        $this->snapshotService = $snapshotService;
    }
    
    /**
     * Display all snapshots
     */
    public function index(Request $request)
    {
        $query = Snapshot::with(['creator', 'snapshotable'])
            ->orderBy('created_at', 'desc');
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('snapshot_type', $request->type);
        }
        
        // Filter by event
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        
        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        $snapshots = $query->paginate(50);
        
        $stats = $this->snapshotService->getStatistics();
        
        return view('admin.snapshots.index', compact('snapshots', 'stats'));
    }
    
    /**
     * Show a specific snapshot
     */
    public function show(Snapshot $snapshot)
    {
        $snapshot->load('creator', 'snapshotable');
        
        // Get previous and next versions
        $previous = $snapshot->previous();
        $next = $snapshot->next();
        
        // Compare with previous if available
        $comparison = null;
        if ($previous) {
            $comparison = $snapshot->compareWith($previous);
        }
        
        return view('admin.snapshots.show', compact('snapshot', 'previous', 'next', 'comparison'));
    }
    
    /**
     * Get snapshots for a specific model (API)
     */
    public function forModel(Request $request)
    {
        $validated = $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);
        
        $snapshots = Snapshot::where('snapshotable_type', $validated['model_type'])
            ->where('snapshotable_id', $validated['model_id'])
            ->orderBy('version', 'desc')
            ->with('creator')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $snapshots,
        ]);
    }
    
    /**
     * Compare two snapshots
     */
    public function compare(Request $request)
    {
        $validated = $request->validate([
            'snapshot1_id' => 'required|exists:snapshots,id',
            'snapshot2_id' => 'required|exists:snapshots,id',
        ]);
        
        $snapshot1 = Snapshot::findOrFail($validated['snapshot1_id']);
        $snapshot2 = Snapshot::findOrFail($validated['snapshot2_id']);
        
        $differences = $snapshot1->compareWith($snapshot2);
        
        return response()->json([
            'success' => true,
            'data' => [
                'snapshot1' => $snapshot1,
                'snapshot2' => $snapshot2,
                'differences' => $differences,
            ],
        ]);
    }
    
    /**
     * Get statistics
     */
    public function statistics()
    {
        $stats = $this->snapshotService->getStatistics();
        
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
    
    /**
     * Get recent snapshots (API)
     */
    public function recent(Request $request)
    {
        $limit = $request->input('limit', 50);
        $snapshots = $this->snapshotService->getRecentSnapshots($limit);
        
        return response()->json([
            'success' => true,
            'data' => $snapshots,
        ]);
    }
    
    /**
     * Get snapshots by type (API)
     */
    public function byType(Request $request, string $type)
    {
        $limit = $request->input('limit', 100);
        $snapshots = $this->snapshotService->getByType($type, $limit);
        
        return response()->json([
            'success' => true,
            'data' => $snapshots,
        ]);
    }
    
    /**
     * Get snapshots by event (API)
     */
    public function byEvent(Request $request, string $event)
    {
        $limit = $request->input('limit', 100);
        $snapshots = $this->snapshotService->getByEvent($event, $limit);
        
        return response()->json([
            'success' => true,
            'data' => $snapshots,
        ]);
    }
    
    /**
     * Restore a snapshot (creates new version, doesn't actually restore)
     */
    public function restore(Snapshot $snapshot)
    {
        try {
            $restoredModel = $snapshot->restore();
            
            return response()->json([
                'success' => true,
                'message' => 'Snapshot restored successfully',
                'data' => $restoredModel,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore snapshot: ' . $e->getMessage(),
            ], 400);
        }
    }
}
