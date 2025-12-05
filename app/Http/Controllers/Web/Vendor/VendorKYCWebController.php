<?php

namespace App\Http\Controllers\Web\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorKYC;
use Illuminate\View\View;

class VendorKYCWebController extends Controller
{
    /**
     * Show KYC submission form
     * GET /vendor/kyc/submit
     */
    public function showSubmitForm(): View
    {
        $vendor = auth()->user();
        $kyc = VendorKYC::where('vendor_id', $vendor->id)->first();
        
        return view('vendor.kyc.submit', compact('kyc'));
    }
}
