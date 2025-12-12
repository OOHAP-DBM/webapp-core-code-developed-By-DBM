<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Modules\DOOH\Models\DOOHScreen;
use Modules\DOOH\Services\DOOHPackageBookingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DOOHController extends Controller
{
    protected DOOHPackageBookingService $doohService;

    public function __construct(DOOHPackageBookingService $doohService)
    {
        $this->doohService = $doohService;
    }

    /**
     * Display DOOH screens listing
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $filters = [
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'search' => $request->input('search'),
            'min_slots' => $request->input('min_slots'),
            'per_page' => $request->input('per_page', 15),
        ];

        $screens = $this->doohService->getAvailableScreens($filters);

        // Get filter options
        $cities = DOOHScreen::active()
            ->distinct()
            ->pluck('city')
            ->filter()
            ->sort()
            ->values();

        $states = DOOHScreen::active()
            ->distinct()
            ->pluck('state')
            ->filter()
            ->sort()
            ->values();

        return view('dooh.index', compact('screens', 'cities', 'states'));
    }

    /**
     * Display single DOOH screen details
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $screen = $this->doohService->getScreenDetails($id);

        if (!$screen || $screen->status !== 'active') {
            abort(404, 'DOOH Screen not found or not available.');
        }

        return view('dooh.show', compact('screen'));
    }
}
