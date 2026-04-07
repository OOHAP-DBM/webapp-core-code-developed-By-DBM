<?php

namespace Modules\DOOH\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Modules\DOOH\Models\DOOHScreen;

class DOOHController extends Controller
{
    /**
     * Admin DOOH multi-step creation wizard (step 1-3)
     */
    public function create(Request $request): View
    {
        $admin = Auth::user();

        if (!$admin->hasAnyRole(['admin', 'superadmin', 'super_admin'])) {
            abort(403, 'Unauthorized to create DOOH hoardings.');
        }

        $step = (int) $request->query('step', 1);
        $step = max(1, min(3, $step));
        $screenId = $request->query('screen_id');

        $draft = null;

        if ($screenId) {
            $draft = DOOHScreen::with([
                'hoarding',
                'hoarding.brandLogos',
                'media',
            ])
                ->where('id', $screenId)
                ->whereHas('hoarding', function ($q) use ($admin) {
                    $q->where('vendor_id', $admin->id);
                })
                ->first();
        }

        $attributes = \Modules\Hoardings\Models\HoardingAttribute::groupedByType();

        return view('dooh.admin.create', [
            'step' => $step,
            'draft' => $draft,
            'attributes' => $attributes,
        ]);
    }

    /**
     * Save current DOOH step and move to next step
     */
    public function store(Request $request, \Modules\DOOH\Services\DOOHScreenService $service): RedirectResponse
    {
        try {
            $admin = Auth::user();

            if (!$admin->hasAnyRole(['admin', 'superadmin', 'super_admin'])) {
                abort(403, 'Unauthorized to create DOOH hoardings.');
            }

            $step = (int) $request->input('step', 1);
            $step = max(1, min(3, $step));
            $screenId = $request->input('screen_id');

            // Ensure validation exceptions redirect back to blade form.
            $request->headers->set('Accept', 'text/html');

            if ($request->input('go_back') === '1') {
                return redirect()->route('admin.dooh.create', [
                    'step' => max(1, $step - 1),
                    'screen_id' => $screenId ?: null,
                ]);
            }

            if ($step === 1) {
                if ($screenId) {
                    $screen = DOOHScreen::where('id', $screenId)
                        ->whereHas('hoarding', fn($q) => $q->where('vendor_id', $admin->id))
                        ->first();

                    if ($screen) {
                        $service->updateStep1($screen, $request->all(), $request->file('media', []));

                        return redirect()->route('admin.dooh.create', [
                            'step' => 2,
                            'screen_id' => $screen->id,
                        ])->with('success', 'Step 1 updated.');
                    }
                }

                $result = $service->storeStep1($admin, $request->all(), $request->file('media', []));

                return redirect()->route('admin.dooh.create', [
                    'step' => 2,
                    'screen_id' => $result['screen']->id ?? null,
                ])->with('success', 'Step 1 completed.');
            }

            if ($step === 2) {
                $screen = DOOHScreen::where('id', $screenId)
                    ->whereHas('hoarding', fn($q) => $q->where('vendor_id', $admin->id))
                    ->firstOrFail();

                $service->storeStep2($screen, $request->all(), $request->file('brand_logos', []));

                return redirect()->route('admin.dooh.create', [
                    'step' => 3,
                    'screen_id' => $screenId,
                ])->with('success', 'Step 2 completed.');
            }

            if ($step === 3) {
                $screen = DOOHScreen::where('id', $screenId)
                    ->whereHas('hoarding', fn($q) => $q->where('vendor_id', $admin->id))
                    ->firstOrFail();

                $service->storeStep3($screen, $request->all());

                return redirect()->route('admin.my-hoardings')
                    ->with('success', 'DOOH hoarding submitted successfully!');
            }

            return redirect()->back();
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            \Log::error('Admin DOOH Store Error', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Something went wrong')->withInput();
        }
    }

    /**
     * Admin DOOH edit multi-step wizard.
     */
    public function edit(Request $request, $id): View|RedirectResponse
    {
        $admin = Auth::user();

        if (!$admin->hasAnyRole(['admin', 'superadmin', 'super_admin'])) {
            abort(403, 'Unauthorized to edit DOOH hoardings.');
        }

        $step = (int) $request->query('step', 1);
        $step = max(1, min(3, $step));

        $screen = DOOHScreen::with([
            'hoarding',
            'hoarding.brandLogos',
            'media',
        ])
            ->whereHas('hoarding', function ($q) use ($admin) {
                $q->where('vendor_id', $admin->id);
            })
            ->findOrFail($id);

        $hoarding = $screen->hoarding;

        if ($hoarding->hoarding_type !== 'dooh') {
            return redirect()->route('admin.hoardings.create', [
                'ooh_id' => $hoarding->oohHoarding?->id,
                'step' => $step,
            ])->with('info', 'Redirected to OOH edit page.');
        }

        $attributes = \Modules\Hoardings\Models\HoardingAttribute::groupedByType();

        return view('dooh.admin.edit', [
            'step' => $step,
            'screen' => $screen,
            'hoarding' => $hoarding,
            'attributes' => $attributes,
        ]);
    }

    /**
     * Update DOOH Screen (Multi-step) for admin.
     */
    public function update(Request $request, $id, \Modules\DOOH\Services\DOOHScreenService $service): RedirectResponse
    {
        $admin = Auth::user();

        if (!$admin->hasAnyRole(['admin', 'superadmin', 'super_admin'])) {
            abort(403, 'Unauthorized to update DOOH hoardings.');
        }

        $step = (int) $request->input('step', 1);
        $step = max(1, min(3, $step));

        $screen = DOOHScreen::whereHas('hoarding', function ($q) use ($admin) {
            $q->where('vendor_id', $admin->id);
        })->findOrFail($id);

        $hoarding = $screen->hoarding;

        if ($hoarding->hoarding_type !== 'dooh') {
            return redirect()->route('admin.hoardings.create', [
                'ooh_id' => $hoarding->oohHoarding?->id,
                'step' => 1,
            ])->with('error', 'This is an OOH hoarding. Please use OOH edit.');
        }

        try {
            switch ($step) {
                case 1:
                    $result = $service->updateStep1($screen, $request->all(), $request->file('media', []));
                    if (!($result['success'] ?? false)) {
                        return back()->withErrors($result['errors'] ?? [])->withInput();
                    }

                    return redirect()->route('admin.dooh.edit', ['id' => $id, 'step' => 2])
                        ->with('success', 'Step 1 saved! Continue to Step 2.');

                case 2:
                    $result = $service->storeStep2($screen, $request->all(), $request->file('brand_logos', []));
                    if (!($result['success'] ?? true)) {
                        return back()->withErrors($result['errors'] ?? [])->withInput();
                    }

                    return redirect()->route('admin.dooh.edit', ['id' => $id, 'step' => 3])
                        ->with('success', 'Step 2 saved! Continue to Step 3.');

                case 3:
                    $result = $service->updateStep3($screen, $request->all());
                    if (!($result['success'] ?? false)) {
                        return back()->withErrors($result['errors'] ?? [])->withInput();
                    }

                    return redirect()->route('admin.my-hoardings')
                        ->with('success', 'DOOH screen updated successfully.');

                default:
                    return back()->withErrors(['step' => 'Invalid step number.']);
            }
        } catch (\Exception $e) {
            \Log::error('Admin DOOH Update Failed', [
                'step' => $step,
                'screen_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['message' => 'Update failed: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
