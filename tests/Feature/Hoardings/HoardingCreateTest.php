<?php

namespace Tests\Feature\Hoardings;

use Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoardingCreateTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->vendor = User::factory()->create([
            'email' => 'vendor@example.com',
        ]);
        $this->vendor->assignRole('vendor');
    }

    /** @test */
    public function it_creates_hoarding_with_valid_data()
    {
        $data = [
            'title' => 'Test Billboard',
            'description' => 'A great location for advertising',
            'address' => '123 Main Street, New York, NY',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'type' => 'billboard',
            'monthly_price' => 5000.00,
            'enable_weekly_booking' => false,
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->postJson('/api/v1/hoardings', $data);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'type',
                'status',
                'location' => ['lat', 'lng'],
                'pricing' => ['monthly', 'weekly', 'enable_weekly_booking'],
                'vendor' => ['id', 'name', 'email'],
            ],
        ]);

        $this->assertDatabaseHas('hoardings', [
            'title' => 'Test Billboard',
            'vendor_id' => $this->vendor->id,
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->actingAs($this->vendor, 'sanctum')
            ->postJson('/api/v1/hoardings', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'title',
            'description',
            'address',
            'lat',
            'lng',
            'type',
            'monthly_price',
        ]);
    }

    /** @test */
    public function it_validates_type_enum()
    {
        $data = [
            'title' => 'Test Billboard',
            'description' => 'A great location',
            'address' => '123 Main Street',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'type' => 'invalid_type',
            'monthly_price' => 5000.00,
        ];

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->postJson('/api/v1/hoardings', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function it_validates_status_enum()
    {
        $data = [
            'title' => 'Test Billboard',
            'description' => 'A great location',
            'address' => '123 Main Street',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'type' => 'billboard',
            'monthly_price' => 5000.00,
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->postJson('/api/v1/hoardings', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_requires_weekly_price_when_weekly_booking_enabled()
    {
        $data = [
            'title' => 'Test Billboard',
            'description' => 'A great location',
            'address' => '123 Main Street',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'type' => 'billboard',
            'monthly_price' => 5000.00,
            'enable_weekly_booking' => true,
            // Missing weekly_price
        ];

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->postJson('/api/v1/hoardings', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['weekly_price']);
    }

    /** @test */
    public function it_validates_coordinate_ranges()
    {
        $data = [
            'title' => 'Test Billboard',
            'description' => 'A great location',
            'address' => '123 Main Street',
            'lat' => 100.0, // Invalid: outside -90 to 90 range
            'lng' => -200.0, // Invalid: outside -180 to 180 range
            'type' => 'billboard',
            'monthly_price' => 5000.00,
        ];

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->postJson('/api/v1/hoardings', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lat', 'lng']);
    }

    /** @test */
    public function it_validates_price_as_positive_number()
    {
        $data = [
            'title' => 'Test Billboard',
            'description' => 'A great location',
            'address' => '123 Main Street',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'type' => 'billboard',
            'monthly_price' => -1000, // Negative price
        ];

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->postJson('/api/v1/hoardings', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['monthly_price']);
    }

    /** @test */
    public function it_automatically_assigns_vendor_id()
    {
        $data = [
            'title' => 'Test Billboard',
            'description' => 'A great location',
            'address' => '123 Main Street',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'type' => 'billboard',
            'monthly_price' => 5000.00,
        ];

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->postJson('/api/v1/hoardings', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('hoardings', [
            'title' => 'Test Billboard',
            'vendor_id' => $this->vendor->id, // Should auto-assign authenticated vendor
        ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $data = [
            'title' => 'Test Billboard',
            'description' => 'A great location',
            'address' => '123 Main Street',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'type' => 'billboard',
            'monthly_price' => 5000.00,
        ];

        $response = $this->postJson('/api/v1/hoardings', $data);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_vendor_role()
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $data = [
            'title' => 'Test Billboard',
            'description' => 'A great location',
            'address' => '123 Main Street',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'type' => 'billboard',
            'monthly_price' => 5000.00,
        ];

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/hoardings', $data);

        $response->assertStatus(403);
    }
}

