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
        $activeTab = $request->query('tab', 'all');
        $search = $request->input('search');
        $letter = $request->input('letter');
        $perPage = (int) $request->input('per_page', 10);
        $statusFilter = strtolower((string) $request->query('status', ''));
        $typeFilter = strtolower((string) $request->query('hoarding_type', $request->query('type', '')));
        $bookedFilter = strtolower((string) $request->query('booked', ''));
        $unsoldFilter = $request->boolean('unsold') || in_array($bookedFilter, ['false', '0', 'no', 'unbooked'], true);
        $categoryFilters = $this->normalizeFilterValues((array) $request->query('category', []));
        $surroundingFilters = $this->normalizeFilterValues((array) $request->query('surroundings', []));
        $availabilityFilters = $this->normalizeFilterValues((array) $request->query('availability', []));
        $resolutionFilters = $this->normalizeFilterValues((array) $request->query('resolution', []));
        $screenSizeMin = $request->filled('screen_size_min') ? max(0, (float) $request->query('screen_size_min')) : null;
        $screenSizeMax = $request->filled('screen_size_max') ? max(0, (float) $request->query('screen_size_max')) : null;
        $hoardingSizeMin = $request->filled('hoarding_size_min') ? max(0, (float) $request->query('hoarding_size_min')) : null;
        $hoardingSizeMax = $request->filled('hoarding_size_max') ? max(0, (float) $request->query('hoarding_size_max')) : null;
        if ($perPage < 1) $perPage = 10;

        $totalCount = Hoarding::where('vendor_id', $vendor->id)->count();
        if ($totalCount === 0) {
            return view('hoardings.vendor.empty');
        }

        // --- Export logic ---
        if ($request->has('export') && $request->has('format')) {
            // Build query for export (no pagination)
            if ($activeTab === 'draft') {
                $query = Hoarding::where('vendor_id', $vendor->id)
                    ->where('status', 'Draft');
                if ($search) {
                    $query->search($search);
                }
                if ($letter) {
                    $query->whereRaw('UPPER(LEFT(title, 1)) = ?', [mb_strtoupper(mb_substr($letter, 0, 1))]);
                }
                $hoardings = $query->orderBy('title')->get();
            } else {
                $statusValues = ['active', 'inactive', 'pending_approval'];
                if (in_array($statusFilter, ['active', 'inactive', 'pending_approval'], true)) {
                    $statusValues = [$statusFilter];
                }

                $query = Hoarding::query()
                    ->withCount('bookings')
                    ->where('vendor_id', $vendor->id)
                    ->whereIn('status', $statusValues);

                if (in_array($typeFilter, ['ooh', 'dooh'], true)) {
                    $query->where('hoarding_type', $typeFilter);
                }

                if ($search) {
                    $query->search($search);
                }
                if ($letter) {
                    $query->whereRaw('UPPER(LEFT(title, 1)) = ?', [mb_strtoupper(mb_substr($letter, 0, 1))]);
                }

                $this->applyVendorListAdvancedFilters($query, [
                    'categories' => $categoryFilters,
                    'surroundings' => $surroundingFilters,
                    'availability' => $availabilityFilters,
                    'resolutions' => $resolutionFilters,
                    'screen_size_min' => $screenSizeMin,
                    'screen_size_max' => $screenSizeMax,
                    'hoarding_size_min' => $hoardingSizeMin,
                    'hoarding_size_max' => $hoardingSizeMax,
                    'force_unbooked' => $unsoldFilter,
                ]);

                $hoardings = $query->orderByDesc('created_at')->get();
            }

            $columns = ['ID', 'Title', 'Type', 'Location', 'Bookings', 'Status'];
            $rows = $hoardings->map(function ($h) {
                $parts = [];
                if (!empty($h->locality)) $parts[] = $h->locality;
                if (!empty($h->city)) $parts[] = $h->city;
                return [
                    $h->id,
                    $h->title,
                    $h->hoarding_type,
                    $parts ? implode(', ', $parts) : '',
                    $h->bookings_count ?? 0,
                    ucfirst($h->status === "active" ? 'Published' : $h->status),
                ];
            });
            $filename = 'my_hoardings_' . now()->format('Ymd_His');
            $format = $request->input('format');

            if ($format === 'excel') {
                $html = '<table>';
                $html .= '<tr>' . implode('', array_map(fn($col) => "<th>{$col}</th>", $columns)) . '</tr>';
                foreach ($rows as $row) {
                    $html .= '<tr>' . implode('', array_map(fn($cell) => "<td>{$cell}</td>", $row)) . '</tr>';
                }
                $html .= '</table>';
                return response($html, 200, [
                    'Content-Type'        => 'application/vnd.ms-excel',
                    'Content-Disposition' => "attachment; filename={$filename}.xls",
                    'Pragma'              => 'no-cache',
                    'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                ]);
            } elseif ($format === 'pdf') {
                $html = '<!DOCTYPE html><html><head><style>';
                $html .= 'body { font-family: Arial, sans-serif; font-size: 12px; }';
                $html .= 'h2 { margin-bottom: 10px; }';
                $html .= 'table { width: 100%; border-collapse: collapse; }';
                $html .= 'th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; }';
                $html .= 'th { background: #f3f4f6; font-weight: bold; }';
                $html .= '</style></head><body>';
                $html .= '<h2>My Hoardings Export</h2>';
                $html .= '<table><thead><tr>';
                foreach ($columns as $col) {
                    $html .= "<th>{$col}</th>";
                }
                $html .= '</tr></thead><tbody>';
                foreach ($rows as $row) {
                    $html .= '<tr>';
                    foreach ($row as $cell) {
                        $html .= "<td>" . htmlspecialchars((string) $cell) . "</td>";
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody></table></body></html>';
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                $pdf->setPaper('a4', 'landscape');
                return $pdf->download("{$filename}.pdf");
            } else {
                $headers = [
                    'Content-Type'        => 'text/csv',
                    'Content-Disposition' => "attachment; filename={$filename}.csv",
                    'Pragma'              => 'no-cache',
                    'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                    'Expires'             => '0',
                ];
                $callback = function () use ($columns, $rows) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
                    foreach ($rows as $row) {
                        fputcsv($file, $row);
                    }
                    fclose($file);
                };
                return response()->stream($callback, 200, $headers);
            }
        }

        // --- Normal page logic ---
        if ($activeTab === 'draft') {
            $query = Hoarding::where('vendor_id', $vendor->id)
                ->where('status', 'Draft');
            if ($search) {
                $query->search($search);
            }
            if ($letter) {
                $query->whereRaw('UPPER(LEFT(title, 1)) = ?', [mb_strtoupper(mb_substr($letter, 0, 1))]);
            }
            $data = $query->orderBy('title')->paginate($perPage)->withQueryString();
        } else {
            $statusValues = ['active', 'inactive', 'pending_approval'];
            if (in_array($statusFilter, ['active', 'inactive', 'pending_approval'], true)) {
                $statusValues = [$statusFilter];
            }

            $query = Hoarding::query()
                ->withCount('bookings')
                ->where('vendor_id', $vendor->id)
                ->whereIn('status', $statusValues);

            if (in_array($typeFilter, ['ooh', 'dooh'], true)) {
                $query->where('hoarding_type', $typeFilter);
            }

            if ($search) {
                $query->search($search);
            }
            if ($letter) {
                $query->whereRaw('UPPER(LEFT(title, 1)) = ?', [mb_strtoupper(mb_substr($letter, 0, 1))]);
            }

            $this->applyVendorListAdvancedFilters($query, [
                'categories' => $categoryFilters,
                'surroundings' => $surroundingFilters,
                'availability' => $availabilityFilters,
                'resolutions' => $resolutionFilters,
                'screen_size_min' => $screenSizeMin,
                'screen_size_max' => $screenSizeMax,
                'hoarding_size_min' => $hoardingSizeMin,
                'hoarding_size_max' => $hoardingSizeMax,
                'force_unbooked' => $unsoldFilter,
            ]);

            $data = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

            if ($letter && $data->total() === 0) {
                $data = $data->setCollection(collect([]));
            }
        }
        $hoardingList = $data->getCollection()->map(function ($h) {
            $parts = [];
            if (!empty($h->locality)) $parts[] = $h->locality;
            if (!empty($h->city)) $parts[] = $h->city;
            return [
                'id' => $h->id,
                'title' => $h->title,
                'hoarding_type' => $h->hoarding_type,
                'location' => $parts ? implode(', ', $parts) : null,
                'bookings_count' => $h->bookings_count ?? 0,
                'status' => ucfirst($h->status === "active" ? 'Published' : $h->status),
            ];
        });
        $data->setCollection($hoardingList);
        return view('hoardings.vendor.list', [
            'hoardings' => $data,
            'activeTab' => $activeTab,
            'perPage' => $perPage
        ]);
    }

    private function normalizeFilterValues(array $values): array
    {
        return collect($values)
            ->map(function ($value) {
                return mb_strtolower(trim((string) $value));
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function applyVendorListAdvancedFilters($query, array $filters): void
    {
        $categoryFilters = $filters['categories'] ?? [];
        $surroundingFilters = $filters['surroundings'] ?? [];
        $availabilityFilters = $filters['availability'] ?? [];
        $resolutionFilters = $filters['resolutions'] ?? [];
        $screenSizeMin = $filters['screen_size_min'] ?? null;
        $screenSizeMax = $filters['screen_size_max'] ?? null;
        $hoardingSizeMin = $filters['hoarding_size_min'] ?? null;
        $hoardingSizeMax = $filters['hoarding_size_max'] ?? null;
        $forceUnbooked = (bool) ($filters['force_unbooked'] ?? false);

        if (!empty($categoryFilters)) {
            $query->where(function ($categoryQuery) use ($categoryFilters) {
                foreach ($categoryFilters as $index => $category) {
                    $token = str_replace(['_', '-', ' '], '', mb_strtolower((string) $category));
                    $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                    $categoryQuery->{$method}(
                        "LOWER(REPLACE(REPLACE(COALESCE(category, ''), ' ', ''), '_', '')) LIKE ?",
                        ['%' . $token . '%']
                    );
                }
            });
        }

        if (!empty($surroundingFilters)) {
            $query->where(function ($surroundingQuery) use ($surroundingFilters) {
                foreach ($surroundingFilters as $index => $surrounding) {
                    $token = str_replace(['_', '-', ' '], '', mb_strtolower((string) $surrounding));
                    $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                    $surroundingQuery->{$method}(
                        "LOWER(REPLACE(REPLACE(COALESCE(road_type, ''), ' ', ''), '_', '')) LIKE ?",
                        ['%' . $token . '%']
                    );
                }
            });
        }

        $hasAvailable = in_array('available', $availabilityFilters, true);
        $hasBooked = in_array('booked', $availabilityFilters, true);

        if ($forceUnbooked || ($hasAvailable && !$hasBooked)) {
            $query->whereDoesntHave('bookings')
                ->whereDoesntHave('posBookings');
        } elseif ($hasBooked && !$hasAvailable) {
            $query->where(function ($bookingQuery) {
                $bookingQuery->whereHas('bookings')
                    ->orWhereHas('posBookings');
            });
        }

        if (!empty($resolutionFilters)) {
            $query->whereHas('doohScreen', function ($screenQuery) use ($resolutionFilters) {
                $screenQuery->where(function ($resolutionQuery) use ($resolutionFilters) {
                    foreach ($resolutionFilters as $index => $resolution) {
                        $normalized = str_replace([' ', '-'], '_', mb_strtolower((string) $resolution));
                        $method = $index === 0 ? 'where' : 'orWhere';

                        if ($normalized === 'led') {
                            $resolutionQuery->{$method}(function ($ledQuery) {
                                $ledQuery->where('screen_type', 'like', '%led%')
                                    ->orWhere(function ($lowResQuery) {
                                        $lowResQuery->whereNotNull('resolution_width')
                                            ->where('resolution_width', '<', 1280);
                                    });
                            });
                        } elseif ($normalized === 'hd') {
                            $resolutionQuery->{$method}(function ($hdQuery) {
                                $hdQuery->where(function ($widthQuery) {
                                    $widthQuery->where('resolution_width', '>=', 1280)
                                        ->where('resolution_width', '<', 3840);
                                })->orWhere(function ($heightQuery) {
                                    $heightQuery->where('resolution_height', '>=', 720)
                                        ->where('resolution_height', '<', 2160);
                                });
                            });
                        } elseif (in_array($normalized, ['ultra_hd', 'ultrahd'], true)) {
                            $resolutionQuery->{$method}(function ($uhdQuery) {
                                $uhdQuery->where('resolution_width', '>=', 3840)
                                    ->orWhere('resolution_height', '>=', 2160);
                            });
                        }
                    }
                });
            });
        }

        if ($screenSizeMin !== null || $screenSizeMax !== null) {
            $query->whereHas('doohScreen', function ($screenQuery) use ($screenSizeMin, $screenSizeMax) {
                if ($screenSizeMin !== null) {
                    $screenQuery->whereRaw('COALESCE(resolution_width, 0) >= ?', [$screenSizeMin]);
                }

                if ($screenSizeMax !== null) {
                    $screenQuery->whereRaw('COALESCE(resolution_width, 0) <= ?', [$screenSizeMax]);
                }
            });
        }

        if ($hoardingSizeMin !== null || $hoardingSizeMax !== null) {
            $query->whereHas('ooh', function ($oohQuery) use ($hoardingSizeMin, $hoardingSizeMax) {
                if ($hoardingSizeMin !== null) {
                    $oohQuery->whereRaw('(COALESCE(width, 0) * COALESCE(height, 0)) >= ?', [$hoardingSizeMin]);
                }

                if ($hoardingSizeMax !== null) {
                    $oohQuery->whereRaw('(COALESCE(width, 0) * COALESCE(height, 0)) <= ?', [$hoardingSizeMax]);
                }
            });
        }
    }

    public function toggleStatus($id)
    {
        $hoarding = Hoarding::where('vendor_id', Auth::id())->findOrFail($id);

        if ($hoarding->status === 'active') {
            $hoarding->status = 'inactive';
            $hoarding->save();
            $msg = 'Hoarding is now Inactive!';
            $type = 'warning'; // orange
        } else {
            $hoarding->status = 'active';
            $hoarding->save();
            $msg = 'Hoarding is now Active!';
            $type = 'success'; // green
        }

        return back()->with([
            'swal_success' => $msg,
            'swal_type' => $type
        ]);
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
    public function bulkDestroy(Request $request)
    {
        $vendor = Auth::user();

        $ids = array_filter(explode(',', $request->input('ids', '')));

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No hoardings selected.'], 422);
        }

        $listings = $vendor->hoardings()->whereIn('id', $ids)->get();

        if ($listings->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No hoardings found.'], 404);
        }

        foreach ($listings as $listing) {
            // Delete primary image
            if ($listing->primary_image) {
                Storage::disk('public')->delete($listing->primary_image);
            }

            // Delete gallery images
            foreach ($listing->galleryImages as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            $listing->delete();
        }

        return response()->json([
            'success' => true,
            'deleted_count' => $listings->count(),
            'message' => $listings->count() . ' hoarding(s) deleted successfully.'
        ]);
    }
}
