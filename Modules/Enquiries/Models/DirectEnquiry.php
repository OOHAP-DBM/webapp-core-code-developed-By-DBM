<?php

namespace Modules\Enquiries\Models;

use Illuminate\Database\Eloquent\Model;

class DirectEnquiry extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'location_city',
        'hoarding_type',
        // 'hoarding_location',
        'preferred_locations',
        'preferred_modes',
        // 'best_way_to_connect',
        'is_verified',
        'remarks'
    ];
      protected $casts = [
        'preferred_locations' => 'array',
        'preferred_modes' => 'array',
    ];

    public function getPreferredLocationsTextAttribute()
    {
        return !empty($this->preferred_locations)
            ? implode(', ', (array) $this->preferred_locations)
            : 'Location needs to be discussed';
    }

}
