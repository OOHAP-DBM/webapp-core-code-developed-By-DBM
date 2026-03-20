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
        return view('vendor.offers.create', compact('enquiry'));
    }
}