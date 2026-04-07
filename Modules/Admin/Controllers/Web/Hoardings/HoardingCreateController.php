<?php

namespace Modules\Admin\Controllers\Web\Hoardings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Modules\Hoardings\Models\OOHHoarding;
use Modules\Hoardings\Services\HoardingListService;
use App\Models\Hoarding;
use Illuminate\Http\RedirectResponse;

/**
 * Admin Hoarding Create Controller
 * 
 * Allows admins to create hoardings using the same multi-step wizard as vendors
 * Reuses vendor OOH and DOOH controllers for hoarding creation
 * 
 * Routes:
 *   GET  /admin/hoardings/add              - Show type selection (OOH/DOOH)
 *   POST /admin/hoardings/select-type      - Handle type selection
 *   GET  /admin/hoardings/create           - Show multi-step creation wizard
 *   POST /admin/hoardings/store            - Store hoarding data
 *   GET  /admin/hoardings/{id}/edit        - Edit existing hoarding
 *   POST /admin/hoardings/{id}/update      - Update hoarding
 */
class HoardingCreateController extends Controller
{
    protected HoardingListService $hoardingService;

    public function __construct(HoardingListService $hoardingService)
    {
        $this->hoardingService = $hoardingService;
    }

    /**
     * Show hoarding type selection screen (OOH/DOOH)
     * GET /admin/hoardings/add
     */
    public function showTypeSelection(Request $request): View
    {
        $admin = Auth::user();
        
        // Verify admin has permission
        if (!$admin->hasAnyRole(['admin', 'superadmin', 'super_admin'])) {
            abort(403, 'Unauthorized to create hoardings.');
        }

        return view('hoardings.admin.add_type_selection', [
            'sidebarActive' => 'add-hoardings',
        ]);
    }

    /**
     * Handle hoarding type selection (OOH/DOOH)
     * POST /admin/hoardings/select-type
     */
    public function handleTypeSelection(Request $request): RedirectResponse
    {
        $admin = Auth::user();
        
        // Verify admin has permission
        if (!$admin->hasAnyRole(['admin', 'superadmin', 'super_admin'])) {
            abort(403, 'Unauthorized to create hoardings.');
        }

        $type = $request->input('hoarding_type');

        if ($type === 'DOOH') {
            // DOOH: Continue DOOH flow in admin context
            session(['hoarding_type' => 'DOOH', 'is_admin_adding' => true]);
            return Redirect::route('admin.dooh.create');
        }

        if ($type === 'OOH') {
            // OOH: Continue OOH flow in admin context
            session(['hoarding_type' => 'OOH', 'is_admin_adding' => true]);
            return Redirect::route('admin.hoardings.create');
        }

        // Invalid type: redirect back
        return Redirect::back()->with('error', 'Please select a valid hoarding type.');
    }

    /**
     * Show multi-step OOH hoarding creation form
     * GET /admin/hoardings/create?step=1&ooh_id=123
     */
    public function create(Request $request): View
    {
        $admin = Auth::user();
        
        // Verify admin has permission
        if (!$admin->hasAnyRole(['admin', 'superadmin', 'super_admin'])) {
            abort(403, 'Unauthorized to create hoardings.');
        }

        $step   = (int) $request->query('step', 1);
        $step   = max(1, min(3, $step));
        $oohId  = $request->query('ooh_id');
        $draft  = null;

        // Load draft hoarding if ooh_id is provided
        if ($oohId) {
            $draft = OOHHoarding::with('hoarding.media')
                ->where('id', $oohId)
                ->whereHas('hoarding', function ($q) use ($admin) {
                    // Admin can only edit hoardings they created
                    $q->where('vendor_id', $admin->id);
                })
                ->first();
        }

        // Get hoarding attributes for form
        $attributes = \Modules\Hoardings\Models\HoardingAttribute::groupedByType();

        return view('hoardings.admin.create', compact('step', 'draft', 'attributes'));
    }

    /**
     * Store hoarding data and move to next step
     * POST /admin/hoardings/store
     */
    public function store(Request $request)
    {
        $admin = Auth::user();
        
        // Verify admin has permission
        if (!$admin->hasAnyRole(['admin', 'superadmin', 'super_admin'])) {
            abort(403, 'Unauthorized to create hoardings.');
        }

        $step   = (int) $request->input('step', 1);
        $oohId  = $request->input('ooh_id');

        // Handle previous button
        if ($request->input('go_back') === '1') {
            $prevStep = max(1, $step - 1);
            return Redirect::route('admin.hoardings.create', [
                'step'   => $prevStep,
                'ooh_id' => $oohId ?: null,
            ]);
        }

        try {
            switch ($step) {
                case 1:
                    return $this->storeStep1($request, $admin, $oohId);

                case 2:
                    return $this->storeStep2($request, $admin, $oohId);

                case 3:
                    return $this->storeStep3($request, $admin, $oohId);

                default:
                    return Redirect::back()->withErrors(['message' => 'Invalid step']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Redirect::back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Admin hoarding store error', [
                'admin_id' => $admin->id,
                'step'     => $step,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            return Redirect::back()->withErrors(['message' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Store Step 1 data
     */
    protected function storeStep1(Request $request, $admin, $oohId)
    {
        $step1Request = app(\Modules\Hoardings\Http\Requests\StoreOOHHoardingStep1Request::class);
        $validated    = $step1Request->validated();
        $mediaFiles   = $step1Request->file('media', []);

        // If ooh_id exists → update existing draft
        if ($oohId) {
            $existing = OOHHoarding::where('id', $oohId)
                ->whereHas('hoarding', fn($q) => $q->where('vendor_id', $admin->id))
                ->first();

            if ($existing) {
                $this->hoardingService->updateStep1(
                    $existing->hoarding,
                    $existing,
                    $validated,
                    $mediaFiles
                );
                return Redirect::route('admin.hoardings.create', ['step' => 2, 'ooh_id' => $existing->id])
                    ->with('success', 'Step 1 updated.');
            }
        }

        // Fresh create: use admin as vendor
        $result      = $this->hoardingService->storeStep1($admin, $validated, $mediaFiles);
        $hoarding    = $result['hoarding'] ?? null;
        $oohHoarding = $hoarding ? OOHHoarding::where('hoarding_id', $hoarding->id)->first() : null;
        $oohId       = $oohHoarding?->id;

        return Redirect::route('admin.hoardings.create', ['step' => 2, 'ooh_id' => $oohId])
            ->with('success', 'Step 1 completed.');
    }

    /**
     * Store Step 2 data
     */
    protected function storeStep2(Request $request, $admin, $oohId)
    {
        $screen = OOHHoarding::where('id', $oohId)
            ->whereHas('hoarding', fn($q) => $q->where('vendor_id', $admin->id))
            ->firstOrFail();

        $parentHoarding = $screen->hoarding;

        // Check brand logos limit
        $deletedIds     = array_filter(array_map('intval', explode(',', $request->input('delete_brand_logos', ''))));
        $existingCount  = $parentHoarding->brandLogos()->count() - count($deletedIds);
        $newCount       = count($request->file('brand_logos', []));

        if ($existingCount + $newCount > 10) {
            return Redirect::back()
                ->withErrors(['brand_logos' => "Maximum 10 logos allowed. You have {$existingCount} existing and are adding {$newCount} more."])
                ->withInput();
        }

        $this->hoardingService->storeStep2(
            $parentHoarding,
            $request->all(),
            $request->file('brand_logos', [])
        );

        return Redirect::route('admin.hoardings.create', ['step' => 3, 'ooh_id' => $oohId])
            ->with('success', 'Step 2 completed.');
    }

    /**
     * Store Step 3 data
     */
    protected function storeStep3(Request $request, $admin, $oohId)
    {
        $screen = OOHHoarding::where('id', $oohId)
            ->whereHas('hoarding', fn($q) => $q->where('vendor_id', $admin->id))
            ->firstOrFail();

        $this->hoardingService->storeStep3($screen, $request->all());

        $status = $screen->hoarding->status;
        $successMsg = ($status === Hoarding::STATUS_ACTIVE)
            ? 'Hoarding created successfully! It is published.'
            : 'Hoarding created successfully! It is now under review and will be published once approved.';

        return Redirect::route('admin.my-hoardings')
            ->with('success', $successMsg);
    }

    /**
     * Edit existing hoarding (redirects to create with ooh_id)
     */
    public function edit(Request $request, $id)
    {
        $admin = Auth::user();
        
        if (!$admin->hasAnyRole(['admin', 'superadmin', 'super_admin'])) {
            abort(403);
        }

        $hoarding = Hoarding::findOrFail($id);

        // Verify admin ownership
        if ($hoarding->vendor_id !== $admin->id) {
            abort(403, 'You are not authorized to edit this hoarding.');
        }

        // Route to OOH or DOOH edit based on type
        if ($hoarding->hoarding_type === 'ooh') {
            $oohHoarding = $hoarding->oohHoarding;
            return Redirect::route('admin.hoardings.create', [
                'ooh_id' => $oohHoarding->id,
                'step'   => 1,
            ]);
        } elseif ($hoarding->hoarding_type === 'dooh') {
            $doohScreen = $hoarding->doohScreen;
            return Redirect::route('admin.dooh.edit', [
                'id'   => $doohScreen->id,
                'step' => 1,
            ]);
        }

        return Redirect::route('admin.hoardings')
            ->with('error', 'Invalid hoarding type.');
    }
}
