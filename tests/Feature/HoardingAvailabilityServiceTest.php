<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Hoarding;
use App\Models\MaintenanceBlock;
use App\Models\User;
use Modules\Hoardings\Services\HoardingAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PROMPT 104: Hoarding Availability Service Tests
 * Tests for availability calendar generation and status determination
 */
class HoardingAvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected HoardingAvailabilityService $service;
    protected User $admin;
    protected User $vendor;
    protected User $customer;
    protected Hoarding $hoarding;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(HoardingAvailabilityService::class);
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->vendor = User::factory()->create(['role' => 'vendor']);
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->hoarding = Hoarding::factory()->create(['vendor_id' => $this->vendor->id]);
    }

    /** @test */
    public function it_returns_all_dates_as_available_when_no_conflicts()
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(6);

        $calendar = $this->service->getAvailabilityCalendar(
            $this->hoarding->id,
            $startDate,
            $endDate
        );

        $this->assertEquals($this->hoarding->id, $calendar['hoarding_id']);
        $this->assertCount(7, $calendar['calendar']);
        
        foreach ($calendar['calendar'] as $day) {
            $this->assertEquals('available', $day['status']);
        }

        $this->assertEquals(7, $calendar['summary']['available_days']);
        $this->assertEquals(0, $calendar['summary']['booked_days']);
        $this->assertEquals(0, $calendar['summary']['blocked_days']);
        $this->assertEquals(0.0, $calendar['summary']['occupancy_rate']);
    }

    /** @test */
    public function it_marks_dates_as_booked_when_confirmed_booking_exists()
    {
        $booking = Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(2),
            'end_date' => Carbon::today()->addDays(4),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $calendar = $this->service->getAvailabilityCalendar(
            $this->hoarding->id,
            Carbon::today(),
            Carbon::today()->addDays(6)
        );

        $bookedDates = collect($calendar['calendar'])->where('status', 'booked');
        $this->assertCount(3, $bookedDates); // Days 2, 3, 4

        $this->assertEquals(4, $calendar['summary']['available_days']);
        $this->assertEquals(3, $calendar['summary']['booked_days']);
    }

    /** @test */
    public function it_marks_dates_as_blocked_when_maintenance_block_active()
    {
        $block = MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Maintenance',
            'start_date' => Carbon::today()->addDays(1),
            'end_date' => Carbon::today()->addDays(3),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
            'block_type' => MaintenanceBlock::TYPE_MAINTENANCE,
        ]);

        $calendar = $this->service->getAvailabilityCalendar(
            $this->hoarding->id,
            Carbon::today(),
            Carbon::today()->addDays(6)
        );

        $blockedDates = collect($calendar['calendar'])->where('status', 'blocked');
        $this->assertCount(3, $blockedDates); // Days 1, 2, 3

        $this->assertEquals(4, $calendar['summary']['available_days']);
        $this->assertEquals(3, $calendar['summary']['blocked_days']);
    }

    /** @test */
    public function it_marks_dates_as_hold_when_payment_hold_exists()
    {
        $hold = Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(2),
            'end_date' => Carbon::today()->addDays(3),
            'status' => Booking::STATUS_PENDING_PAYMENT_HOLD,
            'hold_expiry_at' => Carbon::now()->addHours(2),
        ]);

        $calendar = $this->service->getAvailabilityCalendar(
            $this->hoarding->id,
            Carbon::today(),
            Carbon::today()->addDays(6)
        );

        $holdDates = collect($calendar['calendar'])->where('status', 'hold');
        $this->assertCount(2, $holdDates); // Days 2, 3

        $this->assertEquals(5, $calendar['summary']['available_days']);
        $this->assertEquals(2, $calendar['summary']['hold_days']);
    }

    /** @test */
    public function it_marks_dates_as_partial_when_multiple_statuses_exist()
    {
        // Booking on days 2-3
        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(2),
            'end_date' => Carbon::today()->addDays(3),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Hold on days 3-4 (overlaps day 3)
        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(3),
            'end_date' => Carbon::today()->addDays(4),
            'status' => Booking::STATUS_PENDING_PAYMENT_HOLD,
            'hold_expiry_at' => Carbon::now()->addHours(2),
        ]);

        $calendar = $this->service->getAvailabilityCalendar(
            $this->hoarding->id,
            Carbon::today(),
            Carbon::today()->addDays(6)
        );

        // Day 3 should have both booking and hold
        $day3 = collect($calendar['calendar'])->firstWhere('date', Carbon::today()->addDays(3)->format('Y-m-d'));
        $this->assertEquals('partial', $day3['status']);
    }

    /** @test */
    public function it_prioritizes_blocked_status_over_others()
    {
        // Create maintenance block
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Maintenance',
            'start_date' => Carbon::today()->addDays(2),
            'end_date' => Carbon::today()->addDays(3),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
            'block_type' => MaintenanceBlock::TYPE_MAINTENANCE,
        ]);

        // Create booking on same dates
        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(2),
            'end_date' => Carbon::today()->addDays(3),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $calendar = $this->service->getAvailabilityCalendar(
            $this->hoarding->id,
            Carbon::today(),
            Carbon::today()->addDays(6)
        );

        // Days 2-3 should be marked as 'partial' (both block and booking)
        $day2 = collect($calendar['calendar'])->firstWhere('date', Carbon::today()->addDays(2)->format('Y-m-d'));
        $this->assertEquals('partial', $day2['status']);
    }

    /** @test */
    public function it_ignores_completed_and_cancelled_blocks()
    {
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Completed Maintenance',
            'start_date' => Carbon::today()->addDays(1),
            'end_date' => Carbon::today()->addDays(2),
            'status' => MaintenanceBlock::STATUS_COMPLETED,
            'block_type' => MaintenanceBlock::TYPE_MAINTENANCE,
        ]);

        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Cancelled Maintenance',
            'start_date' => Carbon::today()->addDays(3),
            'end_date' => Carbon::today()->addDays(4),
            'status' => MaintenanceBlock::STATUS_CANCELLED,
            'block_type' => MaintenanceBlock::TYPE_MAINTENANCE,
        ]);

        $calendar = $this->service->getAvailabilityCalendar(
            $this->hoarding->id,
            Carbon::today(),
            Carbon::today()->addDays(6)
        );

        foreach ($calendar['calendar'] as $day) {
            $this->assertEquals('available', $day['status']);
        }
    }

    /** @test */
    public function it_includes_details_when_requested()
    {
        $booking = Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(2),
            'end_date' => Carbon::today()->addDays(3),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $calendar = $this->service->getAvailabilityCalendar(
            $this->hoarding->id,
            Carbon::today(),
            Carbon::today()->addDays(6),
            true // include details
        );

        $day2 = collect($calendar['calendar'])->firstWhere('date', Carbon::today()->addDays(2)->format('Y-m-d'));
        
        $this->assertArrayHasKey('details', $day2);
        $this->assertArrayHasKey('bookings', $day2['details']);
        $this->assertCount(1, $day2['details']['bookings']);
        $this->assertEquals($booking->id, $day2['details']['bookings'][0]['id']);
    }

    /** @test */
    public function it_gets_month_calendar_correctly()
    {
        $year = Carbon::today()->year;
        $month = Carbon::today()->month;

        $calendar = $this->service->getMonthCalendar($this->hoarding->id, $year, $month);

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        
        $this->assertCount($daysInMonth, $calendar['calendar']);
        $this->assertEquals($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01', $calendar['start_date']);
    }

    /** @test */
    public function it_checks_multiple_dates_in_batch()
    {
        $dates = [
            Carbon::today()->addDays(1)->format('Y-m-d'),
            Carbon::today()->addDays(3)->format('Y-m-d'),
            Carbon::today()->addDays(5)->format('Y-m-d'),
        ];

        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(3),
            'end_date' => Carbon::today()->addDays(3),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $results = $this->service->checkMultipleDates($this->hoarding->id, $dates);

        $this->assertCount(3, $results);
        $this->assertEquals('available', $results[0]['status']);
        $this->assertEquals('booked', $results[1]['status']);
        $this->assertEquals('available', $results[2]['status']);
    }

    /** @test */
    public function it_finds_next_available_dates()
    {
        // Block days 2-4
        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(2),
            'end_date' => Carbon::today()->addDays(4),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $result = $this->service->getNextAvailableDates($this->hoarding->id, 5);

        $this->assertEquals(5, $result['found_count']);
        
        // Should find days: 0, 1, 5, 6, 7
        $dates = collect($result['dates'])->pluck('date')->toArray();
        $this->assertContains(Carbon::today()->format('Y-m-d'), $dates);
        $this->assertContains(Carbon::today()->addDays(1)->format('Y-m-d'), $dates);
        $this->assertContains(Carbon::today()->addDays(5)->format('Y-m-d'), $dates);
    }

    /** @test */
    public function it_calculates_occupancy_rate_correctly()
    {
        // Book 3 out of 10 days
        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(2),
            'end_date' => Carbon::today()->addDays(4),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $calendar = $this->service->getAvailabilityCalendar(
            $this->hoarding->id,
            Carbon::today(),
            Carbon::today()->addDays(9)
        );

        // 3 booked out of 10 = 30%
        $this->assertEquals(30.0, $calendar['summary']['occupancy_rate']);
    }

    /** @test */
    public function it_ignores_expired_holds()
    {
        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(2),
            'end_date' => Carbon::today()->addDays(3),
            'status' => Booking::STATUS_PENDING_PAYMENT_HOLD,
            'hold_expiry_at' => Carbon::now()->subHour(), // Expired
        ]);

        $calendar = $this->service->getAvailabilityCalendar(
            $this->hoarding->id,
            Carbon::today(),
            Carbon::today()->addDays(6)
        );

        $this->assertEquals(0, $calendar['summary']['hold_days']);
        $this->assertEquals(7, $calendar['summary']['available_days']);
    }

    /** @test */
    public function it_gets_availability_summary_without_full_calendar()
    {
        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'customer_id' => $this->customer->id,
            'start_date' => Carbon::today()->addDays(2),
            'end_date' => Carbon::today()->addDays(4),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $summary = $this->service->getAvailabilitySummary(
            $this->hoarding->id,
            Carbon::today(),
            Carbon::today()->addDays(6)
        );

        $this->assertArrayHasKey('available_days', $summary);
        $this->assertArrayHasKey('booked_days', $summary);
        $this->assertArrayHasKey('occupancy_rate', $summary);
        
        $this->assertEquals(4, $summary['available_days']);
        $this->assertEquals(3, $summary['booked_days']);
    }
}
