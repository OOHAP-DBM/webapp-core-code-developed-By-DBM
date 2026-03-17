<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestMergeController extends Controller
{
    /**
     * Login ke baad guest LocalStorage data DB mein merge karo.
     * Wishlist + Cart dono handle karta hai.
     */
    public function merge(Request $request)
    {
        $userId   = auth()->id();
        $wishlist = array_filter(array_map('intval', $request->input('wishlist', [])));
        $cart     = array_filter(array_map('intval', $request->input('cart', [])));

        // Find hoarding IDs owned by this vendor
        $ownedHoardingIds = collect();
        if (auth()->user() && auth()->user()->hasRole('vendor')) {
            $ownedHoardingIds = \App\Models\Hoarding::whereIn('id', array_merge($wishlist, $cart))
                ->where('vendor_id', $userId)
                ->pluck('id');
        }

        $skipped = [
            'wishlist' => [],
            'cart' => [],
        ];

        // ─── Wishlist merge ───────────────────────────────────────
        foreach ($wishlist as $hoardingId) {
            if ($ownedHoardingIds->contains($hoardingId)) {
                $skipped['wishlist'][] = $hoardingId;
                continue;
            }
            $exists = \App\Models\Hoarding::where('id', $hoardingId)
                ->whereNull('deleted_at')
                ->exists();
            if (!$exists) continue;
            Wishlist::firstOrCreate([
                'user_id'     => $userId,
                'hoarding_id' => $hoardingId,
            ]);
        }

        // ─── Cart merge ───────────────────────────────────────────
        foreach ($cart as $hoardingId) {
            if ($ownedHoardingIds->contains($hoardingId)) {
                $skipped['cart'][] = $hoardingId;
                continue;
            }
            $exists = \App\Models\Hoarding::where('id', $hoardingId)
                ->whereNull('deleted_at')
                ->exists();
            if (!$exists) continue;
            $alreadyInCart = DB::table('carts')
                ->where('user_id', $userId)
                ->where('hoarding_id', $hoardingId)
                ->exists();
            if (!$alreadyInCart) {
                DB::table('carts')->insert([
                    'user_id'     => $userId,
                    'hoarding_id' => $hoardingId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // Session flag clear karo — dobara merge na ho
        session()->forget('merge_guest_data');

        $response = ['success' => true];
        if (!empty($skipped['wishlist']) || !empty($skipped['cart'])) {
            $response['skipped_owner_hoardings'] = $skipped;
            $response['message'] = 'Some hoardings you own were not merged into your wishlist/cart.';
        }
        return response()->json($response);
    }
}