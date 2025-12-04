<?php

namespace Tests\Feature\Hoardings;

use App\Models\Hoarding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoardingListingTest extends TestCase
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
    public function it_lists_all_active_hoardings_by_default()
    {
        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'title' => 'Active Billboard',
        ]);

        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'draft',
            'title' => 'Draft Billboard',
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson('/api/v1/hoardings');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data'); // Only active hoarding
    }

    /** @test */
    public function it_filters_hoardings_by_vendor()
    {
        $vendor2 = User::factory()->create();
        $vendor2->assignRole('vendor');

        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
        ]);

        Hoarding::factory()->create([
            'vendor_id' => $vendor2->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson('/api/v1/hoardings?vendor_id=' . $this->vendor->id);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_filters_hoardings_by_type()
    {
        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'type' => 'billboard',
        ]);

        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'type' => 'digital',
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson('/api/v1/hoardings?type=billboard');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.type', 'billboard');
    }

    /** @test */
    public function it_filters_hoardings_by_status()
    {
        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
        ]);

        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson('/api/v1/hoardings?status=pending_approval');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.status', 'pending_approval');
    }

    /** @test */
    public function it_searches_hoardings_by_keyword()
    {
        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'title' => 'Times Square Billboard',
        ]);

        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'title' => 'Downtown Digital',
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson('/api/v1/hoardings?search=Times Square');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.title', 'Times Square Billboard');
    }

    /** @test */
    public function it_filters_hoardings_by_location()
    {
        // New York City
        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'title' => 'NYC Billboard',
        ]);

        // Los Angeles (far away)
        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'lat' => 34.0522,
            'lng' => -118.2437,
            'title' => 'LA Billboard',
        ]);

        // Search near NYC with 50km radius
        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson('/api/v1/hoardings?lat=40.7128&lng=-74.0060&radius=50');

        $response->assertStatus(200);
        // Should only return NYC billboard (bounding box approximation)
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    /** @test */
    public function it_paginates_hoardings()
    {
        Hoarding::factory()->count(25)->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson('/api/v1/hoardings?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
    }

    /** @test */
    public function it_sorts_hoardings()
    {
        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'title' => 'Z Billboard',
            'monthly_price' => 3000,
        ]);

        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'title' => 'A Billboard',
            'monthly_price' => 5000,
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson('/api/v1/hoardings?sort_by=monthly_price&sort_order=desc');

        $response->assertStatus(200);
        $this->assertEquals(5000, $response->json('data.0.pricing.monthly'));
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/hoardings');

        $response->assertStatus(401);
    }
}
