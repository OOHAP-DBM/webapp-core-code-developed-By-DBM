<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    // Setting Groups
    const GROUP_GENERAL = 'general';
    const GROUP_BOOKING = 'booking';
    const GROUP_PAYMENT = 'payment';
    const GROUP_COMMISSION = 'commission';
    const GROUP_NOTIFICATION = 'notification';
    const GROUP_KYC = 'kyc';
    const GROUP_DOOH = 'dooh';
    const GROUP_AUTOMATION = 'automation';
    const GROUP_CANCELLATION = 'cancellation';
    const GROUP_REFUND = 'refund';

    // Setting Types
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_JSON = 'json';
    const TYPE_ARRAY = 'array';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'group',
        'tenant_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tenant_id' => 'integer',
    ];

    /**
     * Boot the model and clear cache on save/delete.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            Cache::forget("setting_{$setting->key}_{$setting->tenant_id}");
            Cache::forget("setting_group_{$setting->group}_{$setting->tenant_id}");
        });

        static::deleted(function ($setting) {
            Cache::forget("setting_{$setting->key}_{$setting->tenant_id}");
            Cache::forget("setting_group_{$setting->group}_{$setting->tenant_id}");
        });
    }

    /**
     * Scope a query to only include global settings.
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('tenant_id');
    }

    /**
     * Scope a query to only include tenant-specific settings.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query by group.
     */
    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Get the typed value of the setting.
     *
     * @return mixed
     */
    public function getTypedValue()
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->value, true),
            'array' => json_decode($this->value, true) ?? [],
            default => $this->value,
        };
    }

    /**
     * Set the value with proper type conversion.
     *
     * @param mixed $value
     * @return void
     */
    public function setTypedValue($value)
    {
        $this->value = match ($this->type) {
            'boolean' => $value ? '1' : '0',
            'json', 'array' => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Get a setting value with caching.
     *
     * @param string $key
     * @param mixed $default
     * @param int|null $tenantId
     * @return mixed
     */
    public static function get(string $key, $default = null, ?int $tenantId = null)
    {
        $cacheKey = "setting_{$key}_{$tenantId}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default, $tenantId) {
            $query = self::where('key', $key);

            if ($tenantId === null) {
                $query->whereNull('tenant_id');
            } else {
                $query->where('tenant_id', $tenantId);
            }

            $setting = $query->first();

            return $setting ? $setting->getTypedValue() : $default;
        });
    }

    /**
     * Set a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string $group
     * @param string|null $description
     * @param int|null $tenantId
     * @return Setting
     */
    public static function set(
        string $key,
        $value,
        string $type = self::TYPE_STRING,
        string $group = self::GROUP_GENERAL,
        ?string $description = null,
        ?int $tenantId = null
    ): self {
        // Prepare the value based on type
        $preparedValue = match ($type) {
            self::TYPE_BOOLEAN => $value ? '1' : '0',
            self::TYPE_JSON, self::TYPE_ARRAY => json_encode($value),
            default => (string) $value,
        };

        $setting = self::updateOrCreate(
            [
                'key' => $key,
                'tenant_id' => $tenantId,
            ],
            [
                'value' => $preparedValue,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );

        return $setting;
    }

    /**
     * Get all settings in a group with caching.
     *
     * @param string $group
     * @param int|null $tenantId
     * @return array
     */
    public static function getGroup(string $group, ?int $tenantId = null): array
    {
        $cacheKey = "setting_group_{$group}_{$tenantId}";

        return Cache::remember($cacheKey, 3600, function () use ($group, $tenantId) {
            $query = self::where('group', $group);

            if ($tenantId === null) {
                $query->whereNull('tenant_id');
            } else {
                $query->where('tenant_id', $tenantId);
            }

            return $query->get()->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->getTypedValue()];
            })->toArray();
        });
    }

    /**
     * Get available setting groups.
     *
     * @return array
     */
    public static function getAvailableGroups(): array
    {
        return [
            self::GROUP_GENERAL => 'General Settings',
            self::GROUP_BOOKING => 'Booking Rules',
            self::GROUP_PAYMENT => 'Payment Settings',
            self::GROUP_COMMISSION => 'Commission Settings',
            self::GROUP_NOTIFICATION => 'Notification Templates',
            self::GROUP_KYC => 'KYC Rules',
            self::GROUP_DOOH => 'DOOH API Configuration',
            self::GROUP_AUTOMATION => 'Automation Rules',
            self::GROUP_CANCELLATION => 'Cancellation Rules',
            self::GROUP_REFUND => 'Refund Logic',
        ];
    }

    /**
     * Clear all setting caches.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        Cache::flush();
    }
}
