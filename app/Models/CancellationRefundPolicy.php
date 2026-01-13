<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CancellationRefundPolicy extends Model
{
    protected $fillable = [
        'title',
        'content',
        'effective_date',
        'is_active',
    ];
}
