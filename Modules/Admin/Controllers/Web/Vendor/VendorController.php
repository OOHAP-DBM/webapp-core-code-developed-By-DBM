<?php

namespace Modules\Admin\Controllers\Web\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


use App\Models\VendorProfile;

class VendorController extends Controller
{
    // public function index(Request $request)
    // {
    //     $vendors = User::role('vendor')->orderByDesc('created_at')->paginate(30);
    //     return view('admin.vendors.index', compact('vendors'));
    // }
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending_approval');
        $search = $request->get('search');
        $query = VendorProfile::with('user')
        ->where(function ($main) use ($status, $search) {
            $main->where('onboarding_status', $status);
            if (!empty($search)) {
                $main->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                    });
                    $q->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%");
                });
            }
        });

        $vendors = $query->latest()->paginate(15)->withQueryString();
        $counts = VendorProfile::selectRaw("
            SUM(onboarding_status = 'pending_approval') as requested,
            SUM(onboarding_status = 'approved') as active,
            SUM(onboarding_status = 'suspended') as disabled,
            SUM(onboarding_status = 'rejected') as deleted
        ")->first();
        return view('admin.vendors.index', compact(
            'vendors',
            'status',
            'counts'
        ));
    }

    /**
     * Show requested vendors (pending approval)
     */
    // public function requestedVendors(Request $request)
    // {
    //     $requestedVendors = VendorProfile::with('user')
    //         ->where('onboarding_status', 'pending_approval')
    //         ->orderByDesc('created_at')
    //         ->paginate(15, ['id', 'user_id', 'company_name', 'gstin', 'pan', 'created_at']);

    //     $requestedCount = $requestedVendors->total();

    //     return view('admin.vendors.requested', compact('requestedVendors', 'requestedCount'));
    // }

    public function show($id)
    {
        // 1️⃣ User ko fetch karo (vendor role ke sath)
        $user = User::role('vendor')
            ->with('vendorProfile')
            ->findOrFail($id);

        // 2️⃣ Vendor profile ko alag variable me le lo (easy blade access)
        $vendorProfile = $user->vendorProfile;

        $businessTypes = [
            'proprietorship' => 'Proprietorship',
            'partnership'    => 'Partnership',
            'private_limited'=> 'Private Limited',
            'public_limited' => 'Public Limited',
            'llp'            => 'LLP',
            'other'          => 'Other',
        ];

        return view('admin.vendors.show', compact('user', 'vendorProfile', 'businessTypes'));
    }


    /**
     * Approve vendor with commission
     */

    // public function approve(Request $request, $id)
    // {
    //     $request->validate([
    //         'commission_percentage' => 'required|numeric|min:0|max:100',
    //     ]);

    //     // IMPORTANT: The ID coming from the button is the VendorProfile ID
    //     $profile = VendorProfile::findOrFail($id);

    //     // Update the profile
    //     $profile->update([
    //         'commission_percentage' => $request->commission_percentage,
    //         'onboarding_status'     => 'approved', // This removes them from the 'pending_approval' query
    //         'approved_at'           => now(),
    //         'approved_by'           => auth()->id(),
    //     ]);

    //     // Update the associated User status
    //     if ($profile->user) {
    //         $profile->user->update(['status' => 'active']);
    //     }

    //     if ($request->ajax()) {
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Vendor approved and moved to active list.'
    //         ]);
    //     }

    //     return redirect()->back()->with('success', 'Vendor approved.');
    // }
    public function approve(Request $request, $id)
    {
        // Use manual validator if you want total control, 
        // but $request->validate is fine as long as JS sends 'Accept: application/json'
        $request->validate([
            'commission_percentage' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $profile = VendorProfile::findOrFail($id);

            $profile->update([
                'commission_percentage' => $request->commission_percentage,
                'onboarding_status'     => 'approved',
                'approved_at'           => now(),
                'approved_by'           => auth()->id(),
            ]);

            if ($profile->user) {
                $profile->user->update(['status' => 'active']);

                // Send approval notification to vendor
                $profile->user->notify(new \App\Notifications\VendorApprovedNotification(false));
                $user = $profile->user->load('vendorProfile');

                // Send approval email to vendor
                try {
                    \Mail::to($profile->user->email)->send(
                        new \Modules\Mail\VendorApprovedMail(
                            $profile->user,
                            $request->commission_percentage
                        )
                    );
                } catch (\Exception $e) {
                    \Log::error('Vendor approval email failed: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Vendor approved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject vendor with reason
     */
    // public function reject(Request $request, $id)
    // {
    //     $request->validate([
    //         'reason' => 'required|string|max:255',
    //     ]);

    //     $profile = VendorProfile::findOrFail($id);
    //     $profile->onboarding_status = 'rejected';
    //     $profile->rejection_reason = $request->reason;
    //     $profile->save();

    //     if ($request->ajax()) {
    //         return response()->json(['success' => true, 'message' => 'Vendor has been rejected.']);
    //     }
    //     return redirect()->back()->with('success', 'Vendor rejected.');
    // }

    /**
     * Reject vendor with reason
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        try {
            $profile = VendorProfile::findOrFail($id);

            $profile->update([
                'onboarding_status' => 'rejected',
                'rejection_reason'  => $request->reason,
            ]);

            // Optional: Also update user status if needed
            if ($profile->user) {
                $profile->user->update(['status' => 'inactive']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Vendor has been rejected.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function suspend($id)
    {
        $vendor = User::role('vendor')->findOrFail($id);
        $vendor->status = 'suspended';
        $vendor->save();
        return redirect()->back()->with('success', 'Vendor suspended.');
    }
}
