<?php

namespace Tests\Feature;

use App\Models\MaintenanceBlock;
use App\Models\Hoarding;
use App\Models\Booking;
use App\Models\User;
use App\Services\MaintenanceBlockService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PROMPT 102: Maintenance Blocks Feature Tests
 */
class MaintenanceBlockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MaintenanceBlockService $service;
    protected User $admin;
    protected User $vendor;
    protected Hoarding $hoarding;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        
        $this->service = app(MaintenanceBlockService::class);
        
        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('admin');
        
        $this->vendor = User::factory()->create(['status' => 'active']);
        $this->vendor->assignRole('vendor');
        
        $this->hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => Hoarding::STATUS_ACTIVE,
        ]);
    }

    /** @test */
    public function it_creates_maintenance_block_successfully()
    {
        $data = [
            'hoarding_id' => $this->hoarding->id,
            'title' => 'Annual Maintenance',
            'description' => 'Routine maintenance work',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'block_type' => MaintenanceBlock::TYPE_MAINTENANCE,
        ];

        $block = $this->service->create($data, $this->admin->id);

        $this->assertInstanceOf(MaintenanceBlock::class, $block);
        $this->assertEquals('Annual Maintenance', $block->title);
        $this->assertEquals(MaintenanceBlock::STATUS_ACTIVE, $block->status);
        $this->assertEquals($this->admin->id, $block->created_by);
    }

    /** @test */
    public function it_prevents_overlapping_blocks()
    {
        // Create first block
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'First Block',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        // Try to create overlapping block
        $data = [
            'hoarding_id' => $this->hoarding->id,
            'title' => 'Overlapping Block',
            'start_date' => Carbon::today()->addDays(7),
            'end_date' => Carbon::today()->addDays(12),
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->service->create($data, $this->admin->id);
    }

    /** @test */
    public function it_allows_non_overlapping_blocks()
    {
        // Create first block
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'First Block',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        // Create non-overlapping block
        $data = [
            'hoarding_id' => $this->hoarding->id,
            'title' => 'Second Block',
            'start_date' => Carbon::today()->addDays(15),
            'end_date' => Carbon::today()->addDays(20),
        ];

        $block = $this->service->create($data, $this->admin->id);

        $this->assertInstanceOf(MaintenanceBlock::class, $block);
    }

    /** @test */
    public function it_updates_block_successfully()
    {
        $block = MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Original Title',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        $updated = $this->service->update($block, [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

        $this->assertEquals('Updated Title', $updated->title);
        $this->assertEquals('Updated description', $updated->description);
    }

    /** @test */
    public function it_marks_block_as_completed()
    {
        $block = MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Test Block',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        $updated = $this->service->markCompleted($block);

        $this->assertEquals(MaintenanceBlock::STATUS_COMPLETED, $updated->status);
    }

    /** @test */
    public function it_marks_block_as_cancelled()
    {
        $block = MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Test Block',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        $updated = $this->service->markCancelled($block);

        $this->assertEquals(MaintenanceBlock::STATUS_CANCELLED, $updated->status);
    }

    /** @test */
    public function it_checks_availability_correctly()
    {
        // No blocks - should be available
        $result = $this->service->checkAvailability(
            $this->hoarding->id,
            Carbon::today()->addDays(5),
            Carbon::today()->addDays(10)
        );

        $this->assertTrue($result['available']);

        // Create block
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Test Block',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        // Should not be available
        $result = $this->service->checkAvailability(
            $this->hoarding->id,
            Carbon::today()->addDays(7),
            Carbon::today()->addDays(12)
        );

        $this->assertFalse($result['available']);
        $this->assertCount(1, $result['blocks']);
    }

    /** @test */
    public function it_gets_blocked_dates_for_calendar()
    {
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Maintenance',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(7),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        $blockedDates = $this->service->getBlockedDates(
            $this->hoarding->id,
            Carbon::today(),
            Carbon::today()->addDays(30)
        );

        $this->assertCount(3, $blockedDates); // 3 days blocked
        $this->assertArrayHasKey('date', $blockedDates[0]);
        $this->assertArrayHasKey('blocks', $blockedDates[0]);
    }

    /** @test */
    public function it_gets_statistics_correctly()
    {
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Active Block',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
            'block_type' => MaintenanceBlock::TYPE_MAINTENANCE,
        ]);

        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Completed Block',
            'start_date' => Carbon::today()->subDays(10),
            'end_date' => Carbon::today()->subDays(5),
            'status' => MaintenanceBlock::STATUS_COMPLETED,
            'block_type' => MaintenanceBlock::TYPE_REPAIR,
        ]);

        $stats = $this->service->getStatistics($this->hoarding->id);

        $this->assertEquals(2, $stats['total_blocks']);
        $this->assertEquals(1, $stats['active_blocks']);
        $this->assertEquals(1, $stats['completed_blocks']);
        $this->assertEquals(1, $stats['by_type']['maintenance']);
        $this->assertEquals(1, $stats['by_type']['repair']);
    }

    /** @test */
    public function it_detects_conflicting_bookings()
    {
        // Create a confirmed booking
        $booking = Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'vendor_id' => $this->vendor->id,
            'customer_id' => User::factory()->create()->id,
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $conflicts = $this->service->getConflictingBookings(
            $this->hoarding->id,
            Carbon::today()->addDays(7),
            Carbon::today()->addDays(12)
        );

        $this->assertCount(1, $conflicts);
        $this->assertEquals($booking->id, $conflicts->first()->id);
    }

    /** @test */
    public function it_creates_block_with_conflict_warning()
    {
        // Create a confirmed booking
        Booking::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'vendor_id' => $this->vendor->id,
            'customer_id' => User::factory()->create()->id,
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $data = [
            'hoarding_id' => $this->hoarding->id,
            'title' => 'Emergency Repair',
            'start_date' => Carbon::today()->addDays(7),
            'end_date' => Carbon::today()->addDays(12),
        ];

        // Without force - should fail
        $result = $this->service->createWithConflictCheck($data, $this->admin->id, false);
        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['warnings']);

        // With force - should succeed
        $result = $this->service->createWithConflictCheck($data, $this->admin->id, true);
        $this->assertTrue($result['success']);
        $this->assertInstanceOf(MaintenanceBlock::class, $result['block']);
        $this->assertNotEmpty($result['warnings']);
    }

    /** @test */
    public function model_scope_methods_work_correctly()
    {
        $hoarding2 = Hoarding::factory()->create(['vendor_id' => $this->vendor->id]);

        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Block 1',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        MaintenanceBlock::create([
            'hoarding_id' => $hoarding2->id,
            'created_by' => $this->admin->id,
            'title' => 'Block 2',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_COMPLETED,
        ]);

        // Test forHoarding scope
        $blocks = MaintenanceBlock::forHoarding($this->hoarding->id)->get();
        $this->assertCount(1, $blocks);

        // Test active scope
        $activeBlocks = MaintenanceBlock::active()->get();
        $this->assertCount(1, $activeBlocks);

        // Test overlapping scope
        $overlapping = MaintenanceBlock::overlapping(
            Carbon::today()->addDays(7),
            Carbon::today()->addDays(12)
        )->get();
        $this->assertCount(2, $overlapping);
    }
}
