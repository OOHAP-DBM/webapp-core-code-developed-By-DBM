<?php

namespace Modules\Hoardings\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use Modules\Mail\HoardingPublishedMail;
use Modules\DOOH\Models\DOOHScreen;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Notifications\HoardingApproved;
use App\Events\HoardingStatusChanged;



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

        // Build query with filters
        $query = Hoarding::whereNotNull('vendor_id')
            ->with([
                'vendor:id,name',
                'vendor.vendorProfile:id,user_id,commission_percentage',
            ])
            ->withCount('bookings');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('hoarding_type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('city')) {
            $query->where('city', 'LIKE', "%{$request->city}%");
        }

        $hoardings = $query->orderBy('id', 'desc')->paginate($perPage);

        $completionService = app(\App\Services\HoardingCompletionService::class);
        $hoardings->getCollection()->transform(function ($h) use ($completionService) {
            return (object) [
                'id' => $h->id,
                'vendor_profile_id' => $h->vendor?->vendorProfile?->id,
                'title' => $h->title ?? $h->name,
                'type' => strtoupper($h->hoarding_type),
                'vendor' => $h->vendor,
                'vendor_commission' => $h->vendor?->vendorProfile?->commission_percentage,
                'hoarding_commission' => $h->commission_percent,
                'address' => $h->address,
                'bookings_count' => $h->bookings_count,
                'status' => $h->status,
                'source' => $h->hoarding_type,
                'completion' => $completionService->calculateCompletion($h),
                'expiry_date' => $h->permit_valid_till ? \Carbon\Carbon::parse($h->permit_valid_till) : null,
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
        try {
            // 👇 vendor eagerly load (IMPORTANT)
            $hoarding = Hoarding::with('vendor')->findOrFail($id);

            $hoarding->status = $hoarding->status === 'active'
                ? 'inactive'
                : 'active';

            $hoarding->save();

            // Fire event for notification (single)
            event(new HoardingStatusChanged(collect([$hoarding]), $hoarding->status, auth()->user()));

            return response()->json([
                'success' => true,
                'status'  => $hoarding->status
            ]);

        } catch (\Throwable $e) {
            Log::critical('toggleStatus failed', [
                'hoarding_id' => $id,
                'error'       => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
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

    /**
     * Show all hoardings in draft status for admin.
     */
    public function drafts(Request $request): View
    {
        $perPage = 10;
        $hoardings = Hoarding::where('status', Hoarding::STATUS_DRAFT)
            ->whereNotNull('vendor_id')
            ->with([
                'vendor:id,name',
                'vendor.vendorProfile:id,user_id,commission_percentage',
            ])
            ->withCount('bookings')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        $completionService = app(\App\Services\HoardingCompletionService::class);
        $hoardings->getCollection()->transform(function ($h) use ($completionService) {
            return (object) [
                'id' => $h->id,
                'vendor_profile_id' => $h->vendor?->vendorProfile?->id,
                'title' => $h->title ?? $h->name,
                'type' => strtoupper($h->hoarding_type),
                'vendor' => $h->vendor,
                'vendor_commission' => $h->vendor?->vendorProfile?->commission_percentage,
                'hoarding_commission' => $h->commission_percent,
                'address' => $h->address,
                'bookings_count' => $h->bookings_count,
                'status' => $h->status,
                'source' => $h->hoarding_type,
                'completion' => $completionService->calculateCompletion($h),
            ];
        });

        return view('hoardings.admin.draft-hoardings', [
            'hoardings' => $hoardings
        ]);
    }

    /**
     * Bulk delete hoardings
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:hoardings,id'
        ]);

        try {
            $count = Hoarding::whereIn('id', $request->ids)->delete();
            // Fire event for notification (bulk delete)
            $hoardings = collect();
            // Optionally, you may want to fetch deleted hoardings' info before deletion for notification
            // $hoardings = Hoarding::whereIn('id', $request->ids)->get();
            event(new HoardingStatusChanged($hoardings, 'deleted', auth()->user()));

            return response()->json([
                'success' => true,
                'message' => "{$count} hoarding(s) deleted successfully",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete hoardings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk activate hoardings
     */
    public function bulkActivate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:hoardings,id'
        ]);

        try {
            $count = Hoarding::whereIn('id', $request->ids)
                ->update(['status' => 'active']);
            $hoardings = Hoarding::whereIn('id', $request->ids)->get();
            // Fire event for notification (bulk activate)
            event(new HoardingStatusChanged($hoardings, 'activated', auth()->user()));

            return response()->json([
                'success' => true,
                'message' => "{$count} hoarding(s) activated successfully",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate hoardings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk deactivate hoardings
     */
    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:hoardings,id'
        ]);

        try {
            $count = Hoarding::whereIn('id', $request->ids)
                ->update(['status' => 'inactive']);
            $hoardings = Hoarding::whereIn('id', $request->ids)->get();
            // Fire event for notification (bulk deactivate)
            event(new HoardingStatusChanged($hoardings, 'deactivated', auth()->user()));

            return response()->json([
                'success' => true,
                'message' => "{$count} hoarding(s) deactivated successfully",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate hoardings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve hoardings (set commission and activate)
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:hoardings,id',
            'commission' => 'required|numeric|min:0|max:100'
        ]);

        try {
            $hoardings = Hoarding::whereIn('id', $request->ids)->get();

            // Set commission and activate
            foreach ($hoardings as $hoarding) {
                $hoarding->commission_percent = $request->commission;
                $hoarding->status = 'active';
                $hoarding->save();

                // Send email to vendor
                if ($hoarding->vendor && !empty($hoarding->vendor->email)) {
                    try {
                        Mail::to($hoarding->vendor->email)->send(
                            new HoardingPublishedMail($hoarding)
                        );
                    } catch (\Throwable $e) {
                        Log::error('Hoarding published mail failed', [
                            'hoarding_id' => $hoarding->id,
                            'vendor_email' => $hoarding->vendor->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$hoardings->count()} hoarding(s) approved and activated successfully",
                'count' => $hoardings->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve hoardings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suspend a hoarding
     */
    public function suspend($id)
    {
        try {
            $hoarding = Hoarding::findOrFail($id);
            
            $hoarding->status = 'suspended';
            $hoarding->save();
            // Fire event for notification (single suspend)
            event(new HoardingStatusChanged(collect([$hoarding]), 'suspended', auth()->user()));

            return response()->json([
                'success' => true,
                'message' => 'Hoarding suspended successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to suspend hoarding: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Bulk generate/update slugs for hoardings
     */
    public function bulkUpdateSlugs(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:hoardings,id'
        ]);

        try {
            $hoardings = Hoarding::whereIn('id', $request->ids)->get();
            $updated = 0;
            foreach ($hoardings as $hoarding) {
                $oldSlug = $hoarding->slug;
                $hoarding->slug = $hoarding->generateSlugWithId();
                if ($hoarding->isDirty('slug')) {
                    $hoarding->save();
                    $updated++;
                }
            }
            return response()->json([
                'success' => true,
                'message' => "$updated slug(s) updated successfully",
                'count' => $updated
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update slugs: ' . $e->getMessage()
            ], 500);
        }
    }

}
