<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User,Offer, Booking, BookingPayment, CommissionLog, QuoteRequest, AdminOverride};      
use App\Services\AdminOverrideService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;

/**
 * PROMPT 100: Admin Override System Tests
 * 
 * Tests admin override capabilities for bookings, payments, offers, quotes, 
 * commission with audit logging and revert functionality.
 */
class AdminOverrideTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $superAdmin;
    protected User $admin;
    protected User $vendor;
    protected AdminOverrideService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'vendor', 'guard_name' => 'web']);
        Role::create(['name' => 'customer', 'guard_name' => 'web']);

        // Create users with roles
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->vendor = User::factory()->create();
        $this->vendor->assignRole('vendor');

        $this->service = app(AdminOverrideService::class);
    }

    /** @test */
    public function super_admin_can_override_booking()
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'total_amount' => 10000,
        ]);

        $newData = [
            'status' => 'cancelled',
            'total_amount' => 12000,
        ];

        $override = $this->service->overrideBooking(
            booking: $booking,
            data: $newData,
            admin: $this->superAdmin,
            reason: 'Customer requested cancellation with price adjustment'
        );

        $this->assertInstanceOf(AdminOverride::class, $override);
        $this->assertEquals('booking', $override->override_type);
        $this->assertEquals($this->superAdmin->id, $override->user_id);
        $this->assertEquals('confirmed', $override->original_data['status']);
        $this->assertEquals('cancelled', $override->new_data['status']);
        
        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals(12000, $booking->total_amount);
    }

    /** @test */
    public function admin_can_override_booking()
    {
        $booking = Booking::factory()->create(['status' => 'confirmed']);

        $override = $this->service->overrideBooking(
            booking: $booking,
            data: ['status' => 'cancelled'],
            admin: $this->admin,
            reason: 'Admin requested cancellation'
        );

        $this->assertInstanceOf(AdminOverride::class, $override);
        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
    }

    /** @test */
    public function override_severity_is_determined_correctly()
    {
        $booking = Booking::factory()->create();

        // Critical change - payment status
        $override1 = $this->service->overrideBooking(
            booking: $booking,
            data: ['payment_status' => 'paid'],
            admin: $this->superAdmin,
            reason: 'Critical payment status change'
        );
        $this->assertEquals('critical', $override1->severity);

        // Medium change - start date
        $override2 = $this->service->overrideBooking(
            booking: $booking,
            data: ['start_date' => now()->addDays(7)],
            admin: $this->superAdmin,
            reason: 'Medium priority date change'
        );
        $this->assertEquals('medium', $override2->severity);
    }

    /** @test */
    public function override_tracks_all_changes()
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'total_amount' => 10000,
        ]);

        $override = $this->service->overrideBooking(
            booking: $booking,
            data: [
                'status' => 'cancelled',
                'total_amount' => 12000,
            ],
            admin: $this->superAdmin,
            reason: 'Multiple field changes'
        );

        $this->assertCount(2, $override->changes);
        $this->assertEquals('confirmed', $override->changes['status']['old']);
        $this->assertEquals('cancelled', $override->changes['status']['new']);
        $this->assertEquals(10000, $override->changes['total_amount']['old']);
        $this->assertEquals(12000, $override->changes['total_amount']['new']);
    }

    /** @test */
    public function can_override_payment_details()
    {
        $payment = BookingPayment::factory()->create([
            'vendor_payout_status' => 'pending',
            'vendor_payout_amount' => 8500,
        ]);

        $override = $this->service->overridePayment(
            payment: $payment,
            data: [
                'vendor_payout_status' => 'completed',
                'vendor_payout_amount' => 9000,
            ],
            admin: $this->superAdmin,
            reason: 'Correcting payout amount after recalculation'
        );

        $this->assertEquals('payment', $override->override_type);
        $this->assertEquals('critical', $override->severity);
        
        $payment->refresh();
        $this->assertEquals('completed', $payment->vendor_payout_status);
        $this->assertEquals(9000, $payment->vendor_payout_amount);
    }

    /** @test */
    public function can_override_commission_log()
    {
        $commission = CommissionLog::factory()->create([
            'admin_commission' => 1500,
            'vendor_payout' => 8500,
        ]);

        $override = $this->service->overrideCommission(
            commission: $commission,
            data: [
                'admin_commission' => 1200,
                'vendor_payout' => 8800,
            ],
            admin: $this->superAdmin,
            reason: 'Correcting commission calculation error'
        );

        $this->assertEquals('commission', $override->override_type);
        $this->assertEquals('critical', $override->severity);
        $this->assertArrayHasKey('warning', $override->metadata);
    }

    /** @test */
    public function can_override_quote_request()
    {
        $quote = QuoteRequest::factory()->create([
            'status' => 'pending',
        ]);

        $override = $this->service->overrideQuote(
            quote: $quote,
            data: ['status' => 'accepted'],
            admin: $this->admin,
            reason: 'Manual quote acceptance'
        );

        $this->assertEquals('quote', $override->override_type);
        $quote->refresh();
        $this->assertEquals('accepted', $quote->status);
    }

    /** @test */
    public function can_revert_override()
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'total_amount' => 10000,
        ]);

        // Create override
        $override = $this->service->overrideBooking(
            booking: $booking,
            data: ['status' => 'cancelled', 'total_amount' => 12000],
            admin: $this->admin,
            reason: 'Initial override'
        );

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);

        // Revert override
        $result = $this->service->revertOverride(
            override: $override,
            admin: $this->superAdmin,
            reason: 'Reverting incorrect override'
        );

        $this->assertTrue($result);
        
        $override->refresh();
        $this->assertTrue($override->is_reverted);
        $this->assertEquals($this->superAdmin->id, $override->reverted_by);
        $this->assertNotNull($override->reverted_at);

        $booking->refresh();
        $this->assertEquals('confirmed', $booking->status);
        $this->assertEquals(10000, $booking->total_amount);
    }

    /** @test */
    public function cannot_revert_already_reverted_override()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This override cannot be reverted');

        $booking = Booking::factory()->create();
        
        $override = $this->service->overrideBooking(
            booking: $booking,
            data: ['status' => 'cancelled'],
            admin: $this->admin,
            reason: 'First override'
        );

        // First revert
        $this->service->revertOverride($override, $this->superAdmin, 'First revert');

        // Attempt second revert
        $override->refresh();
        $this->service->revertOverride($override, $this->superAdmin, 'Second revert attempt');
    }

    /** @test */
    public function can_get_override_history_for_model()
    {
        $booking = Booking::factory()->create();

        // Create multiple overrides
        $this->service->overrideBooking(
            booking: $booking,
            data: ['status' => 'cancelled'],
            admin: $this->admin,
            reason: 'First override'
        );

        $this->service->overrideBooking(
            booking: $booking,
            data: ['status' => 'confirmed'],
            admin: $this->superAdmin,
            reason: 'Second override'
        );

        $history = $this->service->getOverrideHistory($booking);

        $this->assertCount(2, $history);
        $this->assertEquals('Second override', $history->first()->reason);
        $this->assertEquals('First override', $history->last()->reason);
    }

    /** @test */
    public function override_captures_request_context()
    {
        $booking = Booking::factory()->create();

        $override = $this->service->overrideBooking(
            booking: $booking,
            data: ['status' => 'cancelled'],
            admin: $this->superAdmin,
            reason: 'Testing context capture'
        );

        $this->assertNotNull($override->ip_address);
        $this->assertNotNull($override->user_agent);
        $this->assertEquals($this->superAdmin->name, $override->user_name);
        $this->assertEquals($this->superAdmin->email, $override->user_email);
    }

    /** @test */
    public function can_get_override_statistics()
    {
        $booking1 = Booking::factory()->create();
        $booking2 = Booking::factory()->create();

        // Create overrides with different severities
        $override1 = $this->service->overrideBooking(
            booking: $booking1,
            data: ['payment_status' => 'paid'],
            admin: $this->superAdmin,
            reason: 'Critical override'
        );

        $override2 = $this->service->overrideBooking(
            booking: $booking2,
            data: ['start_date' => now()->addDays(5)],
            admin: $this->admin,
            reason: 'Medium override'
        );

        // Revert one override
        $this->service->revertOverride($override1, $this->superAdmin, 'Revert test');

        $stats = $this->service->getStatistics();

        $this->assertEquals(2, $stats['total_overrides']);
        $this->assertEquals(1, $stats['total_reverted']);
        $this->assertEquals(50, $stats['revert_rate']);
        $this->assertEquals(1, $stats['critical_count']);
        $this->assertEquals(1, $stats['medium_count']);
    }

    /** @test */
    public function override_model_scopes_work_correctly()
    {
        $booking = Booking::factory()->create();

        $override1 = $this->service->overrideBooking(
            booking: $booking,
            data: ['status' => 'cancelled'],
            admin: $this->superAdmin,
            reason: 'First'
        );

        $override2 = $this->service->overrideBooking(
            booking: $booking,
            data: ['status' => 'confirmed'],
            admin: $this->admin,
            reason: 'Second'
        );

        $this->service->revertOverride($override1, $this->superAdmin, 'Revert');

        // Test scopes
        $this->assertCount(2, AdminOverride::ofType('booking')->get());
        $this->assertCount(1, AdminOverride::byUser($this->superAdmin->id)->get());
        $this->assertCount(1, AdminOverride::reverted()->get());
        $this->assertCount(1, AdminOverride::notReverted()->get());
        $this->assertCount(2, AdminOverride::recent()->get());
    }

    /** @test */
    public function formatted_changes_attribute_works()
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'total_amount' => 10000,
        ]);

        $override = $this->service->overrideBooking(
            booking: $booking,
            data: [
                'status' => 'cancelled',
                'total_amount' => 12000,
            ],
            admin: $this->superAdmin,
            reason: 'Test'
        );

        $formatted = $override->formatted_changes;

        $this->assertIsArray($formatted);
        $this->assertCount(2, $formatted);
        $this->assertEquals('Status', $formatted[0]['field']);
        $this->assertEquals('confirmed', $formatted[0]['old']);
        $this->assertEquals('cancelled', $formatted[0]['new']);
    }

    /** @test */
    public function summary_attribute_provides_readable_description()
    {
        $booking = Booking::factory()->create();

        $override = $this->service->overrideBooking(
            booking: $booking,
            data: ['status' => 'cancelled'],
            admin: $this->superAdmin,
            reason: 'Test'
        );

        $summary = $override->summary;

        $this->assertStringContainsString('Booking', $summary);
        $this->assertStringContainsString($this->superAdmin->name, $summary);
        $this->assertStringContainsString((string)$booking->id, $summary);
    }
}
