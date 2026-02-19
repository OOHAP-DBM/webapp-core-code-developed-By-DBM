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
    /**
     * List all vendors with their hoarding counts.
     */
    public function index(Request $request)
    {
        $query = User::where('active_role', 'vendor')
            ->withCount('hoardings');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhereHas('hoardings', fn($h) => $h->where('city', 'like', "%$search%")
                      ->orWhere('state', 'like', "%$search%"));
            });
        }

        if ($request->filled('state')) {
            $query->whereHas('hoardings', fn($h) => $h->where('state', $request->state));
        }

        if ($request->filled('city')) {
            $query->whereHas('hoardings', fn($h) => $h->where('city', $request->city));
        }

        $vendors = $query->with(['hoardings' => function ($q) {
                $q->select('vendor_id', 'city')->distinct();
            }])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $states = Hoarding::whereNotNull('state')->distinct()->pluck('state')->sort()->values();
        $cities = Hoarding::whereNotNull('city')
            ->when($request->state, fn($q) => $q->where('state', $request->state))
            ->distinct()->pluck('city')->sort()->values();

        return view('admin.settings.commission-setting.index', compact('vendors', 'states', 'cities'));
    }

    /**
     * Show all hoardings for a specific vendor.
     */
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

        if ($request->filled('type')) {
            $query->where('hoarding_type', $request->type);
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $hoardings = $query->paginate(10)->withQueryString();

        $states = Hoarding::where('vendor_id', $vendor->id)
            ->whereNotNull('state')->distinct()->pluck('state')->sort()->values();

        $cities = Hoarding::where('vendor_id', $vendor->id)
            ->whereNotNull('city')
            ->when($request->state, fn($q) => $q->where('state', $request->state))
            ->distinct()->pluck('city')->sort()->values();

        return view('admin.settings.commission-setting.vendor.hoardings', compact('vendor', 'hoardings', 'states', 'cities'));
    }

    /**
     * Get cities for a state (AJAX).
     */
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
     * Save commission settings.
     */
    public function save(Request $request)
    {
        $request->validate([
            'vendor_id'                     => 'nullable|exists:users,id',
            'hoarding_ids'                  => 'nullable|array',
            'hoarding_ids.*'                => 'exists:hoardings,id',
            'apply_all_types'               => 'required|boolean',
            'global_commission'             => 'nullable|numeric|min:0|max:100',
            'ooh_commission'                => 'nullable|numeric|min:0|max:100',
            'dooh_commission'               => 'nullable|numeric|min:0|max:100',
            'apply_all_states'              => 'required|boolean',
            'apply_all_cities'              => 'required|boolean',
            'states'                        => 'nullable|array',
            'states.*.name'                 => 'required_with:states|string',
            'states.*.commission'           => 'nullable|numeric|min:0|max:100',
            'states.*.ooh_commission'       => 'nullable|numeric|min:0|max:100',
            'states.*.dooh_commission'      => 'nullable|numeric|min:0|max:100',
            'cities'                        => 'nullable|array',
            'cities.*.state'                => 'required_with:cities|string',
            'cities.*.name'                 => 'required_with:cities|string',
            'cities.*.commission'           => 'nullable|numeric|min:0|max:100',
            'cities.*.ooh_commission'       => 'nullable|numeric|min:0|max:100',
            'cities.*.dooh_commission'      => 'nullable|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request) {
            $vendorId = $request->vendor_id ?: null;
            $adminId  = auth()->id();

            // Clear existing rules for this vendor/scope
            CommissionSetting::where('vendor_id', $vendorId)->delete();

            // ── GLOBAL / TYPE LEVEL ──────────────────────────────
            if ($request->apply_all_types) {
                if (!is_null($request->global_commission)) {
                    $this->upsert($vendorId, 'all', null, null, [
                        'commission_percent' => $request->global_commission,
                        'set_by'             => $adminId,
                    ]);
                }
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

            // ── STATE LEVEL ──────────────────────────────────────
            if (!$request->apply_all_states && $request->states) {
                foreach ($request->states as $stateData) {
                    if ($request->apply_all_types) {
                        if (!empty($stateData['commission'])) {
                            $this->upsert($vendorId, 'all', $stateData['name'], null, [
                                'commission_percent' => $stateData['commission'],
                                'set_by'             => $adminId,
                            ]);
                        }
                    } else {
                        if (!empty($stateData['ooh_commission'])) {
                            $this->upsert($vendorId, 'ooh', $stateData['name'], null, [
                                'commission_percent' => $stateData['ooh_commission'],
                                'set_by'             => $adminId,
                            ]);
                        }
                        if (!empty($stateData['dooh_commission'])) {
                            $this->upsert($vendorId, 'dooh', $stateData['name'], null, [
                                'commission_percent' => $stateData['dooh_commission'],
                                'set_by'             => $adminId,
                            ]);
                        }
                    }
                }
            }

            // ── CITY LEVEL ───────────────────────────────────────
            if (!$request->apply_all_cities && $request->cities) {
                foreach ($request->cities as $cityData) {
                    if ($request->apply_all_types) {
                        if (!empty($cityData['commission'])) {
                            $this->upsert($vendorId, 'all', $cityData['state'], $cityData['name'], [
                                'commission_percent' => $cityData['commission'],
                                'set_by'             => $adminId,
                            ]);
                        }
                    } else {
                        if (!empty($cityData['ooh_commission'])) {
                            $this->upsert($vendorId, 'ooh', $cityData['state'], $cityData['name'], [
                                'commission_percent' => $cityData['ooh_commission'],
                                'set_by'             => $adminId,
                            ]);
                        }
                        if (!empty($cityData['dooh_commission'])) {
                            $this->upsert($vendorId, 'dooh', $cityData['state'], $cityData['name'], [
                                'commission_percent' => $cityData['dooh_commission'],
                                'set_by'             => $adminId,
                            ]);
                        }
                    }
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Commission settings saved successfully.']);
    }

    /**
     * Delete a commission rule.
     */
    public function destroy(CommissionSetting $commission)
    {
        $commission->delete();
        return response()->json(['success' => true]);
    }

    private function upsert(?int $vendorId, string $type, ?string $state, ?string $city, array $data): void
    {
        CommissionSetting::updateOrCreate(
            [
                'vendor_id'     => $vendorId,
                'hoarding_type' => $type,
                'state'         => $state,
                'city'          => $city,
            ],
            $data
        );
    }
}


