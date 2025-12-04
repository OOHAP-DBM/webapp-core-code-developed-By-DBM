<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HoardingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'vendor' => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'email' => $this->vendor->email,
                'phone' => $this->vendor->phone,
            ],
            'title' => $this->title,
            'description' => $this->description,
            'address' => $this->address,
            'location' => [
                'lat' => (float) $this->lat,
                'lng' => (float) $this->lng,
            ],
            'pricing' => [
                'weekly' => $this->weekly_price ? (float) $this->weekly_price : null,
                'monthly' => (float) $this->monthly_price,
                'enable_weekly_booking' => (bool) $this->enable_weekly_booking,
            ],
            'type' => $this->type,
            'type_label' => $this->type_label,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'is_active' => $this->isActive(),
            'supports_weekly_booking' => $this->supportsWeeklyBooking(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
