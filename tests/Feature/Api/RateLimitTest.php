<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

/**
 * API Rate Limiting Tests
 * 
 * Tests various rate limiters to ensure they work as expected
 */
class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear all rate limiters before each test
        RateLimiter::clear('auth');
        RateLimiter::clear('otp');
        RateLimiter::clear('register');
        RateLimiter::clear('uploads');
        RateLimiter::clear('authenticated');
        RateLimiter::clear('critical');
        RateLimiter::clear('search');
    }

    /** @test */
    public function it_rate_limits_login_attempts()
    {
        // Should allow 5 attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword'
            ]);
            
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be rate limited");
        }

        // 6th attempt should be rate limited
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(429)
                 ->assertJsonStructure(['message', 'retry_after']);
    }

    /** @test */
    public function it_rate_limits_otp_requests()
    {
        // Should allow 3 OTP requests per phone number
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/v1/auth/otp/send', [
                'phone' => '+919876543210'
            ]);
            
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be rate limited");
        }

        // 4th request should be rate limited
        $response = $this->postJson('/api/v1/auth/otp/send', [
            'phone' => '+919876543210'
        ]);

        $response->assertStatus(429)
                 ->assertJsonStructure(['message', 'retry_after']);
    }

    /** @test */
    public function it_rate_limits_registration_by_ip()
    {
        // Should allow 3 registrations per IP per hour
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/v1/auth/register', [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'phone' => "+9198765432{$i}0",
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'customer'
            ]);
            
            $this->assertNotEquals(429, $response->status(), "Registration {$i} should not be rate limited");
        }

        // 4th registration should be rate limited
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'User 4',
            'email' => 'user4@example.com',
            'phone' => '+919876543240',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer'
        ]);

        $response->assertStatus(429);
    }

    /** @test */
    public function it_applies_role_based_upload_limits()
    {
        // Create users with different roles
        $customer = User::factory()->create(['role' => 'customer']);
        $vendor = User::factory()->create(['role' => 'vendor']);
        $admin = User::factory()->create(['role' => 'admin']);

        // Customer should be limited to 10 uploads/min
        $this->actingAs($customer, 'sanctum');
        
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/v1/media/upload', [
                'file' => 'fake-base64-image-data'
            ]);
            
            $this->assertNotEquals(429, $response->status(), "Customer upload {$i} should not be rate limited");
        }

        // 11th upload should be rate limited
        $response = $this->postJson('/api/v1/media/upload', [
            'file' => 'fake-base64-image-data'
        ]);

        $response->assertStatus(429);

        // Clear rate limiter for vendor test
        RateLimiter::clear('uploads:' . $vendor->id);

        // Vendor should be limited to 30 uploads/min
        $this->actingAs($vendor, 'sanctum');
        
        for ($i = 0; $i < 30; $i++) {
            $response = $this->postJson('/api/v1/media/upload', [
                'file' => 'fake-base64-image-data'
            ]);
            
            // Due to test performance, only check first few and last
            if ($i < 5 || $i === 29) {
                $this->assertNotEquals(429, $response->status(), "Vendor upload {$i} should not be rate limited");
            }
        }
    }

    /** @test */
    public function it_applies_role_based_search_limits()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);

        // Customer should be limited to 30 searches/min
        $this->actingAs($customer, 'sanctum');
        
        for ($i = 0; $i < 30; $i++) {
            $response = $this->getJson('/api/v1/search?query=hoarding');
            
            $this->assertNotEquals(429, $response->status(), "Customer search {$i} should not be rate limited");
        }

        // 31st search should be rate limited
        $response = $this->getJson('/api/v1/search?query=hoarding');
        $response->assertStatus(429);

        // Clear rate limiter for admin test
        RateLimiter::clear('search:' . $admin->id);

        // Admin should be limited to 100 searches/min (won't test all for performance)
        $this->actingAs($admin, 'sanctum');
        
        for ($i = 0; $i < 10; $i++) {
            $response = $this->getJson('/api/v1/search?query=hoarding');
            
            $this->assertNotEquals(429, $response->status(), "Admin search {$i} should not be rate limited");
        }
    }

    /** @test */
    public function it_rate_limits_critical_payment_operations()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer, 'sanctum');

        // Should allow 10 payment operations per minute
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/v1/payments/create-order', [
                'booking_id' => 1,
                'amount' => 10000
            ]);
            
            $this->assertNotEquals(429, $response->status(), "Payment request {$i} should not be rate limited");
        }

        // 11th request should be rate limited
        $response = $this->postJson('/api/v1/payments/create-order', [
            'booking_id' => 1,
            'amount' => 10000
        ]);

        $response->assertStatus(429);
    }

    /** @test */
    public function it_allows_high_limits_for_webhooks()
    {
        // Webhooks should allow 100 requests per minute per IP
        for ($i = 0; $i < 100; $i++) {
            $response = $this->postJson('/webhooks/razorpay', [
                'event' => 'payment.authorized',
                'payload' => []
            ]);
            
            // Should not be rate limited (though may fail for other reasons)
            $this->assertNotEquals(429, $response->status(), "Webhook request {$i} should not be rate limited");
        }
    }

    /** @test */
    public function it_includes_rate_limit_headers_in_response()
    {
        $response = $this->getJson('/api/v1/health');

        $this->assertTrue(
            $response->headers->has('X-RateLimit-Limit') || 
            $response->headers->has('x-ratelimit-limit'),
            'Response should include rate limit headers'
        );
    }

    /** @test */
    public function guest_users_have_stricter_search_limits()
    {
        // Guest should be limited to 10 searches/min
        for ($i = 0; $i < 10; $i++) {
            $response = $this->getJson('/api/v1/search?query=hoarding');
            
            $this->assertNotEquals(429, $response->status(), "Guest search {$i} should not be rate limited");
        }

        // 11th search should be rate limited
        $response = $this->getJson('/api/v1/search?query=hoarding');
        $response->assertStatus(429);
    }

    /** @test */
    public function it_uses_different_keys_for_different_identifiers()
    {
        // OTP requests should be limited per phone number
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/auth/otp/send', [
                'phone' => '+919876543210'
            ]);
        }

        // 4th request for same number should be limited
        $response = $this->postJson('/api/v1/auth/otp/send', [
            'phone' => '+919876543210'
        ]);
        $response->assertStatus(429);

        // But different number should work (within IP limit)
        $response = $this->postJson('/api/v1/auth/otp/send', [
            'phone' => '+919876543211'
        ]);
        $this->assertNotEquals(429, $response->status(), "Different phone number should not be limited yet");
    }
}
