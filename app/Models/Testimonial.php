<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Testimonial extends Model
{
    use SoftDeletes;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'user_id',
        'role',
        'message',
        'rating',
        'status',
        'show_on_homepage',
        'approved_at',
        'approved_by',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'rating' => 'integer',
        'show_on_homepage' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    // User who submitted testimonial
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Admin who approved testimonial
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Query Scopes (IMPORTANT)
     */

    // Only approved testimonials
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Only pending testimonials
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Role based (customer / vendor)
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // Homepage visible
    public function scopeHomepage($query)
    {
        return $query->where('show_on_homepage', true);
    }
}
