<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelTranslation extends Model
{
    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'locale',
        'field',
        'value',
    ];

    /**
     * Get the owning translatable model
     */
    public function translatable()
    {
        return $this->morphTo();
    }
}
