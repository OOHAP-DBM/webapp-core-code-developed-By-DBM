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
    public function store(Request $request)
    {
        $vendor = Auth::user();
        // if (!$vendor->vendor_profile || $vendor->vendor_profile->onboarding_status !== 'approved') {
        //     return redirect()->route('vendor.onboarding.waiting')
        //         ->with('error', 'Your vendor onboarding is under review. You can add DOOH screens only after approval.');
        // }

        $step = (int) $request->input('step', 1);
        $step = max(1, min(3, $step));

        // Find or create draft
        $draft = DOOHScreen::where('vendor_id', $vendor->id)
            ->where('status', DOOHScreen::STATUS_DRAFT)
            ->orderByDesc('updated_at')
            ->first();
        if (!$draft) {
            $draft = new DOOHScreen();
            $draft->vendor_id = $vendor->id;
            $draft->status = DOOHScreen::STATUS_DRAFT;
        }

        // Validate and save only fields for current step
        if ($step === 1) {
            $validated = $request->validate([
                'screen_name' => 'required|string|max:255',
                'category' => 'nullable|string|max:255',
                'screen_type' => 'nullable|string|max:255',
                'width' => 'nullable|numeric',
                'height' => 'nullable|numeric',
                'size_unit' => 'nullable|string|max:10',
                'valid_till' => 'nullable|date',
                // ...add other step 1 fields as per Figma
            ]);
            $draft->fill($validated);
            $draft->current_step = 1;
        } elseif ($step === 2) {
            $validated = $request->validate([
                'address' => 'required|string|max:255',
                'pincode' => 'nullable|string|max:20',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                // ...add other step 2 fields as per Figma
            ]);
            $draft->fill($validated);
            $draft->current_step = 2;
        } elseif ($step === 3) {
            $validated = $request->validate([
                'display_price' => 'required|numeric',
                'video_length' => 'nullable|numeric',
                // ...add other step 3 fields as per Figma
            ]);
            $draft->fill($validated);
            $draft->current_step = 3;
        }

        $draft->status = DOOHScreen::STATUS_DRAFT;
        $draft->save();

        // Move to next step or stay if last
        $nextStep = $step < 3 ? $step + 1 : 3;
        return redirect()->route('vendor.dooh.create', ['step' => $nextStep])
            ->with('success', 'Draft saved.');
    }
}
