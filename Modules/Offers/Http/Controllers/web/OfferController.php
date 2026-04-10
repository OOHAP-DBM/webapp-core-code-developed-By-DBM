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
       CREATE — show form
    ════════════════════════════════════════════════════════════ */

    public function create(Request $request)
    {
        $enquiry      = null;
        $enquiryItems = collect();

        if ($request->filled('enquiry_id')) {
            $enquiry = Enquiry::with([
                'customer',
                'items.hoarding.doohScreen',
            ])->find($request->enquiry_id);

            if ($enquiry) {
                // Only items whose hoarding belongs to this vendor
                $enquiryItems = $enquiry->items
                    ->filter(fn($item) => $item->hoarding?->vendor_id === auth()->id())
                    ->values()
                    ->map(function ($item) {
                        // Attach primary image URL
                        $item->image_url = $this->resolveImageUrl($item);
                        return $item;
                    });
            }
        }

        return view('vendor.offers.create', compact('enquiry', 'enquiryItems'));
    }

    /* ════════════════════════════════════════════════════════════
       STORE — handle form submit (AJAX)
    ════════════════════════════════════════════════════════════ */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'enquiry_id'               => 'required|integer|exists:enquiries,id',
            'description'              => 'nullable|string|max:2000',
            'valid_days'               => 'nullable|integer|min:1|max:365',
            'items'                    => 'required|array|min:1',
            'items.*.enquiry_item_id'  => 'nullable|integer|exists:enquiry_items,id',
            'items.*.hoarding_id'      => 'required|integer|exists:hoardings,id',
            'items.*.hoarding_type'    => 'required|in:ooh,dooh',
            'items.*.preferred_start_date' => 'required|date',
            'items.*.preferred_end_date'   => 'required|date|after_or_equal:items.*.preferred_start_date',
            'items.*.offered_price'    => 'nullable|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.package_id'       => 'nullable|integer',
            'items.*.package_type'     => 'nullable|string',
            'items.*.package_label'    => 'nullable|string',
            'items.*.duration_months'  => 'nullable|integer|min:1',
        ]);

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

        try {
            $offer = $this->offerService->createOffer([
                'enquiry_id'  => $validated['enquiry_id'],
                'vendor_id'   => auth()->id(),
                'description' => $validated['description'] ?? null,
                'valid_days'  => $validated['valid_days'] ?? null,
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

        // Attach image URLs to items
        $offer->items->each(fn($item) => $item->image_url = $this->resolveImageUrl($item));

        return view('vendor.offers.show', compact('offer'));
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
        $search = $request->input('search');

        $customers = User::where('active_role', 'customer')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->when($search, fn($q) => $q->where(fn($sub) =>
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
            ))
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone']);

        return response()->json(['data' => $customers]);
    }

    /* ════════════════════════════════════════════════════════════
       PRIVATE HELPERS
    ════════════════════════════════════════════════════════════ */

    private function resolveImageUrl($item): ?string
    {
        if (!$item->hoarding) return null;

        if ($item->hoarding_type === 'ooh') {
            $media = DB::table('hoarding_media')
                ->where('hoarding_id', $item->hoarding->id)
                ->where('is_primary', 1)
                ->first();
            return $media ? asset('storage/' . $media->file_path) : null;
        }

        if ($item->hoarding_type === 'dooh') {
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