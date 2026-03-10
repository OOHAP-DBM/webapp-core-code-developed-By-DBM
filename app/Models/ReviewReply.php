<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewReply extends Model
{
    protected $fillable = [
        'rating_id',
        'user_id',
        'role',
        'reply',
    ];

    // Rating relation
    public function rating()
    {
        return $this->belongsTo(Rating::class);
    }

    // User relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}