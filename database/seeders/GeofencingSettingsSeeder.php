<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\Services\SettingsService;

class GeofencingSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = app(SettingsService::class);

        // Geo-fencing settings for POD (Proof of Delivery) uploads
        $geofenceSettings = [
            [
                'key' => 'pod.geofence_radius_meters',
                'value' => '100',
                'type' => 'integer',
                'group' => 'geofencing',
                'description' => 'Maximum distance in meters from hoarding location for POD uploads. Mounters must be within this radius to upload installation proof.',
            ],
            [
                'key' => 'pod.strict_geofence_validation',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'geofencing',
                'description' => 'Enable strict geo-fence validation. If enabled, POD uploads outside the radius will be rejected. If disabled, distance is recorded but not enforced.',
            ],
            [
                'key' => 'pod.require_gps_coordinates',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'geofencing',
                'description' => 'Require GPS coordinates for all POD uploads. If enabled, uploads without location data will be rejected.',
            ],
            [
                'key' => 'pod.max_gps_accuracy_meters',
                'value' => '50',
                'type' => 'integer',
                'group' => 'geofencing',
                'description' => 'Maximum GPS accuracy threshold in meters. POD uploads with worse accuracy may be flagged or rejected.',
            ],
            [
                'key' => 'pod.log_geofence_violations',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'geofencing',
                'description' => 'Log all geo-fence validation violations for audit and security purposes.',
            ],
            [
                'key' => 'pod.show_distance_to_mounter',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'geofencing',
                'description' => 'Show real-time distance to hoarding location when mounter captures GPS coordinates.',
            ],
            [
                'key' => 'geofencing.alert_threshold_meters',
                'value' => '150',
                'type' => 'integer',
                'group' => 'geofencing',
                'description' => 'Distance threshold for alerting admin about suspicious POD uploads (even if within allowed radius).',
            ],
            [
                'key' => 'geofencing.enable_for_dismounting',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'geofencing',
                'description' => 'Apply geo-fence validation for dismounting proof uploads as well.',
            ],
            [
                'key' => 'geofencing.auto_approve_within_radius',
                'value' => '50',
                'type' => 'integer',
                'group' => 'geofencing',
                'description' => 'Auto-approve POD uploads if within this radius (in meters). 0 to disable auto-approval.',
            ],
        ];

        foreach ($geofenceSettings as $setting) {
            $settings->set(
                key: $setting['key'],
                value: $setting['value'],
                type: $setting['type'],
                description: $setting['description'],
                group: $setting['group']
            );

            $this->command->info("✓ Created setting: {$setting['key']} = {$setting['value']}");
        }

        $this->command->info('');
        $this->command->info('✅ Geo-fencing settings seeded successfully!');
        $this->command->info('');
        $this->command->info('Settings can be managed via:');
        $this->command->info('  - Admin Panel: /admin/settings/geofencing');
        $this->command->info('  - API: PUT /api/v1/settings');
        $this->command->info('');
    }
}
