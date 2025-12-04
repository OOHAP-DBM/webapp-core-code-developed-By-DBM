<?php

namespace Modules\Settings\Repositories;

use App\Models\Setting;
use App\Repositories\BaseRepository;
use Modules\Settings\Repositories\Contracts\SettingRepositoryInterface;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    /**
     * SettingRepository constructor.
     *
     * @param Setting $model
     */
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    /**
     * Get a setting by key.
     *
     * @param string $key
     * @param int|null $tenantId
     * @return Setting|null
     */
    public function findByKey(string $key, ?int $tenantId = null)
    {
        $query = $this->model->where('key', $key);

        if ($tenantId !== null) {
            // Try tenant-specific first, fall back to global
            $setting = $query->where('tenant_id', $tenantId)->first();
            if ($setting) {
                return $setting;
            }
            // Fall back to global setting
            return $this->model->where('key', $key)->whereNull('tenant_id')->first();
        }

        return $query->whereNull('tenant_id')->first();
    }

    /**
     * Get all settings, optionally filtered by tenant.
     *
     * @param int|null $tenantId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(?int $tenantId = null)
    {
        if ($tenantId !== null) {
            // Get both global and tenant-specific settings
            // Tenant-specific settings override global ones
            $globalSettings = $this->model->global()->get()->keyBy('key');
            $tenantSettings = $this->model->forTenant($tenantId)->get()->keyBy('key');
            
            // Merge with tenant settings taking precedence
            return $globalSettings->merge($tenantSettings)->values();
        }

        return $this->model->global()->get();
    }

    /**
     * Get settings by group.
     *
     * @param string $group
     * @param int|null $tenantId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByGroup(string $group, ?int $tenantId = null)
    {
        if ($tenantId !== null) {
            $globalSettings = $this->model->global()->group($group)->get()->keyBy('key');
            $tenantSettings = $this->model->forTenant($tenantId)->group($group)->get()->keyBy('key');
            
            return $globalSettings->merge($tenantSettings)->values();
        }

        return $this->model->global()->group($group)->get();
    }

    /**
     * Create or update a setting.
     *
     * @param array $data
     * @return Setting
     */
    public function createOrUpdate(array $data)
    {
        return $this->model->updateOrCreate(
            [
                'key' => $data['key'],
                'tenant_id' => $data['tenant_id'] ?? null,
            ],
            $data
        );
    }

    /**
     * Delete a setting by key.
     *
     * @param string $key
     * @param int|null $tenantId
     * @return bool
     */
    public function deleteByKey(string $key, ?int $tenantId = null)
    {
        $query = $this->model->where('key', $key);

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        } else {
            $query->whereNull('tenant_id');
        }

        return $query->delete() > 0;
    }

    /**
     * Get all global settings.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getGlobalSettings()
    {
        return $this->model->global()->get();
    }

    /**
     * Bulk update settings.
     *
     * @param array $settings
     * @param int|null $tenantId
     * @return bool
     */
    public function bulkUpdate(array $settings, ?int $tenantId = null)
    {
        try {
            foreach ($settings as $key => $value) {
                $setting = $this->findByKey($key, $tenantId);
                
                if ($setting) {
                    $setting->setTypedValue($value);
                    $setting->save();
                }
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
