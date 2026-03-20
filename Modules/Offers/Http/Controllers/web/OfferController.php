<?php

namespace Modules\Offers\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function create(Request $request)
    {
        $enquiry = null;
        if ($request->has('enquiry_id')) {
            $enquiryId = $request->get('enquiry_id');
            $enquiry = \App\Models\Enquiry::find($enquiryId);
        }

        // Fetch only hoardings belonging to the logged-in vendor
        $hoardings = [];
        if (auth()->check()) {
            $hoardings = \App\Models\Hoarding::where('vendor_id', auth()->id())->get();
        }

        // Only pass all customers if you want to show a dropdown, otherwise leave empty (AJAX will handle search)
        $customers = collect();

        return view('vendor.offers.create', compact('enquiry', 'hoardings', 'customers'));
    }

    /**
     * AJAX: Return customer suggestions for offer form search
     */
    public function customerSuggestions(Request $request)
    {
        $search = $request->input('search');
        $customers = \App\Models\User::where('active_role', 'customer')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone']);

        return response()->json(['data' => $customers]);
    }

    /**
     * Handle creation of a new customer from the offer form modal
     */
    public function createCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = new \App\Models\User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->active_role = 'customer';
        $user->status = 'active';
        $user->password = bcrypt(str_random(10)); // random password, can be reset later
        $user->save();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ]
        ]);
    }
    
}