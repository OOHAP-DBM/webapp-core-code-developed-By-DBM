<?php
// Modules/Admin/Controllers/Web/CommissionSettingController.php
// Full replacement

namespace Modules\Admin\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CommissionSetting;
use App\Models\Hoarding;
use App\Models\User;
use Modules\Admin\Services\CommissionService;
use Illuminate\Http\Request;

class CommissionSettingController extends Controller
{
    public function __construct(protected CommissionService $commissionService) {}

    // ─────────────────────────────────────────────────────
    // Vendor list
    // ─────────────────────────────────────────────────────
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

        $vendors = $query
            ->with(['hoardings' => fn($q) => $q->select('vendor_id', 'city')->distinct()])
            ->orderBy('name')
            ->paginate($request->input('per_page', 10))
            ->withQueryString();

        $states = Hoarding::whereNotNull('state')->distinct()->pluck('state')->sort()->values();
        $cities = Hoarding::whereNotNull('city')
            ->when($request->state, fn($q) => $q->where('state', $request->state))
            ->distinct()->pluck('city')->sort()->values();

        return view('admin.settings.commission-setting.index', compact('vendors', 'states', 'cities'));
    }

    // ─────────────────────────────────────────────────────
    // Vendor hoardings list
    // ─────────────────────────────────────────────────────
    public function vendorHoardings(Request $request, User $vendor)
    {
        $query = Hoarding::where('vendor_id', $vendor->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('city', 'like', "%$search%")
                  ->orWhere('state', 'like', "%$search%")
                  ->orWhere('address', 'like', "%$search%")
                  ->orWhere('locality', 'like', "%$search%");
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

        // Fetch existing rules for prefilling modals
        $existingCommissions   = CommissionSetting::where('vendor_id', $vendor->id)->get();
        $hasExistingCommission = $existingCommissions->isNotEmpty();

        // Build commissionMap: "type|state|city" => percent
        $commissionMap = [];
        foreach ($existingCommissions as $ec) {
            $key = ($ec->hoarding_type ?? 'all')
                 . '|' . ($ec->state ?? '')
                 . '|' . ($ec->city  ?? '');
            $commissionMap[$key] = (float) $ec->commission_percent;
        }

        // Resolve effective commission for every hoarding on this page (centralized)
        $resolvedCommissions = $this->commissionService->resolveForVendor($vendor->id);

        return view('admin.settings.commission-setting.vendor.hoardings',
            compact(
                'vendor', 'hoardings', 'states', 'cities',
                'commissionMap', 'hasExistingCommission', 'resolvedCommissions'
            )
        );
    }

    // ─────────────────────────────────────────────────────
    // AJAX: cities for a state
    // ─────────────────────────────────────────────────────
    public function getCities(Request $request)
    {
        $query = Hoarding::whereNotNull('city');

        if ($request->filled('state'))     { $query->where('state', $request->state); }
        if ($request->filled('vendor_id')) { $query->where('vendor_id', $request->vendor_id); }

        $cities = $query->distinct()->pluck('city')->sort()->values();
        return response()->json($cities);
    }

    // ─────────────────────────────────────────────────────
    // Save commission on a single hoarding (Flow A)
    // ─────────────────────────────────────────────────────
    public function saveHoardingCommission(Request $request, Hoarding $hoarding)
    {
        $request->validate([
            'commission' => 'required|numeric|min:0.01|max:100',
        ]);

        $this->commissionService->saveHoardingCommission($hoarding, (float) $request->commission);

        // Return the resolved commission (may cascade from vendor rules)
        $resolved = $this->commissionService->resolveForHoarding($hoarding->fresh());

        return response()->json([
            'success'             => true,
            'message'             => 'Commission saved for this hoarding.',
            'commission'          => (float) $request->commission,
            'resolved_commission' => $resolved,
        ]);
    }

    // ─────────────────────────────────────────────────────
    // Save vendor-level commission (Flow B)
    // ─────────────────────────────────────────────────────
    public function save(Request $request)
    {
        $validated = $request->validate([
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

        $this->commissionService->saveVendorCommission(
            $validated,
            auth()->id()
        );

        // Return resolved commissions for all vendor hoardings so the UI can refresh
        $resolved = $this->commissionService->resolveForVendorWithMeta((int) $validated['vendor_id']);


        return response()->json([
            'success'             => true,
            'message'             => 'Vendor commission saved successfully.',
            'resolved_commissions' => $resolved,
        ]);
    }

    // ─────────────────────────────────────────────────────
    // Delete a single commission rule
    // ─────────────────────────────────────────────────────
    public function destroy(CommissionSetting $commission)
    {
        $commission->delete();
        return response()->json(['success' => true]);
    }

        
    // ─────────────────────────────────────────────────────
    // Dedicated commission rules page for admin
    // Route: GET /admin/commission/{vendor}/rules
    // Name: admin.commission.vendor.rules
    // ─────────────────────────────────────────────────────
    public function vendorRules(User $vendor)
    {
        $request = request();

        $existingCommissions = CommissionSetting::where('vendor_id', $vendor->id)
            ->orderByRaw("CASE WHEN state IS NULL AND city IS NULL THEN 0 WHEN city IS NULL THEN 1 ELSE 2 END")
            ->orderBy('state')
            ->orderBy('city')
            ->orderBy('hoarding_type')
            ->get();

        // Group into levels for the view
        // Global: all|| only (vendor-wide base commission)
        $globalRules = $existingCommissions->filter(fn($r) => !$r->state && !$r->city && $r->hoarding_type === 'all');

        // Hoarding type: ooh|| / dooh|| only
        $hoardingTypeRules = $existingCommissions->filter(fn($r) => !$r->state && !$r->city && in_array($r->hoarding_type, ['ooh', 'dooh'], true));

        $stateRules  = $existingCommissions->filter(fn($r) =>  $r->state && !$r->city)
                        ->groupBy('state');
        $cityRules   = $existingCommissions->filter(fn($r) =>  $r->state &&  $r->city)
                        ->groupBy(fn($r) => "{$r->state}|||{$r->city}");

        // Hoarding-level overrides (hoardings.commission_percent > 0)
        $hoardingOverrides = Hoarding::where('vendor_id', $vendor->id)
            ->whereNotNull('commission_percent')
            ->where('commission_percent', '>', 0)
            ->select('id', 'title', 'name', 'hoarding_type', 'state', 'city', 'commission_percent')
            ->orderBy('state')
            ->orderBy('city')
            ->get();

        // Resolved commissions for every hoarding (to show effective rate)
        $resolvedCommissions = $this->commissionService->resolveForVendor($vendor->id);

        $effectiveStates = Hoarding::where('vendor_id', $vendor->id)
            ->whereNotNull('state')
            ->distinct()
            ->orderBy('state')
            ->pluck('state');

        $effectiveCities = Hoarding::where('vendor_id', $vendor->id)
            ->whereNotNull('city')
            ->when($request->filled('effective_state'), fn($q) => $q->where('state', $request->input('effective_state')))
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        $effectiveQuery = Hoarding::where('vendor_id', $vendor->id)
            ->select('id', 'title', 'name', 'hoarding_type', 'state', 'city', 'commission_percent')
            ->when($request->filled('effective_search'), function ($q) use ($request) {
                $search = $request->input('effective_search');
                $q->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('effective_type'), fn($q) => $q->where('hoarding_type', $request->input('effective_type')))
            ->when($request->filled('effective_state'), fn($q) => $q->where('state', $request->input('effective_state')))
            ->when($request->filled('effective_city'), fn($q) => $q->where('city', $request->input('effective_city')))
            ->orderBy('state')
            ->orderBy('city')
            ->orderBy('title');

        $effectivePerPage = (int) $request->input('effective_per_page', 10);
        if (!in_array($effectivePerPage, [10, 25, 50], true)) {
            $effectivePerPage = 10;
        }

        $effectiveHoardings = $effectiveQuery->paginate($effectivePerPage, ['*'], 'effective_page')->withQueryString();

        $flatRuleMap = [];
        foreach ($existingCommissions as $rule) {
            $flatRuleMap["{$rule->hoarding_type}|{$rule->state}|{$rule->city}"] = true;
        }

        $hasAnyRules = $existingCommissions->isNotEmpty() || $hoardingOverrides->isNotEmpty();

        return view('admin.settings.commission-setting.vendor.rules',
            compact(
                'vendor',
                'globalRules',
                'hoardingTypeRules',
                'stateRules',
                'cityRules',
                'hoardingOverrides',
                'resolvedCommissions',
                'effectiveHoardings',
                'effectiveStates',
                'effectiveCities',
                'flatRuleMap',
                'hasAnyRules'
            )
        );
    }
}