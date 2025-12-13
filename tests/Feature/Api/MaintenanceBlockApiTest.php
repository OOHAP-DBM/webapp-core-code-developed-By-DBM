<?php

namespace Tests\Feature\Api;

use App\Models\MaintenanceBlock;
use App\Models\Hoarding;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PROMPT 102: Maintenance Blocks API Tests
 */
class MaintenanceBlockApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $vendor;
    protected User $otherVendor;
    protected Hoarding $hoarding;
    protected string $baseUrl = '/api/v1/maintenance-blocks';

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        
        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('admin');
        
        $this->vendor = User::factory()->create(['status' => 'active']);
        $this->vendor->assignRole('vendor');
        
        $this->otherVendor = User::factory()->create(['status' => 'active']);
        $this->otherVendor->assignRole('vendor');
        
        $this->hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => Hoarding::STATUS_ACTIVE,
        ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson($this->baseUrl . '?hoarding_id=' . $this->hoarding->id);
        $response->assertStatus(401);
    }

    /** @test */
    public function admin_can_create_maintenance_block()
    {
        $data = [
            'hoarding_id' => $this->hoarding->id,
            'title' => 'Annual Maintenance',
            'description' => 'Routine cleaning and repairs',
            'start_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
            'end_date' => Carbon::today()->addDays(10)->format('Y-m-d'),
            'block_type' => 'maintenance',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson($this->baseUrl, $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'title', 'start_date', 'end_date', 'status'],
            ]);

        $this->assertDatabaseHas('maintenance_blocks', [
            'hoarding_id' => $this->hoarding->id,
            'title' => 'Annual Maintenance',
        ]);
    }

    /** @test */
    public function vendor_can_create_block_for_own_hoarding()
    {
        $data = [
            'hoarding_id' => $this->hoarding->id,
            'title' => 'Repair Work',
            'start_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
            'end_date' => Carbon::today()->addDays(10)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->postJson($this->baseUrl, $data);

        $response->assertStatus(201);
    }

    /** @test */
    public function vendor_cannot_create_block_for_other_vendor_hoarding()
    {
        $data = [
            'hoarding_id' => $this->hoarding->id,
            'title' => 'Unauthorized Block',
            'start_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
            'end_date' => Carbon::today()->addDays(10)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->otherVendor, 'sanctum')
            ->postJson($this->baseUrl, $data);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson($this->baseUrl, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['hoarding_id', 'title', 'start_date', 'end_date']);
    }

    /** @test */
    public function it_validates_date_logic()
    {
        $data = [
            'hoarding_id' => $this->hoarding->id,
            'title' => 'Invalid Dates',
            'start_date' => Carbon::today()->addDays(10)->format('Y-m-d'),
            'end_date' => Carbon::today()->addDays(5)->format('Y-m-d'), // End before start
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson($this->baseUrl, $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function it_lists_blocks_for_hoarding()
    {
        MaintenanceBlock::factory()->count(3)->create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson($this->baseUrl . '?hoarding_id=' . $this->hoarding->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'count',
            ])
            ->assertJsonPath('count', 3);
    }

    /** @test */
    public function it_filters_blocks_by_status()
    {
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Active',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(5),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Completed',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(5),
            'status' => MaintenanceBlock::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson($this->baseUrl . '?hoarding_id=' . $this->hoarding->id . '&status=active');

        $response->assertStatus(200)
            ->assertJsonPath('count', 1);
    }

    /** @test */
    public function it_updates_maintenance_block()
    {
        $block = MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Original Title',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson($this->baseUrl . '/' . $block->id, [
                'title' => 'Updated Title',
                'description' => 'New description',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('maintenance_blocks', [
            'id' => $block->id,
            'title' => 'Updated Title',
        ]);
    }

    /** @test */
    public function vendor_can_update_own_hoarding_block()
    {
        $block = MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->vendor->id,
            'title' => 'Original',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->putJson($this->baseUrl . '/' . $block->id, [
                'title' => 'Updated by Vendor',
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function vendor_cannot_update_other_vendor_block()
    {
        $block = MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->vendor->id,
            'title' => 'Original',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
        ]);

        $response = $this->actingAs($this->otherVendor, 'sanctum')
            ->putJson($this->baseUrl . '/' . $block->id, [
                'title' => 'Unauthorized Update',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_deletes_maintenance_block()
    {
        $block = MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'To Delete',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(10),
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson($this->baseUrl . '/' . $block->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('maintenance_blocks', ['id' => $block->id]);
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

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson($this->baseUrl . '/' . $block->id . '/complete');

        $response->assertStatus(200)
            ->assertJsonPath('data.status', MaintenanceBlock::STATUS_COMPLETED);
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

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson($this->baseUrl . '/' . $block->id . '/cancel');

        $response->assertStatus(200)
            ->assertJsonPath('data.status', MaintenanceBlock::STATUS_CANCELLED);
    }

    /** @test */
    public function it_checks_availability()
    {
        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson($this->baseUrl . '/check/availability?' . http_build_query([
                'hoarding_id' => $this->hoarding->id,
                'start_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
                'end_date' => Carbon::today()->addDays(10)->format('Y-m-d'),
            ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.available', true);

        // Create a block
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Block',
            'start_date' => Carbon::today()->addDays(7),
            'end_date' => Carbon::today()->addDays(12),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson($this->baseUrl . '/check/availability?' . http_build_query([
                'hoarding_id' => $this->hoarding->id,
                'start_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
                'end_date' => Carbon::today()->addDays(10)->format('Y-m-d'),
            ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.available', false);
    }

    /** @test */
    public function it_gets_blocked_dates_for_calendar()
    {
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Test Block',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(7),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson($this->baseUrl . '/check/blocked-dates?' . http_build_query([
                'hoarding_id' => $this->hoarding->id,
                'start_date' => Carbon::today()->format('Y-m-d'),
                'end_date' => Carbon::today()->addDays(30)->format('Y-m-d'),
            ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'count',
            ])
            ->assertJsonPath('count', 3); // 3 days blocked
    }

    /** @test */
    public function it_gets_statistics()
    {
        MaintenanceBlock::create([
            'hoarding_id' => $this->hoarding->id,
            'created_by' => $this->admin->id,
            'title' => 'Active',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(5),
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson($this->baseUrl . '/check/statistics?hoarding_id=' . $this->hoarding->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_blocks',
                    'active_blocks',
                    'by_type',
                ],
            ]);
    }
}
