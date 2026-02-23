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
        // $vendor = Auth::user();
        // $step = (int) $request->query('step', 1);
        // $step = max(1, min(3, $step));

        // $screenId = $request->query('screen_id');
        // if ($step === 1) {
        //     $draft = null;
        // } else {
        //     $draft = null;
        //     if ($screenId) {
        //         $draft = DOOHScreen::where('id', $screenId)
        //             ->whereHas('hoarding', function ($q) use ($vendor) {
        //                 $q->where('vendor_id', $vendor->id)
        //                     ->where('status', 'draft');
        //             })
        //             ->first();
        //     }else {
        //         $draft = DOOHScreen::whereHas('hoarding', function ($q) use ($vendor) {
        //             $q->where('vendor_id', $vendor->id)
        //                 ->where('status', 'draft');
        //         })
        //             ->orderByDesc('updated_at')
        //             ->first();
        //     }
        // }
        $vendor = Auth::user();
        $step = (int) $request->query('step', 1);
        $step = max(1, min(3, $step));

        $screenId = $request->query('screen_id');
        $draft = null;

        // ✅ Always try to load draft if screen_id is present (even on step 1)
        if ($screenId) {
            $draft = DOOHScreen::where('id', $screenId)
                ->whereHas('hoarding', function ($q) use ($vendor) {
                    $q->where('vendor_id', $vendor->id);
                })
                ->first();
        }

        // Fallback: load latest draft if no screen_id (only for steps > 1)
        if (!$draft && $step > 1) {
            $draft = DOOHScreen::whereHas('hoarding', function ($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id)
                    ->where('status', 'draft');
            })
            ->orderByDesc('updated_at')
            ->first();
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
        $attributes = \Modules\Hoardings\Models\HoardingAttribute::groupedByType();

        return view('dooh.vendor.create', [
            'step' => $step,
            'draft' => $draft,
            'attributes' => $attributes,
        ]);
    }

    /**
     * Save current step as draft and move to next step
     */
    // public function store(Request $request, \Modules\DOOH\Services\DOOHScreenService $service)
    // {
    //     // dd($request->all());
    //   $vendor   = Auth::user();
    // $step     = (int) $request->input('step', 1);
    // $step     = max(1, min(3, $step));
    // $screenId = $request->input('screen_id');

    // // ── Handle Previous button ──
    // if ($request->input('go_back') === '1') {
    //     $previousStep = max(1, $step - 1);
    //     return redirect()->route('vendor.dooh.create', [
    //         'step'      => $previousStep,
    //         'screen_id' => $screenId, // ✅ always pass screen_id back
    //     ]);
    // }

    // if ($step === 1) {
    //     // ✅ If screen_id exists, UPDATE instead of CREATE
    //     if ($screenId) {
    //         $screen = DOOHScreen::where('id', $screenId)
    //             ->whereHas('hoarding', function ($q) use ($vendor) {
    //                 $q->where('vendor_id', $vendor->id);
    //             })->first();

    //         if ($screen) {
    //             $result = $service->updateStep1($screen, $request->all(), $request->file('media', []));
    //             if ($result['success']) {
    //                 return redirect()->route('vendor.dooh.create', [
    //                     'step'      => 2,
    //                     'screen_id' => $screen->id,
    //                 ])->with('success', 'Step 1 updated. Proceed to next step.');
    //             }
    //             return back()->withErrors($result['errors'] ?? [])->withInput();
    //         }
    //     }

    //     // No screen_id → fresh CREATE
    //     $result = $service->storeStep1($vendor, $request->all(), $request->file('media', []));
    //     if ($result['success']) {
    //         $screen   = $result['screen'] ?? null;
    //         $screenId = $screen ? $screen->id : null;
    //         return redirect()->route('vendor.dooh.create', [
    //             'step'      => 2,
    //             'screen_id' => $screenId,
    //         ])->with('success', 'Step 1 completed. Proceed to next step.');
    //     }
    //     return back()->withErrors($result['errors'] ?? [])->withInput();
    // }

    // if ($step === 2) {
    //     $screen = DOOHScreen::where('id', $screenId)
    //         ->whereHas('hoarding', function ($q) use ($vendor) {
    //             $q->where('vendor_id', $vendor->id);
    //         })->firstOrFail();

    //     $result = $service->storeStep2($screen, $request->all(), $request->file('brand_logos', []));

    //     return redirect()->route('vendor.dooh.create', [
    //         'step'      => 3,
    //         'screen_id' => $screenId,
    //     ])->with('success', 'Step 2 completed. Proceed to next step.');
    // }

    // if ($step === 3) {
    //     $screen = DOOHScreen::where('id', $screenId)
    //         ->whereHas('hoarding', function ($q) use ($vendor) {
    //             $q->where('vendor_id', $vendor->id);
    //         })->firstOrFail();

    //         $result = $service->storeStep3($draft, $request->all());
    //         if ($result['success']) {
    //             $draft->hoarding->current_step = 3; // Mark as finished
    //             $draft->save();
    //             $status = $draft->hoarding->status;
    //             $successMsg = ($status === Hoarding::STATUS_ACTIVE)
    //                 ? 'Hoarding submitted successfully! It is published.'
    //                 : 'Hoarding submitted successfully! It is now under review and will be published once approved';
    //             return redirect()->route('vendor.hoardings.myHoardings', ['step' => 3])
    //                 ->with('success', $successMsg);
    //         }
    //         return back()->withErrors($result['errors'])->withInput();
    //     }
    //     return back()->withErrors($result['errors'] ?? [])->withInput();
    // }
    // }

    public function store(Request $request, \Modules\DOOH\Services\DOOHScreenService $service)
    {
        $vendor   = Auth::user();
        $step     = (int) $request->input('step', 1);
        $step     = max(1, min(3, $step));
        $screenId = $request->input('screen_id');

        if ($request->input('go_back') === '1') {
            $previousStep = max(1, $step - 1);
            return redirect()->route('vendor.dooh.create', [
                'step'      => $previousStep,
                'screen_id' => $screenId,
            ]);
        }

        if ($step === 1) {
            if ($screenId) {
                $screen = DOOHScreen::where('id', $screenId)
                    ->whereHas('hoarding', function ($q) use ($vendor) {
                        $q->where('vendor_id', $vendor->id);
                    })->first();

                if ($screen) {
                    $result = $service->updateStep1($screen, $request->all(), $request->file('media', []));
                    if ($result['success']) {
                        return redirect()->route('vendor.dooh.create', [
                            'step'      => 2,
                            'screen_id' => $screen->id,
                        ])->with('success', 'Step 1 updated.');
                    }

                    return back()->withErrors($result['errors'] ?? [])->withInput();
                }
            }

            $result = $service->storeStep1($vendor, $request->all(), $request->file('media', []));
            if ($result['success']) {
                $screen   = $result['screen'] ?? null;
                $screenId = $screen ? $screen->id : null;

                return redirect()->route('vendor.dooh.create', [
                    'step'      => 2,
                    'screen_id' => $screenId,
                ])->with('success', 'Step 1 completed.');
            }

            return back()->withErrors($result['errors'] ?? [])->withInput();
        }

        if ($step === 2) {
            $screen = DOOHScreen::where('id', $screenId)
                ->whereHas('hoarding', function ($q) use ($vendor) {
                    $q->where('vendor_id', $vendor->id);
                })->firstOrFail();

            $service->storeStep2($screen, $request->all(), $request->file('brand_logos', []));

            return redirect()->route('vendor.dooh.create', [
                'step'      => 3,
                'screen_id' => $screenId,
            ])->with('success', 'Step 2 completed.');
        }

        if ($step === 3) {
            $screen = DOOHScreen::where('id', $screenId)
                ->whereHas('hoarding', function ($q) use ($vendor) {
                    $q->where('vendor_id', $vendor->id);
                })->firstOrFail();

            $result = $service->storeStep3($screen, $request->all());
            if ($result['success']) {
                $screen->hoarding->current_step = 3;
                $screen->hoarding->save();

                $status = $screen->hoarding->status;
                $successMsg = ($status === Hoarding::STATUS_ACTIVE)
                    ? 'Hoarding submitted successfully! It is published.'
                    : 'Hoarding submitted successfully! It is now under review and will be published once approved.';
                return redirect()->route('vendor.hoardings.myHoardings', ['step' => 3])
                    ->with('success', $successMsg);
            }

            return back()->withErrors($result['errors'] ?? [])->withInput();
        }

        return back();
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
        $attributes = \Modules\Hoardings\Models\HoardingAttribute::groupedByType();

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
    $step   = (int) $request->input('step', 1);
    $step   = max(1, min(3, $step));

    $screen = DOOHScreen::whereHas('hoarding', function ($q) use ($vendor) {
        $q->where('vendor_id', $vendor->id);
    })->findOrFail($id);

    $hoarding = $screen->hoarding;

    if ($hoarding->hoarding_type !== 'dooh') {
        return redirect()->route('vendor.hoardings.edit', $hoarding->ooh->id)
            ->with('error', 'This is an OOH hoarding. Please use OOH edit.');
    }

    try {
        switch ($step) {

            case 1:
                $result = $service->updateStep1($screen, $request->all(), $request->file('media', []));
                if (!$result['success']) {
                    return back()->withErrors($result['errors'] ?? [])->withInput();
                }
                // ✅ Always go to step 2
                return redirect()->route('vendor.dooh.edit', ['id' => $id, 'step' => 2])
                    ->with('success', 'Step 1 saved! Continue to Step 2.');

            case 2:
                $result = $service->storeStep2($screen, $request->all(), $request->file('brand_logos', []));
                if (!$result['success']) {
                    return back()->withErrors($result['errors'] ?? [])->withInput();
                }
                // ✅ Always go to step 3
                return redirect()->route('vendor.dooh.edit', ['id' => $id, 'step' => 3])
                    ->with('success', 'Step 2 saved! Continue to Step 3.');

            case 3:
                $result = $service->updateStep3($screen, $request->all());
                if (!$result['success']) {
                    return back()->withErrors($result['errors'] ?? [])->withInput();
                }
                // ✅ Only step 3 goes to listings
                return redirect()->route('vendor.hoardings.myHoardings')
                    ->with('success', 'DOOH Screen updated successfully! Once approved by our team, it will be live.');

            default:
                return back()->withErrors(['step' => 'Invalid step number.']);
        }

    } catch (\Exception $e) {
        \Log::error('DOOH Update Failed', [
            'step'      => $step,
            'screen_id' => $id,
            'error'     => $e->getMessage(),
        ]);
        return back()
            ->withErrors(['message' => 'Update failed: ' . $e->getMessage()])
            ->withInput();
    }
    }
}
