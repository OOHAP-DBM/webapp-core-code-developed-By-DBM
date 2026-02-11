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
    public function create()
    {
        return view('admin.customer.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'email' => 'required|email|unique:users,email',
            'address' => 'required|string',
            'pincode' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'password' => 'required|string|min:4',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        $validated['active_role'] = 'customer';
        $validated['status'] = 'active';
        $validated['email_verified_at'] = now(); 
        $validated['phone_verified_at'] = now(); 

        $user = \App\Models\User::create($validated);
        $user->assignRole('customer');
        return redirect()->route('admin.customers.index')->with('success', 'Customer added successfully!');
    }
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'total');
        $search = $request->search;

        $baseQuery = User::where('active_role', 'customer');

        // Tab filters
        $query = clone $baseQuery;
        switch ($tab) {
            case 'week':
                $query->where('created_at', '>=', now()->startOfWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', now()->startOfMonth());
                break;
            case 'deletion':
                $query->where('status', 'deletion_requested');
                break;
            case 'disabled':
                $query->where('status', 'inactive');
                break;
            case 'deleted':
                $query = $query->onlyTrashed();
                break;
            case 'total':
            default:
                // No extra filter
                break;
        }

        $customers = $query
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->select('id', 'name', 'email', 'phone','status', 'created_at')
            ->orderByDesc('created_at')
            ->get();

        // Counts for tabs
        $counts = (object) [
            'total' => $baseQuery->count(),
            'week' => (clone $baseQuery)->where('created_at', '>=', now()->startOfWeek())->count(),
            'month' => (clone $baseQuery)->where('created_at', '>=', now()->startOfMonth())->count(),
            'deletion' => (clone $baseQuery)->where('status', 'deletion_requested')->count(),
            'disabled' => (clone $baseQuery)->where('status', 'inactive')->count(),
            'deleted' => (clone $baseQuery)->onlyTrashed()->count(),
        ];

        // For compatibility with previous code
        $totalCustomers = $counts->total;
        $joinedThisWeek = $counts->week;
        $joinedThisMonth = $counts->month;
        $deletionRequests = $counts->deletion;
        $disabled = $counts->disabled;
        $deleted = $counts->deleted;
        $totalCustomerCount = $totalCustomers;

        return view('admin.customer.index', compact(
            'customers',
            'tab',
            'counts',
            'totalCustomers',
            'joinedThisWeek',
            'joinedThisMonth',
            'deletionRequests',
            'disabled',
            'deleted',
            'totalCustomerCount'
        ));
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
    public function edit($id)
    {
        $user = User::where('active_role', 'customer')->findOrFail($id);
        return view('admin.customer.edit', compact('user'));
    }
    public function update(Request $request, $id)
    {
        $user = User::where('active_role', 'customer')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'avatar' => 'nullable|image|max:2048',
        ]);

        /* ---------- AVATAR UPDATE ---------- */
        if ($request->hasFile('avatar')) {

            // old image delete
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }

            // new upload
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        /* ---------- PASSWORD UPDATE (optional) ---------- */
        if ($request->filled('password')) {
            $validated['password'] = \Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer updated successfully!');
    }
    public function destroy($id)
    {
        try {
            $user = User::where('active_role', 'customer')->findOrFail($id);

            // avatar delete (optional but professional)
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }

            // SOFT DELETE
            $user->delete();

            return response()->json([
                'status' => true,
                'message' => 'Customer deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong!'
            ], 500);
        }
    }
}
