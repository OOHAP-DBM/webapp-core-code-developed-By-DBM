<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'user_id',
        'hoarding_id',
        'rating',
        'review'
    ];

    // User relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Hoarding relation
    public function hoarding()
    {
        return $this->belongsTo(Hoarding::class);
    }
    public function replies()
    {
        return $this->hasMany(ReviewReply::class);
    }

    public function vendorReply()
    {
        return $this->hasOne(ReviewReply::class)->where('role', 'vendor')->latest();
    }
}