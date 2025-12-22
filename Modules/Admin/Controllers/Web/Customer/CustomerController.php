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
        $customers = User::where('active_role', 'customer')
            ->select('id', 'name', 'email', 'created_at')
            ->orderByDesc('created_at')
            ->get();

        $totalCustomerCount = $customers->count();

        return view('admin.customer.index', compact('customers', 'totalCustomerCount'));
    }
}
