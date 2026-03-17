<?php

namespace App\Repositories;

use App\Models\Hoarding;

class HoardingRepository
{
    /**
     * Find duplicate hoarding based on import row data.
     * @param array $data
     * @return Hoarding|null
     */
    public function findDuplicate(array $data)
    {
        $query = Hoarding::query()
            ->where('vendor_id', $data['vendor_id'])
            ->where('city', strtolower(trim($data['city'])))
            ->where(function ($q) use ($data) {
                $q->where('locality', strtolower(trim($data['locality'])))
                  ->orWhere('address', 'like', '%' . strtolower(trim($data['address'])) . '%');
            });

        // Geo match (lat/lng)
        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            $lat = round($data['latitude'], 4);
            $lng = round($data['longitude'], 4);
            $query->whereRaw('ROUND(latitude, 4) = ?', [$lat])
                  ->whereRaw('ROUND(longitude, 4) = ?', [$lng]);
        }

        // OOH logic
        if ($data['type'] === 'OOH') {
            $query->whereHas('oohHoarding', function ($q) use ($data) {
                $q->where('width', $data['width'])
                  ->where('height', $data['height'])
                  ->where('measurement_unit', $data['measurement_unit']);
            });
        }

        // DOOH logic
        if ($data['type'] === 'DOOH') {
            $query->whereHas('doohScreen', function ($q) use ($data) {
                $q->where('screen_type', $data['screen_type'])
                  ->where(function ($q2) use ($data) {
                      $q2->where('width', $data['width'])
                         ->where('height', $data['height'])
                         ->orWhere('resolution_width', $data['resolution_width']);
                  });
            });
        }

        return $query->first();
    }
}
