<?php

namespace Tests\Feature\Settings;

use Modules\Settings\Models\Setting;
use Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\SettingsSeeder::class);

        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('admin');

        $this->customer = User::factory()->create(['status' => 'active']);
        $this->customer->assignRole('customer');
    }

    /** @test */
    public function admin_can_view_settings_page()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.index');
        $response->assertViewHas('groupedSettings');
    }

    /** @test */
    public function non_admin_cannot_view_settings_page()
    {
        $response = $this->actingAs($this->customer)
            ->get(route('admin.settings.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_settings()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), [
                'settings' => [
                    'booking_hold_minutes' => '45',
                    'admin_commission_percent' => '12.50',
                ]
            ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('45', Setting::where('key', 'booking_hold_minutes')->first()->value);
        $this->assertEquals('12.50', Setting::where('key', 'admin_commission_percent')->first()->value);
    }

    /** @test */
    public function settings_are_properly_typed()
    {
        $integerSetting = Setting::where('key', 'booking_hold_minutes')->first();
        $this->assertIsInt($integerSetting->getTypedValue());

        $floatSetting = Setting::where('key', 'admin_commission_percent')->first();
        $this->assertIsFloat($floatSetting->getTypedValue());

        $booleanSetting = Setting::where('key', 'auto_approve_bookings')->first();
        $this->assertIsBool($booleanSetting->getTypedValue());

        $jsonSetting = Setting::where('key', 'dooh_allowed_formats')->first();
        $this->assertIsArray($jsonSetting->getTypedValue());
    }

    /** @test */
    public function settings_service_get_returns_typed_values()
    {
        $service = app(\Modules\Settings\Services\SettingsService::class);

        $intValue = $service->get('booking_hold_minutes');
        $this->assertIsInt($intValue);
        $this->assertEquals(30, $intValue);

        $floatValue = $service->get('admin_commission_percent');
        $this->assertIsFloat($floatValue);
        $this->assertEquals(10.00, $floatValue);

        $boolValue = $service->get('auto_approve_bookings');
        $this->assertIsBool($boolValue);
        $this->assertFalse($boolValue);
    }

    /** @test */
    public function settings_service_set_creates_or_updates_setting()
    {
        $service = app(\Modules\Settings\Services\SettingsService::class);

        // Create new setting
        $service->set('test_setting', 'test_value', 'string', null, 'Test description', 'general');

        $this->assertDatabaseHas('settings', [
            'key' => 'test_setting',
            'value' => 'test_value',
            'type' => 'string',
        ]);

        // Update existing setting
        $service->set('test_setting', 'updated_value');

        $this->assertDatabaseHas('settings', [
            'key' => 'test_setting',
            'value' => 'updated_value',
        ]);
    }

    /** @test */
    public function settings_are_cached()
    {
        $service = app(\Modules\Settings\Services\SettingsService::class);

        // Clear cache first
        Cache::flush();

        // First call should hit database
        $value1 = $service->get('booking_hold_minutes');
        
        // Second call should hit cache
        $value2 = $service->get('booking_hold_minutes');

        $this->assertEquals($value1, $value2);
        
        // Verify cache exists
        $this->assertTrue(Cache::has('settings:global:booking_hold_minutes'));
    }

    /** @test */
    public function cache_is_cleared_when_setting_is_updated()
    {
        $service = app(\Modules\Settings\Services\SettingsService::class);

        // Get setting (caches it)
        $value1 = $service->get('booking_hold_minutes');

        // Update setting
        $service->set('booking_hold_minutes', 60, 'integer');

        // Get setting again (should be new value, not cached)
        $value2 = $service->get('booking_hold_minutes');

        $this->assertNotEquals($value1, $value2);
        $this->assertEquals(60, $value2);
    }

    /** @test */
    public function tenant_specific_settings_override_global_settings()
    {
        $service = app(\Modules\Settings\Services\SettingsService::class);

        // Create tenant-specific setting
        $service->set('booking_hold_minutes', 60, 'integer', 1);

        // Global setting
        $globalValue = $service->get('booking_hold_minutes');
        $this->assertEquals(30, $globalValue);

        // Tenant-specific setting
        $tenantValue = $service->get('booking_hold_minutes', null, 1);
        $this->assertEquals(60, $tenantValue);
    }

    /** @test */
    public function get_by_group_returns_grouped_settings()
    {
        $service = app(\Modules\Settings\Services\SettingsService::class);

        $bookingSettings = $service->getByGroup('booking');

        $this->assertIsArray($bookingSettings);
        $this->assertArrayHasKey('booking_hold_minutes', $bookingSettings);
        $this->assertArrayHasKey('max_future_booking_start_months', $bookingSettings);
    }

    /** @test */
    public function bulk_update_updates_multiple_settings()
    {
        $service = app(\Modules\Settings\Services\SettingsService::class);

        $result = $service->bulkUpdate([
            'booking_hold_minutes' => 45,
            'admin_commission_percent' => 15.00,
            'max_future_booking_start_months' => 6,
        ]);

        $this->assertTrue($result);

        $this->assertEquals(45, $service->get('booking_hold_minutes'));
        $this->assertEquals(15.00, $service->get('admin_commission_percent'));
        $this->assertEquals(6, $service->get('max_future_booking_start_months'));
    }

    /** @test */
    public function admin_can_reset_settings_to_default()
    {
        // Modify a setting
        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), [
                'settings' => [
                    'booking_hold_minutes' => '999',
                ]
            ]);

        // Reset settings
        $response = $this->actingAs($this->admin)
            ->post(route('admin.settings.reset'));

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        // Verify setting is back to default
        $this->assertEquals('30', Setting::where('key', 'booking_hold_minutes')->first()->value);
    }

    /** @test */
    public function admin_can_clear_settings_cache()
    {
        $service = app(\Modules\Settings\Services\SettingsService::class);

        // Cache a setting
        $service->get('booking_hold_minutes');
        $this->assertTrue(Cache::has('settings:global:booking_hold_minutes'));

        // Clear cache via admin endpoint
        $response = $this->actingAs($this->admin)
            ->post(route('admin.settings.clear-cache'));

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function get_returns_default_value_when_setting_not_found()
    {
        $service = app(\Modules\Settings\Services\SettingsService::class);

        $value = $service->get('non_existent_setting', 'default_value');

        $this->assertEquals('default_value', $value);
    }

    /** @test */
    public function has_method_checks_setting_existence()
    {
        $service = app(\Modules\Settings\Services\SettingsService::class);

        $this->assertTrue($service->has('booking_hold_minutes'));
        $this->assertFalse($service->has('non_existent_setting'));
    }

    /** @test */
    public function delete_method_removes_setting()
    {
        $service = app(\Modules\Settings\Services\SettingsService::class);

        // Create a test setting
        $service->set('temp_setting', 'test');

        $this->assertTrue($service->has('temp_setting'));

        // Delete it
        $service->delete('temp_setting');

        $this->assertFalse($service->has('temp_setting'));
        $this->assertDatabaseMissing('settings', ['key' => 'temp_setting']);
    }
}

