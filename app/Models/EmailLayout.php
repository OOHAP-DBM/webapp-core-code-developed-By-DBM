<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailLayout extends Model
{
    protected $fillable = [
        'logo_url',
        'header_html',
        'footer_html',
        'primary_color',
        'font_family',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ek layout ke andar bohot saare templates ho sakte hain
    public function templates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class, 'layout_id');
    }

    // sirf active layout lao
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}