<?php

namespace Modules\Offers\Entities;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        // Add fillable fields
        'title', 'description', 'discount', 'valid_from', 'valid_to',
    ];
}
