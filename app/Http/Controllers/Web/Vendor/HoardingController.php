<?php

namespace App\Http\Controllers\Web\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use Illuminate\Http\Request;
use Modules\Hoardings\Services\HoardingService;

class HoardingController extends Controller
{
    /**
     * @var HoardingService
     */
    protected $hoardingService;

    /**
     * HoardingController constructor.
     *
     * @param HoardingService $hoardingService
     */
    public function __construct(HoardingService $hoardingService)
    {
        $this->hoardingService = $hoardingService;
    }

    /**
     * Display a listing of vendor's hoardings.
     */
    public function index(Request $request)
    {
        $filters = [
            'vendor_id' => auth()->id(),
            'status' => $request->input('status'),
            'type' => $request->input('type'),
            'search' => $request->input('search'),
            'include_all' => true, // Show all statuses for vendor
        ];

        $hoardings = $this->hoardingService->getAll($filters, 12);
        $statistics = $this->hoardingService->getVendorStatistics(auth()->id());

        return view('vendor.hoardings.index', compact('hoardings', 'statistics'));
    }

    /**
     * Show the form for creating a new hoarding.
     */
    public function create()
    {
        $types = $this->hoardingService->getTypes();
        $statuses = $this->hoardingService->getStatuses();

        return view('vendor.hoardings.create', compact('types', 'statuses'));
    }

    /**
     * Store a newly created hoarding.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'weekly_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'required|numeric|min:0',
            'enable_weekly_booking' => 'boolean',
            'type' => 'required|in:billboard,digital,transit,street_furniture,wallscape,mobile',
            'status' => 'nullable|in:draft,pending_approval,active,inactive',
        ]);

        try {
            $validated['vendor_id'] = auth()->id();
            $validated['enable_weekly_booking'] = $request->has('enable_weekly_booking');

            $hoarding = $this->hoardingService->create($validated);

            // Clear statistics cache
            $this->hoardingService->clearVendorStatistics(auth()->id());

            return redirect()->route('vendor.hoardings.index')
                ->with('success', 'Hoarding created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified hoarding.
     */
    public function show(int $id)
    {
        $hoarding = $this->hoardingService->getById($id);

        if (!$hoarding || $hoarding->vendor_id !== auth()->id()) {
            abort(404);
        }

        return view('vendor.hoardings.show', compact('hoarding'));
    }

    /**
     * Show the form for editing the specified hoarding.
     */
    public function edit(int $id)
    {
        $hoarding = $this->hoardingService->getById($id);

        if (!$hoarding || $hoarding->vendor_id !== auth()->id()) {
            abort(404);
        }

        $types = $this->hoardingService->getTypes();
        $statuses = $this->hoardingService->getStatuses();

        return view('vendor.hoardings.edit', compact('hoarding', 'types', 'statuses'));
    }

    /**
     * Update the specified hoarding.
     */
    public function update(Request $request, int $id)
    {
        $hoarding = $this->hoardingService->getById($id);

        if (!$hoarding || $hoarding->vendor_id !== auth()->id()) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'weekly_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'required|numeric|min:0',
            'enable_weekly_booking' => 'boolean',
            'type' => 'required|in:billboard,digital,transit,street_furniture,wallscape,mobile',
            'status' => 'nullable|in:draft,pending_approval,active,inactive',
        ]);

        try {
            $validated['enable_weekly_booking'] = $request->has('enable_weekly_booking');

            $this->hoardingService->update($id, $validated);

            // Clear statistics cache
            $this->hoardingService->clearVendorStatistics(auth()->id());

            return redirect()->route('vendor.hoardings.index')
                ->with('success', 'Hoarding updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified hoarding.
     */
    public function destroy(int $id)
    {
        $hoarding = $this->hoardingService->getById($id);

        if (!$hoarding || $hoarding->vendor_id !== auth()->id()) {
            abort(404);
        }

        $this->hoardingService->delete($id);

        // Clear statistics cache
        $this->hoardingService->clearVendorStatistics(auth()->id());

        return redirect()->route('vendor.hoardings.index')
            ->with('success', 'Hoarding deleted successfully.');
    }
}
