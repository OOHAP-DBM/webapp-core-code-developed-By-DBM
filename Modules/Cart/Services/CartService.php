<?php

namespace Modules\Cart\Services;

use App\Models\Hoarding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    /* ================= OOH ================= */
    private function resolveOOHPrice(Hoarding $hoarding, ?int $packageId, ?string $source): array
    {
        if ($source === 'ooh_package' && $packageId) {

            $pkg = DB::table('hoarding_packages')
                ->where('id', $packageId)
                ->where('hoarding_id', $hoarding->id)
                ->where('is_active', 1)
                ->first();

            if (!$pkg) {
                throw new \Exception('OOH package not found');
            }

            $final = $pkg->base_price_per_month
                - ($pkg->base_price_per_month * $pkg->discount_percent / 100);

            return $this->priceResponse('monthly', $final);
        }

        return $this->priceResponse('monthly', $hoarding->monthly_price);
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
                'hoardings.id as hoarding_id',
                'hoardings.title',
                'hoardings.address',
                'hoardings.city',
                'hoardings.state',
                'hoardings.hoarding_type'
            )
            ->get();

        return $items->map(fn ($item) => $this->attachPackages($item));
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
                ->get();
        }

        if ($item->hoarding_type === 'dooh') {
            $screen = DB::table('dooh_screens')
                ->where('hoarding_id', $item->hoarding_id)
                ->first();

            $item->packages = DB::table('dooh_packages')
                ->where('dooh_screen_id', $screen->id)
                ->where('is_active', 1)
                ->get();
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
}
