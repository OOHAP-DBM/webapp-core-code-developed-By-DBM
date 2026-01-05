<?php


namespace Modules\DOOH\Controllers\Vendor;

use App\Http\Controllers\Controller;


use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Modules\DOOH\Models\DOOHScreen;
use Modules\DOOH\Services\DOOHPackageBookingService;


use Illuminate\Http\RedirectResponse;
use App\Models\Hoarding;

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
        // $draft = DOOHScreen::where('vendor_id', $vendor->id)
        //     ->where('status', DOOHScreen::STATUS_DRAFT)
        //     ->orderByDesc('updated_at')
        //     ->first();
        $draft = DOOHScreen::whereHas('hoarding', function ($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id)
                ->where('status', 'draft'); // ✅ STATUS BELONGS HERE
        })
            ->orderByDesc('updated_at')
            ->first();



        // If draft exists and current_step is set, resume from there
        if ($draft && $draft->current_step && $step < $draft->current_step) {
            // Always resume from last incomplete step
            $step = $draft->current_step;
        }

        // If no draft, create a new one on step 1
        // if (!$draft && $step === 1) {
        //     $draft = new DOOHScreen();
        //     $draft->vendor_id = $vendor->id;
        //     $draft->status = DOOHScreen::STATUS_DRAFT;
        //     $draft->current_step = 1;
        //     $draft->save();
        // }
        if (!$draft && $step === 1) {

            $hoarding = \App\Models\Hoarding::create([
                'vendor_id' => $vendor->id,
                'hoarding_type' => 'dooh',
                'status' => 'draft',
                'approval_status' => 'pending',
                'current_step' => 1,
            ]);

            $draft = DOOHScreen::create([
                'hoarding_id' => $hoarding->id,
                // 'status' => DOOHScreen::STATUS_DRAFT,
              
            ]);
        }


        // Fetch attributes for form dropdowns (categories, etc.)
        $attributes = \App\Models\HoardingAttribute::groupedByType();

        return view('dooh.vendor.create', [
            'step' => $step,
            'draft' => $draft,
            'attributes' => $attributes,
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
            $draft = DOOHScreen::whereHas('hoarding', function ($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id)
                    ->where('status', 'draft'); // use the hoarding's status
            })
                ->orderByDesc('updated_at')
                ->first();

            if (!$draft) {
                return back()->withErrors(['step2' => 'Draft not found.'])->withInput();
            }

            // Handle skip
            if ($request->input('skip_step2')) {
                $draft->current_step = 3;
                $draft->save();
                return redirect()->route('vendor.dooh.create', ['step' => 3])
                    ->with('success', 'Step 2 skipped. Proceed to next step.');
            }

            // Collect all step 2 fields from request
            $data = [
                'nagar_nigam_approved' => $request->input('nagar_nigam_approved'),
                'block_dates' => $request->input('block_dates'),
                'grace_period' => $request->input('grace_period'),
                'audience_types' => $request->input('audience_type'),
                'visible_from' => $request->input('visible_from'),
                'located_at' => $request->input('located_at'),
                'hoarding_visibility' => $request->input('hoarding_visibility'),
                'visibility_details' => $request->input('visibility_details'),
            ];
            $brandLogoFiles = $request->file('brand_logos', []);

            $result = $service->storeStep2($draft, $data, $brandLogoFiles);
            if ($result['success']) {
                return redirect()->route('vendor.dooh.create', ['step' => 3])
                    ->with('success', 'Step 2 completed. Proceed to next step.');
            }
            return back()->withErrors($result['errors'])->withInput();
        }

        if ($step === 3) {
            $draft = DOOHScreen::whereHas('hoarding', function ($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id)
                    ->where('status', DOOHScreen::STATUS_DRAFT); // status check on parent
            })
                ->orderByDesc('updated_at')
                ->first();


            if (!$draft) {
                return back()->withErrors(['step3' => 'Draft not found.'])->withInput();
            }

            // Handle skip

            if ($request->input('skip_step3')) {
                $draft->current_step = 4; // or mark as completed/ready for approval
                $draft->hoarding->status = Hoarding::STATUS_PENDING_APPROVAL;
                $draft->hoarding->save();
                $draft->save();
                return redirect()->route('vendor.dooh.create', ['step' => 3])
                    ->with('success', 'Step 3 skipped. Listing submitted for approval.');
            }

            $result = $service->storeStep3($draft, $request->all());
            if ($result['success']) {
                $draft->hoarding->status = Hoarding::STATUS_PENDING_APPROVAL;
                $draft->hoarding->current_step = 3; // Mark as finished
                $draft->save();

                return redirect()->route('vendor.dooh.create', ['step' => 3])
                    ->with('success', 'All steps completed. Listing submitted for approval.');
            }
            return back()->withErrors($result['errors'])->withInput();
        }
    }
}
