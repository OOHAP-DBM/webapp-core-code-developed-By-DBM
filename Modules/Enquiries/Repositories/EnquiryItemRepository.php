<?php

namespace Modules\Enquiries\Repositories;

use Modules\Enquiries\Models\EnquiryItem;

class EnquiryItemRepository
{
    public function create(
        $enquiry,
        $hoarding,
        $startDate,
        $endDate,
        array $services,
        string $packageType,
        array $meta
    ): EnquiryItem {
        // Normalize hoarding_type to only 'ooh' or 'dooh'
        $type = strtolower($hoarding->hoarding_type);
        if (str_contains($type, 'dooh')) {
            $type = 'dooh';
        } else {
            $type = 'ooh';
        }
        return EnquiryItem::create([
            'enquiry_id' => $enquiry->id,
            'hoarding_id' => $hoarding->id,
            'hoarding_type' => $type,
            'package_type' => $packageType,
            'preferred_start_date' => $startDate,
            'preferred_end_date' => $endDate,
            'expected_duration' => $startDate->diffInDays($endDate) . ' days',
            'services' => $services,
            'meta' => $meta,
            'status' => EnquiryItem::STATUS_NEW,
        ]);
    }
}