<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'business_type',
        'gstin',
        'pan_number',
        'pan_document',
        'country',
        'state',
        'city',
        'pincode',
        'address',
        'bank_name',
        'account_holder_name',
        'account_number',
        'ifsc_code',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}