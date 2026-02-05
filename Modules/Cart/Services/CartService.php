<?php

namespace Modules\Cart\Services;

use App\Models\Hoarding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Wishlist;

class CartService
 
{
    /* =====================================================
     | ADD TO CART
     ===================================================== */
   public function add(
    int $hoardingId,
    ?int $packageId = null,
    ?string $packageSource = null
): array {

    if (!Auth::check()) {
        return $this->response('login_required', false, 'Please login to add item to cart');
    }

    $hoarding = Hoarding::where('status', 'active')->findOrFail($hoardingId);

    // 🔥 FORCE ADD (idempotent behaviour optional)
    DB::table('carts')->updateOrInsert(
        [
            'user_id'     => Auth::id(),
            'hoarding_id' => $hoardingId,
        ],
        [
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
    DB::table('wishlists')
        ->where('user_id', Auth::id())
        ->where('hoarding_id', $hoardingId)
        ->delete();
    $priceData = $this->resolveFinalPrice($hoarding, $packageId, $packageSource);

    return $this->response(
        'added',
        true,
        'Added to cart',
        ['final_price' => $priceData['final_price']]
    );
}


    /* =====================================================
     | REMOVE FROM CART
     ===================================================== */
    public function remove(int $hoardingId): array
    {
        DB::table('carts')
            ->where('user_id', Auth::id())
            ->where('hoarding_id', $hoardingId)
            ->delete();

        return $this->response('removed', false, 'Item removed from cart');
    }

    /* =====================================================
     | PRICE RESOLVER (CORE)
     ===================================================== */
    private function resolveFinalPrice(
        Hoarding $hoarding,
        ?int $packageId,
        ?string $source
    ): array {

        return match ($hoarding->hoarding_type) {
            'ooh'  => $this->resolveOOHPrice($hoarding, $packageId, $source),
            'dooh' => $this->resolveDOOHPrice($hoarding, $packageId, $source),
            default => throw new \Exception('Invalid hoarding type'),
        };
    }

    private function resolveOOHPrice(Hoarding $hoarding,?int $packageId,?string $source): array 
    {

            if (is_null($hoarding->base_monthly_price)) {
                throw new \Exception('Base monthly price not set for hoarding');
            }

            $basePrice = (float) $hoarding->base_monthly_price;

            // ================= PACKAGE CASE =================
            if ($source === 'ooh_package' && $packageId) {

                $pkg = DB::table('hoarding_packages')
                    ->where('id', $packageId)
                    ->where('hoarding_id', $hoarding->id)
                    ->where('is_active', 1)
                    ->first();

                if (!$pkg) {
                    throw new \Exception('OOH package not found');
                }

                $final = $basePrice
                    - ($basePrice * ($pkg->discount_percent ?? 0) / 100);

                return $this->priceResponse('monthly', $final);
            }

            // ================= NO PACKAGE =================
            if (!is_null($hoarding->monthly_price)) {
                return $this->priceResponse(
                    'monthly',
                    (float) $hoarding->monthly_price
                );
            }

       return $this->priceResponse('monthly', $basePrice);
    }


    /* ================= DOOH ================= */
    private function resolveDOOHPrice(Hoarding $hoarding, ?int $packageId, ?string $source): array
    {
        $screen = DB::table('dooh_screens')
            ->where('hoarding_id', $hoarding->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$screen) {
            throw new \Exception('DOOH screen not found');
        }

        if ($source === 'dooh_package' && $packageId) {

            $pkg = DB::table('dooh_packages')
                ->where('id', $packageId)
                ->where('dooh_screen_id', $screen->id)
                ->where('is_active', 1)
                ->first();

            if (!$pkg) {
                throw new \Exception('DOOH package not found');
            }

            $final = $pkg->price_per_month
                - ($pkg->price_per_month * $pkg->discount_percent / 100);

            return $this->priceResponse('package', $final);
        }

        return $this->priceResponse(
            'slot',
            $screen->display_price_per_30s ?: $screen->price_per_slot
        );
    }

    /* =====================================================
     | CART HELPERS
     ===================================================== */
    public function exists(int $hoardingId): bool
    {
        return DB::table('carts')
            ->where('user_id', Auth::id())
            ->where('hoarding_id', $hoardingId)
            ->exists();
    }

    public function getCartHoardingIds(): array
    {
        return DB::table('carts')
            ->where('user_id', Auth::id())
            ->pluck('hoarding_id')
            ->toArray();
    }

    /* =====================================================
     | CART PAGE DATA (UI)
     ===================================================== */
    public function getCartForUI()
    {
        
        $items = DB::table('carts')
            ->join('hoardings', 'hoardings.id', '=', 'carts.hoarding_id')
            ->where('carts.user_id', Auth::id())
            ->select(
                'carts.id as cart_id',
                'carts.package_id', 
                'hoardings.id as hoarding_id',
                'hoardings.title',
                'hoardings.city',
                'hoardings.state',
                'hoardings.locality',
                'hoardings.category',
                'hoardings.hoarding_type',
                'hoardings.monthly_price',
                'hoardings.base_monthly_price',
                'hoardings.grace_period_days',

            )
            ->get();

        return $items->map(fn ($item) => $this->buildCartItem($item));
    }


    public function getCartSummary()
    {
        return (object) [
            'subtotal' => null
        ];
    }


    /* =====================================================
     | INTERNAL HELPERS
     ===================================================== */
    private function attachPackages($item)
    {
        if ($item->hoarding_type === 'ooh') {
            $item->packages = DB::table('hoarding_packages')
                ->where('hoarding_id', $item->hoarding_id)
                ->where('is_active', 1)
                ->get(['id', 'package_name', 'discount_percent', 'min_booking_duration', 'duration_unit', 'services_included', ]);
        }

        if ($item->hoarding_type === 'dooh') {
            $screen = DB::table('dooh_screens')
                ->where('hoarding_id', $item->hoarding_id)
                ->whereNull('deleted_at')
                ->first();

            $item->packages = $screen
                ? DB::table('dooh_packages')
                    ->where('dooh_screen_id', $screen->id)
                    ->where('is_active', 1)
                    ->whereNull('deleted_at')
                    ->get(['id', 'package_name', 'discount_percent', 'min_booking_duration', 'duration_unit', 'services_included', ])
                : collect();
        }


        return $item;
    }

    private function priceResponse(string $type, float $price): array
    {
        return [
            'package_type' => $type,
            'final_price'  => round($price, 2),
        ];
    }

    private function response(
        string $status,
        bool $inCart,
        string $message = '',
        array $extra = []
    ): array {
        return array_merge([
            'status'  => $status,
            'in_cart' => $inCart,
            'message' => $message,
        ], $extra);
    }
    private function buildCartItem($item)
    {
         $item->image_url = asset('assets/images/placeholder.jpg');

        // ---------- OOH IMAGE ----------
        if ($item->hoarding_type === 'ooh') {

            $media = DB::table('hoarding_media')
                ->where('hoarding_id', $item->hoarding_id)
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->first();

            if ($media && !empty($media->file_path)) {
                $item->image_url = asset('storage/' . ltrim($media->file_path, '/'));
            }
        }

        // ---------- DOOH IMAGE ----------
        if ($item->hoarding_type === 'dooh') {

            $screen = DB::table('dooh_screens')
                ->where('hoarding_id', $item->hoarding_id)
                ->whereNull('deleted_at')
                ->first();

            if ($screen) {
                $media = DB::table('dooh_screen_media')
                    ->where('dooh_screen_id', $screen->id)
                    ->orderBy('sort_order')
                    ->first();

                if ($media && !empty($media->file_path)) {
                    $item->image_url = asset('storage/' . ltrim($media->file_path, '/'));
                }
            }
        }
        /* =====================================================
        SIZE
        ===================================================== */
        $item->size = 'N/A';

        if ($item->hoarding_type === 'ooh') {
            $ooh = DB::table('ooh_hoardings')
                ->where('hoarding_id', $item->hoarding_id)
                ->whereNull('deleted_at')
                ->first();

            if ($ooh && $ooh->width && $ooh->height) {
                $item->size =
                    $ooh->width . '×' .
                    $ooh->height . ' ' .
                    ($ooh->measurement_unit ?? '');
            }
        }

        if ($item->hoarding_type === 'dooh') {
            $screen = DB::table('dooh_screens')
                ->where('hoarding_id', $item->hoarding_id)
                ->whereNull('deleted_at')
                ->first();

            if ($screen && $screen->width && $screen->height) {
                $item->size =
                    $screen->width . '×' .
                    $screen->height . ' ' .
                    ($screen->measurement_unit ?? '');
            }else{
                $item->size = $screen->resolution_width . '×' .
                $screen->resolution_height . ' px';
            }
        }

        /* =====================================================
        PACKAGES (FOR DROPDOWN)
        ===================================================== */
        $this->attachPackages($item);
        /* =====================================================
        SELECTED PACKAGE (ONLY FROM CART)
        ===================================================== */
        $item->selected_package = null;

        if ($item->package_id && $item->packages->count()) {
            $item->selected_package = $item->packages
                ->firstWhere('id', $item->package_id);
        }

        /* =====================================================
        PRICE LOGIC (OOH)
        ===================================================== */
        if ($item->hoarding_type === 'ooh') {

            $item->base_monthly_price = round((float) ($item->base_monthly_price ?? 0), 2);
            $item->monthly_price      = round((float) ($item->monthly_price ?? 0), 2);

            // default = monthly
            $item->discounted_monthly_price = $item->base_monthly_price;
            $item->discount_amount = 0;

            if ($item->selected_package) {

                $discountPercent = (float) ($item->selected_package->discount_percent ?? 0);

                if ($discountPercent > 0 && $item->base_monthly_price > 0) {
                    $item->discount_amount = round(
                        ($item->base_monthly_price * $discountPercent) / 100,
                        2
                    );

                    $item->discounted_monthly_price = round(
                        $item->base_monthly_price - $item->discount_amount,
                        2
                    );
                }
            }

            $item->final_price = $item->discounted_monthly_price;

            $item->package_details = $item->selected_package
                ? (object) [
                    'id'                   => $item->selected_package->id,
                    'package_name'         => $item->selected_package->package_name,
                    'discount_percent'     => (float) $item->selected_package->discount_percent,
                    'min_booking_duration' => (int) $item->selected_package->min_booking_duration,
                    'duration_unit'        => $item->selected_package->duration_unit,
                    'services_included'    => is_string($item->selected_package->services_included)
                        ? json_decode($item->selected_package->services_included, true)
                        : ($item->selected_package->services_included ?? []),
                ]
                : null;
        }
        

        /* =====================================================
        PRICE LOGIC (DOOH)
        ===================================================== */
        if ($item->hoarding_type === 'dooh') {

            $screen = DB::table('dooh_screens')
                ->where('hoarding_id', $item->hoarding_id)
                ->whereNull('deleted_at')
                ->first();

            $slotPrice = round(
                (float) (
                    $screen
                        ? ($screen->display_price_per_30s ?: $screen->price_per_slot)
                        : 0
                ),
                2
            );

            // Set base price same as slot price for package calculations
            $item->slot_price = $slotPrice;
            $item->base_monthly_price = $slotPrice;
            $item->monthly_price = $slotPrice;
            $item->final_price = $slotPrice;

            // Handle selected package with discount
            $item->discounted_monthly_price = null;
            $item->discount_amount = 0;

            if ($item->selected_package) {
                $discountPercent = (float) ($item->selected_package->discount_percent ?? 0);
                if ($discountPercent > 0 && $slotPrice > 0) {
                    $item->discount_amount = round(
                        ($slotPrice * $discountPercent) / 100,
                        2
                    );
                    $item->final_price = round($slotPrice - $item->discount_amount, 2);
                }
            }

            $item->package_details = null;
        }

        return $item;
    }
    /**
     * Calculate discounted price for a package
     * @param float $baseMonthlyPrice
     * @param float $discountPercent
     * @return float
     */
    public static function calculateDiscountedPrice(float $baseMonthlyPrice, float $discountPercent): float
    {
        if ($discountPercent > 0) {
            $discount = ($baseMonthlyPrice * $discountPercent) / 100;
            return round($baseMonthlyPrice - $discount, 2);
        }
        return round($baseMonthlyPrice, 2);
    }

}
