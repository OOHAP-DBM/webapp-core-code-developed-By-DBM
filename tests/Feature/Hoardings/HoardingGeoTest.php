<?php

namespace Tests\Feature\Hoardings;

use Modules\Hoardings\Models\Hoarding;
use Modules\Hoardings\Models\HoardingGeo;
use Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoardingGeoTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);

        $this->vendor = User::factory()->create([
            'email' => 'vendor@example.com',
        ]);
        $this->vendor->assignRole('vendor');
    }

    /** @test */
    public function it_returns_hoardings_within_bounding_box()
    {
        // Create hoardings in different locations
        $insideHoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'lat' => 19.0760,
            'lng' => 72.8777,
            'title' => 'Mumbai Billboard',
        ]);

        $outsideHoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'lat' => 28.7041,
            'lng' => 77.1025,
            'title' => 'Delhi Billboard',
        ]);

        // Bounding box around Mumbai (minLat,minLng,maxLat,maxLng)
        $bbox = '18.9,72.7,19.2,72.9';

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson("/api/v1/hoardings?bbox={$bbox}");

        $response->assertStatus(200);
        
        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertContains('Mumbai Billboard', $titles);
        $this->assertNotContains('Delhi Billboard', $titles);
    }

    /** @test */
    public function it_returns_hoardings_within_radius()
    {
        // Mumbai center
        $centerLat = 19.0760;
        $centerLng = 72.8777;

        // Hoarding within 5km
        $nearHoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'lat' => 19.0800, // ~450m away
            'lng' => 72.8800,
            'title' => 'Near Billboard',
        ]);

        // Hoarding far away (>50km)
        $farHoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'lat' => 19.5,
            'lng' => 73.5,
            'title' => 'Far Billboard',
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson("/api/v1/hoardings?lat={$centerLat}&lng={$centerLng}&radius=5");

        $response->assertStatus(200);
        
        // Should return at least 1 (the near one)
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    /** @test */
    public function it_returns_map_pins_in_compact_format()
    {
        Hoarding::factory()->count(3)->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'lat' => 19.0760,
            'lng' => 72.8777,
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson('/api/v1/hoardings/map-pins');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'lat',
                    'lng',
                    'type',
                    'price',
                    'weekly_price',
                ]
            ],
            'total',
        ]);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_checks_point_in_polygon()
    {
        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'lat' => 19.0760,
            'lng' => 72.8777,
        ]);

        // Create a square polygon around the point
        $geojson = [
            'type' => 'Polygon',
            'coordinates' => [[
                [72.85, 19.05], // SW
                [72.90, 19.05], // SE
                [72.90, 19.10], // NE
                [72.85, 19.10], // NW
                [72.85, 19.05], // Close polygon
            ]]
        ];

        $geo = HoardingGeo::create([
            'hoarding_id' => $hoarding->id,
            'geojson' => $geojson,
        ]);

        // Point inside polygon
        $this->assertTrue($geo->isPointInPolygon(19.0760, 72.8777));

        // Point outside polygon
        $this->assertFalse($geo->isPointInPolygon(19.5, 73.5));
    }

    /** @test */
    public function it_calculates_bounding_box_from_geojson()
    {
        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
        ]);

        $geojson = [
            'type' => 'Polygon',
            'coordinates' => [[
                [72.85, 19.05],
                [72.90, 19.05],
                [72.90, 19.10],
                [72.85, 19.10],
                [72.85, 19.05],
            ]]
        ];

        $geo = HoardingGeo::create([
            'hoarding_id' => $hoarding->id,
            'geojson' => $geojson,
        ]);

        $bbox = $geo->calculateBoundingBox();

        $this->assertNotNull($bbox);
        $this->assertEquals(19.05, $bbox['min_lat']);
        $this->assertEquals(19.10, $bbox['max_lat']);
        $this->assertEquals(72.85, $bbox['min_lng']);
        $this->assertEquals(72.90, $bbox['max_lng']);
    }

    /** @test */
    public function it_updates_bounding_box_automatically()
    {
        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
        ]);

        $geojson = [
            'type' => 'Polygon',
            'coordinates' => [[
                [72.85, 19.05],
                [72.90, 19.05],
                [72.90, 19.10],
                [72.85, 19.10],
                [72.85, 19.05],
            ]]
        ];

        $geo = HoardingGeo::create([
            'hoarding_id' => $hoarding->id,
            'geojson' => $geojson,
        ]);

        $geo->updateBoundingBox();
        $geo->refresh();

        $this->assertNotNull($geo->bounding_box);
        $this->assertArrayHasKey('min_lat', $geo->bounding_box);
        $this->assertArrayHasKey('max_lat', $geo->bounding_box);
        $this->assertArrayHasKey('min_lng', $geo->bounding_box);
        $this->assertArrayHasKey('max_lng', $geo->bounding_box);
    }

    /** @test */
    public function it_checks_bounding_box_intersection()
    {
        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
        ]);

        $geo = HoardingGeo::create([
            'hoarding_id' => $hoarding->id,
            'geojson' => [
                'type' => 'Polygon',
                'coordinates' => [[
                    [72.85, 19.05],
                    [72.90, 19.05],
                    [72.90, 19.10],
                    [72.85, 19.10],
                    [72.85, 19.05],
                ]]
            ],
            'bounding_box' => [
                'min_lat' => 19.05,
                'max_lat' => 19.10,
                'min_lng' => 72.85,
                'max_lng' => 72.90,
            ]
        ]);

        // Overlapping bounding box
        $this->assertTrue($geo->intersectsBoundingBox(19.00, 19.07, 72.80, 72.87));

        // Non-overlapping bounding box
        $this->assertFalse($geo->intersectsBoundingBox(20.00, 20.10, 73.00, 73.10));
    }

    /** @test */
    public function it_filters_map_pins_by_type()
    {
        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'type' => 'billboard',
            'lat' => 19.0760,
            'lng' => 72.8777,
        ]);

        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'type' => 'digital',
            'lat' => 19.0800,
            'lng' => 72.8800,
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson('/api/v1/hoardings/map-pins?type=billboard');

        $response->assertStatus(200);
        
        $types = collect($response->json('data'))->pluck('type')->unique();
        $this->assertCount(1, $types);
        $this->assertEquals('billboard', $types->first());
    }

    /** @test */
    public function it_calculates_haversine_distance()
    {
        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'lat' => 19.0760,
            'lng' => 72.8777,
        ]);

        // Point very close (should be < 1km)
        $distance = $hoarding->haversineDistance(19.0800, 72.8800);
        $this->assertLessThan(1, $distance);

        // Point far away (Mumbai to Delhi, ~1150km)
        $distance = $hoarding->haversineDistance(28.7041, 77.1025);
        $this->assertGreaterThan(1000, $distance);
    }

    /** @test */
    public function map_pins_only_returns_active_hoardings()
    {
        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'lat' => 19.0760,
            'lng' => 72.8777,
        ]);

        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'draft',
            'lat' => 19.0800,
            'lng' => 72.8800,
        ]);

        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'inactive',
            'lat' => 19.0850,
            'lng' => 72.8850,
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson('/api/v1/hoardings/map-pins');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total'));
    }

    /** @test */
    public function it_returns_empty_array_when_no_hoardings_in_area()
    {
        // Create hoarding in Mumbai
        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'lat' => 19.0760,
            'lng' => 72.8777,
        ]);

        // Search in Delhi area (far from Mumbai)
        $bbox = '28.5,77.0,28.8,77.3';

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson("/api/v1/hoardings/map-pins?bbox={$bbox}");

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('total'));
        $this->assertEmpty($response->json('data'));
    }
}

