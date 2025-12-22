<?php

namespace Modules\Admin\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


use App\Models\VendorProfile;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::orderByDesc('created_at')->paginate(30);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show customers (users without approved vendor profile)
     */
    public function customers(Request $request)
    {
        $customers = User::leftJoin('vendor_profiles', 'users.id', '=', 'vendor_profiles.user_id')
            ->where(function($query) {
                $query->whereNull('vendor_profiles.id')
                    ->orWhere('vendor_profiles.onboarding_status', '!=', 'approved');
            })
            ->select('users.id', 'users.name', 'users.email', 'users.created_at')
            ->orderByDesc('users.created_at')
            ->get();

        $totalCustomers = $customers->count();

        return view('admin.users.customers', compact('customers', 'totalCustomers'));
    }
}
