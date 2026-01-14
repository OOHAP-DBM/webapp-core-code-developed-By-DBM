<?php

namespace App\Http\Controllers\Vendor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Enquiries\Models\Enquiry;

class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        // Fetch all enquiries for vendor's hoardings
        $vendorId = auth()->id();
        $enquiries = Enquiry::whereHas('items.hoarding', function($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })
        ->with(['items.hoarding', 'customer'])
        ->latest()
        ->paginate(10);

        return view('vendor.enquiries.index', compact('enquiries'));
    }
}
