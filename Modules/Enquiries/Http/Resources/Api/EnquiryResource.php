<?php

namespace Modules\Enquiries\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class EnquiryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'enquiry_no'      => $this->enquiry_no, // 👈 from model accessor
            'requirement'     => $this->customer_note,
            'status'          => $this->status,
            'status_label'    => $this->statusLabel(),
            'vendor_count'    => $this->vendor_count ?? 0,
            'locations_count' => $this->items_count ?? 0,
            'date'            => optional($this->created_at)->format('d M, Y'),
        ];
    }
     private function statusLabel(): string
    {
        return match ($this->status) {
            'submitted' => 'Enquiry Sent',
            'accepted'  => 'Accepted',
            'rejected'  => 'Rejected',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }
}
