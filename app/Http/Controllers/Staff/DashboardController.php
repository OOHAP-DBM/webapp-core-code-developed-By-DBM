<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display staff dashboard
     * Works for both Web and API
     */
    public function index(Request $request)
    {
        $staff = Auth::user();
        
        // Calculate stats
        $stats = [
            'pending' => $staff->assignments()->where('status', 'pending')->count(),
            'in_progress' => $staff->assignments()->where('status', 'in_progress')->count(),
            'completed' => $staff->assignments()->where('status', 'completed')->count(),
            'overdue' => $staff->assignments()
                ->where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->count(),
        ];
        
        // Get recent assignments
        $recentAssignments = $staff->assignments()
            ->with(['booking.hoarding', 'booking.customer'])
            ->latest()
            ->take(10)
            ->get();
        
        // API Response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'staff' => [
                        'id' => $staff->id,
                        'name' => $staff->name,
                        'email' => $staff->email,
                        'phone' => $staff->phone,
                        'staff_type' => $staff->staff_type,
                    ],
                    'stats' => $stats,
                    'recent_assignments' => $recentAssignments
                ]
            ]);
        }
        
        // Web Response
        return view('staff.dashboard', compact('stats', 'recentAssignments'));
    }
}
