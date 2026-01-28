<?php

namespace Modules\Hoardings\Http\Controllers\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Modules\Hoardings\Services\HoardingService;
use App\Models\Hoarding;
use Illuminate\Support\Facades\Storage;

/**
 * Vendor HoardingController
 * Handles Add Hoardings flow (OOH/DOOH type selection)
 * Architectural rules strictly enforced:
 * - DOOH logic is delegated to DOOH module
 * - OOH logic handled here only
 * - Onboarding status checked before all actions
 */
class HoardingController extends Controller
{
    protected $hoardingService;

    public function __construct(HoardingService $hoardingService)
    {
        $this->hoardingService = $hoardingService;
    }

    /**
     * Show hoarding type selection screen (OOH/DOOH)
     * GET /vendor/hoardings/add
     */
    public function showTypeSelection(Request $request)
    {
        // Sidebar highlight: 'add-hoardings' (passed to view)
        return view('hoardings.vendor.add_type_selection', [
            'sidebarActive' => 'add-hoardings',
        ]);
    }

    /**
     * Handle hoarding type selection (OOH/DOOH)
     * POST /vendor/hoardings/select-type
     */
    public function handleTypeSelection(Request $request)
    {
        $user = Auth::user();
        $vendorProfile = $user->vendor_profile ?? null;
        // if (!$vendorProfile || $vendorProfile->onboarding_status !== 'approved') {
        //     // Block access if not approved
        //     return Redirect::route('vendor.onboarding.waiting')
        //         ->with('error', 'Your vendor onboarding is under review. You can add hoardings only after approval.');
        // }
        $type = $request->input('hoarding_type');
        if ($type === 'DOOH') {
            // DOOH: Redirect to DOOH module (NO business logic here)
            return Redirect::route('vendor.dooh.create'); // Vendor DOOH creation route
        }
        if ($type === 'OOH') {
            // OOH: Continue OOH flow, create draft
            Session::put('hoarding_type', 'OOH');
            return Redirect::route('vendor.hoardings.create'); // Existing OOH create route
        }
        // Invalid type: redirect back
        return Redirect::back()->with('error', 'Please select a valid hoarding type.');
    }

    public function index(Request $request)
    {
        $vendor = Auth::user();

        $query = $vendor->hoardings()->with('media');

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('city', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('media_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $listings = $query->latest()->paginate(20);

        // Get unique cities for filter
        $cities = $vendor->hoardings()
            ->select('city')
            ->distinct()
            ->pluck('city');

        return view('vendor.listings.index', compact('listings', 'cities'));
    }

    /**
     * Unified edit entry point - automatically routes to correct controller based on hoarding_type
     * GET /vendor/hoardings/{id}/edit
     */
    public function edit($id)
    {
        $vendor = Auth::user();

        // Find the hoarding and verify ownership
        $hoarding = $vendor->hoardings()->findOrFail($id);
        // Log the edit access attempt
        \Log::info('Vendor accessing hoarding edit', [
            'vendor_id' => $vendor->id,
            'hoarding_id' => $hoarding->id,
            'hoarding_type' => $hoarding->hoarding_type,
            'status' => $hoarding->status
        ]);
       
        // Route to appropriate controller based on hoarding_type
        switch (strtolower($hoarding->hoarding_type)) {
          
            case 'ooh':
                // Get the OOH hoarding child record
                $oohHoarding = $hoarding->oohHoarding;
                // dd($oohHoarding);

                if (!$oohHoarding) {
                    return redirect()
                        ->route('vendor.hoardings.myHoardings')
                        ->with('error', 'OOH hoarding data not found. Please contact support.');
                }

                // Redirect to OOH-specific edit with step parameter if present
                return redirect()->route('vendor.edit.ooh', [
                    'id' => $oohHoarding->id,
                    'step' => request('step', 1)
                ]);

            case 'dooh':
                // Get the DOOH screen child record
                $doohScreen = $hoarding->doohScreen;

                if (!$doohScreen) {
                    return redirect()
                        ->route('vendor.hoardings.myHoardings')
                        ->with('error', 'DOOH screen data not found. Please contact support.');
                }

                // Redirect to DOOH-specific edit with step parameter if present
                return redirect()->route('vendor.dooh.edit', [
                    'id' => $doohScreen->id,
                    'step' => request('step', 1)
                ]);

            default:
                \Log::error('Unknown hoarding type in edit', [
                    'hoarding_id' => $hoarding->id,
                    'type' => $hoarding->hoarding_type
                ]);

                return redirect()
                    ->route('vendor.hoardings.myHoardings')
                    ->with('error', 'Invalid hoarding type. Please contact support.');
        }
    }

    // public function update(Request $request, $id)
    // {
    //     $vendor = Auth::user();
    //     $listing = $vendor->hoardings()->findOrFail($id);

    //     $validated = $request->validate([
    //         // 'title' => 'required|string|max:255',
    //         // 'description' => 'required|string',
    //         'base_monthly_price' => 'required|numeric|min:0',
    //         // Add other validation rules as needed
    //         'primary_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
    //         'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
    //     ]);

    //     // Handle primary image update
    //     if ($request->hasFile('primary_image')) {
    //         if ($listing->primary_image) {
    //             \Storage::disk('public')->delete($listing->primary_image);
    //         }
    //         $validated['primary_image'] = $request->file('primary_image')->store('hoardings', 'public');
    //     }

    //     // Handle gallery images update (optional, simplistic: remove all, add new)
    //     if ($request->hasFile('gallery_images')) {
    //         foreach ($listing->galleryImages as $image) {
    //             \Storage::disk('public')->delete($image->image_path);
    //             $image->delete();
    //         }
    //         foreach ($request->file('gallery_images') as $file) {
    //             $listing->galleryImages()->create([
    //                 'image_path' => $file->store('hoardings/gallery', 'public'),
    //             ]);
    //         }
    //     }

    //     $listing->update($validated);

    //     return redirect()
    //         ->route('hoardings.vendor.index')
    //         ->with('success', 'Listing updated successfully!');
    // }

    public function destroy($id)
    {
        $vendor = Auth::user();
        $listing = $vendor->hoardings()->findOrFail($id);

        // Delete images
        if ($listing->primary_image) {
            Storage::disk('public')->delete($listing->primary_image);
        }

        foreach ($listing->galleryImages as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $listing->delete();

        return response()->json(['success' => true]);
    }

    public function bulkUpdate()
    {
        $vendor = Auth::user();

        $listings = $vendor->hoardings()
            ->select('id', 'title', 'city', 'state', 'base_monthly_price', 'status')
            ->get();

        $cities = $vendor->hoardings()
            ->select('city')
            ->distinct()
            ->pluck('city');

        return view('hoardings.vendor.bulk-update', compact('listings', 'cities'));
    }

    public function bulkUpdateSubmit(Request $request)
    {
        $vendor = Auth::user();

        $validated = $request->validate([
            'selection_method' => 'required|in:manual,filter,all',
            'selected_ids' => 'required_if:selection_method,manual',
            'update_fields' => 'required|array',
        ]);

        // Get listings to update
        $query = $vendor->hoardings();

        if ($validated['selection_method'] === 'manual') {
            $ids = explode(',', $request->selected_ids);
            $query->whereIn('id', $ids);
        } elseif ($validated['selection_method'] === 'filter') {
            if ($request->filled('filter_city')) {
                $query->where('city', $request->filter_city);
            }
            if ($request->filled('filter_type')) {
                $query->where('media_type', $request->filter_type);
            }
            if ($request->filled('filter_status')) {
                $query->where('status', $request->filter_status);
            }
        }
        // 'all' doesn't need additional filters

        $updateData = [];

        // Handle price update
        if (in_array('price', $validated['update_fields'])) {
            $priceMethod = $request->price_method;
            $priceValue = $request->price_value;

            if ($priceMethod === 'fixed') {
                $updateData['base_monthly_price'] = $priceValue;
            } else {
                // For percentage/amount changes, need to update individually
                $listings = $query->get();
                foreach ($listings as $listing) {
                    $newPrice = $listing->base_monthly_price;

                    if ($priceMethod === 'increase_percent') {
                        $newPrice = $listing->base_monthly_price * (1 + $priceValue / 100);
                    } elseif ($priceMethod === 'decrease_percent') {
                        $newPrice = $listing->base_monthly_price * (1 - $priceValue / 100);
                    } elseif ($priceMethod === 'increase_amount') {
                        $newPrice = $listing->base_monthly_price + $priceValue;
                    } elseif ($priceMethod === 'decrease_amount') {
                        $newPrice = $listing->base_monthly_price - $priceValue;
                    }

                    $listing->update(['base_monthly_price' => max(0, $newPrice)]);
                }
            }
        }

        // Handle other fields
        if (in_array('status', $validated['update_fields'])) {
            $updateData['status'] = $request->status_value;
        }

        if (in_array('illumination', $validated['update_fields'])) {
            $updateData['illumination'] = $request->illumination_value === 'illuminated';
        }

        if (in_array('featured', $validated['update_fields'])) {
            $updateData['is_featured'] = $request->featured_value === 'mark_featured';
        }

        if (in_array('availability', $validated['update_fields'])) {
            $updateData['available_from'] = $request->available_from;
        }

        // Apply bulk update
        if (!empty($updateData)) {
            $query->update($updateData);
        }

        return redirect()
            ->route('hoardings.vendor.index')
            ->with('success', 'Listings updated successfully!');
    }

    public function myHoardings(Request $request)
    {
        $vendor = Auth::user();
        $activeTab = $request->query('tab', 'all'); // Detect which tab is active

        // 1. Check if the vendor has ANY hoardings at all (to show empty state)
        $totalCount = Hoarding::where('vendor_id', $vendor->id)->count();
        if ($totalCount === 0) {
            return view('hoardings.vendor.empty');
        }

        // 2. Fetch data based on the active tab
        if ($activeTab === 'draft') {
            $data = Hoarding::where('vendor_id', $vendor->id)
                ->where('status', 'Draft')
                ->orderBy('updated_at', 'desc')
                ->paginate(10);
        } else {
            $filters = [
                'vendor_id' => $vendor->id,
                'status' => ['active', 'inactive', 'pending_approval'],
                'order_by' => 'updated_at',
                'order_dir' => 'desc',
            ];
            // Using your service for published/live hoardings
            $data = $this->hoardingService->getAll($filters, 10);
        }

        // 3. Map the data so the Blade file stays clean
        // We use "through" on paginated items to keep pagination links working
        $hoardingList = $data->getCollection()->map(function ($h) {
            return [
                'id' => $h->id,
                'title' => $h->title,
                'hoarding_type' => $h->hoarding_type,
                'location' => $h->locality . ', ' . $h->city,
                'bookings_count' => $h->bookings_count ?? 0,
                'status' => ucfirst($h->status === "active" ? 'Published' : $h->status),
            ];
        });

        // Update the collection inside the paginator
        $data->setCollection($hoardingList);

        return view('hoardings.vendor.list', [
            'hoardings' => $data,
            'activeTab' => $activeTab
        ]);
    }

    public function toggleStatus($id)
    {
        $hoarding = Hoarding::where('vendor_id', Auth::id())->findOrFail($id);

        // Toggle logic
        $hoarding->status = ($hoarding->status === 'active') ? 'inactive' : 'active';
        $hoarding->save();

        return back()->with('success', 'Hoarding status updated successfully.');
    }
    /**
     * Show all hoardings with completion percentage (API or web).
     */
    public function indexCompletion()
    {
        $vendor = Auth::user();
        $hoardings = $vendor->hoardings()->with(['ooh.packages', 'ooh.brandLogos', 'doohScreen.packages', 'doohScreen.brandLogos'])->get();
        $completionService = app(\App\Services\HoardingCompletionService::class);
        $data = $hoardings->map(function ($hoarding) use ($completionService) {
            return [
                'id' => $hoarding->id,
                'title' => $hoarding->title,
                'completion' => $completionService->calculateCompletion($hoarding),
            ];
        });
        // Return as JSON for API, or pass to view for web
        if (request()->wantsJson()) {
            return response()->json(['data' => $data]);
        }
        return view('hoardings.vendor.completion', ['hoardings' => $data]);
    }

    /**
     * Display the specified hoarding.
     */
    public function show(int $id)
    {

        $hoarding = $this->hoardingService->getById($id);

        if (!$hoarding || $hoarding->vendor_id !== auth()->id()) {
            abort(404);
        }

        return view('hoardings.vendor.show', compact('hoarding'));
    }

    /**
     * Preview hoarding before publishing
     * Vendor can see how the hoarding looks before making it live
     */
    public function preview(int $id)
    {
        $hoarding = $this->hoardingService->getById($id);

        if (!$hoarding || $hoarding->vendor_id !== auth()->id()) {
            abort(404);
        }

        // Only draft and preview hoardings can be previewed
        if (!in_array($hoarding->status, ['draft', 'preview'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft or preview hoardings can be previewed'
            ], 400);
        }

        // Move to preview status if in draft
        if ($hoarding->isDraft()) {
            $hoarding->moveToPreview();
        }

        // Generate preview token if not exists
        if (!$hoarding->preview_token) {
            $hoarding->generatePreviewToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Hoarding moved to preview',
            'preview_url' => route('hoarding.preview.show', ['token' => $hoarding->preview_token]),
            'hoarding' => [
                'id' => $hoarding->id,
                'title' => $hoarding->title,
                'status' => $hoarding->status,
                'preview_token' => $hoarding->preview_token,
            ]
        ]);
    }

    /**
     * Show public preview of hoarding via token
     */
    public function showPreview($token)
    {
        $hoarding = Hoarding::where('preview_token', $token)->firstOrFail();

        // Only published and preview hoardings can be viewed via preview token
        if (!in_array($hoarding->status, ['preview', 'published'])) {
            abort(403);
        }

        return view('hoardings.public.preview', compact('hoarding'));
    }

    /**
     * Publish hoarding (Auto-approve on publish)
     * Vendor confirms they want to make hoarding live
     */
    public function publish(int $id)
    {
        $vendor = Auth::user();
        $hoarding = $this->hoardingService->getById($id);

        if (!$hoarding || $hoarding->vendor_id !== $vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Hoarding not found'
            ], 404);
        }

        // Validate vendor has verified email and mobile
        if (!$vendor->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email address before publishing',
                'redirect' => route('vendor.profile.verify-email')
            ], 422);
        }

        if (!$vendor->isMobileVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your mobile number before publishing',
                'redirect' => route('vendor.profile.verify-mobile')
            ], 422);
        }

        // Only draft and preview hoardings can be published
        if (!$hoarding->canBeEdited()) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft or preview hoardings can be published'
            ], 400);
        }

        // Validate hoarding data completeness (optional - add validation as needed)
        // You can add validation for required fields here

        // Publish the hoarding (auto-approve)
        try {
            $hoarding->publish();

            return response()->json([
                'success' => true,
                'message' => 'Hoarding published successfully and auto-approved',
                'hoarding' => [
                    'id' => $hoarding->id,
                    'title' => $hoarding->title,
                    'status' => $hoarding->status,
                    'published_at' => $hoarding->published_at,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Hoarding publish failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish hoarding'
            ], 500);
        }
    }

    /**
     * Edit hoarding
     * Vendor can edit draft or preview hoardings
     */
    // public function edit(int $id)
    // {
    //     $hoarding = $this->hoardingService->getById($id);

    //     if (!$hoarding || $hoarding->vendor_id !== auth()->id()) {
    //         abort(404);
    //     }

    //     if (!$hoarding->canBeEdited()) {
    //         return redirect()->back()->with('error', 'Only draft or preview hoardings can be edited');
    //     }

    //     // Return edit view/form
    //     return view('hoardings.vendor.edit', compact('hoarding'));
    // }

    /**
     * Update hoarding
     */
    public function update(Request $request, int $id)
    {
        $hoarding = $this->hoardingService->getById($id);

        if (!$hoarding || $hoarding->vendor_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Hoarding not found'], 404);
        }

        if (!$hoarding->canBeEdited()) {
            return response()->json(['success' => false, 'message' => 'This hoarding cannot be edited'], 400);
        }

        try {
            // Validate and update hoarding
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'address' => 'sometimes|string',
                'city' => 'sometimes|string',
                'state' => 'sometimes|string',
                'locality' => 'sometimes|string',
                'pincode' => 'sometimes|string',
                // Add other fields as needed
            ]);

            $hoarding->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Hoarding updated successfully',
                'hoarding' => $hoarding
            ]);
        } catch (\Exception $e) {
            \Log::error('Hoarding update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update hoarding'
            ], 500);
        }
    }

    
}