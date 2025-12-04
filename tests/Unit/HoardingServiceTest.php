<?php

namespace Tests\Unit;

use App\Models\Hoarding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Hoardings\Repositories\Contracts\HoardingRepositoryInterface;
use Modules\Hoardings\Services\HoardingService;
use Tests\TestCase;

class HoardingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected HoardingService $hoardingService;
    protected HoardingRepositoryInterface $hoardingRepository;
    protected User $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hoardingRepository = $this->app->make(HoardingRepositoryInterface::class);
        $this->hoardingService = new HoardingService($this->hoardingRepository);

        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        // Create test vendor
        $this->vendor = User::factory()->create([
            'email' => 'vendor@example.com',
        ]);
        $this->vendor->assignRole('vendor');
    }

    /** @test */
    public function it_creates_hoarding_with_valid_data()
    {
        $data = [
            'vendor_id' => $this->vendor->id,
            'title' => 'Test Billboard',
            'description' => 'A great location',
            'address' => '123 Main St',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'type' => 'billboard',
            'monthly_price' => 5000.00,
            'enable_weekly_booking' => false,
        ];

        $hoarding = $this->hoardingService->create($data);

        $this->assertDatabaseHas('hoardings', [
            'title' => 'Test Billboard',
            'vendor_id' => $this->vendor->id,
            'status' => 'draft', // Default status
        ]);
    }

    /** @test */
    public function it_validates_weekly_price_when_weekly_booking_enabled()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $data = [
            'vendor_id' => $this->vendor->id,
            'title' => 'Test Billboard',
            'description' => 'A great location',
            'address' => '123 Main St',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'type' => 'billboard',
            'monthly_price' => 5000.00,
            'enable_weekly_booking' => true,
            // Missing weekly_price
        ];

        $this->hoardingService->create($data);
    }

    /** @test */
    public function it_calculates_price_correctly()
    {
        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'monthly_price' => 5000.00,
            'weekly_price' => 1500.00,
            'enable_weekly_booking' => true,
        ]);

        // 2 weeks + 1 month
        $price = $this->hoardingService->calculatePrice($hoarding->id, 2, 1);

        $this->assertEquals(8000.00, $price); // (2 * 1500) + (1 * 5000)
    }

    /** @test */
    public function it_calculates_price_with_only_months()
    {
        $hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'monthly_price' => 5000.00,
        ]);

        $price = $this->hoardingService->calculatePrice($hoarding->id, 0, 3);

        $this->assertEquals(15000.00, $price); // 3 * 5000
    }

    /** @test */
    public function it_returns_vendor_statistics()
    {
        // Create multiple hoardings with different statuses
        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'type' => 'billboard',
        ]);

        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'draft',
            'type' => 'digital',
        ]);

        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'pending_approval',
            'type' => 'billboard',
        ]);

        $statistics = $this->hoardingService->getVendorStatistics($this->vendor->id);

        $this->assertEquals(3, $statistics['total']);
        $this->assertEquals(1, $statistics['active']);
        $this->assertEquals(1, $statistics['draft']);
        $this->assertEquals(1, $statistics['pending_approval']);
        $this->assertArrayHasKey('by_type', $statistics);
    }

    /** @test */
    public function it_caches_vendor_statistics()
    {
        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
        ]);

        // First call should cache
        $this->hoardingService->getVendorStatistics($this->vendor->id);

        $cacheKey = "vendor_hoarding_statistics_{$this->vendor->id}";
        $this->assertTrue(Cache::has($cacheKey));
    }

    /** @test */
    public function it_clears_vendor_statistics_cache()
    {
        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
        ]);

        // Cache statistics
        $this->hoardingService->getVendorStatistics($this->vendor->id);

        $cacheKey = "vendor_hoarding_statistics_{$this->vendor->id}";
        $this->assertTrue(Cache::has($cacheKey));

        // Clear cache
        $this->hoardingService->clearVendorStatistics($this->vendor->id);
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function it_searches_hoardings_by_query()
    {
        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'title' => 'Times Square Billboard',
            'description' => 'Prime location',
        ]);

        Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'title' => 'Downtown Digital',
            'description' => 'Modern display',
        ]);

        $results = $this->hoardingService->search('Times Square');

        $this->assertCount(1, $results);
        $this->assertEquals('Times Square Billboard', $results->first()->title);
    }
}
