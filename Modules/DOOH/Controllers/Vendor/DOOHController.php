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
        // if (!$draft && $step === 1) {

        //     $hoarding = \App\Models\Hoarding::create([
        //         'vendor_id' => $vendor->id,
        //         'hoarding_type' => 'dooh',
        //         'status' => 'draft',
        //         'approval_status' => 'pending',
        //         'current_step' => 1,
        //     ]);

        //     $draft = DOOHScreen::create([
        //         'hoarding_id' => $hoarding->id,
        //         // 'status' => DOOHScreen::STATUS_DRAFT,
              
        //     ]);
        // }


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
        $screenId = $request->input('screen_id');
        if ($step === 1) {
            $result = $service->storeStep1($vendor, $request->all(), $request->file('media', []));
            if ($result['success']) {
                return redirect()->route('vendor.dooh.create', ['step' => 2])
                    ->with('success', 'Step 1 completed. Proceed to next step.');
            }
            return back()->withErrors($result['errors'])->withInput();
        }

        if ($step === 2) {
            // $draft = DOOHScreen::whereHas('hoarding', function ($q) use ($vendor) {
            //     $q->where('vendor_id', $vendor->id)
            //         ->where('status', 'draft'); // use the hoarding's status
            // })
            //     ->orderByDesc('updated_at')
            //     ->first();

            // if (!$draft) {
            //     return back()->withErrors(['step2' => 'Draft not found.'])->withInput();
            // }

            // // Handle skip
            // if ($request->input('skip_step2')) {
            //     $draft->current_step = 3;
            //     $draft->save();
            //     return redirect()->route('vendor.dooh.create', ['step' => 3])
            //         ->with('success', 'Step 2 skipped. Proceed to next step.');
            // }

            // // Collect all step 2 fields from request
            // $data = [
            //     'nagar_nigam_approved' => $request->input('nagar_nigam_approved'),
            //     'block_dates' => $request->input('block_dates'),
            //     'grace_period' => $request->input('grace_period'),
            //     'audience_types' => $request->input('audience_type'),
            //     'visible_from' => $request->input('visible_from'),
            //     'located_at' => $request->input('located_at'),
            //     'hoarding_visibility' => $request->input('hoarding_visibility'),
            //     'visibility_details' => $request->input('visibility_details'),
            // ];
            // $brandLogoFiles = $request->file('brand_logos', []);

            // $result = $service->storeStep2($draft, $data, $brandLogoFiles);
            // if ($result['success']) {
            //     return redirect()->route('vendor.dooh.create', ['step' => 3])
            //         ->with('success', 'Step 2 completed. Proceed to next step.');
            // }
            // return back()->withErrors($result['errors'])->withInput();

            $screen = DOOHScreen::where('id', $screenId)
                ->whereHas('hoarding', function ($q) use ($vendor) {
                    $q->where('vendor_id', $vendor->id);
                })->firstOrFail();
            $result = $service->storeStep2($screen, $request->all(), $request->file('brand_logos', []));
            return redirect()->route('vendor.dooh.create', ['step' => 3])
                ->with('success', 'Step 2 completed. Proceed to next step.');
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
                // Auto-generate SEO title if empty
                if (empty($draft->hoarding->title)) {
                    $draft->hoarding->title = $draft->hoarding->generateSeoTitle();
                    $draft->hoarding->save();
                }
                $draft->hoarding->status = Hoarding::STATUS_PENDING_APPROVAL;
                $draft->hoarding->current_step = 3; // Mark as finished
                $draft->save();

                return redirect()->route('vendor.hoardings.myHoardings', ['step' => 3])
                    ->with('success', 'Hoarding submitted successfully! It is now under review and will be published once approved.');
            }
            return back()->withErrors($result['errors'])->withInput();
        }
    }

    /**
     * Show the form for editing the specified DOOH screen.
     */
    // public function edit($id)
    // {
    //     $vendor = Auth::user();
    //     $screen = DOOHScreen::where('id', $id)
    //         ->whereHas('hoarding', function ($q) use ($vendor) {
    //             $q->where('vendor_id', $vendor->id);
    //         })->firstOrFail();
    //     // Fetch attributes for dropdowns if needed
    //     $attributes = \App\Models\HoardingAttribute::groupedByType();
    //     return view('dooh.vendor.edit', [
    //         'screen' => $screen,
    //         'attributes' => $attributes,
    //     ]);
    // }

    /**
     * Update the specified DOOH screen in storage.
     */
    // public function update(Request $request, $id)
    // {
    //     $vendor = Auth::user();
    //     $screen = DOOHScreen::where('id', $id)
    //         ->whereHas('hoarding', function ($q) use ($vendor) {
    //             $q->where('vendor_id', $vendor->id);
    //         })->firstOrFail();

    //     $validated = $request->validate([
    //         'title' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'price_per_month' => 'required|numeric|min:0',
    //         // Add other validation rules as needed
    //         'primary_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
    //         'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
    //     ]);

    //     // Handle primary image update
    //     if ($request->hasFile('primary_image')) {
    //         if ($screen->primary_image) {
    //             \Storage::disk('public')->delete($screen->primary_image);
    //         }
    //         $validated['primary_image'] = $request->file('primary_image')->store('dooh/screens', 'public');
    //     }

    //     // Handle gallery images update (optional, simplistic: remove all, add new)
    //     if ($request->hasFile('gallery_images')) {
    //         foreach ($screen->galleryImages as $image) {
    //             \Storage::disk('public')->delete($image->image_path);
    //             $image->delete();
    //         }
    //         foreach ($request->file('gallery_images') as $file) {
    //             $screen->galleryImages()->create([
    //                 'image_path' => $file->store('dooh/screens/gallery', 'public'),
    //             ]);
    //         }
    //     }

    //     $screen->update($validated);

    //     return redirect()
    //         ->route('hoardings.vendor.index')
    //         ->with('success', 'DOOH screen updated successfully!');
    // }


   
    /**
     * Edit DOOH Screen (Multi-step)
     */
    public function edit(Request $request, $id): View|RedirectResponse
    {
        $vendor = Auth::user();
        $step = (int) $request->query('step', 1);
        $step = max(1, min(3, $step));

        // Find the DOOH screen belonging to this vendor
        $screen = DOOHScreen::whereHas('hoarding', function ($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id);
        })->findOrFail($id);

        $hoarding = $screen->hoarding;

        // If hoarding is not DOOH type, redirect to OOH edit
        if ($hoarding->hoarding_type !== 'dooh') {
            return redirect()->route('vendor.hoardings.edit', ['id' => $hoarding->ooh->id, 'step' => $step])
                ->with('info', 'Redirected to OOH edit page.');
        }

        // Fetch attributes for form dropdowns
        $attributes = \App\Models\HoardingAttribute::groupedByType();

        return view('dooh.vendor.edit', [
            'step' => $step,
            'screen' => $screen,
            'hoarding' => $hoarding,
            'attributes' => $attributes,
        ]);
    }

    /**
     * Update DOOH Screen (Multi-step)
     */
    public function update(Request $request, $id, \Modules\DOOH\Services\DOOHScreenService $service): RedirectResponse
    {
        $vendor = Auth::user();
        $step = (int) $request->input('step', 1);
        $step = max(1, min(3, $step));

        // Find the DOOH screen
        $screen = DOOHScreen::whereHas('hoarding', function ($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id);
        })->findOrFail($id);

        $hoarding = $screen->hoarding;

        // Ensure it's DOOH type
        if ($hoarding->hoarding_type !== 'dooh') {
            return redirect()->route('vendor.hoardings.edit', $hoarding->ooh->id)
                ->with('error', 'This is an OOH hoarding. Please use OOH edit.');
        }

        try {
            switch ($step) {
                case 1:
                    $mediaFiles = $request->file('media', []);
                    $result = $service->updateStep1($screen, $request->all(), $mediaFiles);
                    break;

                case 2:
                    $brandLogoFiles = $request->file('brand_logos', []);
                    $result = $service->storeStep2($screen, $request->all(), $brandLogoFiles);
                    break;

                case 3:
                    // dd($data = $request->all());
                    $result = $service->updateStep3($screen, $request->all());
                    break;

                default:
                    return redirect()->back()->withErrors(['step' => 'Invalid step number']);
            }

            if (!$result['success']) {
                return redirect()->back()
                    ->withErrors($result['errors'] ?? ['error' => 'Update failed'])
                    ->withInput();
            }

            // Navigate to next step or finish
            if ($request->has('save_and_next') && $step < 3) {
                return redirect()->route('vendor.dooh.edit', ['id' => $id, 'step' => $step + 1])
                    ->with('success', "Step {$step} updated! Continue to Step " . ($step + 1));
            }

            // Mark as completed on step 3
            if ($step === 3) {
                if ($hoarding->status === 'draft' || $hoarding->approval_status === 'pending') {
                    $hoarding->update([
                        'status' => 'pending_approval',
                        'approval_status' => 'pending',
                        'current_step' => null,
                    ]);
                }
            }

            return redirect()->route('vendor.hoardings.myHoardings')
                ->with('success', 'DOOH Screen updated successfully!, Once approved by our team, it will be live on the platform.');

        } catch (\Exception $e) {
            \Log::error('DOOH Update Failed', [
                'step' => $step,
                'screen_id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()
                ->withErrors(['message' => 'Update failed: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
