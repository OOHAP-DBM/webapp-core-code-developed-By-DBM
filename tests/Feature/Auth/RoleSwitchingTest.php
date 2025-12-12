<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Services\RoleSwitchingService;
use Tests\TestCase;

class RoleSwitchingTest extends TestCase
{
    use RefreshDatabase;

    protected RoleSwitchingService $roleSwitchingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->roleSwitchingService = app(RoleSwitchingService::class);
    }

    /** @test */
    public function admin_with_vendor_role_can_switch_between_roles()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['admin', 'vendor']);
        $user->update(['active_role' => 'admin']);

        $this->actingAs($user);

        // Switch to vendor
        $response = $this->post(route('auth.switch-role', 'vendor'));
        $response->assertRedirect(route('vendor.dashboard'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('vendor', $user->active_role);
        $this->assertEquals('admin', $user->previous_role);
        $this->assertNotNull($user->last_role_switch_at);
    }

    /** @test */
    public function admin_can_switch_back_to_admin_from_vendor()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['admin', 'vendor']);
        $user->update(['active_role' => 'vendor']);

        $this->actingAs($user);

        // Switch back to admin
        $response = $this->post(route('auth.switch-role', 'admin'));
        $response->assertRedirect(route('admin.dashboard'));

        $user->refresh();
        $this->assertEquals('admin', $user->active_role);
    }

    /** @test */
    public function customer_cannot_switch_roles()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');
        $user->update(['active_role' => 'customer']);

        $this->actingAs($user);

        $response = $this->post(route('auth.switch-role', 'admin'));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $user->refresh();
        $this->assertEquals('customer', $user->active_role);
    }

    /** @test */
    public function vendor_without_admin_role_cannot_switch_to_admin()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('vendor');
        $user->update(['active_role' => 'vendor']);

        $this->actingAs($user);

        $response = $this->post(route('auth.switch-role', 'admin'));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $user->refresh();
        $this->assertEquals('vendor', $user->active_role);
    }

    /** @test */
    public function user_cannot_switch_to_role_they_dont_have()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['admin']);
        $user->update(['active_role' => 'admin']);

        $this->actingAs($user);

        $result = $this->roleSwitchingService->canSwitchToRole($user, 'vendor');
        $this->assertFalse($result);

        $response = $this->post(route('auth.switch-role', 'vendor'));
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function user_cannot_switch_to_same_role()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['admin', 'vendor']);
        $user->update(['active_role' => 'admin']);

        $result = $this->roleSwitchingService->canSwitchToRole($user, 'admin');
        $this->assertFalse($result);
    }

    /** @test */
    public function get_available_roles_returns_correct_roles_for_admin_vendor()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['admin', 'vendor']);
        $user->update(['active_role' => 'admin']);

        $availableRoles = $this->roleSwitchingService->getAvailableRoles($user);

        $this->assertContains('admin', $availableRoles);
        $this->assertContains('vendor', $availableRoles);
        $this->assertCount(2, $availableRoles);
    }

    /** @test */
    public function get_available_roles_returns_empty_for_customer()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');

        $availableRoles = $this->roleSwitchingService->getAvailableRoles($user);

        $this->assertEmpty($availableRoles);
    }

    /** @test */
    public function get_available_roles_returns_empty_for_vendor_only()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('vendor');

        $availableRoles = $this->roleSwitchingService->getAvailableRoles($user);

        $this->assertEmpty($availableRoles);
    }

    /** @test */
    public function active_role_is_set_on_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
            'active_role' => null,
        ]);
        $user->assignRole('customer');

        $this->assertNull($user->active_role);

        $this->post(route('login'), [
            'identifier' => 'test@example.com',
            'password' => 'password',
        ]);

        $user->refresh();
        $this->assertEquals('customer', $user->active_role);
    }

    /** @test */
    public function get_active_dashboard_route_returns_correct_route()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['admin', 'vendor']);
        
        // Test admin role
        $user->update(['active_role' => 'admin']);
        $route = $this->roleSwitchingService->getActiveDashboardRoute($user);
        $this->assertEquals('admin.dashboard', $route);

        // Test vendor role
        $user->update(['active_role' => 'vendor']);
        $route = $this->roleSwitchingService->getActiveDashboardRoute($user);
        $this->assertEquals('vendor.dashboard', $route);
    }

    /** @test */
    public function get_active_layout_returns_correct_layout()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['admin', 'vendor']);
        
        // Test admin layout
        $user->update(['active_role' => 'admin']);
        $layout = $this->roleSwitchingService->getActiveLayout($user);
        $this->assertEquals('layouts.admin', $layout);

        // Test vendor layout
        $user->update(['active_role' => 'vendor']);
        $layout = $this->roleSwitchingService->getActiveLayout($user);
        $this->assertEquals('layouts.vendor', $layout);
    }

    /** @test */
    public function get_active_role_permissions_returns_correct_permissions()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('vendor');
        $user->update(['active_role' => 'vendor']);

        $permissions = $this->roleSwitchingService->getActiveRolePermissions($user);

        $this->assertIsArray($permissions);
        $this->assertContains('hoardings.view', $permissions);
        $this->assertContains('hoardings.create', $permissions);
        $this->assertNotContains('users.delete', $permissions); // Admin only
    }

    /** @test */
    public function role_switch_history_is_tracked()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['admin', 'vendor']);
        $user->update(['active_role' => 'admin']);

        $this->actingAs($user);

        // Switch to vendor
        $this->post(route('auth.switch-role', 'vendor'));

        $user->refresh();
        $history = $this->roleSwitchingService->getRoleSwitchHistory($user);

        $this->assertEquals('vendor', $history['current_role']);
        $this->assertEquals('admin', $history['previous_role']);
        $this->assertNotNull($history['last_switch_at']);
        $this->assertContains('admin', $history['available_roles']);
        $this->assertContains('vendor', $history['available_roles']);
    }

    /** @test */
    public function user_can_switch_roles_method_works_correctly()
    {
        // Multi-role admin user
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole(['admin', 'vendor']);
        $this->assertTrue($admin->canSwitchRoles());

        // Single-role customer
        $customer = User::factory()->create(['status' => 'active']);
        $customer->assignRole('customer');
        $this->assertFalse($customer->canSwitchRoles());

        // Single-role vendor
        $vendor = User::factory()->create(['status' => 'active']);
        $vendor->assignRole('vendor');
        $this->assertFalse($vendor->canSwitchRoles());
    }

    /** @test */
    public function get_active_role_returns_primary_role_if_active_role_not_set()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');
        $user->update(['active_role' => null]);

        $activeRole = $this->roleSwitchingService->getActiveRole($user);

        $this->assertEquals('customer', $activeRole);
        
        // Should auto-set active_role
        $user->refresh();
        $this->assertEquals('customer', $user->active_role);
    }

    /** @test */
    public function active_role_is_reset_if_role_was_revoked()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['admin', 'vendor']);
        $user->update(['active_role' => 'vendor']);

        // Revoke vendor role
        $user->removeRole('vendor');

        // Get active role should reset to primary
        $activeRole = $this->roleSwitchingService->getActiveRole($user);

        $this->assertEquals('admin', $activeRole);
        $user->refresh();
        $this->assertEquals('admin', $user->active_role);
    }

    /** @test */
    public function super_admin_with_vendor_role_can_switch()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['super_admin', 'vendor']);
        $user->update(['active_role' => 'super_admin']);

        $this->actingAs($user);

        $response = $this->post(route('auth.switch-role', 'vendor'));
        $response->assertRedirect(route('vendor.dashboard'));

        $user->refresh();
        $this->assertEquals('vendor', $user->active_role);
    }

    /** @test */
    public function unauthenticated_user_cannot_switch_roles()
    {
        $response = $this->post(route('auth.switch-role', 'admin'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function get_available_roles_endpoint_returns_correct_data()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole(['admin', 'vendor']);
        $user->update(['active_role' => 'admin']);

        $this->actingAs($user);

        $response = $this->getJson(route('auth.available-roles'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_role',
            'available_roles',
            'can_switch',
        ]);

        $data = $response->json();
        $this->assertEquals('admin', $data['current_role']);
        $this->assertTrue($data['can_switch']);
        $this->assertContains('vendor', $data['available_roles']);
    }
}
