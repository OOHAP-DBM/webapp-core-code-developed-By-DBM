<?php

namespace Modules\Admin\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Show total customers (users who are NOT approved vendors)
     */
    // public function index(Request $request)
    // {
    //     $customers = User::leftJoin('vendor_profiles', 'users.id', '=', 'vendor_profiles.user_id')
    //         ->where(function($query) {
    //             $query->whereNull('vendor_profiles.id')
    //                 ->orWhere('vendor_profiles.onboarding_status', '!=', 'approved');
    //         })
    //         ->select('users.id', 'users.name', 'users.email', 'users.created_at')
    //         ->orderByDesc('users.created_at')
    //         ->get();

    //     $totalCustomerCount = $customers->count();

    //     return view('admin.customer.index', compact('customers', 'totalCustomerCount'));
    // }
    public function index(Request $request)
    {
        $search = $request->search;
        $customers = User::where('active_role', 'customer')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->select('id', 'name', 'email', 'phone', 'created_at')
            ->orderByDesc('created_at')
            ->get();
        $totalCustomerCount = User::where('active_role', 'customer')->count();
        return view('admin.customer.index', compact('customers', 'totalCustomerCount'));
    }

    public function show($id)
    {
        // ✅ Customer fetch karo by ID
        $user = \App\Models\User::where('active_role', 'customer')
            ->findOrFail($id);

        // ✅ Us customer ki bookings
        $bookings = \App\Models\Booking::where('customer_id', $user->id)
            ->latest()
            ->get();

        // ✅ Stats calculate
        $stats = [
            'total'     => $bookings->count(),
            'active'    => $bookings->where('status', 'active')->count(),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
        ];

        // ✅ Admin view return
        return view('admin.customer.show', compact(
            'user',
            'bookings',
            'stats'
        ));
    }


}
