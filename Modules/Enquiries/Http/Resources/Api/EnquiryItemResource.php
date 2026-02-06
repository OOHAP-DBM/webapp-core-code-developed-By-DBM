<?php

namespace Modules\Enquiries\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Hoardings\Models\HoardingPackage;
use Modules\DOOH\Models\DOOHPackage;
use App\Helpers\DurationHelper;
use App\Helpers\PricingEngine;


class EnquiryItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            /* ================= Enquiry Summary ================= */
            'id'              => $this->id,
            'enquiry_no'      => $this->enquiry_no,
            'status'          => $this->status,
            'status_label'    => "Waiting for Vendor Response",
            'requirement'     => $this->customer_note,
            'submitted_on'    => optional($this->created_at)->format('d M Y'),
            'last_updated'    => optional($this->updated_at)->format('d M Y, H:i'),
            'total_hoardings' => $this->items_count,
            'total_vendors'   => $this->vendor_count,
            'customer'        => $this->customerDetails(),

            /* ================= Vendors ================= */
            'vendors' => $this->vendors(),

            /* ================= Hoardings ================= */
            'hoardings' => [
                'ooh'  => $this->hoardingsByType('ooh'),
                'dooh' => $this->hoardingsByType('dooh'),
            ],
        ];
    }

    /* ===================================================== */
    /* ================= Vendors =========================== */
    /* ===================================================== */

    protected function vendors()
    {
        return $this->items
            ->pluck('hoarding.vendor')
            ->filter()
            ->unique('id')
            ->values()
            ->map(function ($vendor) {
                $profile = $vendor->vendorProfile;

                return [
                    'user_id'      => $vendor->id,
                    'company_name' => $profile?->company_name,
                    'gstin'        => $profile?->gstin,
                    'city'         => $profile?->city,
                    'state'        => $profile?->state,
                    'phone'        => $profile?->contact_person_phone ?? $vendor->mobile,
                    'email'        => $profile?->contact_person_email ?? $vendor->email,
                ];
            });
    }

    protected function customerDetails(): array
    {
        $customer = $this->customer;

        return [
            'id'            => $customer?->id,
            'name'          => $this->valueOrDash($customer?->name),
            'business_name' => $this->valueOrDash($customer?->company_name),
            'gstin'         => $this->valueOrDash($customer?->gstin),
            'mobile'        => $this->valueOrDash($customer?->phone ?? $this->contact_number),
            'address'       => $this->valueOrDash($customer?->address),
            'email'         => $this->valueOrDash($customer?->email),
        ];
    }

    protected function valueOrDash($value): string
    {
        return $value ? (string) $value : '-';
    }

    /* ===================================================== */
    /* ================= Hoardings ========================= */
    /* ===================================================== */

    protected function hoardingsByType(string $type)
    {
        return $this->items
            ->filter(fn ($item) => $item->hoarding?->hoarding_type === $type)
            ->values()
            ->map(function ($item) {
                return [
                    'hoarding_id'    => $item->hoarding->id,
                    'title'          => $item->hoarding->title,
                    'location'       => $item->hoarding->display_location,
                    'type'           => $item->hoarding->hoarding_type,
                    'campaign_start' => optional($item->preferred_start_date)->format('d M Y'),
                    'campaign_end'   => optional($item->preferred_end_date)->format('d M Y'),
                    'duration_label' => DurationHelper::normalize($item->expected_duration),

                    /* ===== Pricing ===== */
                   'pricing' => PricingEngine::resolve($item),

                    /* ===== Media ===== */
                  'hero_image' => $item->hoarding->heroImage(),
                ];
            });
    }

    /* ===================================================== */
    /* ================= Pricing Resolver ================== */
    /* ===================================================== */

    // protected function resolvePricing($item): array
    // {
    //     $baseMonthly = (float) ($item->hoarding->base_monthly_price ?? 0);
    //     $sellMonthly = (float) ($item->hoarding->monthly_price ?? null);

    //     $durationLabel = DurationHelper::normalize($item->expected_duration);
    //     $multiplier    = DurationHelper::multiplier($item->expected_duration);

    //     $package = $this->resolvePackage($item);
    //     $baseTotal = round($baseMonthly * $multiplier, 2);
    //     $discountPercent = 0;
    //     // Base price only
    //     if (!$package || $package['type'] === 'base') {
    //         return [
    //             'package'        => null,
    //         ];
    //     }

    //     // Package applied
    //     // $discountPercent = data_get(
    //     //     $item->meta,
    //     //     'discount_percent',
    //     //     $package['discount_percent']
    //     // );

    //     $discountedMonthly = calculateDiscountedPrice(
    //         $baseMonthly,
    //         $discountPercent
    //     );

    //     $finalTotal = round($discountedMonthly * $multiplier, 2);

    //     return [
    //         'package'            => $package,
    //         'base_monthly'       => display_price($baseMonthly),
    //         'discount_percent'   => $discountPercent,
    //         'discounted_monthly' => display_price($discountedMonthly),
    //         'duration_label'     => $durationLabel,
    //         'multiplier'         => $multiplier,
    //         'final_price'        => display_price($finalTotal),
    //     ];
    // }

    /* ===================================================== */
    /* ================= Package Resolver ================== */
    /* ===================================================== */

    protected function resolvePackage($item): ?array
    {
        // Base / No package
        if (!$item->package_id) {
            return [
                'type'  => 'base',
                'label' => data_get($item->meta, 'package_label', 'Base Price'),
            ];
        }

        $package = $item->hoarding_type === 'dooh'
            ? DOOHPackage::find($item->package_id)
            : HoardingPackage::find($item->package_id);

        if (!$package) {
            return null;
        }

        return [
            'type'             => 'package',
            'id'               => $package->id,
            'name'             => $package->name,
            'discount_percent' => (float) $package->discount_percent,
        ];
    }

    /* ===================================================== */
    /* ================= Media Resolver ==================== */
    /* ===================================================== */

   
}