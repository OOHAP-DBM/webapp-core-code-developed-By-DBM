<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HoardingAttribute extends Model
{
    protected $fillable = [
        'type', 'label', 'value', 'is_active',
    ];

    /**
     * Scope a query to only include active attributes.
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get attributes grouped by type.
     * @return array
     */
    public static function groupedByType()
    {
        return static::active()->get()->groupBy('type');
    }
}
