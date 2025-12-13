<?php

namespace Tests\Feature;

use App\Services\OfferExpiryService;
use Modules\Enquiries\Models\Enquiry;
use Modules\Offers\Models\Offer;
use App\Models\Hoarding;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PROMPT 105: Offer Model Expiry Tests
 * Tests for expiry-related model methods and scopes
 */
class OfferModelExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendor;
    protected User $customer;
    protected Hoarding $hoarding;
    protected Enquiry $enquiry;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->vendor = User::factory()->create(['role' => 'vendor']);
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->hoarding = Hoarding::factory()->create(['vendor_id' => $this->vendor->id]);
        $this->enquiry = Enquiry::factory()->create([
            'customer_id' => $this->customer->id,
            'hoarding_id' => $this->hoarding->id,
        ]);
    }

    /** @test */
    public function it_detects_expired_offer_by_status()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_EXPIRED,
        ]);

        $this->assertTrue($offer->isExpired());
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

        $this->assertTrue($offer->isExpired());
    }

    /** @test */
    public function it_detects_non_expired_offer()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        $this->assertFalse($offer->isExpired());
    }

    /** @test */
    public function expired_offer_cannot_be_accepted()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $this->assertFalse($offer->canAccept());
    }

    /** @test */
    public function non_expired_sent_offer_can_be_accepted()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        $this->assertTrue($offer->canAccept());
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

        $this->assertEquals(5, $offer->getDaysRemaining());
    }

    /** @test */
    public function it_returns_zero_days_for_expired()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $this->assertEquals(0, $offer->getDaysRemaining());
    }

    /** @test */
    public function it_returns_null_days_when_no_expiry()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => null,
            'valid_until' => null,
        ]);

        $this->assertNull($offer->getDaysRemaining());
    }

    /** @test */
    public function it_generates_expiry_label_for_expiring_today()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addHours(5),
        ]);

        $this->assertEquals('Expires today', $offer->getExpiryLabel());
    }

    /** @test */
    public function it_generates_expiry_label_for_tomorrow()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::tomorrow(),
        ]);

        $this->assertEquals('Expires tomorrow', $offer->getExpiryLabel());
    }

    /** @test */
    public function it_generates_expiry_label_for_multiple_days()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        $this->assertEquals('Expires in 5 days', $offer->getExpiryLabel());
    }

    /** @test */
    public function it_generates_expiry_label_for_expired()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $this->assertEquals('Expired', $offer->getExpiryLabel());
    }

    /** @test */
    public function active_scope_returns_non_expired_sent_offers()
    {
        // Sent and not expired
        Offer::factory()->count(3)->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        // Sent but expired
        Offer::factory()->count(2)->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        // Draft
        Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_DRAFT,
        ]);

        $activeOffers = Offer::active()->get();

        $this->assertCount(3, $activeOffers);
    }

    /** @test */
    public function expired_scope_returns_expired_offers()
    {
        Offer::factory()->count(4)->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_EXPIRED,
        ]);

        Offer::factory()->count(3)->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
        ]);

        $expiredOffers = Offer::expired()->get();

        $this->assertCount(4, $expiredOffers);
    }

    /** @test */
    public function due_to_expire_scope_returns_sent_past_expiry()
    {
        // Due to expire
        Offer::factory()->count(3)->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        // Not due
        Offer::factory()->count(2)->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        // Already expired status
        Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_EXPIRED,
        ]);

        $dueOffers = Offer::dueToExpire()->get();

        $this->assertCount(3, $dueOffers);
    }

    /** @test */
    public function expiring_soon_scope_returns_offers_within_threshold()
    {
        // Expiring in 2 days
        Offer::factory()->count(2)->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(2),
        ]);

        // Expiring in 3 days
        Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(3),
        ]);

        // Expiring in 5 days (outside threshold)
        Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        $expiringSoon = Offer::expiringSoon(3)->get(); // Within 3 days

        $this->assertCount(3, $expiringSoon); // 2 + 1
    }

    /** @test */
    public function it_handles_backward_compatibility_with_valid_until()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => null,
            'valid_until' => Carbon::now()->subDay(),
        ]);

        $this->assertTrue($offer->isExpired());
    }

    /** @test */
    public function it_prioritizes_expires_at_over_valid_until()
    {
        $offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5), // Not expired
            'valid_until' => Carbon::now()->subDay(),  // Expired (ignored)
        ]);

        $this->assertFalse($offer->isExpired());
    }
}
