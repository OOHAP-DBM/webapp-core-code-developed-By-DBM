<?php

namespace Tests\Feature\Hoardings;

use App\Models\Hoarding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoardingApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendor;
    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->vendor = User::factory()->create([
            'email' => 'vendor@example.com',
        ]);
        $this->vendor->assignRole('vendor');

        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
        $this->admin->assignRole('admin');

        $this->customer = User::factory()->create([
            'email' => 'customer@example.com',
        ]);
        $this->customer->assignRole('customer');
    }

    /** @test */
    public function it_shows_hoarding_details()
    {
        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson("/api/v1/hoardings/{$hoarding->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'address',
                'type',
                'status',
                'location' => ['lat', 'lng'],
                'pricing' => ['monthly', 'weekly', 'enable_weekly_booking'],
                'vendor' => ['id', 'name', 'email'],
            ],
        ]);
    }

    /** @test */
    public function it_updates_own_hoarding()
    {
        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->putJson("/api/v1/hoardings/{$hoarding->id}", [
                'title' => 'Updated Title',
                'description' => $hoarding->description,
                'address' => $hoarding->address,
                'lat' => $hoarding->lat,
                'lng' => $hoarding->lng,
                'type' => $hoarding->type,
                'monthly_price' => $hoarding->monthly_price,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('hoardings', [
            'id' => $hoarding->id,
            'title' => 'Updated Title',
        ]);
    }

    /** @test */
    public function it_prevents_updating_others_hoarding()
    {
        $vendor2 = User::factory()->create();
        $vendor2->assignRole('vendor');

        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $vendor2->id,
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->putJson("/api/v1/hoardings/{$hoarding->id}", [
                'title' => 'Hacked Title',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_any_hoarding()
    {
        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/hoardings/{$hoarding->id}", [
                'title' => 'Admin Updated Title',
                'description' => $hoarding->description,
                'address' => $hoarding->address,
                'lat' => $hoarding->lat,
                'lng' => $hoarding->lng,
                'type' => $hoarding->type,
                'monthly_price' => $hoarding->monthly_price,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('hoardings', [
            'id' => $hoarding->id,
            'title' => 'Admin Updated Title',
        ]);
    }

    /** @test */
    public function it_deletes_own_hoarding()
    {
        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->deleteJson("/api/v1/hoardings/{$hoarding->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('hoardings', [
            'id' => $hoarding->id,
        ]);
    }

    /** @test */
    public function it_prevents_deleting_others_hoarding()
    {
        $vendor2 = User::factory()->create();
        $vendor2->assignRole('vendor');

        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $vendor2->id,
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->deleteJson("/api/v1/hoardings/{$hoarding->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('hoardings', [
            'id' => $hoarding->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function admin_can_delete_any_hoarding()
    {
        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/hoardings/{$hoarding->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('hoardings', [
            'id' => $hoarding->id,
        ]);
    }

    /** @test */
    public function customer_cannot_create_hoarding()
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

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/hoardings', $data);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_hoarding()
    {
        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson('/api/v1/hoardings/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_validates_update_data()
    {
        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->putJson("/api/v1/hoardings/{$hoarding->id}", [
                'title' => '', // Empty title
                'type' => 'invalid_type',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'type']);
    }
}
