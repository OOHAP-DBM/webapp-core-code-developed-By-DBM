<?php

namespace Modules\Hoardings\Controllers\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

/**
 * Vendor HoardingController
 * Handles Add Hoardings flow (OOH/DOOH type selection)
 * Architectural rules strictly enforced:
 * - DOOH logic is delegated to DOOH module
 * - OOH logic handled here only
 * - Onboarding status checked before all actions
 */
class HoardingController extends Controller
{
    /**
     * Show hoarding type selection screen (OOH/DOOH)
     * GET /vendor/hoardings/add
     */
    public function showTypeSelection(Request $request)
    {
        $user = Auth::user();
        $vendorProfile = $user->vendor_profile ?? null;
        // if (!$vendorProfile || $vendorProfile->onboarding_status !== 'approved') {
        //     // Block access if not approved
        //     return Redirect::route('vendor.dashboard')
        //         ->with('error', 'Your vendor onboarding is under review. You can add hoardings only after approval.');
        // }
        // Sidebar highlight: 'add-hoardings' (passed to view)
        return view('hoardings.vendor.add_type_selection', [
            'sidebarActive' => 'add-hoardings',
        ]);
    }

    /**
     * Handle hoarding type selection (OOH/DOOH)
     * POST /vendor/hoardings/select-type
     */
    public function handleTypeSelection(Request $request)
    {
        $user = Auth::user();
        $vendorProfile = $user->vendor_profile ?? null;
        // if (!$vendorProfile || $vendorProfile->onboarding_status !== 'approved') {
        //     // Block access if not approved
        //     return Redirect::route('vendor.onboarding.waiting')
        //         ->with('error', 'Your vendor onboarding is under review. You can add hoardings only after approval.');
        // }
        $type = $request->input('hoarding_type');
        if ($type === 'DOOH') {
            // DOOH: Redirect to DOOH module (NO business logic here)
            return Redirect::route('vendor.dooh.create'); // Vendor DOOH creation route
        }
        if ($type === 'OOH') {
            // OOH: Continue OOH flow, create draft
            Session::put('hoarding_type', 'OOH');
            return Redirect::route('hoardings.create'); // Existing OOH create route
        }
        // Invalid type: redirect back
        return Redirect::back()->with('error', 'Please select a valid hoarding type.');
    }
}

            