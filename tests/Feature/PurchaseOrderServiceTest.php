<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Enquiry;
use App\Notifications\PurchaseOrderGeneratedNotification;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Modules\Offers\Models\Offer;
use Modules\Quotations\Events\QuotationApproved;
use Modules\Threads\Models\Thread;
use Modules\Threads\Models\ThreadMessage;
use Tests\TestCase;

/**
 * PROMPT 107: Purchase Order Auto-Generation Tests
 * 
 * Tests PO generation from approved quotations
 */
class PurchaseOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PurchaseOrderService $service;
    protected User $customer;
    protected User $vendor;
    protected Enquiry $enquiry;
    protected Offer $offer;
    protected Quotation $quotation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PurchaseOrderService::class);

        // Create test users
        $this->customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'John Doe',
            'email' => 'customer@test.com',
        ]);

        $this->vendor = User::factory()->create([
            'role' => 'vendor',
            'name' => 'ABC Vendors',
            'email' => 'vendor@test.com',
        ]);

        // Create hoarding
        $hoarding = \App\Models\Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
        ]);

        // Create enquiry
        $this->enquiry = Enquiry::factory()->create([
            'customer_id' => $this->customer->id,
            'hoarding_id' => $hoarding->id,
        ]);

        // Create offer
        $this->offer = Offer::factory()->create([
            'enquiry_id' => $this->enquiry->id,
            'vendor_id' => $this->vendor->id,
            'status' => Offer::STATUS_ACCEPTED,
        ]);

        // Create approved quotation
        $this->quotation = Quotation::factory()->create([
            'offer_id' => $this->offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_APPROVED,
            'approved_at' => now(),
            'items' => [
                ['description' => 'Hoarding Installation', 'quantity' => 1, 'rate' => 50000],
                ['description' => 'Mounting Service', 'quantity' => 1, 'rate' => 10000],
            ],
            'total_amount' => 60000,
            'tax' => 10800,
            'discount' => 0,
            'grand_total' => 70800,
            'payment_mode' => 'full',
        ]);

        Storage::fake('private');
    }

    /** @test */
    public function it_generates_po_from_approved_quotation()
    {
        $po = $this->service->generateFromQuotation($this->quotation);

        $this->assertInstanceOf(PurchaseOrder::class, $po);
        $this->assertNotNull($po->po_number);
        $this->assertEquals($this->quotation->id, $po->quotation_id);
        $this->assertEquals($this->customer->id, $po->customer_id);
        $this->assertEquals($this->vendor->id, $po->vendor_id);
        $this->assertEquals($this->enquiry->id, $po->enquiry_id);
        $this->assertEquals($this->offer->id, $po->offer_id);
        $this->assertEquals(70800, $po->grand_total);
        $this->assertEquals(PurchaseOrder::STATUS_SENT, $po->status);
    }

    /** @test */
    public function it_throws_exception_if_quotation_not_approved()
    {
        $draftQuotation = Quotation::factory()->create([
            'offer_id' => $this->offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_DRAFT,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Quotation must be approved before generating PO');

        $this->service->generateFromQuotation($draftQuotation);
    }

    /** @test */
    public function it_does_not_create_duplicate_po_for_same_quotation()
    {
        // Generate first PO
        $po1 = $this->service->generateFromQuotation($this->quotation);

        // Try to generate again
        $po2 = $this->service->generateFromQuotation($this->quotation);

        $this->assertEquals($po1->id, $po2->id);
        $this->assertEquals($po1->po_number, $po2->po_number);

        // Only one PO should exist
        $this->assertEquals(1, PurchaseOrder::count());
    }

    /** @test */
    public function it_generates_pdf_for_po()
    {
        $po = $this->service->generateFromQuotation($this->quotation);

        $this->assertNotNull($po->pdf_path);
        $this->assertNotNull($po->pdf_generated_at);
        Storage::disk('private')->assertExists($po->pdf_path);
    }

    /** @test */
    public function it_attaches_po_to_thread()
    {
        $po = $this->service->generateFromQuotation($this->quotation);

        $this->assertNotNull($po->thread_id);
        $this->assertNotNull($po->thread_message_id);

        // Check thread message created
        $message = ThreadMessage::find($po->thread_message_id);
        $this->assertNotNull($message);
        $this->assertEquals(ThreadMessage::TYPE_SYSTEM, $message->message_type);
        $this->assertStringContainsString('Purchase Order Generated', $message->message);
        $this->assertNotEmpty($message->attachments);

        // Check attachment details
        $attachment = $message->attachments[0];
        $this->assertEquals($po->getPdfFilename(), $attachment['name']);
        $this->assertEquals('application/pdf', $attachment['type']);
    }

    /** @test */
    public function it_increments_thread_unread_counts()
    {
        $po = $this->service->generateFromQuotation($this->quotation);

        $thread = Thread::find($po->thread_id);
        $this->assertGreaterThan(0, $thread->unread_count_customer);
        $this->assertGreaterThan(0, $thread->unread_count_vendor);
    }

    /** @test */
    public function it_sends_notification_to_vendor()
    {
        Notification::fake();

        $po = $this->service->generateFromQuotation($this->quotation);

        Notification::assertSentTo(
            $this->vendor,
            PurchaseOrderGeneratedNotification::class,
            function ($notification) use ($po) {
                return $notification->po->id === $po->id;
            }
        );
    }

    /** @test */
    public function it_sends_notification_to_customer()
    {
        Notification::fake();

        $po = $this->service->generateFromQuotation($this->quotation);

        Notification::assertSentTo(
            $this->customer,
            PurchaseOrderGeneratedNotification::class,
            function ($notification) use ($po) {
                return $notification->po->id === $po->id;
            }
        );
    }

    /** @test */
    public function it_generates_unique_po_numbers()
    {
        $quotation2 = Quotation::factory()->create([
            'offer_id' => $this->offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_APPROVED,
        ]);

        $po1 = $this->service->generateFromQuotation($this->quotation);
        $po2 = $this->service->generateFromQuotation($quotation2);

        $this->assertNotEquals($po1->po_number, $po2->po_number);
        $this->assertMatchesRegularExpression('/^PO-\d{6}-\d{4}$/', $po1->po_number);
        $this->assertMatchesRegularExpression('/^PO-\d{6}-\d{4}$/', $po2->po_number);
    }

    /** @test */
    public function it_copies_quotation_details_to_po()
    {
        $po = $this->service->generateFromQuotation($this->quotation);

        $this->assertEquals($this->quotation->items, $po->items);
        $this->assertEquals($this->quotation->total_amount, $po->total_amount);
        $this->assertEquals($this->quotation->tax, $po->tax);
        $this->assertEquals($this->quotation->discount, $po->discount);
        $this->assertEquals($this->quotation->grand_total, $po->grand_total);
        $this->assertEquals($this->quotation->payment_mode, $po->payment_mode);
    }

    /** @test */
    public function it_handles_milestone_payments()
    {
        $milestoneQuotation = Quotation::factory()->create([
            'offer_id' => $this->offer->id,
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => Quotation::STATUS_APPROVED,
            'has_milestones' => true,
            'payment_mode' => 'milestone',
            'milestone_count' => 3,
            'milestone_summary' => [
                ['name' => 'Advance', 'percentage' => 30, 'amount' => 21240],
                ['name' => 'Mid-payment', 'percentage' => 40, 'amount' => 28320],
                ['name' => 'Final', 'percentage' => 30, 'amount' => 21240],
            ],
            'grand_total' => 70800,
        ]);

        $po = $this->service->generateFromQuotation($milestoneQuotation);

        $this->assertTrue($po->has_milestones);
        $this->assertEquals(3, $po->milestone_count);
        $this->assertNotEmpty($po->milestone_summary);
        $this->assertCount(3, $po->milestone_summary);
    }

    /** @test */
    public function it_can_regenerate_pdf()
    {
        $po = $this->service->generateFromQuotation($this->quotation);
        $originalPath = $po->pdf_path;

        // Regenerate PDF
        $newPath = $this->service->regeneratePDF($po);

        $this->assertNotNull($newPath);
        Storage::disk('private')->assertExists($newPath);
        Storage::disk('private')->assertMissing($originalPath);
    }

    /** @test */
    public function it_can_cancel_po()
    {
        $po = $this->service->generateFromQuotation($this->quotation);

        $cancelledPo = $this->service->cancelPO($po, 'Customer request', $this->customer->id);

        $this->assertEquals(PurchaseOrder::STATUS_CANCELLED, $cancelledPo->status);
        $this->assertNotNull($cancelledPo->cancelled_at);
        $this->assertEquals('Customer request', $cancelledPo->cancellation_reason);
        $this->assertEquals($this->customer->id, $cancelledPo->cancelled_by);
    }

    /** @test */
    public function it_posts_system_message_on_cancellation()
    {
        $po = $this->service->generateFromQuotation($this->quotation);
        $originalMessageCount = ThreadMessage::where('thread_id', $po->thread_id)->count();

        $this->service->cancelPO($po, 'Order cancelled by customer');

        $newMessageCount = ThreadMessage::where('thread_id', $po->thread_id)->count();
        $this->assertEquals($originalMessageCount + 1, $newMessageCount);

        $lastMessage = ThreadMessage::where('thread_id', $po->thread_id)->latest()->first();
        $this->assertStringContainsString('cancelled', $lastMessage->message);
    }

    /** @test */
    public function it_retrieves_po_by_quotation()
    {
        $po = $this->service->generateFromQuotation($this->quotation);

        $retrieved = $this->service->getByQuotation($this->quotation->id);

        $this->assertNotNull($retrieved);
        $this->assertEquals($po->id, $retrieved->id);
    }

    /** @test */
    public function it_retrieves_customer_pos()
    {
        $po1 = $this->service->generateFromQuotation($this->quotation);

        $customerPOs = $this->service->getCustomerPOs($this->customer->id);

        $this->assertCount(1, $customerPOs);
        $this->assertEquals($po1->id, $customerPOs->first()->id);
    }

    /** @test */
    public function it_retrieves_vendor_pos()
    {
        $po1 = $this->service->generateFromQuotation($this->quotation);

        $vendorPOs = $this->service->getVendorPOs($this->vendor->id);

        $this->assertCount(1, $vendorPOs);
        $this->assertEquals($po1->id, $vendorPOs->first()->id);
    }

    /** @test */
    public function it_triggers_po_generation_on_quotation_approved_event()
    {
        Event::fake([QuotationApproved::class]);

        // Simulate quotation approval
        event(new QuotationApproved($this->quotation));

        Event::assertDispatched(QuotationApproved::class);
    }

    /** @test */
    public function po_status_changes_correctly()
    {
        $po = PurchaseOrder::factory()->create([
            'status' => PurchaseOrder::STATUS_PENDING,
        ]);

        $this->assertTrue($po->canConfirm());
        $this->assertTrue($po->canCancel());

        $po->markAsSent();
        $this->assertEquals(PurchaseOrder::STATUS_SENT, $po->status);
        $this->assertNotNull($po->sent_at);

        $po->markAsConfirmed();
        $this->assertEquals(PurchaseOrder::STATUS_CONFIRMED, $po->status);
        $this->assertNotNull($po->confirmed_at);

        $po->refresh();
        $this->assertFalse($po->canConfirm()); // Cannot confirm again
    }

    /** @test */
    public function vendor_can_acknowledge_po()
    {
        $po = $this->service->generateFromQuotation($this->quotation);

        $this->assertNull($po->vendor_acknowledged_at);

        $po->vendorAcknowledge();

        $this->assertNotNull($po->vendor_acknowledged_at);
    }

    /** @test */
    public function po_has_correct_status_labels()
    {
        $po = PurchaseOrder::factory()->create(['status' => PurchaseOrder::STATUS_PENDING]);
        $this->assertEquals('Pending', $po->getStatusLabel());

        $po->update(['status' => PurchaseOrder::STATUS_SENT]);
        $this->assertEquals('Sent to Vendor', $po->getStatusLabel());

        $po->update(['status' => PurchaseOrder::STATUS_CONFIRMED]);
        $this->assertEquals('Confirmed', $po->getStatusLabel());

        $po->update(['status' => PurchaseOrder::STATUS_CANCELLED]);
        $this->assertEquals('Cancelled', $po->getStatusLabel());
    }

    /** @test */
    public function po_has_correct_badge_colors()
    {
        $po = PurchaseOrder::factory()->create(['status' => PurchaseOrder::STATUS_PENDING]);
        $this->assertEquals('warning', $po->getStatusBadgeColor());

        $po->update(['status' => PurchaseOrder::STATUS_SENT]);
        $this->assertEquals('info', $po->getStatusBadgeColor());

        $po->update(['status' => PurchaseOrder::STATUS_CONFIRMED]);
        $this->assertEquals('success', $po->getStatusBadgeColor());

        $po->update(['status' => PurchaseOrder::STATUS_CANCELLED]);
        $this->assertEquals('danger', $po->getStatusBadgeColor());
    }

    /** @test */
    public function it_generates_correct_pdf_filename()
    {
        $po = $this->service->generateFromQuotation($this->quotation);

        $filename = $po->getPdfFilename();
        $this->assertStringContainsString('purchase-order-', $filename);
        $this->assertStringContainsString($po->po_number, $filename);
        $this->assertStringEndsWith('.pdf', $filename);
    }

    /** @test */
    public function it_formats_grand_total_correctly()
    {
        $po = $this->service->generateFromQuotation($this->quotation);

        $formatted = $po->getFormattedGrandTotal();
        $this->assertEquals('₹ 70,800.00', $formatted);
    }

    /** @test */
    public function it_checks_pdf_existence()
    {
        $po = $this->service->generateFromQuotation($this->quotation);

        $this->assertTrue($po->hasPdf());

        $poWithoutPdf = PurchaseOrder::factory()->create([
            'pdf_path' => null,
            'pdf_generated_at' => null,
        ]);

        $this->assertFalse($poWithoutPdf->hasPdf());
    }
}
