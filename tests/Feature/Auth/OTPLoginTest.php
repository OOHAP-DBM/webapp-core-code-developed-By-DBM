<?php

namespace Tests\Feature\Auth;

use Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OTPLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function user_can_view_otp_form()
    {
        $response = $this->get(route('login.otp'));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.otp');
    }

    /** @test */
    public function user_can_request_otp_with_email()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'status' => 'active'
        ]);
        $user->assignRole('customer');

        $response = $this->post(route('otp.send'), [
            'identifier' => 'test@example.com'
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('show_verify_form', true);
        $response->assertSessionHas('otp_identifier', 'test@example.com');
        
        $user->refresh();
        $this->assertNotNull($user->otp);
        $this->assertNotNull($user->otp_expires_at);
    }

    /** @test */
    public function user_can_request_otp_with_phone()
    {
        $user = User::factory()->create([
            'phone' => '+919876543210',
            'status' => 'active'
        ]);
        $user->assignRole('customer');

        $response = $this->post(route('otp.send'), [
            'identifier' => '+919876543210'
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('show_verify_form', true);
        
        $user->refresh();
        $this->assertNotNull($user->otp);
        $this->assertNotNull($user->otp_expires_at);
    }

    /** @test */
    public function otp_cannot_be_sent_to_nonexistent_user()
    {
        $response = $this->post(route('otp.send'), [
            'identifier' => 'nonexistent@example.com'
        ]);

        $response->assertSessionHasErrors('identifier');
    }

    /** @test */
    public function suspended_user_cannot_request_otp()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'status' => 'suspended'
        ]);

        $response = $this->post(route('otp.send'), [
            'identifier' => 'test@example.com'
        ]);

        $response->assertSessionHasErrors('identifier');
    }

    /** @test */
    public function user_can_verify_correct_otp()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'status' => 'active'
        ]);
        $user->assignRole('customer');
        
        $otp = $user->generateOTP();

        $response = $this->withSession(['otp_identifier' => 'test@example.com'])
            ->post(route('otp.verify'), [
                'identifier' => 'test@example.com',
                'otp' => $otp
            ]);

        $response->assertRedirect($user->getDashboardRoute());
        $this->assertAuthenticatedAs($user);
        
        $user->refresh();
        $this->assertNull($user->otp);
        $this->assertNull($user->otp_expires_at);
    }

    /** @test */
    public function user_cannot_verify_incorrect_otp()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'status' => 'active'
        ]);
        $user->generateOTP();

        $response = $this->withSession(['otp_identifier' => 'test@example.com'])
            ->post(route('otp.verify'), [
                'identifier' => 'test@example.com',
                'otp' => '000000'
            ]);

        $response->assertSessionHasErrors('otp');
        $this->assertGuest();
    }

    /** @test */
    public function user_cannot_verify_expired_otp()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'status' => 'active',
            'otp' => '123456',
            'otp_expires_at' => now()->subMinutes(15) // Expired
        ]);

        $response = $this->withSession(['otp_identifier' => 'test@example.com'])
            ->post(route('otp.verify'), [
                'identifier' => 'test@example.com',
                'otp' => '123456'
            ]);

        $response->assertSessionHasErrors('otp');
        $this->assertGuest();
    }

    /** @test */
    public function user_can_resend_otp()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'status' => 'active',
            'otp' => '123456',
            'otp_expires_at' => now()->addMinutes(10)
        ]);
        $user->assignRole('customer');

        $oldOtp = $user->otp;

        // Clear rate limit cache
        Cache::forget('otp_resend_test@example.com');

        $response = $this->withSession(['otp_identifier' => 'test@example.com'])
            ->post(route('otp.resend'), [
                'identifier' => 'test@example.com'
            ]);

        $response->assertSessionHas('success');
        
        $user->refresh();
        $this->assertNotEquals($oldOtp, $user->otp);
        $this->assertNotNull($user->otp);
    }

    /** @test */
    public function otp_resend_is_rate_limited()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'status' => 'active'
        ]);
        $user->assignRole('customer');

        // First OTP request
        $this->post(route('otp.send'), [
            'identifier' => 'test@example.com'
        ]);

        // Immediate resend attempt (should be blocked)
        $response = $this->withSession(['otp_identifier' => 'test@example.com'])
            ->post(route('otp.resend'), [
                'identifier' => 'test@example.com'
            ]);

        $response->assertSessionHasErrors('identifier');
    }

    /** @test */
    public function phone_is_verified_after_successful_otp_verification()
    {
        $user = User::factory()->create([
            'phone' => '+919876543210',
            'phone_verified_at' => null,
            'status' => 'active'
        ]);
        $user->assignRole('customer');
        
        $otp = $user->generateOTP();

        $this->withSession(['otp_identifier' => '+919876543210'])
            ->post(route('otp.verify'), [
                'identifier' => '+919876543210',
                'otp' => $otp
            ]);

        $user->refresh();
        $this->assertNotNull($user->phone_verified_at);
    }

    /** @test */
    public function last_login_is_updated_after_otp_verification()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'last_login_at' => null,
            'status' => 'active'
        ]);
        $user->assignRole('customer');
        
        $otp = $user->generateOTP();

        $this->withSession(['otp_identifier' => 'test@example.com'])
            ->post(route('otp.verify'), [
                'identifier' => 'test@example.com',
                'otp' => $otp
            ]);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }
}

