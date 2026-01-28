<?php

namespace Modules\Enquiries\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectEnquiry extends Model
{
    protected $fillable = [
        'vendor_id',
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
        'remarks',
        'is_email_verified',
        'is_phone_verified',
    ];

    protected $casts = [
        'preferred_locations' => 'array',
        'preferred_modes' => 'array',
        'is_email_verified' => 'boolean',
        'is_phone_verified' => 'boolean',
        'is_verified' => 'boolean',
    ];

    public function getPreferredLocationsTextAttribute()
    {
        return !empty($this->preferred_locations)
            ? implode(', ', (array) $this->preferred_locations)
            : 'Location needs to be discussed';
    }

}

