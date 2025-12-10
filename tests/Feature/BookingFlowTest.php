<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Hoarding;
use App\Models\BookingDraft;
use App\Services\HoardingBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $customer;
    protected $hoarding;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test customer
        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'test@customer.com'
        ]);

        // Create test hoarding
        $this->hoarding = Hoarding::factory()->create([
            'is_active' => true,
            'status' => 'active',
            'monthly_price' => 50000,
            'weekly_price' => 15000
        ]);

        $this->service = app(HoardingBookingService::class);
    }

    /** @test */
    public function it_can_get_hoarding_details()
    {
        $details = $this->service->getHoardingDetails($this->hoarding->id);

        $this->assertArrayHasKey('hoarding', $details);
        $this->assertArrayHasKey('vendor', $details);
        $this->assertArrayHasKey('availability', $details);
        $this->assertArrayHasKey('booking_rules', $details);
        $this->assertEquals($this->hoarding->id, $details['hoarding']['id']);
    }

    /** @test */
    public function it_can_get_available_packages()
    {
        $packages = $this->service->getAvailablePackages($this->hoarding->id);

        $this->assertArrayHasKey('dooh_packages', $packages);
        $this->assertArrayHasKey('standard_packages', $packages);
        $this->assertIsArray($packages['standard_packages']);
    }

    /** @test */
    public function it_validates_dates_correctly()
    {
        $startDate = now()->addDays(3)->format('Y-m-d');
        $endDate = now()->addDays(33)->format('Y-m-d');

        $result = $this->service->validateDateSelection(
            $this->hoarding->id,
            $startDate,
            $endDate
        );

        $this->assertTrue($result['valid']);
        $this->assertEquals($startDate, $result['start_date']);
        $this->assertEquals($endDate, $result['end_date']);
    }

    /** @test */
    public function it_rejects_past_dates()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Start date cannot be in the past');

        $this->service->validateDateSelection(
            $this->hoarding->id,
            now()->subDays(1)->format('Y-m-d'),
            now()->addDays(30)->format('Y-m-d')
        );
    }

    /** @test */
    public function it_creates_draft_booking()
    {
        $startDate = now()->addDays(3)->format('Y-m-d');
        $endDate = now()->addDays(33)->format('Y-m-d');

        $draft = $this->service->createOrUpdateDraft(
            $this->customer,
            $this->hoarding->id,
            null, // package_id
            $startDate,
            $endDate
        );

        $this->assertInstanceOf(BookingDraft::class, $draft);
        $this->assertEquals($this->customer->id, $draft->customer_id);
        $this->assertEquals($this->hoarding->id, $draft->hoarding_id);
        $this->assertEquals($startDate, $draft->start_date->format('Y-m-d'));
        $this->assertNotNull($draft->total_amount);
        $this->assertNotNull($draft->expires_at);
    }

    /** @test */
    public function it_freezes_price_in_draft()
    {
        $startDate = now()->addDays(3)->format('Y-m-d');
        $endDate = now()->addDays(33)->format('Y-m-d');

        $draft = $this->service->createOrUpdateDraft(
            $this->customer,
            $this->hoarding->id,
            null,
            $startDate,
            $endDate
        );

        $this->assertNotNull($draft->price_snapshot);
        $this->assertIsArray(json_decode($draft->price_snapshot, true));
        $this->assertGreaterThan(0, $draft->base_price);
        $this->assertGreaterThan(0, $draft->total_amount);
    }

    /** @test */
    public function it_generates_review_summary()
    {
        $startDate = now()->addDays(3)->format('Y-m-d');
        $endDate = now()->addDays(33)->format('Y-m-d');

        $draft = $this->service->createOrUpdateDraft(
            $this->customer,
            $this->hoarding->id,
            null,
            $startDate,
            $endDate
        );

        $summary = $this->service->getReviewSummary($draft);

        $this->assertArrayHasKey('draft_id', $summary);
        $this->assertArrayHasKey('hoarding', $summary);
        $this->assertArrayHasKey('booking_period', $summary);
        $this->assertArrayHasKey('pricing', $summary);
        $this->assertEquals($draft->id, $summary['draft_id']);
    }

    /** @test */
    public function draft_expires_after_30_minutes()
    {
        $draft = BookingDraft::factory()->create([
            'customer_id' => $this->customer->id,
            'hoarding_id' => $this->hoarding->id,
            'expires_at' => now()->subMinutes(31)
        ]);

        $this->assertTrue($draft->isExpired());
    }

    /** @test */
    public function draft_is_not_expired_within_window()
    {
        $draft = BookingDraft::factory()->create([
            'customer_id' => $this->customer->id,
            'hoarding_id' => $this->hoarding->id,
            'expires_at' => now()->addMinutes(15)
        ]);

        $this->assertFalse($draft->isExpired());
    }

    /** @test */
    public function it_tracks_flow_steps()
    {
        $draft = BookingDraft::factory()->create([
            'customer_id' => $this->customer->id,
            'hoarding_id' => $this->hoarding->id,
            'step' => BookingDraft::STEP_HOARDING_SELECTED
        ]);

        $draft->updateStep(BookingDraft::STEP_DATES_SELECTED);

        $this->assertEquals(BookingDraft::STEP_DATES_SELECTED, $draft->fresh()->step);
        $this->assertNotNull($draft->fresh()->last_updated_step_at);
    }

    /** @test */
    public function booking_flow_api_requires_authentication()
    {
        $response = $this->getJson("/api/v1/booking/hoarding/{$this->hoarding->id}");
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_customer_can_get_hoarding_details()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/v1/booking/hoarding/{$this->hoarding->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'hoarding',
                    'vendor',
                    'availability',
                    'booking_rules'
                ]
            ]);
    }

    /** @test */
    public function authenticated_customer_can_create_draft()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/booking/draft', [
                'hoarding_id' => $this->hoarding->id,
                'start_date' => now()->addDays(3)->format('Y-m-d'),
                'end_date' => now()->addDays(33)->format('Y-m-d')
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'draft_id',
                    'step',
                    'pricing',
                    'expires_at'
                ]
            ]);
    }

    /** @test */
    public function it_validates_date_input_format()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/booking/validate-dates', [
                'hoarding_id' => $this->hoarding->id,
                'start_date' => 'invalid-date',
                'end_date' => now()->addDays(30)->format('Y-m-d')
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function customer_can_only_access_own_drafts()
    {
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        
        $draft = BookingDraft::factory()->create([
            'customer_id' => $otherCustomer->id,
            'hoarding_id' => $this->hoarding->id
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/v1/booking/draft/{$draft->id}");

        $response->assertStatus(404);
    }
}
