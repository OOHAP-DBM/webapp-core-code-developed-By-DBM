<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Modules\Hoardings\Services\HoardingService;
use App\Models\Hoarding;

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
            abort(404);
        }

        // default empty
        $hoarding->packages = collect();

        /*
        |--------------------------------------------------------------------------
        | DOOH
        |--------------------------------------------------------------------------
        */
        if ($hoarding->hoarding_type === Hoarding::TYPE_DOOH) {

            $screen = $hoarding->doohScreen;

            $hoarding->price_type = 'dooh';
            $hoarding->price_unit = 'Slot';

            // Always show base DOOH price
            $hoarding->price_per_slot = $screen?->price_per_slot;

            // Packages optional
            if ($screen) {
                $hoarding->packages = $screen->packages()
                    ->active()
                    ->orderBy('min_booking_months')
                    ->get();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | OOH
        |--------------------------------------------------------------------------
        */
        else {

            $hoarding->price_type = 'ooh';
            $hoarding->price_unit = 'Month';

            // Always show prices
            $hoarding->monthly_price = $hoarding->monthly_price;
            $hoarding->base_monthly_price = $hoarding->base_monthly_price;

            // Packages optional
            $hoarding->packages = $hoarding->oohPackages()
                ->active()
                ->valid()
                ->orderBy('min_booking_duration')
                ->get();
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
