<?php

namespace Modules\Settings\Repositories\Contracts;

interface SettingRepositoryInterface
{
    /**
     * Get a setting by key.
     *
     * @param string $key
     * @param int|null $tenantId
     * @return \App\Models\Setting|null
     */
    public function findByKey(string $key, ?int $tenantId = null);

    /**
     * Get all settings, optionally filtered by tenant.
     *
     * @param int|null $tenantId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(?int $tenantId = null);

    /**
     * Get settings by group.
     *
     * @param string $group
     * @param int|null $tenantId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByGroup(string $group, ?int $tenantId = null);

    /**
     * Create or update a setting.
     *
     * @param array $data
     * @return \App\Models\Setting
     */
    public function createOrUpdate(array $data);

    /**
     * Delete a setting by key.
     *
     * @param string $key
     * @param int|null $tenantId
     * @return bool
     */
    public function deleteByKey(string $key, ?int $tenantId = null);

    /**
     * Get all global settings.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getGlobalSettings();

    /**
     * Bulk update settings.
     *
     * @param array $settings
     * @param int|null $tenantId
     * @return bool
     */
    public function bulkUpdate(array $settings, ?int $tenantId = null);
}

