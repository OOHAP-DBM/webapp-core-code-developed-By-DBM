<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $vendor = Auth::user();
        $profile = $vendor->vendorProfile;
        // Only allow dashboard if onboarding_status is pending_approval or approved
        if (!$profile || !in_array($profile->onboarding_status, ['pending_approval', 'approved'])) {
            // Redirect to correct onboarding step
            $step = $profile ? $profile->onboarding_step : 1;
            $routes = [
                1 => 'vendor.onboarding.contact-details',
                2 => 'vendor.onboarding.business-info',
                // 3 => 'vendor.onboarding.kyc-documents',
                // 4 => 'vendor.onboarding.bank-details',
                // 5 => 'vendor.onboarding.terms-agreement',
            ];
            $route = $routes[$step] ?? 'vendor.onboarding.contact-details';
            return redirect()->route($route)
                ->with('info', 'Please complete your vendor onboarding.');
        }

        // ...existing code for dashboard stats and view...
        // Calculate stats
        $stats = [
            'total_revenue' => $vendor->bookings()
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
            'revenue_change' => 12.5, // Calculate percentage change
            'active_bookings' => $vendor->bookings()
                ->whereIn('status', ['confirmed', 'active'])
                ->count(),
            'bookings_change' => 5, // New this week
            'total_listings' => $vendor->hoardings()->count(),
            'available_listings' => $vendor->hoardings()
                ->where('status', 'approved')
                ->count(),
            'pending_tasks' => $vendor->tasks()
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'overdue_tasks' => $vendor->tasks()
                ->where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->count(),
        ];
        
        // Revenue chart data (last 7 days)
        $revenueChartLabels = [];
        $revenueChartData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenueChartLabels[] = $date->format('M d');
            $revenueChartData[] = $vendor->bookings()
                ->whereDate('created_at', $date)
                ->where('payment_status', 'paid')
                ->sum('total_amount');
        }
        
        // Booking status stats
        $stats['confirmed_bookings'] = $vendor->bookings()->where('status', 'confirmed')->count();
        $stats['pending_bookings'] = $vendor->bookings()->where('status', 'pending')->count();
        $stats['completed_bookings'] = $vendor->bookings()->where('status', 'completed')->count();
        $stats['cancelled_bookings'] = $vendor->bookings()->where('status', 'cancelled')->count();
        
        // Recent bookings
        $recentBookings = $vendor->bookings()
            ->with(['customer', 'hoarding'])
            ->latest()
            ->take(5)
            ->get();
        
        // Pending tasks
        $pendingTasks = $vendor->tasks()
            ->where('status', '!=', 'completed')
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();
        
        return view('vendor.dashboard', compact(
            'stats',
            'revenueChartLabels',
            'revenueChartData',
            'recentBookings',
            'pendingTasks'
        ));
    }
}
