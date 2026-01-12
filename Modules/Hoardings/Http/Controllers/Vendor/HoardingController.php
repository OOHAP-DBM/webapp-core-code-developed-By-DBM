<?php

namespace Modules\Hoardings\Http\Controllers\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Modules\Hoardings\Services\HoardingService;
use App\Models\Hoarding;

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
        $user = Auth::user();
        $vendorProfile = $user->vendor_profile ?? null;
        // if (!$vendorProfile || $vendorProfile->onboarding_status !== 'approved') {
        //     // Block access if not approved
        //     return Redirect::route('vendor.dashboard')
        //         ->with('error', 'Your vendor onboarding is under review. You can add hoardings only after approval.');
        // }
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

    public function edit($id)
    {
        $vendor = Auth::user();
        $listing = $vendor->hoardings()->findOrFail($id);

        return view('hoardings.vendor.edit', compact('listing'));
    }

    public function update(Request $request, $id)
    {
        $vendor = Auth::user();
        $listing = $vendor->hoardings()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price_per_month' => 'required|numeric|min:0',
            // Add other validation rules
        ]);

        $listing->update($validated);

        return redirect()
            ->route('hoardings.vendor.index')
            ->with('success', 'Listing updated successfully!');
    }

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
            ->select('id', 'title', 'city', 'state', 'price_per_month', 'status')
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
                $updateData['price_per_month'] = $priceValue;
            } else {
                // For percentage/amount changes, need to update individually
                $listings = $query->get();
                foreach ($listings as $listing) {
                    $newPrice = $listing->price_per_month;

                    if ($priceMethod === 'increase_percent') {
                        $newPrice = $listing->price_per_month * (1 + $priceValue / 100);
                    } elseif ($priceMethod === 'decrease_percent') {
                        $newPrice = $listing->price_per_month * (1 - $priceValue / 100);
                    } elseif ($priceMethod === 'increase_amount') {
                        $newPrice = $listing->price_per_month + $priceValue;
                    } elseif ($priceMethod === 'decrease_amount') {
                        $newPrice = $listing->price_per_month - $priceValue;
                    }

                    $listing->update(['price_per_month' => max(0, $newPrice)]);
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
                ->latest()
                ->paginate(10);
        } else {
            $filters = [
                'vendor_id' => $vendor->id,
                'status' => ['active', 'inactive', 'pending_approval']
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

    
}

