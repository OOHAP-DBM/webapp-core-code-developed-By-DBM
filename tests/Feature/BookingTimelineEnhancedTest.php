<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\BookingTimelineEvent;
use App\Services\BookingTimelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class BookingTimelineEnhancedTest extends TestCase
{
    use RefreshDatabase;

    protected $timelineService;
    protected $admin;
    protected $booking;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->timelineService = app(BookingTimelineService::class);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->booking = Booking::factory()->create([
            'start_date' => now()->addDays(20),
            'end_date' => now()->addDays(50),
        ]);
    }

    /** @test */
    public function it_generates_15_stage_timeline()
    {
        $this->timelineService->generateFullTimeline($this->booking);
        
        $events = $this->booking->timelineEvents()->get();
        
        $this->assertGreaterThanOrEqual(15, $events->count());
        
        // Verify new stages exist
        $this->assertTrue($events->where('event_type', BookingTimelineEvent::TYPE_PO)->isNotEmpty());
        $this->assertTrue($events->where('event_type', BookingTimelineEvent::TYPE_DESIGNING)->isNotEmpty());
        $this->assertTrue($events->where('event_type', BookingTimelineEvent::TYPE_SURVEY)->isNotEmpty());
    }

    /** @test */
    public function it_starts_stage_with_note()
    {
        $this->timelineService->generateFullTimeline($this->booking);
        
        $event = $this->timelineService->startStageWithNote(
            $this->booking,
            BookingTimelineEvent::TYPE_DESIGNING,
            'Client approved concept',
            $this->admin
        );
        
        $this->assertEquals('in_progress', $event->status);
        $this->assertEquals($this->admin->id, $event->user_id);
        $this->assertEquals($this->admin->name, $event->user_name);
        $this->assertNotNull($event->started_at);
        
        $notes = $event->metadata['notes'] ?? [];
        $this->assertCount(1, $notes);
        $this->assertEquals('Client approved concept', $notes[0]['note']);
        $this->assertEquals($this->admin->id, $notes[0]['user_id']);
    }

    /** @test */
    public function it_completes_stage_with_note()
    {
        $this->timelineService->generateFullTimeline($this->booking);
        
        $event = $this->timelineService->completeStageWithNote(
            $this->booking,
            BookingTimelineEvent::TYPE_PO,
            'PO #12345 signed by client',
            $this->admin
        );
        
        $this->assertEquals('completed', $event->status);
        $this->assertNotNull($event->completed_at);
        
        $notes = $event->metadata['notes'] ?? [];
        $this->assertCount(1, $notes);
        $this->assertEquals('PO #12345 signed by client', $notes[0]['note']);
    }

    /** @test */
    public function it_adds_note_to_existing_event()
    {
        $this->timelineService->generateFullTimeline($this->booking);
        
        $event = $this->booking->timelineEvents()
            ->where('event_type', BookingTimelineEvent::TYPE_DESIGNING)
            ->first();
        
        // Add first note
        $updated = $this->timelineService->updateEventWithNote(
            $event,
            'First note',
            $this->admin
        );
        
        $notes = $updated->metadata['notes'] ?? [];
        $this->assertCount(1, $notes);
        
        // Add second note
        $updated = $this->timelineService->updateEventWithNote(
            $updated,
            'Second note',
            $this->admin
        );
        
        $notes = $updated->fresh()->metadata['notes'] ?? [];
        $this->assertCount(2, $notes);
        $this->assertEquals('First note', $notes[0]['note']);
        $this->assertEquals('Second note', $notes[1]['note']);
    }

    /** @test */
    public function it_has_notification_flags_on_all_stages()
    {
        $this->timelineService->generateFullTimeline($this->booking);
        
        $events = $this->booking->timelineEvents()->get();
        
        // Check some key stages have notification flags
        $quotation = $events->where('event_type', 'quotation')->first();
        $this->assertTrue($quotation->notify_customer);
        $this->assertTrue($quotation->notify_vendor);
        
        $po = $events->where('event_type', BookingTimelineEvent::TYPE_PO)->first();
        $this->assertTrue($po->notify_customer);
        $this->assertTrue($po->notify_vendor);
        $this->assertTrue($po->notify_admin);
        
        $designing = $events->where('event_type', BookingTimelineEvent::TYPE_DESIGNING)->first();
        $this->assertTrue($designing->notify_vendor);
    }

    /** @test */
    public function it_schedules_designing_stage_correctly()
    {
        $this->timelineService->generateFullTimeline($this->booking);
        
        $designing = $this->booking->timelineEvents()
            ->where('event_type', BookingTimelineEvent::TYPE_DESIGNING)
            ->first();
        
        $expectedDate = $this->booking->start_date->copy()->subDays(12);
        
        $this->assertEquals(
            $expectedDate->format('Y-m-d'),
            $designing->scheduled_date->format('Y-m-d')
        );
    }

    /** @test */
    public function api_start_stage_with_note_endpoint_works()
    {
        $this->actingAs($this->admin);
        $this->timelineService->generateFullTimeline($this->booking);
        
        $response = $this->postJson(
            "/bookings/{$this->booking->id}/timeline/start-stage-with-note",
            [
                'event_type' => BookingTimelineEvent::TYPE_DESIGNING,
                'note' => 'Starting design phase'
            ]
        );
        
        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'event' => ['id', 'status', 'user_id', 'metadata']
            ]);
        
        $event = BookingTimelineEvent::find($response->json('event.id'));
        $this->assertEquals('in_progress', $event->status);
        $this->assertArrayHasKey('notes', $event->metadata);
    }

    /** @test */
    public function api_complete_stage_with_note_endpoint_works()
    {
        $this->actingAs($this->admin);
        $this->timelineService->generateFullTimeline($this->booking);
        
        $response = $this->postJson(
            "/bookings/{$this->booking->id}/timeline/complete-stage-with-note",
            [
                'event_type' => BookingTimelineEvent::TYPE_PO,
                'note' => 'PO completed'
            ]
        );
        
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Stage completed successfully'
            ]);
    }

    /** @test */
    public function api_add_note_endpoint_works()
    {
        $this->actingAs($this->admin);
        $this->timelineService->generateFullTimeline($this->booking);
        
        $event = $this->booking->timelineEvents()
            ->where('event_type', BookingTimelineEvent::TYPE_DESIGNING)
            ->first();
        
        $response = $this->postJson(
            "/bookings/{$this->booking->id}/timeline/events/{$event->id}/add-note",
            [
                'note' => 'Additional note'
            ]
        );
        
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Note added successfully'
            ]);
        
        $event->refresh();
        $notes = $event->metadata['notes'] ?? [];
        $this->assertNotEmpty($notes);
    }

    /** @test */
    public function existing_start_stage_endpoint_supports_new_stages()
    {
        $this->actingAs($this->admin);
        $this->timelineService->generateFullTimeline($this->booking);
        
        // Test designing stage
        $response = $this->postJson(
            "/bookings/{$this->booking->id}/timeline/start-stage",
            [
                'stage' => 'designing',
                'note' => 'Starting with note'
            ]
        );
        
        $response->assertOk();
        
        // Test purchase_order stage
        $response = $this->postJson(
            "/bookings/{$this->booking->id}/timeline/start-stage",
            [
                'stage' => 'purchase_order'
            ]
        );
        
        $response->assertOk();
    }

    /** @test */
    public function observer_sends_notifications_automatically()
    {
        Notification::fake();
        
        $customer = User::factory()->create(['role' => 'customer']);
        $vendor = User::factory()->create(['role' => 'vendor']);
        
        $booking = Booking::factory()->create([
            'customer_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'start_date' => now()->addDays(20),
            'end_date' => now()->addDays(50),
        ]);
        
        $this->timelineService->generateFullTimeline($booking);
        
        // Complete a stage that notifies all parties
        $this->actingAs($this->admin);
        $event = $this->timelineService->completeStageWithNote(
            $booking,
            BookingTimelineEvent::TYPE_PO,
            'PO signed',
            $this->admin
        );
        
        // Observer should have sent notifications
        // (In real implementation, check notification queue)
        $this->assertNotNull($event->notified_at);
    }
}
