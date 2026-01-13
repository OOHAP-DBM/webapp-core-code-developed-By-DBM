<?php

namespace Modules\Hoardings\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Modules\Hoardings\Models\OOHHoarding;
use Modules\Hoardings\Services\HoardingListService;


use Illuminate\Http\RedirectResponse;
use App\Models\Hoarding;

class OOHListingController extends Controller
{
    protected HoardingListService $hoardingService;

    public function __construct(HoardingListService $hoardingService)
    {
        $this->hoardingService = $hoardingService;
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
        $draft = OOHHoarding::whereHas('hoarding', function ($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id)
                ->where('status', 'draft');
        })
            ->orderByDesc('updated_at')
            ->first();

        if ($draft && $draft->current_step && $step < $draft->current_step) {
            $step = $draft->current_step;
        }

        // if (!$draft && $step === 1) {
        // dd($step);

        //     $hoarding = \App\Models\Hoarding::create([
        //         'vendor_id' => $vendor->id,
        //         'hoarding_type' => 'ooh',
        //         'status' => 'draft',
        //         'approval_status' => 'pending',
        //         'current_step' => 1,
        //     ]);
        //     $draft = OOHHoarding::create([
        //         'hoarding_id' => $hoarding->id,
        //     ]);
        // }

        // Fetch attributes for form dropdowns
        $attributes = \App\Models\HoardingAttribute::groupedByType();

        return view('hoardings.vendor.create', [
            'step' => $step,
            'draft' => $draft,
            'attributes' => $attributes,
        ]);
    }

    /**
     * Save current step as draft and move to next step
     */

    public function store(Request $request)
    {
        $vendor = Auth::user();
        $step = (int) $request->input('step', 1);
        $oohId = $request->input('ooh_id'); // Required for Step 2 and 3

        try {
            switch ($step) {
                case 1:
                    // Use validated data and files from FormRequest
                    $step1Request = app(\Modules\Hoardings\Http\Requests\StoreOOHHoardingStep1Request::class);
                    $validated = $step1Request->validated();
                    $mediaFiles = $step1Request->file('media', []);
                    $result = $this->hoardingService->storeStep1($vendor, $validated, $mediaFiles);
                    return redirect()->route('vendor.hoardings.create', ['step' => 2])
                        ->with('success', 'Step 1 completed. Proceed to next step.');

                case 2:
                    $screen = OOHHoarding::where('id', $oohId)
                        ->whereHas('hoarding', function ($q) use ($vendor) {
                            $q->where('vendor_id', $vendor->id);
                        })->firstOrFail();
                    $result = $this->hoardingService->storeStep2($screen, $request->all(), $request->file('brand_logos', []));
                    return redirect()->route('vendor.hoardings.create', ['step' => 3])
                        ->with('success', 'Step 2 completed. Proceed to next step.');

                case 3:
                    $screen = OOHHoarding::where('id', $oohId)
                        ->whereHas('hoarding', function ($q) use ($vendor) {
                            $q->where('vendor_id', $vendor->id);
                        })->firstOrFail();
                    $this->hoardingService->storeStep3($screen, $request->all());
                    return redirect()->route('vendor.hoardings.myHoardings', ['step' => 3])
                        ->with('success', 'Hoarding submitted successfully! It is now under review and will be published once approved.');

                default:
                    return redirect()->back()->withErrors(['message' => 'Invalid step provided']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => $e->getMessage()])->withInput();
        }
    }

    /**
     * GET /api/v1/dooh/draft
     * Resumes existing draft for the vendor
     */
    // public function getDraft()
    // {
    //     $draft = OOHHoarding::with(['media', 'brandLogos', 'slots', 'packages'])
    //         ->whereHas('hoarding', function ($q) {
    //             $q->where('vendor_id', Auth::id());
    //         })
    //         ->where('status', OOHHoarding::STATUS_DRAFT)
    //         ->latest()
    //         ->first();


    //     if (!$draft) {
    //         return response()->json(['message' => 'No active draft found'], 404);
    //     }

    //     return response()->json(['data' => $draft]);
    // }
}
