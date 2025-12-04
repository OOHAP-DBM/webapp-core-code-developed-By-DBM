<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\SettingsSeeder::class);

        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('admin');
        $this->token = $this->admin->createToken('test-token')->plainTextToken;

        $this->customer = User::factory()->create(['status' => 'active']);
        $this->customer->assignRole('customer');
    }

    /** @test */
    public function api_returns_all_settings()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/settings');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'key',
                    'value',
                    'type',
                    'description',
                    'group',
                ]
            ]
        ]);
    }

    /** @test */
    public function api_returns_settings_by_group()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/settings?group=booking');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'key',
                    'value',
                    'type',
                    'description',
                    'group',
                ]
            ]
        ]);

        // Verify all returned settings are from booking group
        $data = $response->json('data');
        foreach ($data as $setting) {
            $this->assertEquals('booking', $setting['group']);
        }
    }

    /** @test */
    public function api_returns_single_setting()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/settings/booking_hold_minutes');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'key' => 'booking_hold_minutes',
                'value' => 30,
            ]
        ]);
    }

    /** @test */
    public function api_returns_404_for_non_existent_setting()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/settings/non_existent_key');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Setting not found',
        ]);
    }

    /** @test */
    public function api_can_update_settings_bulk()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/settings', [
                'settings' => [
                    'booking_hold_minutes' => 45,
                    'admin_commission_percent' => 12.50,
                ]
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);

        // Verify updates
        $this->assertDatabaseHas('settings', [
            'key' => 'booking_hold_minutes',
            'value' => '45',
        ]);
    }

    /** @test */
    public function api_can_update_single_setting()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/settings/booking_hold_minutes', [
                'value' => 60,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Setting updated successfully',
            'data' => [
                'key' => 'booking_hold_minutes',
                'value' => 60,
            ]
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'booking_hold_minutes',
            'value' => '60',
        ]);
    }

    /** @test */
    public function api_validates_bulk_update_request()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/settings', [
                // Missing settings array
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['settings']);
    }

    /** @test */
    public function api_validates_single_update_request()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/settings/booking_hold_minutes', [
                // Missing value
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['value']);
    }

    /** @test */
    public function api_can_clear_cache()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/settings/clear-cache');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Settings cache cleared successfully',
        ]);
    }

    /** @test */
    public function non_admin_cannot_access_settings_api()
    {
        $customerToken = $this->customer->createToken('customer-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $customerToken)
            ->getJson('/api/v1/settings');

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_settings_api()
    {
        $response = $this->getJson('/api/v1/settings');

        $response->assertStatus(401);
    }

    /** @test */
    public function api_supports_tenant_specific_settings()
    {
        // Create tenant-specific setting
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/settings/booking_hold_minutes', [
                'value' => 90,
                'tenant_id' => 1,
            ]);

        $response->assertStatus(200);

        // Verify global setting unchanged
        $globalResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/settings/booking_hold_minutes');

        $globalResponse->assertJson([
            'data' => [
                'value' => 30, // Original global value
            ]
        ]);

        // Verify tenant-specific setting exists
        $tenantResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/settings/booking_hold_minutes?tenant_id=1');

        $tenantResponse->assertJson([
            'data' => [
                'value' => 90, // Tenant override
            ]
        ]);
    }
}
