<?php

namespace Modules\Enquiries\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class EnquiryResource extends JsonResource
{
    
    public function toArray($request)
    {
        $hoardingsCount = isset($this->vendor_hoardings_count)
        ? $this->vendor_hoardings_count   // vendor context
        : ($this->items_count ?? 0); 
        return [
            'id'              => $this->id,
            'enquiry_no'      => $this->enquiry_no, // 👈 from model accessor
            'requirement'     => $this->customer_note,
            'status'          => $this->status,
            'status_label'    => $this->statusLabel(),
            'customer_name'   => $this->customer?->name,
            'customer_email'  => $this->customer?->email,
            'customer_phone'  => $this->customer?->phone,
            'vendor_count'    => $this->vendor_count ?? 0,
            'total_hoardings' => $hoardingsCount,
            'locations_count'=> $hoardingsCount,
            'created_at'            => optional($this->created_at)->format('d M, Y'),
            'preferred_campaign_start' => $this->enquiryCampaignStartDate(),
        ];
    }
     private function statusLabel(): string
    {
        return match ($this->status) {
            'new' => 'Waiting For Vendor Response',
            'accepted'  => 'Accepted',
            'rejected'  => 'Rejected',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
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

}