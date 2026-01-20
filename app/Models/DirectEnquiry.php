<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectEnquiry extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'location_city',
        'hoarding_type',
        'hoarding_location',
        'remarks'
    ];
}
