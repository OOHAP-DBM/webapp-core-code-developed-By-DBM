<?php

namespace Modules\Admin\Controllers\Web\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\VendorProfile;
use App\Mail\VendorEnabledMail;
use App\Mail\VendorDisabledMail;
use Illuminate\Support\Facades\Mail;

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
        $query = VendorProfile::with(['user' => function($q) {
            $q->withCount([
                'hoardings',
                'activeHoardings as active_hoardings_count'
            ]);
        }])
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

        // Attach hoarding counts to each vendor profile for blade compatibility
        foreach ($vendors as $vendor) {
            $vendor->total_hoardings_count = $vendor->user->hoardings_count ?? 0;
            $vendor->active_hoardings_count = $vendor->user->active_hoardings_count ?? 0;
        }
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
    public function create()
    {
        return view('admin.vendors.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'phone'         => 'required|string|max:20|unique:users,phone',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:4',

            'company_name'  => 'required|string|max:255',
            'gstin'         => 'nullable|string|max:20',
            'pan'           => 'nullable|string|max:20',
            'address'       => 'required|string|max:500',
            'city'          => 'required|string|max:100',
            'state'         => 'required|string|max:100',
            'pincode'       => 'required|string|max:10',
            'status'        => 'required|in:active,inactive',
        ]);

        DB::beginTransaction();

        try {
            // ---------------- USER CREATE ----------------
            $user = User::create([
                'name'              => $request->name,
                'phone'             => $request->phone,
                'email'             => $request->email,
                'password'          => Hash::make($request->password),

                'active_role'       => 'vendor',
                'status'            => $request->status,

                // ADMIN VERIFIED
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]);

            $user->assignRole('vendor');

            // Send welcome mail to vendor
            try {
                \Mail::to($user->email)->send(new \Modules\Mail\VendorWelcomeMail($user));
            } catch (\Exception $e) {
                \Log::error('Vendor welcome mail failed: ' . $e->getMessage());
            }

            // ---------------- VENDOR PROFILE CREATE ----------------
            VendorProfile::create([
                'user_id'                   => $user->id,

                // AUTO APPROVED (ADMIN CREATED)
                'onboarding_status'         => 'approved',
                'onboarding_step'           => 3,
                'onboarding_completed_at'   => now(),
                'approved_at'               => now(),
                'approved_by'               => auth()->id(),

                // BUSINESS DETAILS
                'company_name'              => $request->company_name,
                'gstin'                     => $request->gstin,
                'pan'                       => $request->pan,
                'registered_address'        => $request->address,
                'city'                      => $request->city,
                'state'                     => $request->state,
                'pincode'                   => $request->pincode,

                // KYC AUTO VERIFIED
                'kyc_verified'              => 1,
                'kyc_verified_at'           => now(),

                // TERMS AUTO ACCEPT
                'terms_accepted'            => 1,
                'terms_accepted_at'         => now(),
                'terms_ip_address'          => request()->ip(),

                // DEFAULT COMMISSION
                'commission_percentage'     => 10,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.vendors.index', ['status' => 'approved'])
                ->with('success', 'Vendor created and auto-approved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'vendor_ids' => 'required|array',
            'commission_percentage' => 'required|numeric|min:1|max:100'
        ]);

        DB::beginTransaction();

        try {

            foreach ($request->vendor_ids as $vendorProfileId) {

                $vendor = VendorProfile::find($vendorProfileId);
                if(!$vendor) continue;

                // vendor profile approve
                $vendor->update([
                    'onboarding_status'      => 'approved',
                    'approved_at'            => now(),
                    'approved_by'            => auth()->id(),
                    'kyc_verified'           => 1,
                    'kyc_verified_at'        => now(),
                    'commission_percentage'  => $request->commission_percentage,
                    'onboarding_completed_at'=> now(),
                    'terms_accepted'         => 1,
                    'terms_accepted_at'      => now(),
                    'terms_ip_address'       => request()->ip(),
                ]);

                // user activate
                $user = User::find($vendor->user_id);
                if($user){
                    $user->update([
                        'status' => 1,
                        'email_verified_at' => now(),
                        'phone_verified_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Selected vendors approved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Approval failed'
            ],500);
        }
    }
    public function bulkDisable(Request $request)
    {
        $vendors = VendorProfile::whereIn('id',$request->vendor_ids)->get();

        foreach ($vendors as $vendor) {
            $vendor->update([
                'onboarding_status' => 'suspended',
                'suspended_at' => now()
            ]);

            // optional: block login
            if ($vendor->user) {
                $vendor->user->update([
                    'is_active' => 0,
                    'status' =>'suspended'
                ]);

                // Send disabled mail
                if ($vendor->user->email) {
                    try {
                        \Mail::to($vendor->user->email)->send(new VendorDisabledMail($vendor->user));
                    } catch (\Exception $e) {
                        \Log::error('Vendor disabled mail failed: ' . $e->getMessage());
                    }
                }
            }
        }

        return response()->json([
            'success'=>true,
            'message'=>'Selected vendors disabled successfully'
        ]);
    }
    public function bulkEnable(Request $request)
    {
        $vendors = VendorProfile::with('user')
            ->whereIn('id', $request->vendor_ids)
            ->get();

        foreach ($vendors as $vendor) {
            $vendor->update([
                'onboarding_status' => 'approved',
                'suspended_at' => null
            ]);
            if($vendor->user){
                $vendor->user->update([
                    'is_active' => 1,
                    'status' =>'active'
                ]);

                // Send enabled mail
                if ($vendor->user->email) {
                    try {
                        \Mail::to($vendor->user->email)->send(new VendorEnabledMail($vendor->user));
                    } catch (\Exception $e) {
                        \Log::error('Vendor enabled mail failed: ' . $e->getMessage());
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Selected vendors enabled successfully'
        ]);
    }
    public function export(Request $request)
    {
        $status = $request->get('status', 'approved');
        $format = $request->get('format', 'csv');

        $vendors = VendorProfile::with('user')
            ->where('onboarding_status', $status)
            ->get();

        $columns = ['ID', 'Name', 'Email', 'Phone', 'Company', 'City', 'State', 'Status'];

        $rows = $vendors->map(function ($vendor) {
            return [
                $vendor->id,
                $vendor->user->name ?? '',
                $vendor->user->email ?? '',
                $vendor->user->phone ?? '',
                $vendor->company_name ?? '',
                $vendor->city ?? '',
                $vendor->state ?? '',
                $vendor->onboarding_status ?? '',
            ];
        });

        $filename = "vendors_{$status}_" . now()->format('Ymd_His');

        if ($format === 'excel') {

            $html = '<table>';
            $html .= '<tr>' . implode('', array_map(fn($col) => "<th>{$col}</th>", $columns)) . '</tr>';
            foreach ($rows as $row) {
                $html .= '<tr>' . implode('', array_map(fn($cell) => "<td>{$cell}</td>", $row)) . '</tr>';
            }
            $html .= '</table>';

            return response($html, 200, [
                'Content-Type'        => 'application/vnd.ms-excel',
                'Content-Disposition' => "attachment; filename={$filename}.xls",
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            ]);

        } elseif ($format === 'pdf') {

            $html = '<!DOCTYPE html><html><head><style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                h2 { margin-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; }
                th { background: #f3f4f6; font-weight: bold; }
            </style></head><body>';
            $html .= "<h2>Vendors Export - " . ucfirst(str_replace('_', ' ', $status)) . "</h2>";
            $html .= '<table><thead><tr>';
            foreach ($columns as $col) {
                $html .= "<th>{$col}</th>";
            }
            $html .= '</tr></thead><tbody>';
            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= "<td>" . htmlspecialchars((string) $cell) . "</td>";
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table></body></html>';

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('a4', 'landscape');

            return $pdf->download("{$filename}.pdf");

        } else {

            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename={$filename}.csv",
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            $callback = function () use ($columns, $rows) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);
                foreach ($rows as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }
    }
    public function hoardings($vendorId)
    {
        $vendor = User::with('vendorProfile')->findOrFail($vendorId);
        $search = request('search');

        $approvedQuery = $vendor->hoardings()->whereIn('status', ['active', 'inactive']);
        $pendingQuery = $vendor->hoardings()->where('status', 'Pending_Approval');

                if ($search) {
                        $approvedQuery->where(function($q) use ($search) {
                                $q->where('title', 'like', "%$search%")
                                    ->orWhere('city', 'like', "%$search%")
                                    ;
                        });
                        $pendingQuery->where(function($q) use ($search) {
                                $q->where('title', 'like', "%$search%")
                                    ->orWhere('city', 'like', "%$search%")
                                    ;
                        });
                }

        $approvedHoardings = $approvedQuery->orderByDesc('id')->paginate(10, ['*'], 'approved_page');
        $pendingHoardings = $pendingQuery->orderByDesc('id')->paginate(10, ['*'], 'pending_page');

        return view('admin.vendors.hoardings', compact('vendor', 'approvedHoardings', 'pendingHoardings'));
    }
}
