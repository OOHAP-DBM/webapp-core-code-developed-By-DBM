<?php

namespace Modules\Offers\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Hoarding;
use App\Models\User;
use Modules\Enquiries\Models\Enquiry;
use Modules\Offers\Services\OfferService;

class OfferController extends Controller
{
    public function __construct(
        protected OfferService $offerService
    ) {}

    /* ════════════════════════════════════════════════════════════
       INDEX
    ════════════════════════════════════════════════════════════ */

    public function index(Request $request)
    {
        $offers = $this->offerService->getVendorOffers(auth()->id());
        return view('offers::vendor.offers.index', compact('offers'));
    }

    /* ════════════════════════════════════════════════════════════
       CREATE — show form
    ════════════════════════════════════════════════════════════ */

    public function create(Request $request)
    {
        $enquiry          = null;
        $enquiryItems     = collect();
        $enquiryItemsJson = '[]';

        if ($request->filled('enquiry_id')) {
            $enquiry = Enquiry::with([
                'customer',
                'items.hoarding.doohScreen',
            ])->find($request->enquiry_id);

            if ($enquiry) {
                $vendorId = auth()->id();

                $enquiryItems = $enquiry->items
                    ->filter(fn($item) => $item->hoarding?->vendor_id === $vendorId)
                    ->values()
                    ->map(function ($item) {
                        $item->image_url = $this->resolveImageUrl($item);
                        return $item;
                    });

                $enquiryItemsJson = json_encode(
                    $enquiryItems->map(fn($item) => [
                        'id'                   => $item->id,
                        'hoarding_id'          => $item->hoarding_id,
                        'hoarding_type'        => $item->hoarding_type,
                        'package_id'           => $item->package_id,
                        'package_type'         => $item->package_type,
                        'package_label'        => $item->package_label,
                        'preferred_start_date' => $item->preferred_start_date?->format('Y-m-d'),
                        'preferred_end_date'   => $item->preferred_end_date?->format('Y-m-d'),
                        'duration_months'      => $item->duration_months,
                        'services'             => $item->services,
                        'hoarding' => $item->hoarding ? [
                            'id'                  => $item->hoarding->id,
                            'title'               => $item->hoarding->title ?? $item->hoarding->name,
                            'price_per_month'     => $item->hoarding->price_per_month ?? $item->hoarding->monthly_rental ?? 0,
                            'display_location'    => $item->hoarding->display_location ?? $item->hoarding->city ?? '',
                            'total_slots_per_day' => $item->hoarding->doohScreen->total_slots_per_day ?? 300,
                            'image_url'           => $item->image_url ?? null,
                        ] : null,
                    ])->values()->all(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            }
        }

        // Fetch vendor's active hoardings for inventory panel
        $hoardings = Hoarding::with([
                'hoardingMedia.is_primary',
                'doohScreen.media',
            ])
            ->where('vendor_id', auth()->id())
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        return view('offers::vendor.offers.create', compact(
            'enquiry',
            'enquiryItems',
            'enquiryItemsJson',
            'hoardings',
        ));
    }

    /* ════════════════════════════════════════════════════════════
       STORE — handle form submit (AJAX)
    ════════════════════════════════════════════════════════════ */

    public function store(Request $request)
    {
        $validated = $request->validate([
            // enquiry_id is nullable — vendor can create offer without an enquiry
            'enquiry_id'               => 'nullable|integer|exists:enquiries,id',
            'customer_id'              => 'nullable|integer|exists:users,id',
            'description'              => 'nullable|string|max:2000',
            'valid_days'               => 'nullable|integer|min:1|max:365',
            'valid_till'               => 'nullable|date',
            'send_via'                 => 'nullable|array',
            'send_via.*'               => 'in:email,whatsapp',
            'items'                    => 'required|array|min:1',
            'items.*.enquiry_item_id'  => 'nullable|integer|exists:enquiry_items,id',
            'items.*.hoarding_id'      => 'required|integer|exists:hoardings,id',
            'items.*.hoarding_type'    => 'required|in:ooh,dooh,OOH,DOOH',
            'items.*.preferred_start_date' => 'required|date',
            'items.*.preferred_end_date'   => 'required|date|after_or_equal:items.*.preferred_start_date',
            'items.*.offered_price'    => 'nullable|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.package_id'       => 'nullable|integer',
            'items.*.package_type'     => 'nullable|string|max:255',
            'items.*.package_label'    => 'nullable|string|max:255',
            'items.*.duration_months'  => 'nullable|integer|min:1',
        ]);

        // Normalise hoarding_type to lowercase
        foreach ($validated['items'] as &$itemData) {
            $itemData['hoarding_type'] = strtolower($itemData['hoarding_type']);
        }
        unset($itemData);

        // Ensure vendor owns all hoardings
        $hoardingIds = collect($validated['items'])->pluck('hoarding_id');
        $unauthorized = Hoarding::whereIn('id', $hoardingIds)
            ->where('vendor_id', '!=', auth()->id())
            ->exists();

        if ($unauthorized) {
            return response()->json([
                'success' => false,
                'message' => 'One or more hoardings do not belong to you.',
            ], 403);
        }

        // Resolve valid_days from valid_till if provided
        $validDays = $validated['valid_days'] ?? 30;
        if (!empty($validated['valid_till'])) {
            $validDays = max(1, now()->diffInDays($validated['valid_till']));
        }

        try {
            $offer = $this->offerService->createOffer([
                'enquiry_id'  => $validated['enquiry_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'vendor_id'   => auth()->id(),
                'description' => $validated['description'] ?? null,
                'valid_days'  => $validDays,
                'status'      => 'draft',
                'items'       => $validated['items'],
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'Offer created as draft.',
                'offer_id' => $offer->id,
                'version'  => $offer->version,
                'redirect' => route('vendor.offers.show', $offer->id),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /* ════════════════════════════════════════════════════════════
       SEND OFFER
    ════════════════════════════════════════════════════════════ */

    public function send(Request $request, int $offerId)
    {
        try {
            $offer = $this->offerService->sendOffer($offerId);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Offer sent to customer.',
                    'status'  => $offer->status,
                ]);
            }

            return redirect()
                ->route('vendor.offers.show', $offerId)
                ->with('success', 'Offer sent successfully.');

        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /* ════════════════════════════════════════════════════════════
       SHOW
    ════════════════════════════════════════════════════════════ */

    public function show(int $offerId)
    {
        $offer = $this->offerService->find($offerId);

        if (!$offer) {
            abort(404);
        }

        // Ensure vendor can only see their own offers
        if ($offer->vendor_id !== auth()->id()) {
            abort(403);
        }

        $offer->items->each(fn($item) => $item->image_url = $this->resolveImageUrl($item));

        return view('offers::vendor.offers.show', compact('offer'));
    }

    /* ════════════════════════════════════════════════════════════
       DELETE DRAFT
    ════════════════════════════════════════════════════════════ */

    public function destroy(int $offerId)
    {
        try {
            $this->offerService->deleteDraft($offerId, auth()->id());
            return response()->json(['success' => true, 'message' => 'Draft deleted.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /* ════════════════════════════════════════════════════════════
       CUSTOMER SUGGESTIONS (AJAX)
    ════════════════════════════════════════════════════════════ */

    public function customerSuggestions(Request $request)
    {
        $search = $request->input('search', '');

        $customers = User::where('active_role', 'customer')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->when($search, fn($q) => $q->where(fn($sub) =>
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
            ))
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone', 'business_name', 'gstin', 'address']);

        return response()->json(['data' => $customers]);
    }

    /* ════════════════════════════════════════════════════════════
       PRIVATE HELPERS
    ════════════════════════════════════════════════════════════ */

    private function resolveImageUrl($item): ?string
    {
        if (!$item->hoarding) return null;

        $hoardingType = strtolower($item->hoarding_type ?? 'ooh');

        if ($hoardingType === 'ooh') {
            $media = DB::table('hoarding_media')
                ->where('hoarding_id', $item->hoarding->id)
                ->where('is_primary', 1)
                ->first();

            if (!$media) {
                // Fall back to any media
                $media = DB::table('hoarding_media')
                    ->where('hoarding_id', $item->hoarding->id)
                    ->orderByDesc('is_primary')
                    ->first();
            }

            return $media ? asset('storage/' . $media->file_path) : null;
        }

        if ($hoardingType === 'dooh') {
            $doohScreenId = optional($item->hoarding->doohScreen)->id;
            if (!$doohScreenId) return null;

            $media = DB::table('dooh_screen_media')
                ->where('dooh_screen_id', $doohScreenId)
                ->orderByDesc('is_primary')
                ->first();

            return $media ? asset('storage/' . $media->file_path) : null;
        }

        return null;
    }
}