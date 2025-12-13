<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Hoarding;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PROMPT 101: Booking Overlap API Endpoint Tests
 */
class BookingOverlapApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $vendor;
    protected Hoarding $hoarding;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->vendor = User::factory()->create(['role' => 'vendor']);
        $this->hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_checks_overlap_successfully()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/booking-overlap/check', [
                'hoarding_id' => $this->hoarding->id,
                'start_date' => Carbon::today()->addDays(10)->format('Y-m-d'),
                'end_date' => Carbon::today()->addDays(20)->format('Y-m-d'),
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'available',
                'message',
                'conflicts_count',
            ])
            ->assertJson([
                'success' => true,
                'available' => true,
            ]);
    }

    /** @test */
    public function it_returns_conflicts_when_dates_overlap()
    {
        // Create existing booking
        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'start_date' => Carbon::today()->addDays(10),
            'end_date' => Carbon::today()->addDays(20),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/booking-overlap/check', [
                'hoarding_id' => $this->hoarding->id,
                'start_date' => Carbon::today()->addDays(15)->format('Y-m-d'),
                'end_date' => Carbon::today()->addDays(25)->format('Y-m-d'),
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'available' => false,
            ])
            ->assertJsonPath('conflicts_count', 1);
    }

    /** @test */
    public function it_returns_detailed_response_when_requested()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/booking-overlap/check', [
                'hoarding_id' => $this->hoarding->id,
                'start_date' => Carbon::today()->addDays(10)->format('Y-m-d'),
                'end_date' => Carbon::today()->addDays(20)->format('Y-m-d'),
                'detailed' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'available',
                    'conflicts',
                    'message',
                    'checked_period',
                ],
            ]);
    }

    /** @test */
    public function quick_availability_check_returns_boolean()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson('/api/v1/booking-overlap/is-available?' . http_build_query([
                'hoarding_id' => $this->hoarding->id,
                'start_date' => Carbon::today()->addDays(10)->format('Y-m-d'),
                'end_date' => Carbon::today()->addDays(20)->format('Y-m-d'),
            ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'available',
                'hoarding_id',
                'dates',
            ])
            ->assertJson([
                'success' => true,
                'available' => true,
            ]);
    }

    /** @test */
    public function it_validates_request_parameters()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/booking-overlap/check', [
                'hoarding_id' => 'invalid',
                'start_date' => 'not-a-date',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['hoarding_id', 'start_date', 'end_date']);
    }

    /** @test */
    public function it_rejects_past_start_dates()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/booking-overlap/check', [
                'hoarding_id' => $this->hoarding->id,
                'start_date' => Carbon::yesterday()->format('Y-m-d'),
                'end_date' => Carbon::tomorrow()->format('Y-m-d'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    /** @test */
    public function it_rejects_end_date_before_start_date()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/booking-overlap/check', [
                'hoarding_id' => $this->hoarding->id,
                'start_date' => Carbon::today()->addDays(20)->format('Y-m-d'),
                'end_date' => Carbon::today()->addDays(10)->format('Y-m-d'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function it_performs_batch_check()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/booking-overlap/batch-check', [
                'hoarding_id' => $this->hoarding->id,
                'date_ranges' => [
                    [
                        'start' => Carbon::today()->addDays(10)->format('Y-m-d'),
                        'end' => Carbon::today()->addDays(15)->format('Y-m-d'),
                    ],
                    [
                        'start' => Carbon::today()->addDays(20)->format('Y-m-d'),
                        'end' => Carbon::today()->addDays(25)->format('Y-m-d'),
                    ],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_ranges_checked',
                    'all_available',
                    'results',
                ],
            ])
            ->assertJsonPath('data.total_ranges_checked', 2);
    }

    /** @test */
    public function it_gets_occupied_dates()
    {
        // Create booking
        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'start_date' => Carbon::today()->addDays(10),
            'end_date' => Carbon::today()->addDays(15),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson('/api/v1/booking-overlap/occupied-dates?' . http_build_query([
                'hoarding_id' => $this->hoarding->id,
                'start_date' => Carbon::today()->format('Y-m-d'),
                'end_date' => Carbon::today()->addDays(30)->format('Y-m-d'),
            ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'hoarding_id',
                    'period',
                    'occupied_dates',
                    'total_occupied_days',
                ],
            ])
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function it_finds_next_available_slot()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson('/api/v1/booking-overlap/find-next-slot?' . http_build_query([
                'hoarding_id' => $this->hoarding->id,
                'duration_days' => 7,
            ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'slot_found',
                'data',
                'message',
            ])
            ->assertJson([
                'success' => true,
                'slot_found' => true,
            ]);
    }

    /** @test */
    public function it_generates_availability_report()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson('/api/v1/booking-overlap/availability-report?' . http_build_query([
                'hoarding_id' => $this->hoarding->id,
                'start_date' => Carbon::today()->format('Y-m-d'),
                'end_date' => Carbon::today()->addDays(30)->format('Y-m-d'),
            ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'hoarding_id',
                    'period',
                    'statistics' => [
                        'confirmed_bookings',
                        'active_holds',
                        'occupied_days',
                        'available_days',
                        'occupancy_rate',
                    ],
                    'occupied_dates',
                ],
            ]);
    }

    /** @test */
    public function it_gets_detailed_conflicts()
    {
        // Create booking
        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'start_date' => Carbon::today()->addDays(10),
            'end_date' => Carbon::today()->addDays(15),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson('/api/v1/booking-overlap/conflicts?' . http_build_query([
                'hoarding_id' => $this->hoarding->id,
                'start_date' => Carbon::today()->addDays(12)->format('Y-m-d'),
                'end_date' => Carbon::today()->addDays(18)->format('Y-m-d'),
            ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'has_conflicts',
                'conflicts',
                'conflict_details',
                'message',
            ])
            ->assertJson([
                'success' => true,
                'has_conflicts' => true,
            ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->postJson('/api/v1/booking-overlap/check', [
            'hoarding_id' => $this->hoarding->id,
            'start_date' => Carbon::today()->addDays(10)->format('Y-m-d'),
            'end_date' => Carbon::today()->addDays(20)->format('Y-m-d'),
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function batch_check_validates_maximum_ranges()
    {
        $tooManyRanges = array_fill(0, 25, [
            'start' => Carbon::today()->addDays(10)->format('Y-m-d'),
            'end' => Carbon::today()->addDays(15)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/booking-overlap/batch-check', [
                'hoarding_id' => $this->hoarding->id,
                'date_ranges' => $tooManyRanges,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_ranges']);
    }
}
