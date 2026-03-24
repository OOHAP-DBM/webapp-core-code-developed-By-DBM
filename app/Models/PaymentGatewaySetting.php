<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentGatewaySetting extends Model
{
    protected $fillable = [
        'gateway',
        'key_id',
        'key_secret',
        'webhook_secret',
        'currency',
        'mode',
        'is_active',
        'business_name',
        'business_logo',
        'theme_color',
        'meta',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta'      => 'array',
    ];

    // ✅ Never expose secrets in JSON/array/API responses
    protected $hidden = ['key_secret', 'webhook_secret'];

    // =========================================================
    // MUTATORS — encrypt on save
    // =========================================================

    public function setKeySecretAttribute(?string $value): void
    {
        $this->attributes['key_secret'] = $value
            ? Crypt::encryptString($value)
            : null;
    }

    public function setWebhookSecretAttribute(?string $value): void
    {
        $this->attributes['webhook_secret'] = $value
            ? Crypt::encryptString($value)
            : null;
    }

    // =========================================================
    // ACCESSORS — decrypt on read
    // =========================================================

    public function getKeySecretAttribute(?string $value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null; // corrupted or wrong APP_KEY
        }
    }

    public function getWebhookSecretAttribute(?string $value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    // =========================================================
    // HELPERS
    // =========================================================

    public function isLive(): bool
    {
        return $this->mode === 'live';
    }

    public function isTest(): bool
    {
        return $this->mode === 'test';
    }

    // ✅ Check raw encrypted value — NOT decrypted accessor
    // because $hidden hides it from ->key_secret in some contexts
    public function isConfigured(): bool
    {
        return !empty($this->key_id)
            && !empty($this->attributes['key_secret'])
            && $this->is_active;
    }

    // ✅ Singleton getter for razorpay
    public static function getRazorpay(): ?self
    {
        return static::where('gateway', 'razorpay')->first();
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
