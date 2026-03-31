<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplateVariable extends Model
{
    protected $fillable = [
        'template_id',
        'variable_name',
        'variable_type',
        'is_required',
        'default_value',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    // ye variable kis template ka hai
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    // sirf required variables
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }
}