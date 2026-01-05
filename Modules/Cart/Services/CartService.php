<?php

namespace Modules\Cart\Services;

use App\Models\Hoarding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * =========================
     * ADD TO CART (PACKAGE BASED)
     * =========================
     */
    public function add(
        int $hoardingId,
        ?int $packageId = null,
        ?string $packageSource = null // ooh_package | dooh_package | slot
    ): array {
        if (!Auth::check()) {
            return [
                'status'  => 'login_required',
                'message' => 'Please login to add item to cart'
            ];
        }

        $hoarding = Hoarding::where('status', 'active')->findOrFail($hoardingId);

        // One hoarding only once in cart
        if ($this->exists($hoardingId)) {
            return [
                'status'  => 'exists',
                'message' => 'Item already in cart'
            ];
        }

        // 🔥 FINAL PRICE RESOLUTION
        $priceData = $this->resolveFinalPrice($hoarding, $packageId, $packageSource);

        DB::table('carts')->insert([
            'user_id'      => Auth::id(),
            'hoarding_id'  => $hoardingId,
            'package_type' => $priceData['package_type'],
            'price'        => $priceData['final_price'],
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return [
            'status'      => 'added',
            'final_price' => $priceData['final_price']
        ];
    }

    /**
     * =========================
     * FINAL PRICE DECIDER (CORE)
     * =========================
     */
    private function resolveFinalPrice(
        Hoarding $hoarding,
        ?int $packageId,
        ?string $source
    ): array {

        /* ---------------- OOH ---------------- */
        if ($hoarding->hoarding_type === 'ooh') {

            // Package based (figma cards)
            if ($source === 'ooh_package' && $packageId) {

                $pkg = DB::table('hoarding_packages')
                    ->where('id', $packageId)
                    ->where('hoarding_id', $hoarding->id)
                    ->where('is_active', 1)
                    ->first();

                if (!$pkg) {
                    throw new \Exception('OOH package not found');
                }

                $discount = ($pkg->base_price_per_month * $pkg->discount_percent) / 100;
                $final = $pkg->base_price_per_month - $discount;

                return [
                    'package_type' => 'monthly',
                    'final_price' => round($final, 2),
                ];
            }

            // Fallback (no package)
            return [
                'package_type' => 'monthly',
                'final_price' => (float) $hoarding->monthly_price,
            ];
        }

        /* ---------------- DOOH ---------------- */
        if ($hoarding->hoarding_type === 'dooh') {

            $screen = DB::table('dooh_screens')
                ->where('hoarding_id', $hoarding->id)
                ->whereNull('deleted_at')
                ->first();

            if (!$screen) {
                throw new \Exception('DOOH screen not found');
            }

            // DOOH package
            if ($source === 'dooh_package' && $packageId) {

                $pkg = DB::table('dooh_packages')
                    ->where('id', $packageId)
                    ->where('dooh_screen_id', $screen->id)
                    ->where('is_active', 1)
                    ->first();

                if (!$pkg) {
                    throw new \Exception('DOOH package not found');
                }

                $discount = ($pkg->price_per_month * $pkg->discount_percent) / 100;
                $final = $pkg->price_per_month - $discount;

                return [
                    'package_type' => 'package',
                    'final_price' => round($final, 2),
                ];
            }

            // Slot based fallback
            return [
                'package_type' => 'slot',
                'final_price' => (float) ($screen->display_price_per_30s ?: $screen->price_per_slot),
            ];
        }

        throw new \Exception('Invalid hoarding type');
    }

    /**
     * =========================
     * REMOVE
     * =========================
     */
    public function remove(int $hoardingId): void
    {
        DB::table('carts')
            ->where('user_id', Auth::id())
            ->where('hoarding_id', $hoardingId)
            ->delete();
    }

    /**
     * =========================
     * UI STATE HELPERS
     * =========================
     */
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

    /**
     * =========================
     * CART PAGE DATA
     * =========================
     */
    public function getUserCart()
    {
        return DB::table('carts')
            ->join('hoardings', 'hoardings.id', '=', 'carts.hoarding_id')
            ->leftJoin('dooh_screens', 'dooh_screens.hoarding_id', '=', 'hoardings.id')
            ->where('carts.user_id', Auth::id())
            ->select([
                'carts.id as cart_id',
                'carts.price',
                'carts.package_type',

                'hoardings.id as hoarding_id',
                'hoardings.title',
                'hoardings.city',
                'hoardings.state',
                'hoardings.hoarding_type',

                'dooh_screens.slot_duration_seconds',
            ])
            ->get();
    }

    public function getCartSummary()
    {
        return DB::table('carts')
            ->where('user_id', Auth::id())
            ->selectRaw('SUM(price) as subtotal')
            ->first();
    }
    public function getPackagesForUI(Hoarding $hoarding): array
{
    // ---------- OOH ----------
    if ($hoarding->hoarding_type === 'ooh') {

        return DB::table('hoarding_packages')
            ->where('hoarding_id', $hoarding->id)
            ->where('is_active', 1)
            ->get()
            ->map(function ($p) {
                $discount = ($p->base_price_per_month * $p->discount_percent) / 100;
                return [
                    'id' => $p->id,
                    'title' => $p->package_name,
                    'strike_price' => $p->base_price_per_month,
                    'final_price' => $p->base_price_per_month - $discount,
                    'save' => $discount,
                    'services' => $p->services_included,
                ];
            })
            ->toArray();
    }

    // ---------- DOOH ----------
    $screen = DB::table('dooh_screens')->where('hoarding_id', $hoarding->id)->first();

    return DB::table('dooh_packages')
        ->where('dooh_screen_id', $screen->id)
        ->where('is_active', 1)
        ->get()
        ->map(function ($p) {
            $discount = ($p->price_per_month * $p->discount_percent) / 100;
            return [
                'id' => $p->id,
                'title' => $p->package_name,
                'final_price' => $p->price_per_month - $discount,
                'save' => $discount,
            ];
        })
        ->toArray();
}

}
