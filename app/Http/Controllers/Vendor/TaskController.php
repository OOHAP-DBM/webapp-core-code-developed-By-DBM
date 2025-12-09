<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $vendor = Auth::user();
        
        // Calculate stats
        $stats = [
            'pending' => $vendor->tasks()->where('status', 'pending')->count(),
            'in_progress' => $vendor->tasks()->where('status', 'in_progress')->count(),
            'completed' => $vendor->tasks()->where('status', 'completed')->count(),
            'overdue' => $vendor->tasks()
                ->where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->count(),
        ];
        
        // Get tasks by status
        $pendingTasks = $vendor->tasks()
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->get();
        
        $inProgressTasks = $vendor->tasks()
            ->where('status', 'in_progress')
            ->orderBy('due_date', 'asc')
            ->get();
        
        $completedTasks = $vendor->tasks()
            ->where('status', 'completed')
            ->latest('completed_at')
            ->take(10)
            ->get();
        
        // Get related bookings for task creation
        $bookings = $vendor->bookings()
            ->whereIn('status', ['confirmed', 'active'])
            ->with('hoarding')
            ->get();
        
        return view('vendor.tasks.index', compact(
            'stats',
            'pendingTasks',
            'inProgressTasks',
            'completedTasks',
            'bookings'
        ));
    }
    
    public function store(Request $request)
    {
        $vendor = Auth::user();
        
        $validated = $request->validate([
            'type' => 'required|in:graphics,printing,mounting,maintenance',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'booking_id' => 'nullable|exists:bookings,id',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'required|date',
        ]);
        
        $validated['status'] = 'pending';
        $validated['vendor_id'] = $vendor->id;
        
        $task = $vendor->tasks()->create($validated);
        
        return redirect()
            ->route('vendor.tasks.index')
            ->with('success', 'Task created successfully!');
    }
    
    public function show($id)
    {
        $vendor = Auth::user();
        $task = $vendor->tasks()
            ->with(['booking.hoarding', 'booking.customer'])
            ->findOrFail($id);
        
        return view('vendor.tasks.show', compact('task'));
    }
    
    public function start($id)
    {
        $vendor = Auth::user();
        $task = $vendor->tasks()->findOrFail($id);
        
        if ($task->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending tasks can be started'
            ], 400);
        }
        
        $task->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Task started successfully!'
        ]);
    }
    
    public function complete($id)
    {
        $vendor = Auth::user();
        $task = $vendor->tasks()->findOrFail($id);
        
        if ($task->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Task is already completed'
            ], 400);
        }
        
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress' => 100,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Task completed successfully!'
        ]);
    }
    
    public function updateProgress(Request $request, $id)
    {
        $vendor = Auth::user();
        $task = $vendor->tasks()->findOrFail($id);
        
        $validated = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);
        
        $task->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Progress updated successfully!'
        ]);
    }
    
    public function destroy($id)
    {
        $vendor = Auth::user();
        $task = $vendor->tasks()->findOrFail($id);
        
        $task->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully!'
        ]);
    }
}
