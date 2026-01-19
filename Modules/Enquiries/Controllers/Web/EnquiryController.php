<?php

namespace App\Http\Controllers\Web\Customer;
namespace Modules\Enquiries\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Enquiries\Models\Enquiry;
use Modules\Enquiries\Models\EnquiryItem;
use Carbon\Carbon;
use Illuminate\Container\Attributes\Log;
use Modules\DOOH\Models\DOOHPackage;
use Modules\Hoardings\Models\HoardingPackage;
use Modules\Cart\Services\CartService;
use Modules\Enquiries\Services\EnquiryService;


class EnquiryController extends Controller
{
    /* =====================================================
     | INDEX
     ===================================================== */
    /**
     * Apply customer-only middleware to all methods except store
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:customer'])->except(['store']);
    }

    public function index(Request $request)
    {
        if (!auth()->check() || !auth()->user()->hasRole('customer')) {
            return response()->json([
                'success' => false,
                'message' => 'Only customers can view enquiries.'
            ], 403);
        }
        $query = Enquiry::where('customer_id', Auth::id())
            // ->with(['items.hoarding', 'quotation'])
            ->with(['items.hoarding'])

            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enquiries = $query->paginate(10);

        return view('customer.enquiries.index', compact('enquiries'));
    }

    /* =====================================================
     | CREATE
     ===================================================== */
    public function create(Request $request)
    {
        // Role validation (redundant but defensive)
        if (!auth()->check() || !auth()->user()->hasRole('customer')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only customers can create enquiries.'
                ], 403);
            }
            return redirect()->route('login')
                ->with('error', 'Please login as a customer to create an enquiry.');
        }

        \Log::info('Enquiry Create Request', $request->all());
        $hoarding = null;
        $packages = collect();
        $pricingDisplay = null;
        $monthsField = [
            'editable' => true,
            'value' => null
        ];

        if ($request->filled('hoarding_id')) {
            $hoarding = Hoarding::with(['doohScreen', 'oohPackages'])->where('status', 'approved')
                ->findOrFail($request->hoarding_id);
            // Get only relevant packages (OOH or DOOH)
            if ($hoarding->hoarding_type === 'dooh' && $hoarding->doohScreen) {
                $packages = $hoarding->doohScreen->packages()->where('is_active', 1)->get();
            } else {
                $packages = $hoarding->oohPackages()->where('is_active', 1)->get();
            }
            // Prepare pricing display logic (do not multiply by months)
            if ($packages->count()) {
                $pricingDisplay = [
                    'type' => 'package',
                    'text' => 'Select a package to see price',
                    'packages' => $packages->map(function($pkg) use ($hoarding) {
                        // Show package price only, do not multiply by months
                        $price = $pkg->price_per_month ?? $pkg->price_per_day ?? 0;
                        return [
                            'id' => $pkg->id,
                            'name' => $pkg->package_name,
                            'min_booking_duration' => $pkg->min_booking_duration,
                            'discount_percent' => $pkg->discount_percent,
                            'price' => $price,
                        ];
                    })
                ];
            } elseif ($hoarding->monthly_price) {
                $pricingDisplay = [
                    'type' => 'monthly',
                    'price' => $hoarding->monthly_price,
                    'text' => 'Monthly price',
                ];
            } elseif ($hoarding->base_monthly_price) {
                $pricingDisplay = [
                    'type' => 'base_monthly',
                    'price' => $hoarding->base_monthly_price,
                    'text' => 'Base monthly price',
                ];
            } elseif ($hoarding->doohScreen && $hoarding->doohScreen->price_per_10_sec_slot) {
                $pricingDisplay = [
                    'type' => 'slot',
                    'price' => $hoarding->doohScreen->price_per_10_sec_slot,
                    'text' => 'This is price per 10-second slot. Final price depends on slot duration and loop.',
                ];
            }
        }

        return view('customer.enquiry-create', compact('hoarding', 'packages', 'pricingDisplay', 'monthsField'));
    }

    public function store(Request $request, EnquiryService $enquiryService)
    {
        // 🔒 ENFORCE CUSTOMER ROLE (CRITICAL BACKEND VALIDATION)
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to submit an enquiry.'
            ], 401);
        }

        \Log::info('========== ENQUIRY MODAL POST DATA ==========', $request->all());
        
        return $enquiryService->createEnquiry($request);
    }
    // public function store(Request $request)
    // {
    //     logger('ENQUIRY REQUEST RAW', $request->all());

    //     $validated = $request->validate([
    //         'hoarding_id'          => 'required',
    //         'hoarding_id.*'        => 'exists:hoardings,id',
    //         'package_id'           => 'nullable',
    //         'package_id.*'         => 'nullable|exists:hoarding_packages,id',
    //         'package_label'        => 'nullable',
    //         'amount'               => 'required',
    //         'amount.*'             => 'numeric|min:0',
    //         'duration_type'        => 'required|string',
    //         'preferred_start_date' => 'required|date',
    //         'preferred_end_date'   => 'nullable|date',
    //         'customer_name'        => 'required|string',
    //         'customer_mobile'      => 'nullable|string',
    //         'customer_email'       => 'nullable|email',
    //         'message'              => 'nullable|string',

    //         // DOOH-specific fields
    //         'video_duration'       => 'nullable|integer|in:15,30',
    //         'slots_count'          => 'nullable|integer|min:1',
    //         'slot'                 => 'nullable|string',
    //         'duration_days'        => 'nullable|integer|min:1',
    //     ]);

    //     // Normalize inputs (Handles both single string or array inputs)
    //     $hoardingIds   = (array) ($validated['hoarding_id'] ?? []);
    //     $packageIds    = (array) ($validated['package_id'] ?? []);
    //     $packageLabels = (array) ($validated['package_label'] ?? []);
    //     $amounts       = (array) ($validated['amount'] ?? []);

    //     return DB::transaction(function () use ($validated, $hoardingIds, $packageIds, $packageLabels, $amounts) {
    //         $user = auth()->user();
    //         $customerEmail = $validated['customer_email'] ?? $user->email ?? null;

    //         // 1. Create the Main Enquiry Header
    //         $enquiry = Enquiry::create([
    //             'customer_id'    => $user->id,
    //             'source'         => $user->role ?? 'user',
    //             'status'         => Enquiry::STATUS_SUBMITTED,
    //             'customer_note'  => $validated['message'] ?? null,
    //             'contact_number' => $validated['customer_mobile'],
    //             'customer_email' => $customerEmail,
    //         ]);

    //         $vendorGroups = []; // Used to group items by vendor to avoid duplicate emails

    //         // 2. Process each Hoarding Item
    //         foreach ($hoardingIds as $index => $hoardingId) {
    //             $hoarding = Hoarding::with('vendor')->findOrFail($hoardingId);
    //             $startDate = Carbon::parse($validated['preferred_start_date']);

    //             $packageId    = $packageIds[$index] ?? null;
    //             $packageLabel = $packageLabels[$index] ?? 'Base Price';
    //             $amount       = $amounts[$index] ?? 0;

    //             /* --- Handle Package & Duration Logic --- */
    //             if (!empty($packageId)) {
    //                 $package = HoardingPackage::findOrFail($packageId);
    //                 $endDate = (clone $startDate)->addMonths($package->min_booking_duration);
    //                 $packageType = 'package';
    //                 // Industry Detail: Fetch services included in package
    //                 $serviceNames = is_string($package->services_included) ? json_decode($package->services_included, true) : ($package->services_included ?? []);
    //                 $priceMap = is_string($hoarding->service_prices) ? json_decode($hoarding->service_prices, true) : ($hoarding->service_prices ?? []);
    //                 $services = $this->buildServicesWithPrice($serviceNames, $priceMap);
    //             } else {
    //                 $endDate = !empty($validated['preferred_end_date']) ? Carbon::parse($validated['preferred_end_date']) : (clone $startDate)->addMonth();
    //                 $packageType = 'base';
    //                 $services = $this->buildBaseOOHServices($hoarding);
    //             }
    //             $expectedDuration = $startDate->diffInDays($endDate) . ' days';

    //             /* --- Construct Industry-Level Meta --- */
    //             $meta = [
    //                 'package_label'   => $packageLabel,
    //                 'amount'          => $amount,
    //                 'duration_type'   => $validated['duration_type'],
    //                 'customer_name'   => $validated['customer_name'],
    //                 'customer_email'  => $customerEmail,
    //                 'customer_mobile' => $validated['customer_mobile'],
    //             ];

    //             // Add DOOH Specs specifically if applicable
    //             if ($hoarding->hoarding_type === 'dooh') {
    //                 $meta['dooh_specs'] = [
    //                     'video_duration' => $validated['video_duration'] ?? 15,
    //                     'slots_per_day'  => $validated['slots_count'] ?? 120,
    //                     'loop_interval'  => $validated['slot'] ?? 'Standard Loop',
    //                     'total_days'     => $validated['duration_days'] ?? $startDate->diffInDays($endDate)
    //                 ];
    //             }

    //             // 3. Save the Enquiry Item
    //             $enquiryItem = EnquiryItem::create([
    //                 'enquiry_id'           => $enquiry->id,
    //                 'hoarding_id'          => $hoarding->id,
    //                 'hoarding_type'        => $hoarding->hoarding_type,
    //                 'package_id'           => $packageId,
    //                 'package_type'         => $packageType,
    //                 'preferred_start_date' => $startDate,
    //                 'preferred_end_date'   => $endDate,
    //                 'expected_duration'    => $expectedDuration,
    //                 'services'             => $services,
    //                 'meta'                 => $meta,
    //                 'status'               => EnquiryItem::STATUS_NEW,
    //             ]);

    //             // Group by Vendor for consolidated notifications
    //             if ($hoarding->vendor_id) {
    //                 $vendorGroups[$hoarding->vendor_id][] = $enquiryItem;
    //             }
    //         }

    //         // 4. Send Consolidation Notifications
    //         foreach ($vendorGroups as $vendorId => $items) {
    //             $vendor = \App\Models\User::find($vendorId);
    //             if ($vendor) {
    //                 // We pass the whole array of items so the vendor gets ONE email listing all hoardings
    //                 $vendor->notify(new \Modules\Enquiries\Notifications\VendorEnquiryNotification($enquiry, $items));
    //             }
    //         }

    //         // Notify Admin with the full enquiry context
    //         $admin = \App\Models\User::where('active_role', 'admin')->first();
    //         if ($admin) {
    //             $admin->notify(new \Modules\Enquiries\Notifications\AdminEnquiryNotification($enquiry));
    //         }

    //         return response()->json([
    //             'success'    => true,
    //             'enquiry_id' => $enquiry->id,
    //             'message'    => 'Enquiry submitted successfully to vendors',
    //         ]);
    //     });
    // }




    /* =====================================================
     | SHOW
     ===================================================== */
    public function show($id)
    {
        $enquiry = Enquiry::where('customer_id', Auth::id())
            ->with(['items.hoarding.vendor', 'customer', 'offers'])
            ->findOrFail($id);
        
        $enquiry = $this->enrichEnquiryDataForCustomer($enquiry);
        
        return view('customer.enquiries.show', compact('enquiry'));
    }

    /**
     * Enrich enquiry data with image URLs from media tables
     */
    private function enrichEnquiryDataForCustomer($enquiry)
    {
        $enquiry->load(['items' => function ($query) {
            $query->with(['hoarding']);
        }]);

        foreach ($enquiry->items as $item) {
            // Get image URL based on hoarding type
            if ($item->hoarding) {
                if ($item->hoarding->hoarding_type === 'ooh') {
                    // OOH: Get from hoarding_media table
                    $media = DB::table('hoarding_media')
                        ->where('hoarding_id', $item->hoarding->id)
                        ->where('is_primary', true)
                        ->first();
                    $item->image_url = $media ? asset('storage/' . $media->file_path) : null;
                } else if ($item->hoarding->hoarding_type === 'dooh') {
                    // DOOH: Get from dooh_screen_media table via dooh_screen_id on hoarding
                    $doohScreenId = $item->hoarding->dooh_screen_id ?? ($item->hoarding->doohScreen->id ?? null);
                    if ($doohScreenId) {
                        $media = DB::table('dooh_screen_media')
                            ->where('dooh_screen_id', $doohScreenId)
                            ->orderBy('is_primary', 'desc')
                            ->orderBy('sort_order', 'asc')
                            ->first();
                        $item->image_url = $media ? asset('storage/' . $media->file_path) : null;
                    } else {
                        $item->image_url = null;
                    }
                } else {
                    $item->image_url = null;
                }
            } else {
                $item->image_url = null;
            }
            $item->package_name = '-';
            $item->discount_percent = '-';

            if ($item->hoarding_type === 'ooh' && !empty($item->package_id)) {
                $package = DB::table('hoarding_packages')
                    ->where('id', $item->package_id)
                    ->first();

                if ($package) {
                    $item->package_name = $package->package_name;
                    $item->discount_percent = $package->discount_percent;
                }
            }
            if ($item->hoarding_type === 'dooh' && !empty($item->package_id)) {
                $package = DB::table('dooh_packages')
                    ->where('id', $item->package_id)
                    ->first();

                if ($package) {
                    $item->package_name = $package->package_name;
                    $item->discount_percent = $package->discount_percent;
                }
            }

            // Attach final price using EnquiryPriceCalculator
            $item->final_price = \App\Services\EnquiryPriceCalculator::calculate($item);
        }

        return $enquiry;
    }

    /* =====================================================
     | CANCEL
     ===================================================== */
    public function cancel(int $id)
    {
        $enquiry = Enquiry::where('customer_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $enquiry->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Enquiry cancelled successfully',
        ]);
    }

    /* =====================================================
     | SERVICE PRICE BUILDER (CORE)
     ===================================================== */
    private function buildServicesWithPrice(array $serviceNames, array $priceMap = []): array
    {
        $services = [];

        foreach ($serviceNames as $service) {
            $price = (int) ($priceMap[$service] ?? 0);

            $services[] = [
                'name'  => $service,
                'price' => $price,
                'type'  => $price > 0 ? 'paid' : 'free',
            ];
        }

        return $services;
    }
    private function buildBaseOOHServices(Hoarding $hoarding): array
    {
        $services = [];

        // Graphics
        if ((int) $hoarding->graphics_included === 1) {
            $services[] = [
                'name'  => 'graphics',
                'price' => 0,
                'type'  => 'free',
            ];
        } elseif (!empty($hoarding->graphics_charge) && $hoarding->graphics_charge > 0) {
            $services[] = [
                'name'  => 'graphics',
                'price' => (int) $hoarding->graphics_charge,
                'type'  => 'paid',
            ];
        }

        // Survey
        if (!empty($hoarding->survey_charge) && $hoarding->survey_charge > 0) {
            $services[] = [
                'name'  => 'survey',
                'price' => (int) $hoarding->survey_charge,
                'type'  => 'paid',
            ];
        }

        return $services;
    }
    public function shortlisted(CartService $cartService)
    {
        return response()->json(
            $cartService->getCartForUI()
        );
    }
    public function packages($id)
    {
        return DB::table('hoarding_packages')
            ->where('hoarding_id', $id)
            ->where('is_active', 1)
            ->select(
                'id',
                'package_name',
                'discount_percent',
                'duration',
                'duration_unit'
            )
            ->get();
    }
}
