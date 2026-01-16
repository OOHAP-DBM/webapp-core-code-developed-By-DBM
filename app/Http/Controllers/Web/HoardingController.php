<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Modules\Hoardings\Services\HoardingService;
use App\Models\Hoarding;
use Illuminate\Support\Facades\DB;

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
        $hoarding = Hoarding::with([
        'hoardingMedia',       
        'doohScreen.media',     
        ])->findOrFail($id);

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
                    ->orderBy('min_booking_duration')
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
     * Get packages by hoarding ID (API endpoint)
     */
    public function getPackages(int $id)
    {
        try {
            $hoarding = Hoarding::findOrFail($id);
            $packages = [];

            if ($hoarding->hoarding_type === 'ooh') {
                $pkgs = DB::table('hoarding_packages')
                    ->where('hoarding_id', $id)
                    ->where('is_active', 1)
                    ->get();

                $packages = $pkgs->map(function($pkg) use ($hoarding) {
                    $basePrice = $hoarding->base_monthly_price;  // Use base_monthly_price instead of monthly_price
                    $discountPercent = (float) ($pkg->discount_percent ?? 0);
                    $finalPrice = $basePrice - ($basePrice * $discountPercent / 100);
                    
                    return [
                        'id' => $pkg->id,
                        'name' => $pkg->package_name,
                        'price' => round($finalPrice, 2),
                        'discount_percent' => $discountPercent,  // Include discount_percent
                        'months' => (int) ($pkg->min_booking_duration ?? 1),
                    ];
                })->toArray();
            } 
            else if ($hoarding->hoarding_type === 'dooh') {
                $screen = DB::table('dooh_screens')
                    ->where('hoarding_id', $id)
                    ->first();

                if ($screen) {
                    $pkgs = DB::table('dooh_packages')
                        ->where('dooh_screen_id', $screen->id)
                        ->where('is_active', 1)
                        ->get();

                    $packages = $pkgs->map(function($pkg) {
                        return [
                            'id' => $pkg->id,
                            'name' => $pkg->package_name,
                            'price' => (int) ($pkg->slots_per_month ?? 0),
                            'discount_percent' => (float) ($pkg->discount_percent ?? 0),  // Include discount_percent
                            'months' => (int) ($pkg->min_booking_duration ?? 1),
                        ];
                    })->toArray();
                }
            }

            return response()->json([
                'success' => true,
                'packages' => $packages,
                'hoarding_type' => $hoarding->hoarding_type,
            ]);

        } catch (\Exception $e) {
            \Log::error('Packages API Error', ['hoarding_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch packages'], 500);
        }
    }

    /**
     * Display map view with all hoardings.
     */
    public function map()
    {
        return view('hoardings.map');
    }
}
