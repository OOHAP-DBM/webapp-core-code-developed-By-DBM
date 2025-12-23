<?php

namespace Tests\Feature;

use App\Models\Quotation;
use App\Models\Booking;
use App\Models\User;
use App\Models\Hoarding;
use App\Services\QuotationExpiryService;
use App\Models\Offer;
use Modules\Enquiries\Models\Enquiry;
use Modules\Threads\Models\Thread;
use Modules\Threads\Models\ThreadMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * PROMPT 106: Quotation Expiry Service Tests
 * Tests for quotation deadline + auto-cancel functionality
 */
class QuotationExpiryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected QuotationExpiryService $service;
    protected User $vendor;
    protected User $customer;
    protected Hoarding $hoarding;
    protected Enquiry $enquiry;
    protected Offer $offer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(QuotationExpiryService::class);

        $this->vendor = User::factory()->create(['role' => 'vendor']);
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->hoarding = Hoarding::factory()->create(['vendor_id' => $this->vendor->id]);
        $this->enquiry = Enquiry::factory()->create([
            'customer_id' => $this->customer->id,
            'hoarding_id' => $this->hoarding->id,
        ]);
        $this->offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'sent_at' => Carbon::now()->subDays(10),
            'expires_at' => Carbon::now()->subDays(1), // Expired
        ]);
    }

    /** @test */
    public function it_detects_expired_quotation_based_on_offer_expiry()
    {
        $quotation = Quotation::factory()->create([
            'offer_id' => $this->offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        $this->assertTrue($this->service->isQuotationExpired($quotation));
    }

    /** @test */
    public function it_does_not_detect_expired_for_approved_quotation()
    {
        $quotation = Quotation::factory()->create([
            'offer_id' => $this->offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_APPROVED,
        ]);

        $this->assertFalse($this->service->isQuotationExpired($quotation));
    }

    /** @test */
    public function it_does_not_detect_expired_for_active_offer()
    {
        $activeOffer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5), // Not expired
        ]);

        $quotation = Quotation::factory()->create([
            'offer_id' => $activeOffer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        $this->assertFalse($this->service->isQuotationExpired($quotation));
    }

    /** @test */
    public function it_marks_quotation_as_expired()
    {
        $quotation = Quotation::factory()->create([
            'offer_id' => $this->offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
            'notes' => 'Original notes',
        ]);

        $result = $this->service->markQuotationExpired($quotation);

        $this->assertEquals(Quotation::STATUS_REJECTED, $result->status);
        $this->assertStringContainsString('AUTO-EXPIRED', $result->notes);
        $this->assertStringContainsString('Original notes', $result->notes);
    }

    /** @test */
    public function it_processes_expired_quotations()
    {
        Notification::fake();

        // Create 3 expired quotations
        for ($i = 0; $i < 3; $i++) {
            Quotation::factory()->create([
                'offer_id' => $this->offer->id,
                'customer_id' => $this->customer->id,
                'vendor_id' => $this->vendor->id,
                'status' => Quotation::STATUS_SENT,
            ]);
        }

        // Create 2 active quotations (should not be processed)
        $activeOffer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        for ($i = 0; $i < 2; $i++) {
            Quotation::factory()->create([
                'offer_id' => $activeOffer->id,
                'customer_id' => $this->customer->id,
                'vendor_id' => $this->vendor->id,
                'status' => Quotation::STATUS_SENT,
            ]);
        }

        $count = $this->service->processExpiredQuotations();

        $this->assertEquals(3, $count);
        $this->assertEquals(3, Quotation::where('status', Quotation::STATUS_REJECTED)->count());
        $this->assertEquals(2, Quotation::where('status', Quotation::STATUS_SENT)->count());
    }

    /** @test */
    public function it_auto_cancels_related_bookings()
    {
        \App\Models\Setting::setValue('quotation_auto_cancel_enabled', true);

        $quotation = Quotation::factory()->create([
            'offer_id' => $this->offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        // Create pending booking
        $booking = Booking::factory()->create([
            'quotation_id' => $quotation->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'hoarding_id' => $this->hoarding->id,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);

        Notification::fake();

        $this->service->processExpiredQuotations();

        $booking->refresh();

        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals('cancelled', $booking->payment_status);
        $this->assertStringContainsString('Quotation expired', $booking->cancellation_reason);
        $this->assertNotNull($booking->cancelled_at);
        $this->assertEquals('system', $booking->cancelled_by);
    }

    /** @test */
    public function it_does_not_cancel_bookings_when_disabled()
    {
        \App\Models\Setting::setValue('quotation_auto_cancel_enabled', false);

        $quotation = Quotation::factory()->create([
            'offer_id' => $this->offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        $booking = Booking::factory()->create([
            'quotation_id' => $quotation->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'hoarding_id' => $this->hoarding->id,
            'status' => 'pending_payment',
        ]);

        $this->service->processExpiredQuotations();

        $booking->refresh();

        $this->assertEquals('pending_payment', $booking->status);
    }

    /** @test */
    public function it_updates_thread_with_expiry_message()
    {
        $quotation = Quotation::factory()->create([
            'offer_id' => $this->offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        Notification::fake();

        $this->service->processExpiredQuotations();

        // Check thread message was created
        $thread = Thread::where('enquiry_id', $this->enquiry->id)->first();
        $this->assertNotNull($thread);

        $message = ThreadMessage::where('thread_id', $thread->id)
            ->where('message_type', ThreadMessage::TYPE_SYSTEM)
            ->where('quotation_id', $quotation->id)
            ->first();

        $this->assertNotNull($message);
        $this->assertStringContainsString('expired', strtolower($message->message));
        $this->assertStringContainsString('Quotation #' . $quotation->id, $message->message);
    }

    /** @test */
    public function it_gets_quotations_expiring_soon()
    {
        // Expiring in 2 days
        $expiringSoon1 = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(2),
        ]);

        Quotation::factory()->create([
            'offer_id' => $expiringSoon1->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        // Expiring in 5 days (outside 3-day threshold)
        $expiringLater = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        Quotation::factory()->create([
            'offer_id' => $expiringLater->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        $quotations = $this->service->getQuotationsExpiringSoon(3);

        $this->assertCount(1, $quotations);
        $this->assertEquals($expiringSoon1->id, $quotations->first()->offer_id);
    }

    /** @test */
    public function it_gets_quotations_expiring_today()
    {
        // Expiring today
        $expiringToday = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::today()->addHours(12),
        ]);

        Quotation::factory()->create([
            'offer_id' => $expiringToday->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        // Expiring tomorrow
        $expiringTomorrow = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::tomorrow(),
        ]);

        Quotation::factory()->create([
            'offer_id' => $expiringTomorrow->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        $quotations = $this->service->getQuotationsExpiringToday();

        $this->assertCount(1, $quotations);
        $this->assertEquals($expiringToday->id, $quotations->first()->offer_id);
    }

    /** @test */
    public function it_gets_expiry_statistics()
    {
        // Active quotations
        $activeOffer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        for ($i = 0; $i < 5; $i++) {
            Quotation::factory()->create([
                'offer_id' => $activeOffer->id,
                'customer_id' => $this->customer->id,
                'vendor_id' => $this->vendor->id,
                'status' => Quotation::STATUS_SENT,
            ]);
        }

        // Expired quotations
        for ($i = 0; $i < 3; $i++) {
            Quotation::factory()->create([
                'offer_id' => $this->offer->id,
                'customer_id' => $this->customer->id,
                'vendor_id' => $this->vendor->id,
                'status' => Quotation::STATUS_REJECTED,
                'notes' => 'AUTO-EXPIRED: ' . now(),
            ]);
        }

        $stats = $this->service->getExpiryStatistics();

        $this->assertEquals(5, $stats['total_active']);
        $this->assertEquals(3, $stats['total_expired']);
        $this->assertEquals(37.5, $stats['expiry_rate']); // 3/(5+3) * 100
    }

    /** @test */
    public function it_sends_expiry_notifications()
    {
        Notification::fake();

        $quotation = Quotation::factory()->create([
            'offer_id' => $this->offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        \App\Models\Setting::setValue('quotation_notify_on_expiry', true);

        $this->service->processExpiredQuotations();

        Notification::assertSentTo(
            $this->customer,
            \App\Notifications\QuotationExpiredNotification::class
        );

        Notification::assertSentTo(
            $this->vendor,
            \App\Notifications\QuotationExpiredNotification::class
        );
    }

    /** @test */
    public function it_does_not_send_notifications_when_disabled()
    {
        Notification::fake();

        $quotation = Quotation::factory()->create([
            'offer_id' => $this->offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        \App\Models\Setting::setValue('quotation_notify_on_expiry', false);

        $this->service->processExpiredQuotations();

        Notification::assertNothingSent();
    }

    /** @test */
    public function it_sends_booking_cancellation_notifications()
    {
        Notification::fake();

        $quotation = Quotation::factory()->create([
            'offer_id' => $this->offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        $booking = Booking::factory()->create([
            'quotation_id' => $quotation->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'hoarding_id' => $this->hoarding->id,
            'status' => 'pending_payment',
        ]);

        \App\Models\Setting::setValue('quotation_auto_cancel_enabled', true);

        $this->service->processExpiredQuotations();

        Notification::assertSentTo(
            $this->customer,
            \App\Notifications\QuotationBookingCancelledNotification::class
        );

        Notification::assertSentTo(
            $this->vendor,
            \App\Notifications\QuotationBookingCancelledNotification::class
        );
    }

    /** @test */
    public function it_gets_days_remaining()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        $quotation = Quotation::factory()->create([
            'offer_id' => $offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        $days = $this->service->getDaysRemaining($quotation);

        $this->assertEquals(5, $days);
    }

    /** @test */
    public function it_gets_expiry_label()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        $quotation = Quotation::factory()->create([
            'offer_id' => $offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        $label = $this->service->getExpiryLabel($quotation);

        $this->assertEquals('Expires in 5 days', $label);
    }

    /** @test */
    public function it_sends_expiry_warnings()
    {
        Notification::fake();

        \App\Models\Setting::setValue('quotation_expiry_warning_days', 2);

        // Expiring in 2 days
        $expiringSoon = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(2),
        ]);

        $quotation = Quotation::factory()->create([
            'offer_id' => $expiringSoon->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_SENT,
        ]);

        $count = $this->service->sendExpiryWarnings();

        $this->assertEquals(1, $count);

        Notification::assertSentTo(
            $this->customer,
            \App\Notifications\QuotationExpiryWarningNotification::class
        );

        Notification::assertSentTo(
            $this->vendor,
            \App\Notifications\QuotationExpiryWarningNotification::class
        );
    }
}
