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
        array $meta,
        $packageId = null,
        $months = 1
    ): EnquiryItem {
        // Normalize hoarding_type to only 'ooh' or 'dooh'
        $type = strtolower($hoarding->hoarding_type);
        if (str_contains($type, 'dooh')) {
            $type = 'dooh';
        } else {
            $type = 'ooh';
        }
        
        // Format duration as "1-month", "2-month", etc.
        $durationText = $months . '-month';
        
        // LOG: Show what meta is being saved
        \Log::info('[ENQUIRY ITEM REPO] Saving to database:', [
            'meta_dooh_specs' => $meta['dooh_specs'] ?? null,
            'meta_discount_percent' => $meta['discount_percent'] ?? null,
            'all_meta' => $meta,
        ]);
        
        return EnquiryItem::create([
            'enquiry_id' => $enquiry->id,
            'hoarding_id' => $hoarding->id,
            'hoarding_type' => $type,
            'package_id' => $packageId,  // SAVE PACKAGE ID
            'package_type' => $packageType,
            'preferred_start_date' => $startDate,
            'preferred_end_date' => $endDate,
            'expected_duration' => $durationText,  // Show as "1-month", "2-month", "5-month"
            'services' => $services,
            'meta' => $meta,
            'status' => EnquiryItem::STATUS_NEW,
        ]);
    }
}