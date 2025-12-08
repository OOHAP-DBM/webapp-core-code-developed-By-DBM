<?php

namespace Tests\Feature\Auth;

use Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function user_can_view_registration_form()
    {
        $response = $this->get(route('register'));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /** @test */
    public function user_can_register_as_customer()
    {
        $response = $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+919876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
            'terms' => true
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+919876543210',
            'status' => 'active'
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole('customer'));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_can_register_as_vendor()
    {
        $response = $this->post(route('register'), [
            'name' => 'Vendor Inc',
            'email' => 'vendor@example.com',
            'phone' => '+919876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'vendor',
            'terms' => true
        ]);

        $response->assertRedirect(route('vendor.dashboard'));
        
        $user = User::where('email', 'vendor@example.com')->first();
        $this->assertTrue($user->hasRole('vendor'));
    }

    /** @test */
    public function registration_requires_all_fields()
    {
        $response = $this->post(route('register'), []);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'password', 'terms']);
        $this->assertGuest();
    }

    /** @test */
    public function email_must_be_unique()
    {
        User::factory()->create([
            'email' => 'john@example.com'
        ]);

        $response = $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+919876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
            'terms' => true
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function phone_must_be_unique()
    {
        User::factory()->create([
            'phone' => '+919876543210'
        ]);

        $response = $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+919876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
            'terms' => true
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    /** @test */
    public function password_must_be_at_least_8_characters()
    {
        $response = $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+919876543210',
            'password' => 'pass',
            'password_confirmation' => 'pass',
            'role' => 'customer',
            'terms' => true
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function password_confirmation_must_match()
    {
        $response = $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+919876543210',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
            'role' => 'customer',
            'terms' => true
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function user_must_accept_terms()
    {
        $response = $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+919876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
            'terms' => false
        ]);

        $response->assertSessionHasErrors('terms');
        $this->assertGuest();
    }

    /** @test */
    public function registered_user_password_is_hashed()
    {
        $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+919876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
            'terms' => true
        ]);

        $user = User::where('email', 'john@example.com')->first();
        
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /** @test */
    public function registration_defaults_to_customer_role_if_not_specified()
    {
        $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+919876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole('customer'));
    }
}

