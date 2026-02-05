<?php

namespace Modules\Enquiries\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class EnquiryItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            /* ================= Enquiry Summary ================= */
            'id'              => $this->id,
            'enquiry_no'      => $this->enquiry_no,
            'status'          => $this->status,
            'status_label'    => ucfirst(str_replace('_', ' ', $this->status)),
            'requirement'     => $this->customer_note,
            'submitted_on'    => $this->created_at?->format('d M Y'),
            'last_updated'    => $this->updated_at?->format('d M Y, H:i'),
            'total_hoardings' => $this->items_count,
            'total_vendors'   => $this->vendor_count,

            /* ================= Vendor Details ================= */
            'vendors' => $this->vendors(),

            /* ================= Hoardings ================= */
            'hoardings' => [
                'ooh'  => $this->hoardingsByType('ooh'),
                'dooh' => $this->hoardingsByType('dooh'),
            ],

            /* ================= Offers ================= */
            'offers' => [],
        ];
    }

    /* ---------------- Helpers ---------------- */

    protected function vendors()
    {
        return $this->items
            ->pluck('hoarding.vendor')
            ->filter()
            ->unique('id')
            ->values()
            ->map(fn ($vendor) => [
                'id'            => $vendor->id,
                'name'          => $vendor->name,
                'business_name' => $vendor->company_name ?? 'N/A',
                'gst'           => $vendor->gst_number ?? 'N/A',
                'email'         => $vendor->email,
                'phone'         => $vendor->mobile,
                'address'       => $vendor->address ?? 'N/A',
            ]);
    }

    protected function hoardingsByType(string $type)
    {
        return $this->items
            ->filter(fn ($item) => $item->hoarding?->hoarding_type === $type)
            ->values()
            ->map(fn ($item) => [
                'hoarding_id'     => $item->hoarding->id,
                'title'           => $item->hoarding->title,
                'location'        => $item->hoarding->display_location,
                'type'            => $type,
                'campaign_start'  => optional($item->preferred_start_date)->format('d M Y'),
                'campaign_duration' => $item->expected_duration,
                'package'         => $item->package_type ?? '-',
                'price'           => data_get($item->meta, 'amount', 0),
                'hero_image'      => $item->hoarding->hero_image_url,
            ]);
    }
}
