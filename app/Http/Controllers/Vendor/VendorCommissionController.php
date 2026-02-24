<?php
namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\CommissionSetting;
use App\Models\Hoarding;
use App\Models\User;
use App\Notifications\CommissionAgreementAcceptedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Admin\Services\CommissionService;

class VendorCommissionController extends Controller
{
    public function __construct(protected CommissionService $commissionService) {}

    public function index(Request $request)
    {
        $vendor   = auth()->user();
        $vendorId = $vendor->id;

        // Fetch all commission_settings rules for this vendor
        $existingCommissions = CommissionSetting::where('vendor_id', $vendorId)
            ->orderByRaw("CASE WHEN state IS NULL AND city IS NULL THEN 0 WHEN city IS NULL THEN 1 ELSE 2 END")
            ->orderBy('state')
            ->orderBy('city')
            ->orderBy('hoarding_type')
            ->get();

        $globalRules = $existingCommissions->filter(fn($r) => !$r->state && !$r->city);
        $stateRules  = $existingCommissions->filter(fn($r) =>  $r->state && !$r->city)->groupBy('state');
        $cityRules   = $existingCommissions->filter(fn($r) =>  $r->state &&  $r->city)
                          ->groupBy(fn($r) => "{$r->state}|||{$r->city}");

        // Hoarding-level overrides
        $hoardingOverrides = Hoarding::where('vendor_id', $vendorId)
            ->whereNotNull('commission_percent')
            ->where('commission_percent', '>', 0)
            ->select('id', 'title', 'name', 'hoarding_type', 'state', 'city', 'commission_percent')
            ->orderBy('state')->orderBy('city')
            ->get();

        // Resolved effective rate for every hoarding
        $resolvedCommissions = $this->commissionService->resolveForVendor($vendorId);

        $effectiveStates = Hoarding::where('vendor_id', $vendorId)
            ->whereNotNull('state')
            ->distinct()
            ->orderBy('state')
            ->pluck('state');

        $effectiveCities = Hoarding::where('vendor_id', $vendorId)
            ->whereNotNull('city')
            ->when($request->filled('effective_state'), fn($q) => $q->where('state', $request->input('effective_state')))
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        $effectiveQuery = Hoarding::where('vendor_id', $vendorId)
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

        $allHoardings = $effectiveQuery
            ->paginate($effectivePerPage, ['*'], 'effective_page')
            ->withQueryString();

        $hasAnyRules = $existingCommissions->isNotEmpty() || $hoardingOverrides->isNotEmpty();

        // Unread commission notifications for the agreement banner
        $pendingAgreement = $vendor->notifications()
            ->whereNull('read_at')
            ->whereIn('data->type', ['commission_set', 'commission_updated'])
            ->latest()
            ->first();

        $supportAdmin = User::query()
            ->where('active_role', 'admin')
            ->where(function ($query) {
                $query->whereNotNull('email')
                    ->orWhereNotNull('phone');
            })
            ->orderBy('id')
            ->first();

        $supportContact = [
            'name' => $supportAdmin?->name,
            'email' => $supportAdmin?->email,
            'phone' => $supportAdmin?->phone,
        ];

        return view('vendor.commission.index', compact(
            'vendor',
            'globalRules',
            'stateRules',
            'cityRules',
            'hoardingOverrides',
            'resolvedCommissions',
            'allHoardings',
            'effectiveStates',
            'effectiveCities',
            'hasAnyRules',
            'pendingAgreement',
            'supportContact'
        ));
    }

    // Vendor agrees to the commission with auditable, idempotent persistence
    public function agree(Request $request, string $notificationId): JsonResponse
    {
        $vendor = auth()->user();
        $notification = $vendor->notifications()->findOrFail($notificationId);

        $payload = (array) $notification->data;
        $isCommissionChange = in_array(($payload['type'] ?? null), ['commission_set', 'commission_updated'], true);

        if (!$isCommissionChange) {
            throw ValidationException::withMessages([
                'notification' => 'This notification does not require commission agreement.',
            ]);
        }

        if ($notification->read_at !== null && data_get($payload, 'agreement.accepted_at')) {
            return response()->json([
                'success' => true,
                'already_agreed' => true,
                'accepted_at' => data_get($payload, 'agreement.accepted_at'),
            ]);
        }

        $agreedAt = now();

        DB::transaction(function () use ($vendor, $notification, $payload, $request, $agreedAt): void {
            $vendorProfile = $vendor->vendorProfile()->lockForUpdate()->first();

            if (!$vendorProfile) {
                throw ValidationException::withMessages([
                    'vendor_profile' => 'Vendor profile is required before agreeing to commission terms.',
                ]);
            }

            $updates = [
                'commission_agreement_accepted' => true,
            ];

            $commission = $payload['commission'] ?? null;
            if (is_numeric($commission)) {
                $updates['commission_percentage'] = (float) $commission;
            }

            $vendorProfile->fill($updates)->save();

            $notification->forceFill([
                'read_at' => $agreedAt,
                'data' => array_merge($payload, [
                    'agreement' => [
                        'accepted_at' => $agreedAt->toIso8601String(),
                        'accepted_ip' => $request->ip(),
                        'accepted_user_id' => $vendor->id,
                    ],
                ]),
            ])->save();
        });

        $acceptedCommission = is_numeric($payload['commission'] ?? null)
            ? (float) $payload['commission']
            : null;

        $admins = User::query()
            ->where('active_role', 'admin')
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new CommissionAgreementAcceptedNotification(
                vendorName: $vendor->name,
                vendorId: $vendor->id,
                commission: $acceptedCommission,
                acceptedAt: $agreedAt->toDateTimeString(),
            ));
        }

        return response()->json([
            'success' => true,
            'already_agreed' => false,
            'accepted_at' => $agreedAt->toIso8601String(),
        ]);
    }
}