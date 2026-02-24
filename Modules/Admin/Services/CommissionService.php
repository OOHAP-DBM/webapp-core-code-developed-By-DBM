<?php
// Modules/Admin/Services/CommissionService.php

namespace Modules\Admin\Services;

use App\Models\CommissionSetting;
use App\Models\Hoarding;
use App\Models\User;
use App\Models\VendorProfile;
use App\Notifications\CommissionNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class CommissionService
{
    /**
     * Resolve the effective commission % for a single hoarding.
     * Priority (highest → lowest):
     *   1. Hoarding-level override  (hoardings.commission_percent)
     *   2. city + type
     *   3. city + 'all'
     *   4. state + type
     *   5. state + 'all'
     *   6. global type
     *   7. global 'all'
     *   8. null
     */
    public function resolveForHoarding(Hoarding $hoarding): ?float
    {
        // 1. Hoarding-specific override — only if explicitly set and > 0
        if ($hoarding->commission_percent !== null && (float) $hoarding->commission_percent > 0) {
            return (float) $hoarding->commission_percent;
        }

        $vendorId = $hoarding->vendor_id;
        $type     = $hoarding->hoarding_type; // 'ooh' | 'dooh'
        $state    = $hoarding->state;
        $city     = $hoarding->city;

        $rules = CommissionSetting::where('vendor_id', $vendorId)
            ->get()
            ->keyBy(fn($r) => "{$r->hoarding_type}|{$r->state}|{$r->city}");

        $checks = [
            "{$type}|{$state}|{$city}",   // city + exact type
            "all|{$state}|{$city}",        // city + all
            "{$type}|{$state}|",           // state + exact type
            "all|{$state}|",               // state + all
            "{$type}||",                   // global exact type
            "all||",                       // global all
        ];

        foreach ($checks as $key) {
            if (isset($rules[$key])) {
                return (float) $rules[$key]->commission_percent;
            }
        }

        return null;
    }

    /**
     * Resolve commission for all hoardings of a vendor at once (bulk, N+0 queries).
     * Returns: array<hoarding_id, float|null>
     */
    public function resolveForVendor(int $vendorId): array
    {
        $hoardings = Hoarding::where('vendor_id', $vendorId)
            ->select('id', 'vendor_id', 'hoarding_type', 'state', 'city', 'commission_percent')
            ->get();

        $rules = CommissionSetting::where('vendor_id', $vendorId)
            ->get()
            ->keyBy(fn($r) => "{$r->hoarding_type}|{$r->state}|{$r->city}");

        $result = [];
        foreach ($hoardings as $hoarding) {
            $result[$hoarding->id] = $this->resolveFromRules($hoarding, $rules);
        }
        return $result;
    }

    /**
     * Save vendor-level commission settings and send notification.
     */
    public function saveVendorCommission(array $data, int $adminId): void
    {
        $vendorId = (int) $data['vendor_id'];

        $isFirstTime = CommissionSetting::where('vendor_id', $vendorId)->doesntExist();

        DB::transaction(function () use ($data, $vendorId, $adminId) {
            CommissionSetting::where('vendor_id', $vendorId)->delete();

            if ($data['apply_to_all_types']) {
                $this->upsert($vendorId, 'all', null, null, [
                    'commission_percent' => $data['base_commission'],
                    'set_by'             => $adminId,
                ]);
            } else {
                if (!is_null($data['ooh_commission'] ?? null)) {
                    $this->upsert($vendorId, 'ooh', null, null, [
                        'commission_percent' => $data['ooh_commission'],
                        'set_by'             => $adminId,
                    ]);
                }
                if (!is_null($data['dooh_commission'] ?? null)) {
                    $this->upsert($vendorId, 'dooh', null, null, [
                        'commission_percent' => $data['dooh_commission'],
                        'set_by'             => $adminId,
                    ]);
                }
            }

            if (!($data['apply_all_states'] ?? true) && !empty($data['states'])) {
                foreach ($data['states'] as $sd) {
                    if ($data['apply_to_all_types'] && !empty($sd['commission'])) {
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

            if (!($data['apply_all_cities'] ?? true) && !empty($data['cities'])) {
                foreach ($data['cities'] as $cd) {
                    if ($data['apply_to_all_types'] && !empty($cd['commission'])) {
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

            $profileCommission = ($data['apply_to_all_types'] ?? true)
                ? (float) ($data['base_commission'] ?? 0)
                : (float) ($data['ooh_commission'] ?? $data['dooh_commission'] ?? $data['base_commission'] ?? 0);

            $this->saveVendorProfileCommission($vendorId, $profileCommission);
        });

        // Send notification after transaction commits
        $this->notifyVendor($vendorId, $data, $isFirstTime);
    }

    /**
     * Save commission directly on a hoarding row and notify vendor.
     */
    public function saveHoardingCommission(Hoarding $hoarding, float $commission): void
    {
        $isFirstTime = !($hoarding->commission_percent > 0); // only true if no real value was set

        $hoarding->update(['commission_percent' => $commission]);

        // $this->saveVendorProfileCommission((int) $hoarding->vendor_id, $commission);

        $this->notifyVendor($hoarding->vendor_id, [
            'apply_to_all_types' => false,
            'base_commission'    => $commission,
        ], $isFirstTime, $hoarding);
    }

    // ─────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────

    private function resolveFromRules(Hoarding $hoarding, Collection $rules): ?float
    {
        // Hoarding-level override — only if explicitly set and > 0
        if ($hoarding->commission_percent !== null && (float) $hoarding->commission_percent > 0) {
            return (float) $hoarding->commission_percent;
        }

        $type  = $hoarding->hoarding_type;
        $state = $hoarding->state ?? '';
        $city  = $hoarding->city  ?? '';

        $checks = [
            "{$type}|{$state}|{$city}",
            "all|{$state}|{$city}",
            "{$type}|{$state}|",
            "all|{$state}|",
            "{$type}||",
            "all||",
        ];

        foreach ($checks as $key) {
            if (isset($rules[$key])) {
                return (float) $rules[$key]->commission_percent;
            }
        }

        return null;
    }

    private function notifyVendor(int $vendorId, array $data, bool $isFirstTime, ?Hoarding $hoarding = null): void
    {
        try {
            $vendor = User::find($vendorId);
            if (!$vendor) return;

            $commissionType = ($data['apply_to_all_types'] ?? true) ? 'all' : 'mixed';

            // For "mixed" (separate OOH/DOOH), use ooh_commission as the base for display
            // For "all", use base_commission
            $baseCommission = $commissionType === 'all'
                ? (float) $data['base_commission']
                : (float) ($data['ooh_commission'] ?? $data['dooh_commission'] ?? $data['base_commission']);

            $vendor->notify(new CommissionNotification(
                type:           $isFirstTime ? 'set' : 'updated',
                commission:     $baseCommission,
                commissionType: $commissionType,
                oohCommission:  isset($data['ooh_commission'])  ? (float) $data['ooh_commission']  : null,
                doohCommission: isset($data['dooh_commission']) ? (float) $data['dooh_commission'] : null,
                hoardingName:   $hoarding?->title ?? $hoarding?->name ?? null, // NEW
            ));
        } catch (\Throwable $e) {
            Log::error('Commission notification failed', [
                'vendor_id' => $vendorId,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    private function upsert(?int $vendorId, string $type, ?string $state, ?string $city, array $data): void
    {
        CommissionSetting::updateOrCreate(
            ['vendor_id' => $vendorId, 'hoarding_type' => $type, 'state' => $state, 'city' => $city],
            $data
        );
    }

    private function saveVendorProfileCommission(int $vendorId, float $commission): void
    {
        $vendorProfile = VendorProfile::firstOrNew(['user_id' => $vendorId]);
        $vendorProfile->commission_percentage = $commission;
        $vendorProfile->commission_agreement_accepted = false;
        $vendorProfile->save();
    }


    /**
     * Like resolveForVendor() but also returns whether each hoarding has an individual override.
     * Returns: array<hoarding_id, ['commission' => float|null, 'is_override' => bool]>
     */
    public function resolveForVendorWithMeta(int $vendorId): array
    {
        $hoardings = Hoarding::where('vendor_id', $vendorId)
            ->select('id', 'vendor_id', 'hoarding_type', 'state', 'city', 'commission_percent')
            ->get();

        $rules = CommissionSetting::where('vendor_id', $vendorId)
            ->get()
            ->keyBy(fn($r) => "{$r->hoarding_type}|{$r->state}|{$r->city}");

        $result = [];
        foreach ($hoardings as $hoarding) {
            $hasOverride = $hoarding->commission_percent !== null
                        && (float) $hoarding->commission_percent > 0;

            $result[$hoarding->id] = [
                'commission'  => $this->resolveFromRules($hoarding, $rules),
                'is_override' => $hasOverride,
            ];
        }
        return $result;
    }
}