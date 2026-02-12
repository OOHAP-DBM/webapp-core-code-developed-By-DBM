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
            'enquiry_no'      => $this->formatted_id,
            'status'          => $this->status,
            'status_label'    => $this->statusLabel(),
            'requirement'     => $this->customer_note,
            'submitted_on'    => optional($this->created_at)->format('d M Y'),
           'preferred_campaign_start' => $this->enquiryCampaignStartDate(),
            'last_updated'    => optional($this->updated_at)->format('d M Y, H:i'),
            'total_hoardings' => $this->items_count,
            'total_locations' => $this->items_count,
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
                    'name'         => $vendor->name,
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
                    'preferred_campaign_start' => optional($item->preferred_start_date)->format('d M Y'),
                    'preferred_campaign_end'   => optional($item->preferred_end_date)->format('d M Y'),
                    'duration_label' => DurationHelper::normalize($item->expected_duration),

                    /* ===== Pricing ===== */
                   'pricing' => PricingEngine::resolve($item),

                    /* ===== Media ===== */
                  'hero_image' => $item->hoarding->heroImage(),
                ];
            });
    }

   private function statusLabel(): string
    {
        // Get viewer type from additional data, fallback to auto-detection
        $viewerType = $this->additional['viewer_type'] ?? 'user';
        
        return match ($this->status) {
            'submitted' => $viewerType === 'owner' ? 'Enquiry Received' : 'Enquiry Sent: Waiting for Vendor Response',
            'new' => 'Waiting For Vendor Response',
            'accepted'  => 'Accepted',
            'rejected'  => 'Rejected',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }

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


       protected function enquiryCampaignStartDate(): ?string
        {
            $date = $this->items
                ->pluck('preferred_start_date')
                ->filter()
                ->sort()
                ->first();

            return optional($date)->format('d M Y');
        }
    /* ===================================================== */
    /* ================= Media Resolver ==================== */
    /* ===================================================== */

   
}