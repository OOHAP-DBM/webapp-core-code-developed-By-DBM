<?php

namespace Modules\Search\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use App\Models\User;
use Modules\Cart\Services\CartService;

class VendorPublicController extends Controller
{
    public function show($vendor, CartService $cartService)
    {
        $vendorData = User::where('id', $vendor)
            ->where('active_role', 'vendor')
            ->whereHas('vendorProfile', function ($q) {
                $q->where('onboarding_status', 'approved')
                ->whereNull('deleted_at');
            })
            ->with('vendorProfile')
            ->firstOrFail();
        $query = Hoarding::query()
        ->leftJoin('dooh_screens', 'dooh_screens.hoarding_id', '=', 'hoardings.id')
        ->where('hoardings.vendor_id', $vendor)
        ->where('hoardings.status', 'active')
        ->select('hoardings.*')
        ->with([
            'vendor',
            'hoardingMedia',
            'doohScreen.media',
            'doohScreen.packages',
            'oohPackages'
        ]);

        $sort = request('sort');
        if ($sort === 'low_high') {
            $query->orderByRaw("
                CASE
                    /* DOOH */
                    WHEN hoardings.hoarding_type = 'dooh'
                        THEN COALESCE(dooh_screens.price_per_slot, 0)

                    /* OOH (monthly_price agar hai) */
                    WHEN hoardings.monthly_price IS NOT NULL
                        AND hoardings.monthly_price > 0
                        THEN hoardings.monthly_price

                    /* OOH fallback */
                    ELSE COALESCE(hoardings.base_monthly_price, 0)
                END ASC
            ");
        }
        elseif ($sort === 'high_low') {
            $query->orderByRaw("
                CASE
                    WHEN hoardings.hoarding_type = 'dooh'
                        THEN COALESCE(dooh_screens.price_per_slot, 0)

                    WHEN hoardings.monthly_price IS NOT NULL
                        AND hoardings.monthly_price > 0
                        THEN hoardings.monthly_price

                    ELSE COALESCE(hoardings.base_monthly_price, 0)
                END DESC
            ");
        }
        elseif ($sort === 'latest') {
            $query->orderBy('hoardings.created_at', 'desc');
        }
        else {
            $query->orderByDesc('hoardings.is_featured')
                ->orderByDesc('hoardings.created_at');
        }
        $hoardings = $query->paginate(8)->withQueryString();
        $hoardings->getCollection()->transform(function ($hoarding) {

            if ($hoarding->hoarding_type === 'dooh') {

                $hoarding->price_type = 'dooh';
                $hoarding->base_price_for_enquiry =
                    (float) optional($hoarding->doohScreen)->price_per_slot;

            } else {

                $hoarding->price_type = 'ooh';
                $hoarding->base_price_for_enquiry =
                    (float) ($hoarding->monthly_price ?? 0);

                $hoarding->monthly_price_display = $hoarding->monthly_price;
                $hoarding->base_monthly_price_display = $hoarding->base_monthly_price;
            }

            $hoarding->grace_period_days = (int) ($hoarding->grace_period_days ?? 0);

            return $hoarding;
        });
        $cartIds = auth()->check()
            ? $cartService->getCartHoardingIds()
            : [];
        return view('search.vendor-profile', [
            'vendor'   => $vendorData,
            'hoardings'=> $hoardings,
            'cartIds'  => $cartIds
        ]);
    }
}
