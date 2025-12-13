<?php

namespace Tests\Feature;

use App\Models\MaintenanceBlock;
use App\Models\Hoarding;
use App\Models\User;
use App\Services\BookingOverlapValidator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PROMPT 102: Test integration between Maintenance Blocks and Overlap Validator
 */
class MaintenanceBlockOverlapIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected BookingOverlapValidator $validator;
    protected User $vendor;
    protected Hoarding $hoarding;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        
        $this->validator = app(BookingOverlapValidator::class);
        
        $this->vendor = User::factory()->create(['status' => 'active']);
        $this->vendor->assignRole('vendor');
        
        $this->hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => Hoarding::STATUS_ACTIVE,
        ]);
    }

    /** @test */
    public function overlap_validator_detects_maintenance_blocks()
    {
        // Create a maintenance block
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->vendor->id,
            'title' => 'Maintenance Work',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        // Check for overlap
        $result = $this->validator->validateAvailability(
            $this->hoarding->id,
            Carbon::today()->addDays(7),
            Carbon::today()->addDays(12),
            null,
            false // No grace period
        );

        $this->assertFalse($result['available']);
        $this->assertCount(1, $result['conflicts']);
        $this->assertEquals('maintenance_block', $result['conflicts']->first()['type']);
    }

    /** @test */
    public function overlap_validator_ignores_completed_blocks()
    {
        // Create a completed maintenance block
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->vendor->id,
            'title' => 'Completed Work',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_COMPLETED,
        ]);

        // Check for overlap
        $result = $this->validator->validateAvailability(
            $this->hoarding->id,
            Carbon::today()->addDays(7),
            Carbon::today()->addDays(12),
            null,
            false
        );

        $this->assertTrue($result['available']);
        $this->assertCount(0, $result['conflicts']);
    }

    /** @test */
    public function overlap_validator_ignores_cancelled_blocks()
    {
        // Create a cancelled maintenance block
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->vendor->id,
            'title' => 'Cancelled Work',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_CANCELLED,
        ]);

        // Check for overlap
        $result = $this->validator->validateAvailability(
            $this->hoarding->id,
            Carbon::today()->addDays(7),
            Carbon::today()->addDays(12),
            null,
            false
        );

        $this->assertTrue($result['available']);
    }

    /** @test */
    public function overlap_validator_builds_correct_conflict_message_for_blocks()
    {
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->vendor->id,
            'title' => 'Emergency Repair',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        $result = $this->validator->validateAvailability(
            $this->hoarding->id,
            Carbon::today()->addDays(7),
            Carbon::today()->addDays(12),
            null,
            false
        );

        $this->assertStringContainsString('maintenance', strtolower($result['message']));
    }

    /** @test */
    public function quick_availability_check_returns_false_with_blocks()
    {
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->vendor->id,
            'title' => 'Maintenance',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        $isAvailable = $this->validator->isAvailable(
            $this->hoarding->id,
            Carbon::today()->addDays(7),
            Carbon::today()->addDays(12)
        );

        $this->assertFalse($isAvailable);
    }

    /** @test */
    public function occupied_dates_includes_maintenance_blocks()
    {
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->vendor->id,
            'title' => 'Maintenance',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(7),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
            'block_type' => MaintenanceBlock::TYPE_MAINTENANCE,
        ]);

        $occupiedDates = $this->validator->getOccupiedDates(
            $this->hoarding->id,
            Carbon::today(),
            Carbon::today()->addDays(30)
        );

        $this->assertNotEmpty($occupiedDates);
        
        // Find a date that should be blocked
        $blockedDate = collect($occupiedDates)->first(function($item) {
            return isset($item['maintenance_blocks']) && !empty($item['maintenance_blocks']);
        });
        
        $this->assertNotNull($blockedDate);
    }

    /** @test */
    public function conflict_details_includes_maintenance_block_info()
    {
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->vendor->id,
            'title' => 'Painting Work',
            'description' => 'Repainting the billboard',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
            'block_type' => MaintenanceBlock::TYPE_MAINTENANCE,
        ]);

        $result = $this->validator->validateAvailability(
            $this->hoarding->id,
            Carbon::today()->addDays(7),
            Carbon::today()->addDays(12),
            null,
            false
        );

        $this->assertFalse($result['available']);
        $this->assertArrayHasKey('conflict_details', $result);
        
        $conflict = $result['conflicts']->first();
        $this->assertEquals('maintenance_block', $conflict['type']);
        $this->assertEquals('Painting Work', $conflict['title']);
        $this->assertEquals('maintenance', $conflict['block_type']);
    }
}
