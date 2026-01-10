<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutLeader extends Model
{
    protected $fillable = [
        'name',
        'designation',
        'bio',
        'image',
        'sort_order'
    ];
}
