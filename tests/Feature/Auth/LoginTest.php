<?php

namespace Tests\Feature\Auth;

use Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function user_can_view_login_form()
    {
        $response = $this->get(route('login'));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function user_can_login_with_email()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);
        
        $user->assignRole('customer');

        $response = $this->post(route('login'), [
            'identifier' => 'test@example.com',
            'password' => 'password123',
            'remember' => false
        ]);

        $response->assertRedirect($user->getDashboardRoute());
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_can_login_with_phone()
    {
        $user = User::factory()->create([
            'phone' => '+919876543210',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);
        
        $user->assignRole('customer');

        $response = $this->post(route('login'), [
            'identifier' => '+919876543210',
            'password' => 'password123',
            'remember' => false
        ]);

        $response->assertRedirect($user->getDashboardRoute());
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_cannot_login_with_incorrect_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);

        $response = $this->post(route('login'), [
            'identifier' => 'test@example.com',
            'password' => 'wrongpassword',
            'remember' => false
        ]);

        $response->assertSessionHasErrors('identifier');
        $this->assertGuest();
    }

    /** @test */
    public function suspended_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'suspended'
        ]);

        $response = $this->post(route('login'), [
            'identifier' => 'test@example.com',
            'password' => 'password123',
            'remember' => false
        ]);

        $response->assertSessionHasErrors('identifier');
        $this->assertGuest();
    }

    /** @test */
    public function inactive_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'inactive'
        ]);

        $response = $this->post(route('login'), [
            'identifier' => 'test@example.com',
            'password' => 'password123',
            'remember' => false
        ]);

        $response->assertSessionHasErrors('identifier');
        $this->assertGuest();
    }

    /** @test */
    public function user_last_login_is_updated_after_successful_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'last_login_at' => null
        ]);
        
        $user->assignRole('customer');

        $this->post(route('login'), [
            'identifier' => 'test@example.com',
            'password' => 'password123',
            'remember' => false
        ]);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }

    /** @test */
    public function authenticated_user_is_redirected_based_on_role()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);
        $admin->assignRole('admin');

        $vendor = User::factory()->create([
            'email' => 'vendor@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);
        $vendor->assignRole('vendor');

        $customer = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);
        $customer->assignRole('customer');

        // Test admin redirect
        $this->post(route('login'), [
            'identifier' => 'admin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'));

        // Test vendor redirect
        $this->post(route('login'), [
            'identifier' => 'vendor@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('vendor.dashboard'));

        $this->post(route('logout'));

        // Test customer redirect
        $this->post(route('login'), [
            'identifier' => 'customer@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('customer.dashboard'));
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create([
            'status' => 'active'
        ]);
        $user->assignRole('customer');

        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}

