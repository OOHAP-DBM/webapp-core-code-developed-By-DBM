<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $vendor = Auth::user();
        
        $query = $vendor->hoardings()->with('media');
        
        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
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
    
    public function create(Request $request)
    {
        $type = $request->get('type', 'ooh');
        return view('vendor.listings.create', compact('type'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'media_type' => 'required|in:ooh,dooh',
            'type' => 'required|string',
            'orientation' => 'nullable|in:horizontal,vertical,square',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|digits:6',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'width' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'illumination' => 'nullable|boolean',
            'resolution' => 'nullable|string',
            'slot_duration' => 'nullable|integer|min:1',
            'slots_per_hour' => 'nullable|integer|min:1',
            'price_per_month' => 'required|numeric|min:0',
            'price_per_slot' => 'nullable|numeric|min:0',
            'printing_cost' => 'nullable|numeric|min:0',
            'installation_cost' => 'nullable|numeric|min:0',
            'maintenance_cost' => 'nullable|numeric|min:0',
            'available_from' => 'nullable|date',
            'minimum_booking_days' => 'nullable|integer|min:1',
            'is_featured' => 'nullable|boolean',
            'traffic_type' => 'nullable|in:high,medium,low',
            'audience_type' => 'nullable|string',
            'landmark' => 'nullable|string',
            'primary_image' => 'required|image|max:5120',
            'gallery_images.*' => 'nullable|image|max:5120',
        ]);
        
        $vendor = Auth::user();
        
        // Handle primary image upload
        if ($request->hasFile('primary_image')) {
            $validated['primary_image'] = $request->file('primary_image')
                ->store('hoardings', 'public');
        }
        
        // Create listing
        $listing = $vendor->hoardings()->create($validated);
        
        // Handle gallery images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $image) {
                $path = $image->store('hoardings', 'public');
                $listing->galleryImages()->create(['image_path' => $path]);
            }
        }
        
        return redirect()
            ->route('vendor.listings.index')
            ->with('success', 'Listing created successfully! It will be reviewed by admin.');
    }
    
    public function edit($id)
    {
        $vendor = Auth::user();
        $listing = $vendor->hoardings()->findOrFail($id);
        
        return view('vendor.listings.edit', compact('listing'));
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
            ->route('vendor.listings.index')
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
        
        return view('vendor.listings.bulk-update', compact('listings', 'cities'));
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
            ->route('vendor.listings.index')
            ->with('success', 'Listings updated successfully!');
    }
}
