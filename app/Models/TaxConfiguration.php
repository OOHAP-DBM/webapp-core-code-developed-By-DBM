<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * PROMPT 109: Global Tax Configuration
 * 
 * Admin-configurable tax settings (GST, TCS, TDS)
 * Works alongside TaxRule (PROMPT 62) for rate management
 */
class TaxConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'config_type',
        'value',
        'data_type',
        'group',
        'is_active',
        'applies_to',
        'country_code',
        'metadata',
        'validation_rules',
    ];

    protected $casts = [
        'value' => 'string', // Will be typed in accessor
        'is_active' => 'boolean',
        'metadata' => 'array',
        'validation_rules' => 'array',
    ];

    // Configuration types
    const TYPE_GST = 'gst';
    const TYPE_TCS = 'tcs';
    const TYPE_TDS = 'tds';
    const TYPE_GENERAL = 'general';

    // Data types
    const DATA_BOOLEAN = 'boolean';
    const DATA_INTEGER = 'integer';
    const DATA_FLOAT = 'float';
    const DATA_STRING = 'string';
    const DATA_ARRAY = 'array';

    // Groups
    const GROUP_TAX_RATES = 'tax_rates';
    const GROUP_TAX_RULES = 'tax_rules';
    const GROUP_TCS_RULES = 'tcs_rules';
    const GROUP_TDS_RULES = 'tds_rules';
    const GROUP_EXEMPTIONS = 'exemptions';

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($config) {
            Cache::forget("tax_config_{$config->key}");
            Cache::forget("tax_config_group_{$config->group}");
        });

        static::deleted(function ($config) {
            Cache::forget("tax_config_{$config->key}");
            Cache::forget("tax_config_group_{$config->group}");
        });
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('config_type', $type);
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeForCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    /**
     * Get typed value with caching
     */
    public function getTypedValue()
    {
        return match ($this->data_type) {
            self::DATA_BOOLEAN => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            self::DATA_INTEGER => (int) $this->value,
            self::DATA_FLOAT => (float) $this->value,
            self::DATA_ARRAY => json_decode($this->value, true) ?? [],
            default => $this->value,
        };
    }

    /**
     * Set typed value
     */
    public function setTypedValue($value): void
    {
        $this->value = match ($this->data_type) {
            self::DATA_BOOLEAN => $value ? '1' : '0',
            self::DATA_ARRAY => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Get configuration value by key with caching
     */
    public static function getValue(string $key, $default = null)
    {
        return Cache::remember("tax_config_{$key}", 3600, function () use ($key, $default) {
            $config = static::where('key', $key)->where('is_active', true)->first();
            return $config ? $config->getTypedValue() : $default;
        });
    }

    /**
     * Get all configs in a group with caching
     */
    public static function getGroup(string $group): array
    {
        return Cache::remember("tax_config_group_{$group}", 3600, function () use ($group) {
            return static::where('group', $group)
                ->where('is_active', true)
                ->get()
                ->mapWithKeys(function ($config) {
                    return [$config->key => $config->getTypedValue()];
                })
                ->toArray();
        });
    }

    /**
     * Set configuration value
     */
    public static function setValue(
        string $key,
        $value,
        string $dataType = self::DATA_STRING,
        ?string $configType = self::TYPE_GENERAL,
        ?string $group = self::GROUP_TAX_RULES,
        ?string $description = null
    ): self {
        $config = static::firstOrNew(['key' => $key]);
        $config->data_type = $dataType;
        $config->setTypedValue($value);
        $config->config_type = $configType;
        $config->group = $group;
        $config->description = $description ?? $config->description;
        $config->is_active = true;
        $config->save();

        return $config;
    }

    /**
     * Validate value against rules
     */
    public function validate($value): bool
    {
        if (empty($this->validation_rules)) {
            return true;
        }

        foreach ($this->validation_rules as $rule => $ruleValue) {
            switch ($rule) {
                case 'min':
                    if ($value < $ruleValue) return false;
                    break;
                case 'max':
                    if ($value > $ruleValue) return false;
                    break;
                case 'in':
                    if (!in_array($value, $ruleValue)) return false;
                    break;
                case 'regex':
                    if (!preg_match($ruleValue, $value)) return false;
                    break;
            }
        }

        return true;
    }

    /**
     * Get all GST configurations
     */
    public static function getGSTConfig(): array
    {
        return static::byType(self::TYPE_GST)->active()->get()->mapWithKeys(function ($config) {
            return [$config->key => $config->getTypedValue()];
        })->toArray();
    }

    /**
     * Get all TCS configurations
     */
    public static function getTCSConfig(): array
    {
        return static::byType(self::TYPE_TCS)->active()->get()->mapWithKeys(function ($config) {
            return [$config->key => $config->getTypedValue()];
        })->toArray();
    }

    /**
     * Get all TDS configurations
     */
    public static function getTDSConfig(): array
    {
        return static::byType(self::TYPE_TDS)->active()->get()->mapWithKeys(function ($config) {
            return [$config->key => $config->getTypedValue()];
        })->toArray();
    }
}
