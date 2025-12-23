<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\OfferExpiryService;
use Modules\Enquiries\Models\Enquiry;
use App\Models\Offer;
use App\Models\Hoarding;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PROMPT 105: Offer Auto-Expiry Service Tests
 * Tests for offer expiry logic and automation
 */
class OfferExpiryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OfferExpiryService $service;
    protected User $vendor;
    protected User $customer;
    protected Hoarding $hoarding;
    protected Enquiry $enquiry;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(OfferExpiryService::class);
        
        $this->vendor = User::factory()->create(['role' => 'vendor']);
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->hoarding = Hoarding::factory()->create(['vendor_id' => $this->vendor->id]);
        $this->enquiry = Enquiry::factory()->create([
            'customer_id' => $this->customer->id,
            'hoarding_id' => $this->hoarding->id,
        ]);
    }

    /** @test */
    public function it_gets_default_expiry_days_from_settings()
    {
        Setting::create([
            'key' => 'offer_default_expiry_days',
            'value' => '10',
            'type' => Setting::TYPE_INTEGER,
            'group' => 'general',
        ]);

        $days = $this->service->getDefaultExpiryDays();

        $this->assertEquals(10, $days);
    }

    /** @test */
    public function it_returns_default_when_no_setting_exists()
    {
        $days = $this->service->getDefaultExpiryDays();

        $this->assertEquals(7, $days); // OfferExpiryService::DEFAULT_EXPIRY_DAYS
    }

    /** @test */
    public function it_calculates_expiry_timestamp_for_sent_offer()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'sent_at' => Carbon::parse('2025-12-10 10:00:00'),
            'expiry_days' => 5,
        ]);

        $expiryTimestamp = $this->service->calculateExpiryTimestamp($offer);

        $this->assertNotNull($expiryTimestamp);
        $this->assertEquals('2025-12-15', $expiryTimestamp->format('Y-m-d'));
    }

    /** @test */
    public function it_returns_null_expiry_for_draft_offers()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_DRAFT,
        ]);

        $expiryTimestamp = $this->service->calculateExpiryTimestamp($offer);

        $this->assertNull($expiryTimestamp);
    }

    /** @test */
    public function it_sets_offer_expiry_when_sent()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
        ]);

        $this->service->setOfferExpiry($offer, 10);
        $offer->refresh();

        $this->assertNotNull($offer->sent_at);
        $this->assertEquals(10, $offer->expiry_days);
        $this->assertNotNull($offer->expires_at);
        $this->assertEquals(10, $offer->sent_at->diffInDays($offer->expires_at));
    }

    /** @test */
    public function it_uses_default_expiry_when_not_specified()
    {
        Setting::create([
            'key' => 'offer_default_expiry_days',
            'value' => '7',
            'type' => Setting::TYPE_INTEGER,
            'group' => 'general',
        ]);

        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
        ]);

        $this->service->setOfferExpiry($offer);
        $offer->refresh();

        $this->assertEquals(7, $offer->expiry_days);
    }

    /** @test */
    public function it_detects_expired_offer_by_expires_at()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $isExpired = $this->service->isOfferExpired($offer);

        $this->assertTrue($isExpired);
    }

    /** @test */
    public function it_detects_offer_not_expired()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        $isExpired = $this->service->isOfferExpired($offer);

        $this->assertFalse($isExpired);
    }

    /** @test */
    public function it_marks_offer_as_expired()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $result = $this->service->markOfferExpired($offer);
        $offer->refresh();

        $this->assertTrue($result);
        $this->assertEquals(Offer::STATUS_EXPIRED, $offer->status);
        $this->assertNotNull($offer->expired_at);
    }

    /** @test */
    public function it_does_not_mark_non_expired_offer()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        $result = $this->service->markOfferExpired($offer);

        $this->assertFalse($result);
        $this->assertEquals(Offer::STATUS_SENT, $offer->status);
    }

    /** @test */
    public function it_expires_all_due_offers()
    {
        // Create expired offers
        Offer::factory()->count(3)->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        // Create non-expired offer
        Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        $expiredCount = $this->service->expireAllDueOffers();

        $this->assertEquals(3, $expiredCount);
        $this->assertEquals(3, Offer::where('status', Offer::STATUS_EXPIRED)->count());
        $this->assertEquals(1, Offer::where('status', Offer::STATUS_SENT)->count());
    }

    /** @test */
    public function it_gets_offers_expiring_soon()
    {
        // Expiring in 2 days
        Offer::factory()->count(2)->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(2),
        ]);

        // Expiring in 5 days
        Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        $expiringSoon = $this->service->getOffersExpiringSoon(3); // Within 3 days

        $this->assertCount(2, $expiringSoon);
    }

    /** @test */
    public function it_gets_offers_expiring_today()
    {
        // Expiring today
        Offer::factory()->count(2)->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addHours(5),
        ]);

        // Expiring tomorrow
        Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::tomorrow(),
        ]);

        $expiringToday = $this->service->getOffersExpiringToday();

        $this->assertCount(2, $expiringToday);
    }

    /** @test */
    public function it_extends_offer_expiry()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'sent_at' => Carbon::now()->subDays(3),
            'expiry_days' => 7,
            'expires_at' => Carbon::now()->addDays(4),
        ]);

        $originalExpiry = $offer->expires_at->copy();

        $this->service->extendOfferExpiry($offer, 3);
        $offer->refresh();

        $this->assertEquals(
            $originalExpiry->addDays(3)->format('Y-m-d'),
            $offer->expires_at->format('Y-m-d')
        );
    }

    /** @test */
    public function it_resets_offer_expiry()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'sent_at' => Carbon::now()->subDays(5),
            'expiry_days' => 7,
            'expires_at' => Carbon::now()->addDays(2),
        ]);

        $this->service->resetOfferExpiry($offer, 10);
        $offer->refresh();

        $this->assertEquals(10, $offer->expiry_days);
        $this->assertTrue($offer->sent_at->isToday());
        $this->assertEquals(10, now()->diffInDays($offer->expires_at));
    }

    /** @test */
    public function it_validates_offer_acceptance_for_non_expired()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        [$canAccept, $reason] = $this->service->validateOfferAcceptance($offer);

        $this->assertTrue($canAccept);
        $this->assertNull($reason);
    }

    /** @test */
    public function it_rejects_acceptance_of_expired_offer()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        [$canAccept, $reason] = $this->service->validateOfferAcceptance($offer);

        $this->assertFalse($canAccept);
        $this->assertStringContainsString('expired', $reason);
    }

    /** @test */
    public function it_rejects_acceptance_of_draft_offer()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_DRAFT,
        ]);

        [$canAccept, $reason] = $this->service->validateOfferAcceptance($offer);

        $this->assertFalse($canAccept);
        $this->assertStringContainsString('not in \'sent\' status', $reason);
    }

    /** @test */
    public function it_calculates_days_remaining()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        $daysRemaining = $this->service->getDaysRemaining($offer);

        $this->assertEquals(5, $daysRemaining);
    }

    /** @test */
    public function it_returns_zero_days_for_expired_offer()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $daysRemaining = $this->service->getDaysRemaining($offer);

        $this->assertEquals(0, $daysRemaining);
    }

    /** @test */
    public function it_returns_null_days_when_no_expiry_set()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => null,
            'valid_until' => null,
        ]);

        $daysRemaining = $this->service->getDaysRemaining($offer);

        $this->assertNull($daysRemaining);
    }

    /** @test */
    public function it_gets_expiry_statistics()
    {
        // Create various offers
        Offer::factory()->count(5)->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(10),
        ]);

        Offer::factory()->count(3)->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_EXPIRED,
        ]);

        Offer::factory()->count(2)->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addHours(5), // Today
        ]);

        $stats = $this->service->getExpiryStatistics();

        $this->assertEquals(7, $stats['sent_offers']); // 5 + 2
        $this->assertEquals(3, $stats['expired_offers']);
        $this->assertEquals(2, $stats['expiring_today']);
        $this->assertArrayHasKey('default_expiry_days', $stats);
    }

    /** @test */
    public function it_throws_exception_when_extending_non_sent_offer()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_DRAFT,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only sent offers can be extended');

        $this->service->extendOfferExpiry($offer, 5);
    }

    /** @test */
    public function it_throws_exception_when_extending_with_invalid_days()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Additional days must be greater than 0');

        $this->service->extendOfferExpiry($offer, 0);
    }
}
