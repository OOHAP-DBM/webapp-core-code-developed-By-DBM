<?php

namespace Modules\Hoardings\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use Modules\DOOH\Models\DOOHScreen;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class VendorHoardingController extends Controller
{
    /**
     * Vendor Hoardings + DOOH Screens (Admin)
     */
    public function index(Request $request): View
    {
        /* =========================
         | 1. OOH HOARDINGS
         ========================= */
        $ooh = Hoarding::query()
            ->whereNotNull('vendor_id')
            ->with([
                'vendor:id,name',
                'vendor.vendorProfile:id,user_id,commission_percentage',
            ])
            ->select([
                'id',
                'vendor_id',
                'title',
                'type',
                'address',
                'status',
                'bookings_count',
                'created_at',
            ])
            ->get();

        /* =========================
         | 2. DOOH SCREENS
         ========================= */
        $dooh = DoohScreen::query()
            ->whereNotNull('vendor_id')
            ->with([
                'vendor:id,name',
                'vendor.vendorProfile:id,user_id,commission_percentage',
            ])
            ->select([
                'id',
                'vendor_id',
                'name',
                'address',
                'status',
                'created_at',
            ])
            ->get()
            ->map(function ($screen) {
                return (object) [
                    'id'             => $screen->id,
                    'vendor_id'      => $screen->vendor_id,
                    'title'          => $screen->name,            // 👈 blade compatible
                    'type'           => 'digital',                // 👈 DOOH
                    'address'        => $screen->address,
                    'status'         => $screen->status,
                    'bookings_count' => 0,                          // DOOH bookings later
                    'created_at'     => $screen->created_at,
                    'vendor'         => $screen->vendor,            // 👈 IMPORTANT
                ];
            });

        /* =========================
         | 3. MERGE + SORT
         ========================= */
        $merged = collect()
            ->merge($ooh)
            ->merge($dooh)
            ->sortByDesc('created_at')
            ->values();

        /* =========================
         | 4. PAGINATION (10)
         ========================= */
        $perPage = 10;
        $page    = LengthAwarePaginator::resolveCurrentPage();

        $hoardings = new LengthAwarePaginator(
            $merged->forPage($page, $perPage),
            $merged->count(),
            $perPage,
            $page,
            [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('hoardings.admin.vendor-hoardings', compact('hoardings'));
    }
    public function toggleStatus(Hoarding $hoarding): JsonResponse
    {
        try {
            DB::beginTransaction();

            // 🔐 Vendor must exist
            $vendor = $hoarding->vendor;
            if (!$vendor) {
                return response()->json([
                    'message' => 'Vendor not found for this hoarding'
                ], 422);
            }

            // 🔐 Vendor profile must exist
            $vendorProfile = $vendor->vendorProfile;
            if (!$vendorProfile) {
                return response()->json([
                    'message' => 'Vendor profile not found. Complete vendor onboarding.'
                ], 422);
            }

            // 🔐 Commission must be set before publish
            if (
                $hoarding->status !== Hoarding::STATUS_ACTIVE &&
                (float) $vendorProfile->commission_percentage <= 0
            ) {
                return response()->json([
                    'requires_commission' => true,
                    'message' => 'Please set commission before publishing'
                ], 422);
            }

            // 🔄 Toggle status
            $newStatus = $hoarding->status === Hoarding::STATUS_ACTIVE
                ? Hoarding::STATUS_INACTIVE
                : Hoarding::STATUS_ACTIVE;

            $hoarding->update([
                'status' => $newStatus,
                'approved_at' => $newStatus === Hoarding::STATUS_ACTIVE ? now() : null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'label' => $newStatus === Hoarding::STATUS_ACTIVE ? 'Published' : 'Unpublished'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            // 🔥 THIS LOG IS IMPORTANT
            logger()->error('Hoarding toggle failed', [
                'hoarding_id' => $hoarding->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Something went wrong while updating status'
            ], 500);
        }
    }

    /**
     * Save commission then publish
     */
    public function setCommission(Request $request, Hoarding $hoarding): JsonResponse
    {
        $request->validate([
            'commission_percentage' => 'required|numeric|min:0|max:100'
        ]);

        $vendor = $hoarding->vendor;
        if (!$vendor || !$vendor->vendorProfile) {
            return response()->json([
                'message' => 'Vendor profile missing'
            ], 422);
        }

        $vendor->vendorProfile->update([
            'commission_percentage' => $request->commission_percentage
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}
