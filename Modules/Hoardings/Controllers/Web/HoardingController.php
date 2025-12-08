<?php

namespace Modules\Hoardings\Controllers\Web;

use App\Http\Controllers\Controller;
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
     * Display the specified hoarding.
     */
    public function show(int $id)
    {
        $hoarding = $this->hoardingService->getById($id);

        if (!$hoarding || !$hoarding->isActive()) {
            abort(404, 'Hoarding not found or not available.');
        }

        return view('hoardings.show', compact('hoarding'));
    }

    /**
     * Display map view with all hoardings.
     */
    public function map()
    {
        return view('hoardings.map');
    }
}
