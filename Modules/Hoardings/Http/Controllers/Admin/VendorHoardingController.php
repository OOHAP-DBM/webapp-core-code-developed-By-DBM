<?php

namespace Modules\Hoardings\Http\Controllers\Admin;

use Illuminate\Support\Str;
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
use App\Services\NotificationEmailService;

class VendorHoardingController extends Controller
{
    protected NotificationEmailService $notificationEmailService;
    public function __construct(NotificationEmailService $notificationEmailService)
    {
        $this->notificationEmailService = $notificationEmailService;
    }
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
        'vendor:id,name'
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
            $query->where(function ($q) use ($search) {
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
                'display_location' => $h->display_location,
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

            $oldStatus = $hoarding->status;
            $hoarding->status = $hoarding->status === 'active'
                ? 'inactive'
                : 'active';

            $hoarding->save();

            // Fire event for notification (single)
            event(new HoardingStatusChanged(collect([$hoarding]), $hoarding->status, auth()->user()));

            // Send database notification to vendor
            if ($hoarding->vendor) {
                if ($hoarding->status === 'active') {
                    $hoarding->vendor->notify(new HoardingApproved($hoarding));
                } else {
                    $hoarding->vendor->notify(
                        new \App\Notifications\HoardingRejectedNotification($hoarding, 'Your hoarding has been deactivated')
                    );
                }
            }

            // 📱 Send push notification to vendor
            if ($hoarding->vendor && $hoarding->vendor->fcm_token) {
                try {
                    if ($hoarding->status === 'active') {
                        $title = 'Hoarding Activated ✅';
                        $message = 'Your hoarding "' . ($hoarding->title ?? $hoarding->name ?? 'N/A') . '" has been activated and is now live!';
                        $type = 'vendor_hoarding_activated';
                        $action = 'activated';
                    } else {
                        $title = 'Hoarding Deactivated ⏸️';
                        $message = 'Your hoarding "' . ($hoarding->title ?? $hoarding->name ?? 'N/A') . '" has been deactivated.';
                        $type = 'vendor_hoarding_deactivated';
                        $action = 'deactivated';
                    }

                    $sent = send(
                        $hoarding->vendor->fcm_token,
                        $title,
                        $message,
                        [
                            'type'          => $type,
                            'hoarding_id'   => $hoarding->id,
                            'hoarding_type' => strtoupper($hoarding->hoarding_type),
                            'status'        => $hoarding->status,
                            'action'        => $action
                        ]
                    );

                    if (!$sent) {
                        Log::warning("FCM push notification failed for vendor ID {$hoarding->vendor->id} on hoarding status toggle", [
                            'hoarding_id' => $hoarding->id
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error("Failed to send push notification to vendor on hoarding status toggle", [
                        'vendor_id'   => $hoarding->vendor->id,
                        'hoarding_id' => $hoarding->id,
                        'error'       => $e->getMessage()
                    ]);
                }
            }

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
                'vendor.vendorProfile:vendor_profiles.id,vendor_profiles.user_id,vendor_profiles.commission_percentage',
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
                'display_location' => $h->display_location,
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
            // Fetch hoardings before deletion for event
            $hoardings = Hoarding::whereIn('id', $request->ids)->get();
            $count = $hoardings->count();
            Hoarding::whereIn('id', $request->ids)->delete();
            event(new HoardingStatusChanged($hoardings, 'deleted', auth()->user()));
            return response()->json([
                'success' => true,
                'message' => $count . ' ' . Str::plural('Hoarding', $count) . ' deleted successfully',
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
    // public function bulkActivate(Request $request)
    // {
    //     $request->validate([
    //         'ids' => 'required|array',
    //         'ids.*' => 'required|integer|exists:hoardings,id'
    //     ]);

    //     try {
    //         $count = Hoarding::whereIn('id', $request->ids)
    //             ->update(['status' => 'active']);
    //         $hoardings = Hoarding::with('vendor')->whereIn('id', $request->ids)->get();
    //         event(new HoardingStatusChanged($hoardings, 'activated', auth()->user()));

    //         // Send notifications to vendors
    //         foreach ($hoardings as $hoarding) {
    //             // Send database notification
    //             if ($hoarding->vendor) {
    //                 $hoarding->vendor->notify(new HoardingApproved($hoarding));
    //             }

    //             // 📱 Send push notification to vendor on hoarding activation
    //             if ($hoarding->vendor && $hoarding->vendor->fcm_token) {
    //                 try {
    //                     $sent = send(
    //                         $hoarding->vendor->fcm_token,
    //                         'Hoarding Activated ✅',
    //                         'Your hoarding "' . ($hoarding->title ?? $hoarding->name ?? 'N/A') . '" has been activated and is now live!',
    //                         [
    //                             'type'          => 'vendor_hoarding_activated',
    //                             'hoarding_id'   => $hoarding->id,
    //                             'hoarding_type' => strtoupper($hoarding->hoarding_type),
    //                             'status'        => 'active',
    //                             'action'        => 'activated'
    //                         ]
    //                     );

    //                     if (!$sent) {
    //                         Log::warning("FCM push notification failed for vendor ID {$hoarding->vendor->id} on hoarding activation", [
    //                             'hoarding_id' => $hoarding->id
    //                         ]);
    //                     }
    //                 } catch (\Throwable $e) {
    //                     Log::error("Failed to send push notification to vendor on hoarding activation", [
    //                         'vendor_id'   => $hoarding->vendor->id,
    //                         'hoarding_id' => $hoarding->id,
    //                         'error'       => $e->getMessage()
    //                     ]);
    //                 }
    //             }
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => $count . ' ' . Str::plural('Hoarding', $count) . ' activated successfully',
    //             'count' => $count
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to activate hoardings: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function bulkActivate(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|integer|exists:hoardings,id',
        ]);

        try {
            $count    = Hoarding::whereIn('id', $request->ids)->update(['status' => 'active']);
            $hoardings = Hoarding::with('vendor')->whereIn('id', $request->ids)->get();

            // event(new HoardingStatusChanged($hoardings, 'activated', auth()->user()));

            $this->notifyVendorsGrouped(
                $hoardings,
                'activated',
                'Hoardings Activated ✅',
                '{count} of your hoardings have been activated and are now live!',
                'vendor_hoarding_bulk_activated'
            );

            return response()->json([
                'success' => true,
                'message' => $count . ' ' . Str::plural('Hoarding', $count) . ' activated successfully',
                'count'   => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Bulk deactivate hoardings
     */
    // public function bulkDeactivate(Request $request)
    // {
    //     $request->validate([
    //         'ids' => 'required|array',
    //         'ids.*' => 'required|integer|exists:hoardings,id'
    //     ]);

    //     try {
    //         $count = Hoarding::whereIn('id', $request->ids)
    //             ->update(['status' => 'inactive']);
    //         $hoardings = Hoarding::with('vendor')->whereIn('id', $request->ids)->get();
    //         event(new HoardingStatusChanged($hoardings, 'deactivated', auth()->user()));

    //         // Send notifications to vendors
    //         foreach ($hoardings as $hoarding) {
    //             // Send database notification
    //             if ($hoarding->vendor) {
    //                 $hoarding->vendor->notify(
    //                     new \App\Notifications\HoardingRejectedNotification($hoarding, 'Your hoarding has been deactivated')
    //                 );
    //             }

    //             // 📱 Send push notification to vendor on hoarding deactivation
    //             if ($hoarding->vendor && $hoarding->vendor->fcm_token) {
    //                 try {
    //                     $sent = send(
    //                         $hoarding->vendor->fcm_token,
    //                         'Hoarding Deactivated ⏸️',
    //                         'Your hoarding "' . ($hoarding->title ?? $hoarding->name ?? 'N/A') . '" has been deactivated.',
    //                         [
    //                             'type'          => 'vendor_hoarding_deactivated',
    //                             'hoarding_id'   => $hoarding->id,
    //                             'hoarding_type' => strtoupper($hoarding->hoarding_type),
    //                             'status'        => 'inactive',
    //                             'action'        => 'deactivated'
    //                         ]
    //                     );

    //                     if (!$sent) {
    //                         Log::warning("FCM push notification failed for vendor ID {$hoarding->vendor->id} on hoarding deactivation", [
    //                             'hoarding_id' => $hoarding->id
    //                         ]);
    //                     }
    //                 } catch (\Throwable $e) {
    //                     Log::error("Failed to send push notification to vendor on hoarding deactivation", [
    //                         'vendor_id'   => $hoarding->vendor->id,
    //                         'hoarding_id' => $hoarding->id,
    //                         'error'       => $e->getMessage()
    //                     ]);
    //                 }
    //             }
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => $count . ' ' . Str::plural('Hoarding', $count) . ' deactivated successfully',
    //             'count' => $count
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to deactivate hoardings: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|integer|exists:hoardings,id',
        ]);

        try {
            $count     = Hoarding::whereIn('id', $request->ids)->update(['status' => 'inactive']);
            $hoardings = Hoarding::with('vendor')->whereIn('id', $request->ids)->get();

            // event(new HoardingStatusChanged($hoardings, 'deactivated', auth()->user()));

            $this->notifyVendorsGrouped(
                $hoardings,
                'deactivated',
                'Hoardings Deactivated ⏸️',
                '{count} of your hoardings have been deactivated.',
                'vendor_hoarding_bulk_deactivated'
            );

            return response()->json([
                'success' => true,
                'message' => $count . ' ' . Str::plural('Hoarding', $count) . ' deactivated successfully',
                'count'   => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Bulk approve hoardings (set commission and activate)
     */
    // public function bulkApprove(Request $request)
    // {
    //     $request->validate([
    //         'ids' => 'required|array',
    //         'ids.*' => 'required|integer|exists:hoardings,id'
    //     ]);

    //     try {
    //         $hoardings = Hoarding::whereIn('id', $request->ids)->get();

    //         // Only activate
    //         foreach ($hoardings as $hoarding) {
    //             $hoarding->status = 'active';
    //             $hoarding->save();

    //             // Send email to vendor
    //             if ($hoarding->vendor && !empty($hoarding->vendor->email)) {
    //                 try {
    //                     Mail::to($hoarding->vendor->email)->send(
    //                         new HoardingPublishedMail($hoarding)
    //                     );
    //                 } catch (\Throwable $e) {
    //                     Log::error('Hoarding published mail failed', [
    //                         'hoarding_id' => $hoarding->id,
    //                         'vendor_email' => $hoarding->vendor->email,
    //                         'error' => $e->getMessage(),
    //                     ]);
    //                 }
    //             }
    //             // Send database notification to vendor
    //             if ($hoarding->vendor) {
    //                 $hoarding->vendor->notify(new HoardingApproved($hoarding));
    //             }

    //             // 📱 Send push notification to vendor on hoarding approval
    //             if ($hoarding->vendor && $hoarding->vendor->fcm_token) {
    //                 try {
    //                     $sent = send(
    //                         $hoarding->vendor->fcm_token,
    //                         'Hoarding Approved ✅',
    //                         'Your hoarding "' . ($hoarding->title ?? $hoarding->name ?? 'N/A') . '" has been approved and is now live!',
    //                         [
    //                             'type'          => 'vendor_hoarding_approved',
    //                             'hoarding_id'   => $hoarding->id,
    //                             'hoarding_type' => strtoupper($hoarding->hoarding_type),
    //                             'status'        => 'active',
    //                             'action'        => 'approved'
    //                         ]
    //                     );

    //                     if (!$sent) {
    //                         Log::warning("FCM push notification failed for vendor ID {$hoarding->vendor->id} on hoarding approval", [
    //                             'hoarding_id' => $hoarding->id
    //                         ]);
    //                     }
    //                 } catch (\Throwable $e) {
    //                     Log::error("Failed to send push notification to vendor on hoarding approval", [
    //                         'vendor_id'   => $hoarding->vendor->id,
    //                         'hoarding_id' => $hoarding->id,
    //                         'error'       => $e->getMessage()
    //                     ]);
    //                 }
    //             }
    //         }

    //         $count = $hoardings->count();
    //         return response()->json([
    //             'success' => true,
    //             'message' => $count . ' ' . Str::plural('Hoarding', $count) . ' approved and activated successfully',
    //             'count' => $count
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to approve hoardings: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|integer|exists:hoardings,id',
        ]);

        try {
            $hoardings = Hoarding::with('vendor')->whereIn('id', $request->ids)->get();

            foreach ($hoardings as $hoarding) {
                $hoarding->status = 'active';
                $hoarding->save();
            }

            // Send ONE bulk email per vendor (replaces per-hoarding HoardingPublishedMail)
            $this->notifyVendorsGrouped(
                $hoardings,
                'approved',
                'Hoardings Approved ✅',
                '{count} of your hoardings have been approved and are now live!',
                'vendor_hoarding_bulk_approved'
            );

            $count = $hoardings->count();
            return response()->json([
                'success' => true,
                'message' => $count . ' ' . Str::plural('Hoarding', $count) . ' approved and activated successfully',
                'count'   => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Bulk reject hoardings
     */
    // public function bulkReject(Request $request)
    // {
    //     $request->validate([
    //         'ids' => 'required|array',
    //         'ids.*' => 'required|integer|exists:hoardings,id',
    //         'rejection_reason' => 'nullable|string|max:500'
    //     ]);

    //     try {
    //         $hoardings = Hoarding::whereIn('id', $request->ids)->get();
    //         $rejectionReason = $request->input('rejection_reason', 'Admin decision');

    //         foreach ($hoardings as $hoarding) {
    //             $hoarding->status = 'rejected';
    //             $hoarding->save();

    //             // Fire event for notification
    //             event(new HoardingStatusChanged(collect([$hoarding]), 'rejected', auth()->user()));

    //             // Send database notification to vendor
    //             if ($hoarding->vendor) {
    //                 $hoarding->vendor->notify(
    //                     new \App\Notifications\HoardingRejectedNotification($hoarding, $rejectionReason)
    //                 );
    //             }

    //             // 📱 Send push notification to vendor on hoarding rejection
    //             if ($hoarding->vendor && $hoarding->vendor->fcm_token) {
    //                 try {
    //                     $message = 'Your hoarding "' . ($hoarding->title ?? $hoarding->name ?? 'N/A') . '" has been rejected';
    //                     if ($rejectionReason) {
    //                         $message .= '. Reason: ' . $rejectionReason;
    //                     }

    //                     $sent = send(
    //                         $hoarding->vendor->fcm_token,
    //                         'Hoarding Rejected ❌',
    //                         $message,
    //                         [
    //                             'type'          => 'vendor_hoarding_rejected',
    //                             'hoarding_id'   => $hoarding->id,
    //                             'hoarding_type' => strtoupper($hoarding->hoarding_type),
    //                             'status'        => 'rejected',
    //                             'reason'        => $rejectionReason,
    //                             'action'        => 'rejected'
    //                         ]
    //                     );

    //                     if (!$sent) {
    //                         Log::warning("FCM push notification failed for vendor ID {$hoarding->vendor->id} on hoarding rejection", [
    //                             'hoarding_id' => $hoarding->id
    //                         ]);
    //                     }
    //                 } catch (\Throwable $e) {
    //                     Log::error("Failed to send push notification to vendor on hoarding rejection", [
    //                         'vendor_id'   => $hoarding->vendor->id,
    //                         'hoarding_id' => $hoarding->id,
    //                         'error'       => $e->getMessage()
    //                     ]);
    //                 }
    //             }
    //         }

    //         $count = $hoardings->count();
    //         return response()->json([
    //             'success' => true,
    //             'message' => $count . ' ' . Str::plural('Hoarding', $count) . ' rejected successfully',
    //             'count' => $count
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to reject hoardings: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'ids'              => 'required|array',
            'ids.*'            => 'required|integer|exists:hoardings,id',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        try {
            $hoardings       = Hoarding::with('vendor')->whereIn('id', $request->ids)->get();
            $rejectionReason = $request->input('rejection_reason', 'Admin decision');

            foreach ($hoardings as $hoarding) {
                $hoarding->status = 'rejected';
                $hoarding->save();
            }
            // if($hoardings->count() ==1) {
            //   event(new HoardingStatusChanged($hoardings, 'rejected', auth()->user()));
            // }

            // Pass rejection reason via a custom notification subclass or extra array
            // Option: temporarily store reason so notifyVendorsGrouped can use it
            $this->notifyVendorsGroupedWithReason(
                $hoardings,
                'rejected',
                $rejectionReason,
                'Hoardings Rejected ❌',
                '{count} of your hoardings have been rejected. Reason: ' . $rejectionReason,
                'vendor_hoarding_bulk_rejected'
            );

            $count = $hoardings->count();
            return response()->json([
                'success' => true,
                'message' => $count . ' ' . Str::plural('Hoarding', $count) . ' rejected successfully',
                'count'   => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    // Variant of the helper that carries a rejection reason
    private function notifyVendorsGroupedWithReason(
        \Illuminate\Support\Collection $hoardings,
        string $action,
        string $reason,
        ?string $fcmTitle = null,
        ?string $fcmBody = null,
        ?string $fcmType = null
    ): void {
        $adminName = auth()->user()?->name ?? 'Admin';
        $grouped   = $hoardings->filter(fn($h) => $h->vendor !== null)
                            ->groupBy(fn($h) => $h->vendor->id);

        foreach ($grouped as $vendorId => $vendorHoardings) {
            $vendor = $vendorHoardings->first()->vendor;

            $vendor->notify(
                new \Modules\Hoardings\Notifications\HoardingBulkStatusNotification(
                    $vendorHoardings,
                    $action,
                    $adminName,
                    $reason        // pass reason to notification if you extend it
                )
            );

            if ($vendor->fcm_token && $fcmTitle && $fcmType) {
                try {
                    $count = $vendorHoardings->count();
                    $body  = str_replace('{count}', $count, $fcmBody ?? "{$count} hoardings {$action}.");

                    send($vendor->fcm_token, $fcmTitle, $body, [
                        'type'         => $fcmType,
                        'hoarding_ids' => $vendorHoardings->pluck('id')->toArray(),
                        'count'        => $count,
                        'action'       => $action,
                        'reason'       => $reason,
                    ]);
                } catch (\Throwable $e) {
                    Log::error("Bulk FCM (reject) exception for vendor {$vendor->id}", ['error' => $e->getMessage()]);
                }
            }
        }
    }

    /**
     * Suspend a hoarding
     */
    public function suspend($id)
    {
        try {
            $hoarding = Hoarding::with('vendor')->findOrFail($id);

            $hoarding->status = 'suspended';
            $hoarding->save();

            // Fire event (primary email + database - same as before)
            event(new HoardingStatusChanged(collect([$hoarding]), 'suspended', auth()->user()));

            // Database notification (same as before)
            if ($hoarding->vendor) {
                $hoarding->vendor->notify(
                    new \App\Notifications\HoardingRejectedNotification(
                        $hoarding,
                        'Your hoarding has been suspended by admin'
                    )
                );

                // ✅ Additional emails pe bhi bhejo - service se
                $vendorProfile    = $hoarding->vendor->vendorProfile;
                $additionalEmails = $vendorProfile->additional_emails ?? [];
                $emailPreferences = $vendorProfile->email_preferences ?? [];

                foreach ($additionalEmails as $additionalEmail) {
                    $pref = $emailPreferences[$additionalEmail] ?? null;
                    if (
                        $pref &&
                        ($pref['verified'] ?? false) === true &&
                        ($pref['notifications'] ?? false) === true
                    ) {
                        try {
                            Mail::to($additionalEmail)->send(
                                new \Modules\Hoardings\Mail\VendorHoardingBulkStatusMail(
                                    $hoarding->vendor,
                                    collect([$hoarding]),
                                    'suspended',
                                    auth()->user()?->name ?? 'Admin'
                                )
                            );
                        } catch (\Throwable $e) {
                            Log::error("Suspend additional email failed", [
                                'email' => $additionalEmail,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
            }

            // Push notification (same as before)
            if ($hoarding->vendor && $hoarding->vendor->fcm_token) {
                try {
                    $sent = send(
                        $hoarding->vendor->fcm_token,
                        'Hoarding Suspended ⏸️',
                        'Your hoarding "' . ($hoarding->title ?? $hoarding->name ?? 'N/A') . '" has been suspended.',
                        [
                            'type'          => 'vendor_hoarding_suspended',
                            'hoarding_id'   => $hoarding->id,
                            'hoarding_type' => strtoupper($hoarding->hoarding_type),
                            'status'        => 'suspended',
                            'action'        => 'suspended'
                        ]
                    );

                    if (!$sent) {
                        Log::warning("FCM push notification failed for vendor ID {$hoarding->vendor->id} on hoarding suspension", [
                            'hoarding_id' => $hoarding->id
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error("Failed to send push notification to vendor on hoarding suspension", [
                        'vendor_id'   => $hoarding->vendor->id,
                        'hoarding_id' => $hoarding->id,
                        'error'       => $e->getMessage()
                    ]);
                }
            }

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
                'message' => $updated . ' ' . Str::plural('slug', $updated) . ' updated successfully',
                'count' => $updated
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update slugs: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Send ONE email + database notification + push per vendor.
     * Accepts a collection of hoardings (already grouped or not – grouping happens here).
     */
    private function notifyVendorsGrouped(
    \Illuminate\Support\Collection $hoardings,
    string $action,
    ?string $fcmTitle = null,
    ?string $fcmBodyTemplate = null,
    ?string $fcmType = null
): void {
    $adminName = auth()->user()?->name ?? 'Admin';
 
    $grouped = $hoardings->filter(fn($h) => $h->vendor !== null)
                        ->groupBy(fn($h) => $h->vendor->id);
 
    foreach ($grouped as $vendorId => $vendorHoardings) {
        $vendor = $vendorHoardings->first()->vendor;
 
        // 1️⃣ Database notification (same as before)
        $vendor->notify(
            new \Modules\Hoardings\Notifications\HoardingBulkStatusNotification(
                $vendorHoardings,
                $action,
                $adminName
            )
        );
 
        // 2️⃣ ✅ Email - NotificationEmailService se
        // Global check + primary email + additional valid emails
        // Same blade: emails.vendor_hoarding_bulk_status
        try {
            $this->notificationEmailService->send(
                $vendor,
                new \Modules\Hoardings\Mail\VendorHoardingBulkStatusMail(
                    $vendor,
                    $vendorHoardings,
                    $action,
                    $adminName
                )
            );
        } catch (\Throwable $e) {
            Log::error("Bulk hoarding email failed for vendor {$vendor->id}", [
                'action' => $action,
                'error'  => $e->getMessage()
            ]);
        }
 
        // 3️⃣ Push notification (same as before)
        if ($vendor->fcm_token && $fcmTitle && $fcmType) {
            try {
                $count = $vendorHoardings->count();
                $body  = $fcmBodyTemplate
                    ? str_replace('{count}', $count, $fcmBodyTemplate)
                    : "{$count} " . Str::plural('hoarding', $count) . " have been {$action}.";
 
                $sent = send(
                    $vendor->fcm_token,
                    $fcmTitle,
                    $body,
                    [
                        'type'         => $fcmType,
                        'hoarding_ids' => $vendorHoardings->pluck('id')->toArray(),
                        'count'        => $count,
                        'action'       => $action,
                    ]
                );
 
                if (!$sent) {
                    Log::warning("Bulk FCM failed for vendor {$vendor->id}", ['action' => $action]);
                }
            } catch (\Throwable $e) {
                Log::error("Bulk FCM exception for vendor {$vendor->id}", [
                    'error'  => $e->getMessage(),
                    'action' => $action,
                ]);
            }
        }
    }
}
}
