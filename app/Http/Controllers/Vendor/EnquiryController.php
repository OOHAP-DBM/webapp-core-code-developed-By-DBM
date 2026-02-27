<?php

namespace App\Http\Controllers\Vendor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Enquiries\Models\Enquiry;
use App\Models\Offer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\EnquiryPriceCalculator;

class EnquiryController extends Controller
{
    /**
     * Show the vendor enquiry detail page.
     */
    public function show($id)
    {
        $vendorId = auth()->id();
        $enquiry = \Modules\Enquiries\Models\Enquiry::with([
            'items' => function($q) {
                $q->with([
                    'hoarding' => function($q) {
                        $q->with('media');
                    },
                    'package'
                ]);
            },
            'customer'
        ])->findOrFail($id);

        // Only allow if vendor has access to this enquiry
        $hasAccess = $enquiry->items->contains(function($item) use ($vendorId) {
            return $item->hoarding?->vendor_id === $vendorId;
        });
        if (!$hasAccess) {
            abort(403, 'Unauthorized');
        }

        $enquiry = $this->enrichEnquiryData($enquiry);
        return view('vendor.enquiries.show', compact('enquiry'));
    }
    
    public function index(Request $request)
    {
        // Fetch all enquiries for vendor's hoardings with search and filter
        $vendorId = auth()->id();
        $query = Enquiry::whereHas('items.hoarding', function($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        });

        // SEARCH by enquiry id or customer info
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('id', $search);
            });
        }

        // FILTER by created_at date
        $dateFilter = $request->input('date_filter', 'all');
        if ($dateFilter && $dateFilter !== 'all') {
            if ($dateFilter === 'last_week') {
                $query->where('created_at', '>=', now()->subWeek());
            } elseif ($dateFilter === 'last_month') {
                $query->where('created_at', '>=', now()->subMonth());
            } elseif ($dateFilter === 'last_year') {
                $query->where('created_at', '>=', now()->subYear());
            } elseif ($dateFilter === 'custom') {
                $from = $request->input('from_date');
                $to = $request->input('to_date');
                if ($from && $to) {
                    $query->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()]);
                } elseif ($from) {
                    $query->where('created_at', '>=', Carbon::parse($from)->startOfDay());
                } elseif ($to) {
                    $query->where('created_at', '<=', Carbon::parse($to)->endOfDay());
                }
            }
        }

        $enquiries = $query
            ->with([
                'items' => function($q) {
                    $q->with([
                        'hoarding' => function($q) {
                            $q->with('media');
                        },
                        'package'
                    ]);
                },
                'customer'
            ])
            ->latest()
            ->paginate(10)
            ->appends($request->all());

        // Process each enquiry with additional data
        $enquiries->getCollection()->transform(function($enquiry) {
            return $this->enrichEnquiryData($enquiry);
        });

        return view('vendor.enquiries.index', compact('enquiries'));
    }

    /**
     * Enrich enquiry with calculated data
     */
    private function enrichEnquiryData($enquiry)
    {
        $vendorId = auth()->id();
        
        // SECURITY: Filter items to show only current vendor's hoardings
        $enquiry->items = $enquiry->items->filter(function($item) use ($vendorId) {
            return $item->hoarding?->vendor_id === $vendorId;
        })->values();
        
        // Get locations
        $enquiry->locations = $enquiry->items
            ->map(function($item) {
                return $item->hoarding?->locality;
            })
            ->filter()
            ->unique()
            ->values();

        // Enrich each item with image URL
        $enquiry->items = $enquiry->items->map(function($item) {
            if ($item->hoarding_type === 'dooh') {
                // For DOOH: Get image from dooh_screen_media table via hoarding_id
                $doohMedia = \DB::table('dooh_screen_media')
                    ->where('dooh_screen_id', function($q) use ($item) {
                        $q->select('id')
                            ->from('dooh_screens')
                            ->where('hoarding_id', $item->hoarding_id)
                            ->limit(1);
                    })
                    ->where('is_primary', 1)
                    ->orWhere(function($q) use ($item) {
                        $q->where('dooh_screen_id', function($q2) use ($item) {
                            $q2->select('id')
                                ->from('dooh_screens')
                                ->where('hoarding_id', $item->hoarding_id)
                                ->limit(1);
                        });
                    })
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('sort_order', 'asc')
                    ->first();
                
                $item->image_url = $doohMedia ? asset('storage/' . $doohMedia->file_path) : null;
            } else {
                // For OOH: Get image from hoarding_media table
                $oohMedia = \DB::table('hoarding_media')
                    ->where('hoarding_id', $item->hoarding_id)
                    ->where('is_primary', 1)
                    ->orWhere(function($q) use ($item) {
                        $q->where('hoarding_id', $item->hoarding_id);
                    })
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('sort_order', 'asc')
                    ->first();
                
                $item->image_url = $oohMedia ? asset('storage/' . $oohMedia->file_path) : null;
            }
            $item->package_name = '-';
            $item->discount_percent = '-';
            if ($item->hoarding_type === 'ooh' && !empty($item->package_id)) {
                $package = DB::table('hoarding_packages')
                    ->where('id', $item->package_id)
                    ->first();

                if ($package) {
                    $item->package = $package; // Ensure calculator gets correct package
                    $item->package_name = $package->package_name;
                    $item->discount_percent = $package->discount_percent;
                }
            }
            if ($item->hoarding_type === 'dooh' && !empty($item->package_id)) {
                $package = DB::table('dooh_packages')
                    ->where('id', $item->package_id)
                    ->first();

                if ($package) {
                    $item->package = $package; // Ensure calculator gets correct package
                    $item->package_name = $package->package_name;
                    $item->discount_percent = $package->discount_percent;
                }
            }
            // Final price calculation
            $item->final_price = \App\Services\EnquiryPriceCalculator::calculate($item);

            return $item;
        });

        // Get offer validity information
        $latestOffer = Offer::where('enquiry_id', $enquiry->id)->latest()->first();
        $enquiry->valid_till = $latestOffer?->expires_at ?? $latestOffer?->valid_until;
        
        // Calculate days left
        if ($enquiry->valid_till) {
            $daysLeft = now()->diffInDays($enquiry->valid_till, false);
            $enquiry->days_left = $daysLeft;
            $enquiry->validity_status = $daysLeft <= 0 ? 'expired' : ($daysLeft <= 3 ? 'expiring_soon' : 'valid');
        } else {
            $enquiry->validity_status = 'no_offer';
        }

        // Add status color mapping
        $enquiry->status_colors = [
            'draft' => ['text' => 'text-gray-600', 'bg' => 'bg-gray-50'],
            'submitted' => ['text' => 'text-blue-600', 'bg' => 'bg-blue-50'],
            'responded' => ['text' => 'text-orange-500', 'bg' => 'bg-orange-50'],
            'accepted' => ['text' => 'text-green-600', 'bg' => 'bg-green-50'],
            'rejected' => ['text' => 'text-red-500', 'bg' => 'bg-red-50'],
            'cancelled' => ['text' => 'text-red-600', 'bg' => 'bg-red-50'],
            'pending' => ['text' => 'text-yellow-600', 'bg' => 'bg-yellow-50'],
        ];
        
        $enquiry->status_color = $enquiry->status_colors[$enquiry->status] ?? $enquiry->status_colors['draft'];

        return $enquiry;
    }
}
