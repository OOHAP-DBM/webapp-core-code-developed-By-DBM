<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSwitchingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function api_get_available_roles_returns_correct_data()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['admin', 'vendor']);
        $user->update(['active_role' => 'admin']);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/auth/roles/available');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'current_role',
                'available_roles',
                'can_switch',
                'all_assigned_roles',
                'last_switch',
            ],
        ]);

        $data = $response->json('data');
        $this->assertEquals('admin', $data['current_role']);
        $this->assertTrue($data['can_switch']);
        $this->assertContains('admin', $data['available_roles']);
        $this->assertContains('vendor', $data['available_roles']);
    }

    /** @test */
    public function api_switch_role_returns_new_token()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['admin', 'vendor']);
        $user->update(['active_role' => 'admin']);

        $oldToken = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $oldToken,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/auth/roles/switch', [
            'role' => 'vendor',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'new_role',
                'previous_role',
                'token',
                'token_type',
                'permissions',
            ],
        ]);

        $data = $response->json('data');
        $this->assertEquals('vendor', $data['new_role']);
        $this->assertEquals('admin', $data['previous_role']);
        $this->assertNotEquals($oldToken, $data['token']);
        $this->assertEquals('Bearer', $data['token_type']);
        $this->assertIsArray($data['permissions']);
    }

    /** @test */
    public function api_old_token_is_revoked_after_role_switch()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['admin', 'vendor']);
        $user->update(['active_role' => 'admin']);

        $oldToken = $user->createToken('auth_token')->plainTextToken;

        // Switch role
        $switchResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $oldToken,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/auth/roles/switch', [
            'role' => 'vendor',
        ]);

        $newToken = $switchResponse->json('data.token');

        // Verify old token was deleted from database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', explode('|', $oldToken)[1]),
        ]);

        // New token should work
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $newToken,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
    }

    /** @test */
    public function api_customer_cannot_switch_roles()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');
        $user->update(['active_role' => 'customer']);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/auth/roles/switch', [
            'role' => 'admin',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'You do not have permission to switch to this role',
        ]);
    }

    /** @test */
    public function api_user_cannot_switch_to_unassigned_role()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('admin');
        $user->update(['active_role' => 'admin']);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/auth/roles/switch', [
            'role' => 'vendor',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function api_switch_role_validates_role_parameter()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['admin', 'vendor']);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Missing role parameter
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/auth/roles/switch', []);

        $response->assertStatus(422);

        // Invalid role
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/auth/roles/switch', [
            'role' => 'invalid_role',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function api_get_active_permissions_returns_correct_permissions()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('vendor');
        $user->update(['active_role' => 'vendor']);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/auth/roles/permissions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'active_role',
                'permissions',
            ],
        ]);

        $data = $response->json('data');
        $this->assertEquals('vendor', $data['active_role']);
        $this->assertIsArray($data['permissions']);
        $this->assertContains('hoardings.create', $data['permissions']);
        $this->assertNotContains('users.delete', $data['permissions']); // Admin only
    }

    /** @test */
    public function api_login_returns_active_role()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $user->assignRole('customer');

        $response = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'user',
                'token',
                'token_type',
                'active_role',
            ],
        ]);

        $this->assertEquals('customer', $response->json('data.active_role'));
    }

    /** @test */
    public function api_register_sets_active_role()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
        ]);

        $response->assertStatus(201);
        
        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user->active_role);
        $this->assertEquals('customer', $user->active_role);
    }

    /** @test */
    public function api_unauthenticated_user_cannot_access_role_endpoints()
    {
        // Get available roles
        $response = $this->getJson('/api/v1/auth/roles/available');
        $response->assertStatus(401);

        // Switch role
        $response = $this->postJson('/api/v1/auth/roles/switch', ['role' => 'admin']);
        $response->assertStatus(401);

        // Get permissions
        $response = $this->getJson('/api/v1/auth/roles/permissions');
        $response->assertStatus(401);
    }
}
