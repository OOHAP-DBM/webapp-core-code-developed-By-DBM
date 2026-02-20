<?php


namespace Modules\Admin\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CommissionSetting;
use App\Models\Hoarding;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CommissionSettingController extends Controller
{
     public function index(Request $request)
    {
        $query = User::where('active_role', 'vendor')->withCount('hoardings');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhereHas('hoardings', fn($h) =>
                      $h->where('city', 'like', "%$search%")
                        ->orWhere('state', 'like', "%$search%")
                  );
            });
        }

        if ($request->filled('state')) {
            $query->whereHas('hoardings', fn($h) => $h->where('state', $request->state));
        }

        if ($request->filled('city')) {
            $query->whereHas('hoardings', fn($h) => $h->where('city', $request->city));
        }

        $vendors = $query->with(['hoardings' => fn($q) => $q->select('vendor_id', 'city')->distinct()])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $states = Hoarding::whereNotNull('state')->distinct()->pluck('state')->sort()->values();
        $cities = Hoarding::whereNotNull('city')
            ->when($request->state, fn($q) => $q->where('state', $request->state))
            ->distinct()->pluck('city')->sort()->values();

        return view('admin.settings.commission-setting.index', compact('vendors', 'states', 'cities'));
    }

    // public function vendorHoardings(Request $request, User $vendor)
    // {
    //     $query = Hoarding::where('vendor_id', $vendor->id);

    //     if ($request->filled('search')) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->where('name', 'like', "%$search%")
    //               ->orWhere('city', 'like', "%$search%")
    //               ->orWhere('location', 'like', "%$search%");
    //         });
    //     }

    //     if ($request->filled('type')) {
    //         $query->where('hoarding_type', $request->type);
    //     }

    //     if ($request->filled('state')) {
    //         $query->where('state', $request->state);
    //     }

    //     if ($request->filled('city')) {
    //         $query->where('city', $request->city);
    //     }

    //     $hoardings = $query->paginate(10)->withQueryString();

    //     $states = Hoarding::where('vendor_id', $vendor->id)
    //         ->whereNotNull('state')->distinct()->pluck('state')->sort()->values();

    //     $cities = Hoarding::where('vendor_id', $vendor->id)
    //         ->whereNotNull('city')
    //         ->when($request->state, fn($q) => $q->where('state', $request->state))
    //         ->distinct()->pluck('city')->sort()->values();

    //     return view('admin.settings.commission-setting.vendor.hoardings', compact('vendor', 'hoardings', 'states', 'cities'));
    // }

// Add: fetch existing commission settings for this vendor to prefill modals

public function vendorHoardings(Request $request, User $vendor)
{
    $query = Hoarding::where('vendor_id', $vendor->id);

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%$search%")
              ->orWhere('city', 'like', "%$search%")
              ->orWhere('location', 'like', "%$search%");
        });
    }

    if ($request->filled('type'))  { $query->where('hoarding_type', $request->type); }
    if ($request->filled('state')) { $query->where('state', $request->state); }
    if ($request->filled('city'))  { $query->where('city', $request->city); }

    $hoardings = $query->paginate(10)->withQueryString();

    $states = Hoarding::where('vendor_id', $vendor->id)
        ->whereNotNull('state')->distinct()->pluck('state')->sort()->values();

    $cities = Hoarding::where('vendor_id', $vendor->id)
        ->whereNotNull('city')
        ->when($request->state, fn($q) => $q->where('state', $request->state))
        ->distinct()->pluck('city')->sort()->values();

    // ── NEW: fetch existing commission rules for prefilling ──
    $existingCommissions = CommissionSetting::where('vendor_id', $vendor->id)->get();

    // Build a lookup map: "type|state|city" => commission_percent
    // e.g. "all||" => 15, "ooh|UP|" => 12, "all|UP|Lucknow" => 18
    $commissionMap = [];
    foreach ($existingCommissions as $ec) {
        $key = ($ec->hoarding_type ?? 'all') . '|' . ($ec->state ?? '') . '|' . ($ec->city ?? '');
        $commissionMap[$key] = (float) $ec->commission_percent;
    }

    $hasExistingCommission = $existingCommissions->isNotEmpty();

    return view('admin.settings.commission-setting.vendor.hoardings',
        compact('vendor', 'hoardings', 'states', 'cities', 'commissionMap', 'hasExistingCommission')
    );
}
    public function getCities(Request $request)
    {
        $query = Hoarding::whereNotNull('city');

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $cities = $query->distinct()->pluck('city')->sort()->values();
        return response()->json($cities);
    }

    /**
     * Save commission directly on a single hoarding row.
     */
    public function saveHoardingCommission(Request $request, Hoarding $hoarding)
    {
        $request->validate([
            'commission' => 'required|numeric|min:0|max:100',
        ]);

        $hoarding->update(['commission_percent' => $request->commission]);

        return response()->json([
            'success'    => true,
            'message'    => 'Commission saved for this hoarding.',
            'commission' => (float) $hoarding->commission_percent,
        ]);
    }

    /**
     * Save vendor-level commission to commission_settings table.
     */
    public function save(Request $request)
    {
        $request->validate([
            'vendor_id'                => 'required|exists:users,id',
            'base_commission'          => 'required|numeric|min:0|max:100',
            'apply_to_all_types'       => 'required|boolean',
            'ooh_commission'           => 'required_if:apply_to_all_types,false|nullable|numeric|min:0|max:100',
            'dooh_commission'          => 'required_if:apply_to_all_types,false|nullable|numeric|min:0|max:100',
            'apply_all_states'         => 'required|boolean',
            'apply_all_cities'         => 'required|boolean',
            'states'                   => 'nullable|array',
            'states.*.name'            => 'required_with:states|string',
            'states.*.commission'      => 'nullable|numeric|min:0|max:100',
            'states.*.ooh_commission'  => 'nullable|numeric|min:0|max:100',
            'states.*.dooh_commission' => 'nullable|numeric|min:0|max:100',
            'cities'                   => 'nullable|array',
            'cities.*.state'           => 'required_with:cities|string',
            'cities.*.name'            => 'required_with:cities|string',
            'cities.*.commission'      => 'nullable|numeric|min:0|max:100',
            'cities.*.ooh_commission'  => 'nullable|numeric|min:0|max:100',
            'cities.*.dooh_commission' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request) {
            $vendorId = (int) $request->vendor_id;
            $adminId  = auth()->id();

            CommissionSetting::where('vendor_id', $vendorId)->delete();

            if ($request->apply_to_all_types) {
                $this->upsert($vendorId, 'all', null, null, [
                    'commission_percent' => $request->base_commission,
                    'set_by'             => $adminId,
                ]);
            } else {
                if (!is_null($request->ooh_commission)) {
                    $this->upsert($vendorId, 'ooh', null, null, [
                        'commission_percent' => $request->ooh_commission,
                        'set_by'             => $adminId,
                    ]);
                }
                if (!is_null($request->dooh_commission)) {
                    $this->upsert($vendorId, 'dooh', null, null, [
                        'commission_percent' => $request->dooh_commission,
                        'set_by'             => $adminId,
                    ]);
                }
            }

            if (!$request->apply_all_states && $request->states) {
                foreach ($request->states as $sd) {
                    if ($request->apply_to_all_types && !empty($sd['commission'])) {
                        $this->upsert($vendorId, 'all', $sd['name'], null, [
                            'commission_percent' => $sd['commission'],
                            'set_by'             => $adminId,
                        ]);
                    } else {
                        if (!empty($sd['ooh_commission'])) {
                            $this->upsert($vendorId, 'ooh', $sd['name'], null, [
                                'commission_percent' => $sd['ooh_commission'],
                                'set_by'             => $adminId,
                            ]);
                        }
                        if (!empty($sd['dooh_commission'])) {
                            $this->upsert($vendorId, 'dooh', $sd['name'], null, [
                                'commission_percent' => $sd['dooh_commission'],
                                'set_by'             => $adminId,
                            ]);
                        }
                    }
                }
            }

            if (!$request->apply_all_cities && $request->cities) {
                foreach ($request->cities as $cd) {
                    if ($request->apply_to_all_types && !empty($cd['commission'])) {
                        $this->upsert($vendorId, 'all', $cd['state'], $cd['name'], [
                            'commission_percent' => $cd['commission'],
                            'set_by'             => $adminId,
                        ]);
                    } else {
                        if (!empty($cd['ooh_commission'])) {
                            $this->upsert($vendorId, 'ooh', $cd['state'], $cd['name'], [
                                'commission_percent' => $cd['ooh_commission'],
                                'set_by'             => $adminId,
                            ]);
                        }
                        if (!empty($cd['dooh_commission'])) {
                            $this->upsert($vendorId, 'dooh', $cd['state'], $cd['name'], [
                                'commission_percent' => $cd['dooh_commission'],
                                'set_by'             => $adminId,
                            ]);
                        }
                    }
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Vendor commission saved successfully.']);
    }

    public function destroy(CommissionSetting $commission)
    {
        $commission->delete();
        return response()->json(['success' => true]);
    }

    private function upsert(?int $vendorId, string $type, ?string $state, ?string $city, array $data): void
    {
        CommissionSetting::updateOrCreate(
            ['vendor_id' => $vendorId, 'hoarding_type' => $type, 'state' => $state, 'city' => $city],
            $data
        );
    }
}


