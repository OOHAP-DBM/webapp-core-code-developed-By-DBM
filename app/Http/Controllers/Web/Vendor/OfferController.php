<?php

namespace App\Http\Controllers\Web\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    /**
     * Show the form for creating a new offer.
     */
    /**
     * Show the form for creating a new offer.
     * Optionally accepts an enquiry to prefill the form.
     */
    public function create(Request $request)
    {
        $enquiry = null;
        // If an enquiry_id is provided, try to load the enquiry model
        if ($request->has('enquiry_id')) {
            $enquiryId = $request->get('enquiry_id');
            $enquiry = \App\Models\Enquiry::find($enquiryId);
        }
        return view('vendor.offers.create', compact('enquiry'));
    }
}
