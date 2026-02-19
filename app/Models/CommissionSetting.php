<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionSetting extends Model
{
   protected $table = 'commission_settings';

    protected $fillable = [
        'vendor_id',
        'hoarding_type',
        'state',
        'city',
        'commission_percent',
        'set_by',
        'notes',
    ];

    protected $casts = [
        'commission_percent' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function setBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by');
    }

    /**
     * Resolve the effective commission for a hoarding.
     * Priority: city > state > type > global
     */
    public static function resolveFor(Hoarding $hoarding): ?self
    {
        $vendorId = $hoarding->vendor_id;
        $type     = $hoarding->hoarding_type;
        $state    = $hoarding->state;
        $city     = $hoarding->city;

        $candidates = self::where(function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId)
                  ->orWhereNull('vendor_id');
            })
            ->where(function ($q) use ($type) {
                $q->where('hoarding_type', $type)
                  ->orWhere('hoarding_type', 'all');
            })
            ->get();

        $rule = $candidates->first(fn($c) =>
            $c->city === $city && $c->state === $state && $c->hoarding_type === $type
        );
        if ($rule) return $rule;

        $rule = $candidates->first(fn($c) =>
            $c->city === $city && $c->state === $state && $c->hoarding_type === 'all'
        );
        if ($rule) return $rule;

        $rule = $candidates->first(fn($c) =>
            is_null($c->city) && $c->state === $state && $c->hoarding_type === $type
        );
        if ($rule) return $rule;

        $rule = $candidates->first(fn($c) =>
            is_null($c->city) && $c->state === $state && $c->hoarding_type === 'all'
        );
        if ($rule) return $rule;

        $rule = $candidates->first(fn($c) =>
            is_null($c->city) && is_null($c->state) && $c->hoarding_type === $type
        );
        if ($rule) return $rule;

        return $candidates->first(fn($c) =>
            is_null($c->city) && is_null($c->state) && $c->hoarding_type === 'all'
        );
    }
}


