<?php

namespace Modules\Hoardings\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use Modules\DOOH\Models\DOOHScreen;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;


class VendorHoardingController extends Controller
{
    // public function index(Request $request): View
    // {
    //     /* -------------------- OOH -------------------- */
    //     $ooh = Hoarding::whereNotNull('vendor_id')
    //         ->with([
    //             'vendor:id,name',
    //             'vendor.vendorProfile:id,user_id,commission_percentage',
    //         ])
    //         ->withCount('bookings')
    //         ->get()
    //         ->map(fn ($h) => (object) [
    //             'id' => $h->id,
    //             'title' => $h->title,
    //             'type' => 'ooh',
    //             'vendor' => $h->vendor,
    //             'vendor_commission' => $h->vendor?->vendorProfile?->commission_percentage,
    //             'hoarding_commission' => $h->commission_percent,
    //             'address' => $h->address,
    //             'bookings_count' => $h->bookings_count,
    //             'status' => $h->status,
    //             'source' => 'ooh',
    //         ]);

    //     /* -------------------- DOOH -------------------- */
    //     $dooh = DOOHScreen::whereNotNull('vendor_id')
    //         ->with([
    //             'vendor:id,name',
    //             'vendor.vendorProfile:id,user_id,commission_percentage',
    //         ])
    //         ->withCount('bookings')
    //         ->get()
    //         ->map(fn ($s) => (object) [
    //             'id' => $s->id,
    //             'title' => $s->name,
    //             'type' => 'DOOH',
    //             'vendor' => $s->vendor,
    //             'vendor_commission' => $s->vendor?->vendorProfile?->commission_percentage,
    //             'hoarding_commission' => $s->commission_percent,
    //             'address' => $s->address,
    //             'bookings_count' => $s->bookings_count,
    //             'status' => $s->status,
    //             'source' => 'dooh',
    //         ]);

    //     /* -------------------- MERGE + PAGINATE -------------------- */
    //     $collection = $ooh->merge($dooh)
    //         ->sortByDesc('id')
    //         ->values();

    //     $perPage = 10;
    //     $page = LengthAwarePaginator::resolveCurrentPage();

    //     $paginated = new LengthAwarePaginator(
    //         $collection->forPage($page, $perPage)->values(),
    //         $collection->count(),
    //         $perPage,
    //         $page,
    //         [
    //             'path' => LengthAwarePaginator::resolveCurrentPath(),
    //         ]
    //     );

    //     return view('hoardings.admin.vendor-hoardings', [
    //         'hoardings' => $paginated
    //     ]);
    // }
    public function index(Request $request): View
    {
        $perPage = 10;

        // We fetch everything from the single 'hoardings' table
        $hoardings = Hoarding::whereNotNull('vendor_id')
            ->with([
                'vendor:id,name',
                'vendor.vendorProfile:id,user_id,commission_percentage',
            ])
            ->withCount('bookings')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        // Transform the collection to match your expected UI structure
        $hoardings->getCollection()->transform(function ($h) {
            return (object) [
                'id' => $h->id,
                'vendor_profile_id' => $h->vendor?->vendorProfile?->id,
                'title' => $h->title ?? $h->name,
                'type' => strtoupper($h->hoarding_type), // Result: OOH or DOOH
                'vendor' => $h->vendor,
                'vendor_commission' => $h->vendor?->vendorProfile?->commission_percentage,
                'hoarding_commission' => $h->commission_percent,
                'address' => $h->address,
                'bookings_count' => $h->bookings_count,
                'status' => $h->status,
                'source' => $h->hoarding_type, // 'ooh' or 'dooh'
            ];
        });

        return view('hoardings.admin.vendor-hoardings', [
            'hoardings' => $hoardings
        ]);
    }
    // public function toggleStatus(Request $request, $id)
    // {
    //     $source = $request->input('source'); // ooh / dooh

    //     if ($source === 'dooh') {
    //         $model = DoohScreen::findOrFail($id);
    //     } else {
    //         $model = Hoarding::findOrFail($id);
    //     }

    //     // Toggle logic
    //     $model->status = $model->status === 'active'
    //         ? 'inactive'
    //         : 'active';

    //     $model->save();

    //     return response()->json([
    //         'success' => true,
    //         'status'  => $model->status
    //     ]);
    // }
    public function toggleStatus(Request $request, $id)
    {
        $hoarding = Hoarding::findOrFail($id);

        $hoarding->status = $hoarding->status === 'active'
            ? 'inactive'
            : 'active';

        $hoarding->save();

        return response()->json([
            'success' => true,
            'status'  => $hoarding->status
        ]);
    }
    public function setCommission(Request $request, $id)
    {
        $request->validate([
            'commission' => 'required|numeric|min:0|max:100',
        ]);
        $hoarding = Hoarding::findOrFail($id);
        $hoarding->commission_percent = $request->commission;
        $hoarding->save();

        return response()->json([
            'success' => true,
            'message' => 'Hoarding commission saved'
        ]);
    }


}
