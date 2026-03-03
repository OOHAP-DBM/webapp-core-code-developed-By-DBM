<?php

namespace Modules\POS\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Modules\POS\Models\POSBooking;
use Modules\POS\Models\PosCustomer;
use App\Models\User;

class AdminPosController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|superadmin']);
    }

    private function resolveEffectiveVendorId(Request $request): int
    {
        $sessionKey = 'pos.selected_vendor_id';
        $requestedVendorId = $request->input('vendor_id') ?? $request->query('vendor_id');

        if (!empty($requestedVendorId)) {
            $vendor = User::query()
                ->whereKey((int) $requestedVendorId)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'vendor');
                })
                ->first();

            if (!$vendor) {
                abort(422, 'Invalid vendor selected for POS context.');
            }

            if ($request->hasSession()) {
                $request->session()->put($sessionKey, (int) $vendor->id);
            }

            return (int) $vendor->id;
        }

        $sessionVendorId = $request->hasSession() ? $request->session()->get($sessionKey) : null;
        if (!empty($sessionVendorId)) {
            $exists = User::query()
                ->whereKey((int) $sessionVendorId)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'vendor');
                })
                ->exists();

            if ($exists) {
                return (int) $sessionVendorId;
            }
        }

        $fallbackVendorId = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'vendor');
            })
            ->orderBy('id')
            ->value('id');

        if (!$fallbackVendorId) {
            abort(422, 'No vendor available for POS context.');
        }

        if ($request->hasSession()) {
            $request->session()->put($sessionKey, (int) $fallbackVendorId);
        }

        return (int) $fallbackVendorId;
    }

    private function resolveAdminBookingScope(Request $request): string
    {
        $sessionKey = 'pos.admin_booking_scope';
        $allowed = ['overall', 'mine', 'vendor'];

        $requestedScope = strtolower((string) ($request->input('booking_scope') ?? $request->query('booking_scope') ?? ''));
        if ($requestedScope !== '' && in_array($requestedScope, $allowed, true)) {
            if ($request->hasSession()) {
                $request->session()->put($sessionKey, $requestedScope);
            }

            return $requestedScope;
        }

        $sessionScope = $request->hasSession() ? (string) $request->session()->get($sessionKey, '') : '';
        if ($sessionScope !== '' && in_array($sessionScope, $allowed, true)) {
            return $sessionScope;
        }

        if ($request->hasSession()) {
            $request->session()->put($sessionKey, 'vendor');
        }

        return 'vendor';
    }

    private function vendorSwitcherPayload(int $selectedVendorId, string $selectedBookingScope): array
    {
        $vendors = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'vendor');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return [
            'posVendors' => $vendors,
            'selectedPosVendorId' => $selectedVendorId,
            'selectedPosBookingScope' => $selectedBookingScope,
        ];
    }

    public function dashboard(Request $request)
    {
        $selectedBookingScope = $this->resolveAdminBookingScope($request);
        $selectedVendorId = $this->resolveEffectiveVendorId($request);
        return View::make('vendor.pos.dashboard', [
            'posBasePath' => '/admin/pos',
            'posRoutePrefix' => 'admin.pos',
            'posLayout' => 'layouts.admin',
        ] + $this->vendorSwitcherPayload($selectedVendorId, $selectedBookingScope));
    }

    public function index(Request $request)
    {
        $selectedBookingScope = $this->resolveAdminBookingScope($request);
        $selectedVendorId = $this->resolveEffectiveVendorId($request);
        return View::make('vendor.pos.list', [
            'posBasePath' => '/admin/pos',
            'posRoutePrefix' => 'admin.pos',
            'posLayout' => 'layouts.admin',
        ] + $this->vendorSwitcherPayload($selectedVendorId, $selectedBookingScope));
    }

    public function create(Request $request)
    {
        $selectedBookingScope = $this->resolveAdminBookingScope($request);
        $selectedVendorId = $this->resolveEffectiveVendorId($request);
        $viewData = [
            'posBasePath' => '/admin/pos',
            'posRoutePrefix' => 'admin.pos',
            'posLayout' => 'layouts.admin',
        ] + $this->vendorSwitcherPayload($selectedVendorId, $selectedBookingScope);

        $viewData['posScope'] = $selectedBookingScope;
        if ($selectedBookingScope === 'mine') {
            // Only show admin's own hoardings and self as customer
            $adminUser = Auth::user();
            $viewData['adminCustomer'] = [
                'id' => $adminUser->id,
                'name' => $adminUser->name,
                'phone' => $adminUser->phone,
                'email' => $adminUser->email,
            ];
            // Fetch only admin's hoardings
            $viewData['adminHoardings'] = \App\Models\Hoarding::where('vendor_id', $adminUser->id)->active()->get();
            $viewData['activeVendorId'] = $adminUser->id;
        } elseif ($selectedBookingScope === 'vendor') {
            $viewData['activeVendorId'] = $selectedVendorId;
        } else {
            $viewData['activeVendorId'] = null;
        }
        return View::make('vendor.pos.create', $viewData);
    }

    public function show(Request $request, $id)
    {
        $selectedBookingScope = $this->resolveAdminBookingScope($request);
        $selectedVendorId = $this->resolveEffectiveVendorId($request);
        return View::make('vendor.pos.show', [
            'bookingId' => $id,
            'posBasePath' => '/admin/pos',
            'posRoutePrefix' => 'admin.pos',
            'posLayout' => 'layouts.admin',
        ] + $this->vendorSwitcherPayload($selectedVendorId, $selectedBookingScope));
    }

    public function edit(Request $request, $id)
    {
        $selectedBookingScope = $this->resolveAdminBookingScope($request);
        $selectedVendorId = $this->resolveEffectiveVendorId($request);
        return View::make('vendor.pos.show', [
            'bookingId' => $id,
            'posBasePath' => '/admin/pos',
            'posRoutePrefix' => 'admin.pos',
            'posLayout' => 'layouts.admin',
        ] + $this->vendorSwitcherPayload($selectedVendorId, $selectedBookingScope));
    }

    public function customers(Request $request)
    {
        $selectedBookingScope = $this->resolveAdminBookingScope($request);
        $vendorId = $this->resolveEffectiveVendorId($request);
        $customers = collect();

        if ($selectedBookingScope === 'mine') {
            // Only show real customers created by admin
            $adminUser = Auth::user();
            $customers = PosCustomer::where('created_by', $adminUser->id)->get();
        } elseif ($selectedBookingScope === 'overall') {
            // All vendors: show all customers for all vendors
            $bookingCustomers = POSBooking::whereNotNull('customer_id')
                ->pluck('customer_id')
                ->unique()
                ->toArray();
            $posCustomerUserIds = PosCustomer::whereNotNull('user_id')
                ->pluck('user_id')
                ->unique()
                ->toArray();
            $allUserIds = collect($bookingCustomers)
                ->merge($posCustomerUserIds)
                ->unique()
                ->filter()
                ->values()
                ->toArray();
            $users = User::whereIn('id', $allUserIds)
                ->with('posProfile')
                ->get();
            $customers = $users->map(function ($user) {
                $bookings = POSBooking::where('customer_id', $user->id)->get();
                $totalBookings = $bookings->count();
                $totalSpent = $bookings->sum('total_amount');
                $lastBookingAt = $bookings->max('created_at');
                $name = $user->name;
                if ($user->posProfile && $user->posProfile->business_name) {
                    $name = $user->posProfile->business_name;
                }
                return [
                    'id' => $user->id,
                    'name' => $name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'total_bookings' => $totalBookings,
                    'total_spent' => $totalSpent,
                    'last_booking_at' => $lastBookingAt,
                    'is_active' => $totalBookings > 0,
                ];
            });
        } else {
            // 'vendor' scope: show only for selected vendor
            $bookingCustomers = POSBooking::where('vendor_id', $vendorId)
                ->whereNotNull('customer_id')
                ->pluck('customer_id')
                ->unique()
                ->toArray();
            $posCustomerUserIds = PosCustomer::where('vendor_id', $vendorId)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->unique()
                ->toArray();
            $allUserIds = collect($bookingCustomers)
                ->merge($posCustomerUserIds)
                ->unique()
                ->filter()
                ->values()
                ->toArray();
            $users = User::whereIn('id', $allUserIds)
                ->with(['posProfile' => function ($query) use ($vendorId) {
                    $query->where('vendor_id', $vendorId);
                }])
                ->get();
            $customers = $users->map(function ($user) use ($vendorId) {
                $bookings = POSBooking::where('vendor_id', $vendorId)
                    ->where('customer_id', $user->id)
                    ->get();
                $totalBookings = $bookings->count();
                $totalSpent = $bookings->sum('total_amount');
                $lastBookingAt = $bookings->max('created_at');
                $name = $user->name;
                if ($user->posProfile && $user->posProfile->business_name) {
                    $name = $user->posProfile->business_name;
                }
                return [
                    'id' => $user->id,
                    'name' => $name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'total_bookings' => $totalBookings,
                    'total_spent' => $totalSpent,
                    'last_booking_at' => $lastBookingAt,
                    'is_active' => $totalBookings > 0,
                ];
            });
        }

        $totalCustomers = $customers->count();

        $viewData = [
            'customers' => $customers,
            'totalCustomers' => $totalCustomers,
            'posBasePath' => '/admin/pos',
            'posRoutePrefix' => 'admin.pos',
            'posLayout' => 'layouts.admin',
            'posScope' => $selectedBookingScope,
        ] + $this->vendorSwitcherPayload($vendorId, $selectedBookingScope);

        return View::make('vendor.pos.customers', $viewData);
    }

    /**
     * Show a single customer details for admin POS
     */
   public function showCustomer($id)
    {
        $vendorId = $this->resolveEffectiveVendorId(request());

        // Find user by ID
        $user = User::findOrFail($id);

        // Get POS profile for this vendor (if exists)
        $posProfile = $user->posProfile()->where('vendor_id', $vendorId)->first();

        // Get all bookings for this user and vendor
        $bookings = POSBooking::where('vendor_id', $vendorId)
            ->where('customer_id', $user->id)
            ->get();

        $totalBookings = $bookings->count();
        $totalSpent = $bookings->sum('total_amount');
        $lastBookingAt = $bookings->max('created_at');

        // Prefer posProfile business name if exists
        $name = $user->name;
        if ($posProfile && $posProfile->business_name) {
            $name = $posProfile->business_name;
        }

        $customer = [
            'id' => $user->id,
            'name' => $name,
            'phone' => $user->phone,
            'email' => $user->email,
            'total_bookings' => $totalBookings,
            'total_spent' => $totalSpent,
            'last_booking_at' => $lastBookingAt,
            'is_active' => $totalBookings > 0,
            'pos_profile' => $posProfile,
            'bookings' => $bookings,
        ];

        return view('vendor.pos.customers.show', compact('customer'));
    }
    // Extend: edit, view, etc. as needed
}
