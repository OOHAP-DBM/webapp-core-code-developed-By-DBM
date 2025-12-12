<?php

namespace App\Http\Controllers\Web;

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
     * Display all hoardings listing
     */
    public function index()
    {
        $hoardings = $this->hoardingService->getActiveHoardings([
            'per_page' => 12,
            'with' => ['vendor', 'media']
        ]);

        return view('hoardings.index', compact('hoardings'));
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
