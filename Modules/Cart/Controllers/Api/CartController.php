<?php

namespace Modules\Cart\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cart\Services\CartService;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {
        $this->middleware('auth:sanctum')->except(['list', 'count']);
    }

    /* =====================================================
     | ADD / UPDATE CART
     ===================================================== */
    public function add(Request $request)
    {
        $request->validate([
            'hoarding_id'    => 'required|integer',
            'package_id'     => 'nullable|integer',
            'package_source' => 'nullable|string',
        ]);

        $result = $this->cartService->add(
            $request->hoarding_id,
            $request->package_id,
            $request->package_source
        );

        return $this->apiResponse(
            success: true,
            status: $result['status'],
            message: $result['message'],
            data: [
                'in_cart'     => $result['in_cart'],
                'final_price' => $result['final_price'] ?? null,
                'cart_count'  => $this->cartCount(),
            ]
        );
    }

    /* =====================================================
     | REMOVE FROM CART
     ===================================================== */
    public function remove(int $hoardingId)
    {
        $result = $this->cartService->remove($hoardingId);

        return $this->apiResponse(
            success: true,
            status: $result['status'],
            message: $result['message'],
            data: [
                'in_cart'    => false,
                'cart_count' => $this->cartCount(),
            ]
        );
    }

    /* =====================================================
     | COUNT — logged in + guest dono
     ===================================================== */
    public function count()
    {
        $user = $this->resolveUser();

        if ($user) {
            Auth::setUser($user);
            $count = count($this->cartService->getCartHoardingIds());
        } else {
            $count = count($this->guestIds());
        }

        return $this->apiResponse(
            success: true,
            status: 'ok',
            data: ['cart_count' => $count]
        );
    }

    /* =====================================================
     | LIST — logged in + guest dono
     ===================================================== */
    public function list()
    {
        $user = $this->resolveUser();

        if ($user) {
            Auth::setUser($user);
            $items = $this->cartService->getCartForUI();

            return $this->apiResponse(
                success: true,
                status: 'ok',
                data: [
                    'items'      => $items,
                    'cart_count' => count($items),
                ]
            );
        }

        // ── GUEST: ?ids=20,22,25 se ─────────────────────────────
        $ids = $this->guestIds();

        if (empty($ids)) {
            return $this->apiResponse(
                success: true,
                status: 'ok',
                data: ['items' => [], 'cart_count' => 0]
            );
        }

        // DB se same data lo jo CartService getCartForUI() mein hota hai
        $rows = \Illuminate\Support\Facades\DB::table('carts')
            ->rightJoin('hoardings', function ($join) use ($ids) {
                $join->on('hoardings.id', '=', 'carts.hoarding_id')
                    ->whereIn('hoardings.id', $ids);
            })
            ->whereNull('hoardings.deleted_at')
            ->where('hoardings.status', \App\Models\Hoarding::STATUS_ACTIVE)
            ->whereIn('hoardings.id', $ids)
            ->select(
                \Illuminate\Support\Facades\DB::raw('NULL as cart_id'),
                \Illuminate\Support\Facades\DB::raw('NULL as package_id'),
                'hoardings.id as hoarding_id',
                'hoardings.title',
                'hoardings.slug',
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

        // Same buildCartItem() use karo jo logged-in ke liye use hoti hai
        $items = $rows->map(fn($item) => $this->cartService->buildCartItem($item));

        return $this->apiResponse(
            success: true,
            status: 'ok',
            data: [
                'items'      => $items,
                'cart_count' => $items->count(),
            ]
        );
    }
    /* ================= HELPERS ================= */

    private function cartCount(): int
    {
        return Auth::check()
            ? count($this->cartService->getCartHoardingIds())
            : 0;
    }

    private function guestIds(): array
    {
        return array_filter(
            array_map('intval', explode(',', request()->query('ids', '')))
        );
    }

    private function resolveUser()
    {
        if (Auth::check()) {
            return Auth::user();
        }

        try {
            $token = request()->bearerToken();
            if (!$token) return null;

            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if (!$accessToken) return null;

            return $accessToken->tokenable;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function apiResponse(
        bool   $success,
        string $status,
        string $message = '',
        array  $data = []
    ) {
        return response()->json([
            'success' => $success,
            'status'  => $status,
            'data'    => (object) $data,
            'message' => $message,
        ]);
    }
}