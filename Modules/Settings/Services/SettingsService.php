<?php

namespace Modules\Settings\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Settings\Repositories\Contracts\SettingRepositoryInterface;

class SettingsService
{
    /**
     * Cache key prefix for settings.
     */
    const CACHE_PREFIX = 'settings';

    /**
     * Cache TTL in seconds (1 hour).
     */
    const CACHE_TTL = 3600;

    /**
     * @var SettingRepositoryInterface
     */
    protected $repository;

    /**
     * SettingsService constructor.
     *
     * @param SettingRepositoryInterface $repository
     */
    public function __construct(SettingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @param int|null $tenantId
     * @return mixed
     */
    public function get(string $key, $default = null, ?int $tenantId = null)
    {
        $cacheKey = $this->getCacheKey($key, $tenantId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default, $tenantId) {
            $setting = $this->repository->findByKey($key, $tenantId);
            
            if (!$setting) {
                return $default;
            }

            return $setting->getTypedValue();
        });
    }

    /**
     * Set a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param int|null $tenantId
     * @param string|null $description
     * @param string $group
     * @return \App\Models\Setting
     */
    public function set(
        string $key,
        $value,
        string $type = 'string',
        ?int $tenantId = null,
        ?string $description = null,
        string $group = 'general'
    ) {
        $setting = $this->repository->createOrUpdate([
            'key' => $key,
            'value' => $this->convertValueForStorage($value, $type),
            'type' => $type,
            'description' => $description,
            'group' => $group,
            'tenant_id' => $tenantId,
        ]);

        // Clear cache
        $this->forget($key, $tenantId);

        return $setting;
    }

    /**
     * Get all settings.
     *
     * @param int|null $tenantId
     * @return array
     */
    public function getAll(?int $tenantId = null): array
    {
        $cacheKey = $this->getCacheKey('all', $tenantId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenantId) {
            $settings = $this->repository->getAll($tenantId);
            
            return $settings->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->getTypedValue()];
            })->toArray();
        });
    }

    /**
     * Get settings by group.
     *
     * @param string $group
     * @param int|null $tenantId
     * @return array
     */
    public function getByGroup(string $group, ?int $tenantId = null): array
    {
        $cacheKey = $this->getCacheKey("group:{$group}", $tenantId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($group, $tenantId) {
            $settings = $this->repository->getByGroup($group, $tenantId);
            
            return $settings->mapWithKeys(function ($setting) {
                return [$setting->key => [
                    'value' => $setting->getTypedValue(),
                    'type' => $setting->type,
                    'description' => $setting->description,
                    'group' => $setting->group,
                ]];
            })->toArray();
        });
    }

    /**
     * Get all settings with metadata (for admin UI).
     *
     * @param int|null $tenantId
     * @return \Illuminate\Support\Collection
     */
    public function getAllWithMetadata(?int $tenantId = null)
    {
        return $this->repository->getAll($tenantId);
    }

    /**
     * Bulk update settings.
     *
     * @param array $settings
     * @param int|null $tenantId
     * @return bool
     */
    public function bulkUpdate(array $settings, ?int $tenantId = null): bool
    {
        $result = $this->repository->bulkUpdate($settings, $tenantId);

        if ($result) {
            // Clear all settings cache for this tenant
            $this->clearCache($tenantId);
        }

        return $result;
    }

    /**
     * Delete a setting.
     *
     * @param string $key
     * @param int|null $tenantId
     * @return bool
     */
    public function delete(string $key, ?int $tenantId = null): bool
    {
        $result = $this->repository->deleteByKey($key, $tenantId);

        if ($result) {
            $this->forget($key, $tenantId);
        }

        return $result;
    }

    /**
     * Forget a cached setting.
     *
     * @param string $key
     * @param int|null $tenantId
     * @return void
     */
    public function forget(string $key, ?int $tenantId = null): void
    {
        Cache::forget($this->getCacheKey($key, $tenantId));
        Cache::forget($this->getCacheKey('all', $tenantId));
    }

    /**
     * Clear all cached settings.
     *
     * @param int|null $tenantId
     * @return void
     */
    public function clearCache(?int $tenantId = null): void
    {
        if ($tenantId !== null) {
            Cache::forget($this->getCacheKey('all', $tenantId));
            // Clear group caches
            foreach (['general', 'booking', 'payment', 'dooh', 'notification', 'commission'] as $group) {
                Cache::forget($this->getCacheKey("group:{$group}", $tenantId));
            }
        } else {
            Cache::forget($this->getCacheKey('all', null));
        }
    }

    /**
     * Get the cache key for a setting.
     *
     * @param string $key
     * @param int|null $tenantId
     * @return string
     */
    protected function getCacheKey(string $key, ?int $tenantId = null): string
    {
        $tenant = $tenantId !== null ? "tenant:{$tenantId}" : 'global';
        return self::CACHE_PREFIX . ":{$tenant}:{$key}";
    }

    /**
     * Convert value for storage based on type.
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    protected function convertValueForStorage($value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'json', 'array' => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Check if a setting exists.
     *
     * @param string $key
     * @param int|null $tenantId
     * @return bool
     */
    public function has(string $key, ?int $tenantId = null): bool
    {
        return $this->repository->findByKey($key, $tenantId) !== null;
    }
}

