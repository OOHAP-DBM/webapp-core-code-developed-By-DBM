<?php


namespace Modules\DOOH\Controllers\Vendor;

use App\Http\Controllers\Controller;


use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Modules\DOOH\Models\DOOHScreen;
use Modules\DOOH\Services\DOOHPackageBookingService;

use Illuminate\Http\RedirectResponse;

class DOOHController extends Controller
{
    protected DOOHPackageBookingService $doohService;

    public function __construct(DOOHPackageBookingService $doohService)
    {
        $this->doohService = $doohService;
    }

    /**
     * Multi-step DOOH creation wizard (step 1-3)
     */
    public function create(Request $request): View
    {
        $vendor = Auth::user();
        // if (!$vendor->vendor_profile || $vendor->vendor_profile->onboarding_status !== 'approved') {
        //     return redirect()->route('vendor.onboarding.waiting')
        //         ->with('error', 'Your vendor onboarding is under review. You can add DOOH screens only after approval.');
        // }

        $step = (int) $request->query('step', 1);
        $step = max(1, min(3, $step));

        // Find or create draft for this vendor
        $draft = DOOHScreen::where('vendor_id', $vendor->id)
            ->where('status', DOOHScreen::STATUS_DRAFT)
            ->orderByDesc('updated_at')
            ->first();

        // If draft exists and current_step is set, resume from there
        if ($draft && $draft->current_step && $step < $draft->current_step) {
            // Always resume from last incomplete step
            $step = $draft->current_step;
        }

        // If no draft, create a new one on step 1
        if (!$draft && $step === 1) {
            $draft = new DOOHScreen();
            $draft->vendor_id = $vendor->id;
            $draft->status = DOOHScreen::STATUS_DRAFT;
            $draft->current_step = 1;
            $draft->save();
        }

        return view('dooh.vendor.create', [
            'step' => $step,
            'draft' => $draft,
        ]);
    }

    /**
     * Save current step as draft and move to next step
     */
    public function store(Request $request, \Modules\DOOH\Services\DOOHScreenService $service)
    {
        $vendor = Auth::user();
        $step = (int) $request->input('step', 1);
        $step = max(1, min(3, $step));

        if ($step === 1) {
            $result = $service->storeStep1($vendor, $request->all(), $request->file('media', []));
            if ($result['success']) {
                return redirect()->route('vendor.dooh.create', ['step' => 2])
                    ->with('success', 'Step 1 completed. Proceed to next step.');
            }
            return back()->withErrors($result['errors'])->withInput();
        }

        if ($step === 2) {
            $validated = $request->validate([
                'address'   => 'required|string|max:255',
                'pincode'   => 'required|string|max:20',
                'locality'  => 'required|string|max:100',
                'city'      => 'required|string|max:100',
                'state'     => 'required|string|max:100',
                'latitude'  => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'nearby_landmarks' => 'nullable|array',
                'geotag'    => 'nullable|url|max:255',
            ]);
            $draft = DOOHScreen::where('vendor_id', $vendor->id)
                ->where('status', DOOHScreen::STATUS_DRAFT)
                ->orderByDesc('updated_at')
                ->first();
            if ($draft) {
                $draft->fill($validated);
                $draft->current_step = 2;
                $draft->step1_completed = true;
                $draft->save();
            }
            return redirect()->route('vendor.dooh.create', ['step' => 3])
                ->with('success', 'Step 2 completed. Proceed to next step.');
        }

        if ($step === 3) {
            $validated = $request->validate([
                'price_per_slot'        => 'required|numeric|min:1',
                'price_per_month'       => 'nullable|numeric|min:0',
                'minimum_booking_amount'=> 'nullable|numeric|min:0',
                'min_slots_per_day'     => 'nullable|integer|min:1',
                'allowed_formats'       => 'nullable|array',
                'max_file_size_mb'      => 'nullable|integer|min:1',
                'is_municipal_approved' => 'nullable|boolean',
                'approval_document_path'=> 'nullable|string|max:255',
                'has_power_backup'      => 'nullable|boolean',
                'facing_direction'      => 'nullable|string|max:32',
                'expected_footfall'     => 'nullable|integer|min:0',
                'expected_eyeballs'     => 'nullable|integer|min:0',
            ]);
            $draft = DOOHScreen::where('vendor_id', $vendor->id)
                ->where('status', DOOHScreen::STATUS_DRAFT)
                ->orderByDesc('updated_at')
                ->first();
            if ($draft) {
                $draft->fill($validated);
                $draft->current_step = 3;
                $draft->step2_completed = true;
                $draft->step3_completed = true;
                $draft->status = DOOHScreen::STATUS_PENDING_APPROVAL;
                $draft->save();
            }
            return redirect()->route('vendor.dooh.create', ['step' => 3])
                ->with('success', 'All steps completed. Listing submitted for approval.');
        }
    }
}
