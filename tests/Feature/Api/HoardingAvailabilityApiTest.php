<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Hoarding;
use App\Models\MaintenanceBlock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * PROMPT 104: Hoarding Availability API Tests
 * Tests for availability calendar API endpoints
 */
class HoardingAvailabilityApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $vendor;
    protected User $customer;
    protected Hoarding $hoarding;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->vendor = User::factory()->create(['role' => 'vendor']);
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->hoarding = Hoarding::factory()->create(['vendor_id' => $this->vendor->id]);
    }

    /** @test */
    public function it_requires_authentication_for_calendar_endpoint()
    {
        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/calendar?' . http_build_query([
            'start_date' => Carbon::today()->format('Y-m-d'),
            'end_date' => Carbon::today()->addDays(6)->format('Y-m-d'),
        ]));

        $response->assertUnauthorized();
    }

    /** @test */
    public function it_validates_required_fields_for_calendar_request()
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/calendar');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    /** @test */
    public function it_validates_date_format()
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/calendar?' . http_build_query([
            'start_date' => '2025/12/20', // Invalid format
            'end_date' => '2025-12-27',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_date']);
    }

    /** @test */
    public function it_validates_end_date_after_start_date()
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/calendar?' . http_build_query([
            'start_date' => '2025-12-27',
            'end_date' => '2025-12-20',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function it_validates_maximum_date_range()
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/calendar?' . http_build_query([
            'start_date' => Carbon::today()->format('Y-m-d'),
            'end_date' => Carbon::today()->addDays(731)->format('Y-m-d'), // > 2 years
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function it_gets_availability_calendar_successfully()
    {
        Sanctum::actingAs($this->customer);

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(6);

        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/calendar?' . http_build_query([
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'hoarding_id',
                'start_date',
                'end_date',
                'total_days',
                'summary' => [
                    'available_days',
                    'booked_days',
                    'blocked_days',
                    'hold_days',
                    'partial_days',
                    'occupancy_rate',
                ],
                'calendar' => [
                    '*' => [
                        'date',
                        'day_of_week',
                        'status',
                    ],
                ],
            ],
        ]);

        $this->assertEquals(7, $response->json('data.total_days'));
    }

    /** @test */
    public function it_includes_details_when_requested()
    {
        Sanctum::actingAs($this->customer);

        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(2),
            'end_date' => Carbon::today()->addDays(3),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/calendar?' . http_build_query([
            'start_date' => Carbon::today()->format('Y-m-d'),
            'end_date' => Carbon::today()->addDays(6)->format('Y-m-d'),
            'include_details' => true,
        ]));

        $response->assertOk();
        
        $calendar = $response->json('data.calendar');
        $bookedDay = collect($calendar)->firstWhere('status', 'booked');
        
        $this->assertArrayHasKey('details', $bookedDay);
        $this->assertArrayHasKey('bookings', $bookedDay['details']);
    }

    /** @test */
    public function it_gets_availability_summary()
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/summary?' . http_build_query([
            'start_date' => Carbon::today()->format('Y-m-d'),
            'end_date' => Carbon::today()->addDays(6)->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'hoarding_id',
                'start_date',
                'end_date',
                'summary',
            ],
        ]);
    }

    /** @test */
    public function it_gets_month_calendar()
    {
        Sanctum::actingAs($this->customer);

        $year = Carbon::today()->year;
        $month = Carbon::today()->month;

        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . "/availability/month/{$year}/{$month}");

        $response->assertOk();
        
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $this->assertEquals($daysInMonth, $response->json('data.total_days'));
    }

    /** @test */
    public function it_validates_year_and_month_for_month_calendar()
    {
        Sanctum::actingAs($this->customer);

        // Invalid year
        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/month/2200/5');
        $response->assertStatus(422);

        // Invalid month
        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/month/2025/13');
        $response->assertStatus(422);
    }

    /** @test */
    public function it_checks_multiple_dates()
    {
        Sanctum::actingAs($this->customer);

        $dates = [
            Carbon::today()->addDays(1)->format('Y-m-d'),
            Carbon::today()->addDays(3)->format('Y-m-d'),
            Carbon::today()->addDays(5)->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/check-dates', [
            'dates' => $dates,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'hoarding_id',
                'requested_dates',
                'results',
            ],
        ]);

        $this->assertCount(3, $response->json('data.results'));
    }

    /** @test */
    public function it_validates_dates_array_for_batch_check()
    {
        Sanctum::actingAs($this->customer);

        // Missing dates
        $response = $this->postJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/check-dates', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['dates']);

        // Empty array
        $response = $this->postJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/check-dates', [
            'dates' => [],
        ]);
        $response->assertStatus(422);

        // Invalid date format
        $response = $this->postJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/check-dates', [
            'dates' => ['2025/12/20'],
        ]);
        $response->assertStatus(422);
    }

    /** @test */
    public function it_limits_batch_check_to_100_dates()
    {
        Sanctum::actingAs($this->customer);

        $dates = [];
        for ($i = 0; $i <= 100; $i++) {
            $dates[] = Carbon::today()->addDays($i)->format('Y-m-d');
        }

        $response = $this->postJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/check-dates', [
            'dates' => $dates,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['dates']);
    }

    /** @test */
    public function it_gets_next_available_dates()
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/next-available?' . http_build_query([
            'count' => 5,
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'hoarding_id',
                'requested_count',
                'found_count',
                'searched_until',
                'dates',
            ],
        ]);

        $this->assertEquals(5, $response->json('data.found_count'));
    }

    /** @test */
    public function it_validates_next_available_parameters()
    {
        Sanctum::actingAs($this->customer);

        // Invalid count (too high)
        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/next-available?count=101');
        $response->assertStatus(422);

        // Invalid start_from (past date)
        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/next-available?start_from=2020-01-01');
        $response->assertStatus(422);

        // Invalid max_search_days (too high)
        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/next-available?max_search_days=800');
        $response->assertStatus(422);
    }

    /** @test */
    public function it_gets_heatmap_data()
    {
        Sanctum::actingAs($this->customer);

        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(2),
            'end_date' => Carbon::today()->addDays(3),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/heatmap?' . http_build_query([
            'start_date' => Carbon::today()->format('Y-m-d'),
            'end_date' => Carbon::today()->addDays(6)->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'hoarding_id',
                'start_date',
                'end_date',
                'summary',
                'heatmap' => [
                    '*' => [
                        'date',
                        'status',
                        'color',
                        'label',
                    ],
                ],
            ],
        ]);

        // Check color codes
        $heatmap = $response->json('data.heatmap');
        $bookedDay = collect($heatmap)->firstWhere('status', 'booked');
        $this->assertEquals('#ef4444', $bookedDay['color']); // Red for booked

        $availableDay = collect($heatmap)->firstWhere('status', 'available');
        $this->assertEquals('#22c55e', $availableDay['color']); // Green for available
    }

    /** @test */
    public function it_performs_quick_check()
    {
        Sanctum::actingAs($this->customer);

        $date = Carbon::today()->addDays(2)->format('Y-m-d');

        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/quick-check?date=' . $date);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'hoarding_id',
                'date',
                'status',
            ],
        ]);

        $this->assertEquals($date, $response->json('data.date'));
    }

    /** @test */
    public function it_requires_date_for_quick_check()
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/quick-check');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function calendar_reflects_all_status_types_correctly()
    {
        Sanctum::actingAs($this->customer);

        // Create booked dates
        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(1),
            'end_date' => Carbon::today()->addDays(1),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Create hold dates
        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(3),
            'end_date' => Carbon::today()->addDays(3),
            'status' => Booking::STATUS_PENDING_PAYMENT_HOLD,
            'hold_expiry_at' => Carbon::now()->addHours(2),
        ]);

        // Create maintenance block
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Maintenance',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(5),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
            'block_type' => MaintenanceBlock::TYPE_MAINTENANCE,
        ]);

        $response = $this->getJson('/api/v1/hoardings/' . $this->hoarding->id . '/availability/calendar?' . http_build_query([
            'start_date' => Carbon::today()->format('Y-m-d'),
            'end_date' => Carbon::today()->addDays(6)->format('Y-m-d'),
        ]));

        $response->assertOk();

        $calendar = $response->json('data.calendar');
        
        // Verify specific statuses
        $this->assertEquals('available', $calendar[0]['status']); // Day 0
        $this->assertEquals('booked', $calendar[1]['status']);    // Day 1
        $this->assertEquals('available', $calendar[2]['status']); // Day 2
        $this->assertEquals('hold', $calendar[3]['status']);      // Day 3
        $this->assertEquals('available', $calendar[4]['status']); // Day 4
        $this->assertEquals('blocked', $calendar[5]['status']);   // Day 5
        $this->assertEquals('available', $calendar[6]['status']); // Day 6
    }
}
