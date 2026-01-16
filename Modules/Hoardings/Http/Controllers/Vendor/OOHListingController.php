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

    /**
     * Edit OOH Hoarding (Multi-step)
     */
   
    public function edit(Request $request, $id): View|RedirectResponse
    {
        $vendor = Auth::user();
        $step = (int) $request->query('step', 1);
        $step = max(1, min(3, $step));

        // Find the OOH hoarding belonging to this vendor WITH hoarding relationship
        $oohHoarding = OOHHoarding::with('hoarding')->whereHas('hoarding', function ($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id);
        })->findOrFail($id);

        $hoarding = $oohHoarding->hoarding;

        // If hoarding is not OOH type, redirect to DOOH edit
        if ($hoarding->hoarding_type !== 'ooh') {
            return redirect()->route('vendor.dooh.edit', ['id' => $hoarding->doohScreen->id, 'step' => $step])
                ->with('info', 'Redirected to DOOH edit page.');
        }

        // Fetch attributes for form dropdowns
        $attributes = \App\Models\HoardingAttribute::groupedByType();
        
        return view('hoardings.vendor.edit', [
            'step' => $step,
            'listing' => $oohHoarding,
            'hoarding' => $hoarding,
            'attributes' => $attributes,
        ]);
    }

    /**
     * Update OOH Hoarding (Multi-step)
     */
    public function update(Request $request, $id): RedirectResponse
    {

        $vendor = Auth::user();
        $step = (int) $request->input('step', 1);
        $step = max(1, min(3, $step));

        // Find the OOH hoarding
        $oohHoarding = OOHHoarding::whereHas('hoarding', function ($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id);
        })->findOrFail($id);

        $hoarding = $oohHoarding->hoarding;

        // Ensure it's OOH type
        if ($hoarding->hoarding_type !== 'ooh') {
            return redirect()->route('vendor.dooh.edit', $hoarding->doohScreen->id)
                ->with('error', 'This is a DOOH hoarding. Please use DOOH edit.');
        }

        try {
            switch ($step) {
                case 1:
                    return $this->updateStep1($request, $oohHoarding,$hoarding);

                case 2:
                    return $this->updateStep2($request, $oohHoarding,$hoarding);

                case 3:
                    return $this->updateStep3($request, $oohHoarding,$hoarding);

                default:
                    return redirect()->back()->withErrors(['step' => 'Invalid step number']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('OOH Update Failed', [
                'step' => $step,
                'ooh_id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->withErrors(['message' => 'Update failed: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Update Step 1: Basic Info & Media
     */
   
    protected function updateStep1(Request $request, OOHHoarding $oohHoarding, Hoarding $hoarding): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'screen_type' => 'nullable|string',
            'width' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:1',
            'measurement_unit' => 'required|in:sqft,sqm',
            'address' => 'required|string',
            'locality' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'required|string',
            'pincode' => 'required|string|max:10',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'landmarks' => 'nullable|array', 
            'base_monthly_price' => 'required|numeric|min:0',
            'monthly_price' => 'nullable|numeric|min:0',
            'media.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $mediaFiles = $request->file('media', []);

        $result = $this->hoardingService->updateStep1($hoarding, $oohHoarding, $validated, $mediaFiles);

        if (!$result['success']) {
            return redirect()->back()
                ->withErrors($result['errors'] ?? ['error' => 'Update failed'])
                ->withInput();
        }

        // Determine next step or finish
        if ($request->has('save_and_next')) {
            return redirect()->route('vendor.edit.ooh', ['id' => $oohHoarding->id, 'step' => 2])
                ->with('success', 'Step 1 updated! Continue to Step 2.');
        }

        return redirect()->route('vendor.hoardings.myHoardings')
            ->with('success', 'Hoarding updated successfully!');
    }
    /**
     * Update Step 2: Settings & Visibility
     */
    protected function updateStep2(Request $request, OOHHoarding $oohHoarding, Hoarding $hoarding): RedirectResponse
    {
        $validated = $request->validate([
            'grace_period_days' => 'nullable|integer|min:0|max:30',
            'blocked_dates_json' => 'nullable|json',
            'nagar_nigam_approved' => 'required|boolean',
            'permit_number' => 'nullable|string|max:255',
            'permit_valid_till' => 'nullable|date|after:today',
            'audience_type' => 'nullable|array',
            'audience_type.*' => 'nullable|string',
            'visible_from' => 'nullable|array',
            'visible_from.*' => 'nullable|string',
            'located_at' => 'nullable|array',
            'located_at.*' => 'nullable|string',
            'visibility_type' => 'nullable|in:one_way,both_side',
            'visibility_start' => 'nullable|array',
            'visibility_start.*' => 'nullable|string',
            'visibility_end' => 'nullable|array',
            'visibility_end.*' => 'nullable|string',
            'expected_footfall' => 'nullable|integer|min:0',
            'expected_eyeball' => 'nullable|integer|min:0',
            'brand_logos' => 'nullable|array',
            'brand_logos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'delete_brand_logos' => 'nullable|array',
            'delete_brand_logos.*' => 'nullable|integer',
        ]);

        $brandLogoFiles = $request->file('brand_logos', []);

        $result = $this->hoardingService->storeStep2($hoarding, $validated, $brandLogoFiles);

        if (!$result['success']) {
            return redirect()->back()
                ->withErrors($result['errors'] ?? ['error' => 'Update failed'])
                ->withInput();
        }

        if ($request->has('save_and_next')) {
            return redirect()->route('vendor.edit.ooh', ['id' => $oohHoarding->id, 'step' => 3])
                ->with('success', 'Step 2 updated! Continue to Step 3.');
        }

        return redirect()->route('vendor.hoardings.myHoardings')
            ->with('success', 'Settings updated successfully!');
    }

    
    /**
     * Update Step 3: Pricing & Packages
     */
    protected function updateStep3(Request $request, OOHHoarding $oohHoarding, Hoarding $hoarding): RedirectResponse
    {
        $validated = $request->validate([
            'enable_weekly_booking' => 'nullable|boolean',
            'weekly_price_1' => 'nullable|numeric|min:0',
            'weekly_price_2' => 'nullable|numeric|min:0',
            'weekly_price_3' => 'nullable|numeric|min:0',
            'mounting_included' => 'nullable|boolean',
            'mounting_charge' => 'nullable|numeric|min:0',
            'printing_included' => 'nullable|boolean',
            'printing_charge' => 'nullable|numeric|min:0',
            'material_type' => 'nullable|in:flex,vinyl,canvas',
            'graphics_included' => 'nullable|boolean',
            'graphics_charge' => 'nullable|numeric|min:0',
            'lighting_included' => 'nullable|boolean',
            'lighting_charge' => 'nullable|numeric|min:0',
            'lighting_type' => 'nullable|in:front-lit,back-lit,led,none',
            'remounting_charge' => 'nullable|numeric|min:0',
            'survey_charge' => 'nullable|numeric|min:0',
            'offer_id' => 'nullable|array',
            'offer_id.*' => 'nullable|integer',
            'offer_name' => 'nullable|array',
            'offer_name.*' => 'nullable|string|max:255',
            'offer_duration' => 'nullable|array',
            'offer_duration.*' => 'nullable|integer|min:1',
            'offer_unit' => 'nullable|array',
            'offer_unit.*' => 'nullable|in:weeks,months',
            'offer_discount' => 'nullable|array',
            'offer_discount.*' => 'nullable|numeric|min:0|max:100',
            'offer_end_date' => 'nullable|array',
            'offer_end_date.*' => 'nullable|date|after:today',
            'offer_services' => 'nullable|array',
        ]);

        // Pass the OOHHoarding (child), not the parent Hoarding
        $result = $this->hoardingService->storeStep3($oohHoarding, $validated);

        if (!$result['success']) {
            return redirect()->back()
                ->withErrors($result['errors'] ?? ['error' => 'Update failed'])
                ->withInput();
        }

        // Mark as completed and submit for approval if still draft
        if ($hoarding->status === 'draft' || $hoarding->approval_status === 'pending') {
            $hoarding->update([
                'status' => 'pending_approval',
                'approval_status' => 'pending',
                'current_step' => null, // Clear draft step
            ]);
        }

        return redirect()->route('vendor.hoardings.myHoardings')
            ->with('success', 'Hoarding updated and submitted for review!');
    }
}
