<?php

namespace Modules\Admin\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin HoardingController
 * 
 * Handles admin-side hoarding operations including:
 * - Viewing hoarding details/preview
 * - Approving/rejecting hoardings
 * 
 * IMPORTANT: This controller is READ-ONLY for previewing hoardings.
 * It does NOT modify hoarding data or status workflows.
 */
class HoardingController extends Controller
{
    /**
     * Display a comprehensive preview of a hoarding for admin review.
     * 
     * This method loads ALL hoarding data including:
     * - Basic info (title, type, category, description)
     * - Media (images for OOH, images/videos for DOOH)
     * - Location details with coordinates
     * - Dimensions and physical attributes
     * - Pricing structures (different for OOH vs DOOH)
     * - Packages and offers
     * - Permit and legal information
     * - Vendor details
     * - Booking rules and availability
     * 
     * @param int $id Hoarding ID
     * @return View
     */
    public function show(int $id): View
    {
        // Load hoarding with ALL necessary relationships
        // Using eager loading to prevent N+1 queries
        $hoarding = Hoarding::with([
            // Vendor info
            'vendor',
            
            // OOH-specific relationships
            'hoardingMedia' => function($query) {
                $query->orderBy('is_primary', 'desc')
                      ->orderBy('sort_order');
            },
            'ooh',
            
            // DOOH-specific relationships
            'doohScreen',
            'doohScreen.media' => function($query) {
                $query->orderBy('sort_order');
            },
            'doohScreen.packages' => function($query) {
                $query->orderBy('min_booking_duration');
            },
            
            // Packages for both types
            'oohPackages' => function($query) {
                $query->where(function($q) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                      })
                      ->orderBy('min_booking_duration');
            },
        ])->findOrFail($id);

        // Initialize packages collection (will be populated based on type)
        $hoarding->packages = collect();
        $hoarding->dimensions = null;
        $hoarding->technical_specs = null;

        /*
        |--------------------------------------------------------------------------
        | DOOH HOARDING PROCESSING
        |--------------------------------------------------------------------------
        | For DOOH hoardings, we extract:
        | - Screen specifications (resolution, size, type)
        | - Slot-based pricing
        | - Video requirements
        | - Packages from doohScreen
        */
        if ($hoarding->hoarding_type === Hoarding::TYPE_DOOH) {
            $screen = $hoarding->doohScreen;

            if ($screen) {
                // Set pricing type for view
                $hoarding->price_type = 'dooh';
                $hoarding->price_unit = 'Slot';

                // Extract DOOH-specific pricing
                $hoarding->price_per_slot = $screen->price_per_slot;
                $hoarding->price_per_10_sec = $screen->price_per_10_sec_slot;
                $hoarding->price_per_30_sec = $screen->display_price_per_30s;
                $hoarding->minimum_booking_amount = $screen->minimum_booking_amount;

                // Screen dimensions
                $hoarding->dimensions = [
                    'width' => $screen->width,
                    'height' => $screen->height,
                    'unit' => $screen->measurement_unit ?? 'feet',
                    'screen_size' => $screen->screen_size,
                ];

                // Technical specifications for DOOH
                $hoarding->technical_specs = [
                    'screen_type' => $screen->screen_type,
                    'resolution_width' => $screen->resolution_width,
                    'resolution_height' => $screen->resolution_height,
                    'slot_duration_seconds' => $screen->slot_duration_seconds,
                    'loop_duration_seconds' => $screen->loop_duration_seconds,
                    'slots_per_loop' => $screen->slots_per_loop,
                    'total_slots_per_day' => $screen->total_slots_per_day,
                    'available_slots_per_day' => $screen->available_slots_per_day,
                    'allowed_formats' => $screen->allowed_formats,
                    'max_file_size_mb' => $screen->max_file_size_mb,
                    'video_length' => $screen->video_length,
                ];

                // Load DOOH packages
                $hoarding->packages = $screen->packages;

                // Additional charges
                $hoarding->graphics_included = $screen->graphics_included;
                $hoarding->graphics_charge = $screen->graphics_price;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | OOH HOARDING PROCESSING
        |--------------------------------------------------------------------------
        | For OOH hoardings, we extract:
        | - Physical dimensions and area
        | - Monthly/weekly pricing
        | - Material and mounting details
        | - Printing and installation charges
        | - Packages from hoarding
        */
        else {
            $ooh = $hoarding->ooh;

            // Set pricing type for view
            $hoarding->price_type = 'ooh';
            $hoarding->price_unit = 'Month';

            // Extract OOH pricing (with fallback)
            $hoarding->monthly_price = $hoarding->monthly_price ?? $hoarding->base_monthly_price;
            $hoarding->base_monthly_price = $hoarding->base_monthly_price;
            $hoarding->weekly_price = $hoarding->weekly_price_1;
            $hoarding->supports_weekly = $hoarding->enable_weekly_booking;

            if ($ooh) {
                // Physical dimensions
                $hoarding->dimensions = [
                    'width' => $ooh->width,
                    'height' => $ooh->height,
                    'unit' => $ooh->measurement_unit ?? 'feet',
                    'area_sqft' => $ooh->calculated_area_sqft,
                ];

                // OOH-specific details
                $hoarding->technical_specs = [
                    'lighting_type' => $ooh->lighting_type,
                    'material_type' => $ooh->material_type,
                    'mounting_type' => $ooh->mounting_type,
                ];

                // Installation charges
                $hoarding->printing_included = $ooh->printing_included;
                $hoarding->printing_charge = $ooh->printing_charge;
                $hoarding->mounting_included = $ooh->mounting_included;
                $hoarding->mounting_charge = $ooh->mounting_charge;
                $hoarding->remounting_included = $ooh->remounting_included;
                $hoarding->remounting_charge = $ooh->remounting_charge;
                $hoarding->lighting_included = $ooh->lighting_included;
                $hoarding->lighting_charge = $ooh->lighting_charge;
            }

            // Load OOH packages
            $hoarding->packages = $hoarding->oohPackages;
        }

        /*
        |--------------------------------------------------------------------------
        | PREPARE MEDIA FOR DISPLAY
        |--------------------------------------------------------------------------
        | Organize media files for gallery display
        */
        $hoarding->media_files = collect();

        if ($hoarding->hoarding_type === Hoarding::TYPE_DOOH && $hoarding->doohScreen) {
            // DOOH: Get media from doohScreen
            $hoarding->media_files = $hoarding->doohScreen->media->map(function($media) {
                return [
                    'url' => asset('storage/' . $media->file_path),
                    'type' => $media->media_type ?? 'image',
                    'is_primary' => $media->is_primary ?? false,
                ];
            });
        } elseif ($hoarding->hoarding_type === Hoarding::TYPE_OOH) {
            // OOH: Get media from hoardingMedia
            $hoarding->media_files = $hoarding->hoardingMedia->map(function($media) {
                return [
                    'url' => asset('storage/' . $media->file_path),
                    'type' => $media->media_type ?? 'image',
                    'is_primary' => $media->is_primary ?? false,
                ];
            });
        }

        // Fallback to placeholder if no media
        if ($hoarding->media_files->isEmpty()) {
            $hoarding->media_files = collect([[
                'url' => asset('assets/images/placeholder.jpg'),
                'type' => 'image',
                'is_primary' => true,
            ]]);
        }

        // Return admin-specific preview view
        return view('hoardings.admin.preview', compact('hoarding'));
    }

    /**
     * Display the hoardings listing page
     */
    public function index(): View
    {
        return view('admin.hoardings.index');
    }

    /**
     * Approve a hoarding
     * 
     * Note: This method exists for route compatibility.
     * Actual approval logic should be handled by dedicated approval workflow.
     */
    public function approve(Request $request, int $id)
    {
        // Approval logic would go here
        // Not implementing as per instructions to not modify status workflow
        return redirect()->back()->with('success', 'Hoarding approval functionality pending implementation.');
    }

    /**
     * Reject a hoarding
     * 
     * Note: This method exists for route compatibility.
     * Actual rejection logic should be handled by dedicated approval workflow.
     */
    public function reject(Request $request, int $id)
    {
        // Rejection logic would go here
        // Not implementing as per instructions to not modify status workflow
        return redirect()->back()->with('success', 'Hoarding rejection functionality pending implementation.');
    }
}
