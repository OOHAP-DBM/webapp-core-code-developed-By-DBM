<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Enquiry;
use Modules\Offers\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'po_number' => PurchaseOrder::generatePoNumber(),
            'quotation_id' => Quotation::factory(),
            'customer_id' => User::factory()->state(['role' => 'customer']),
            'vendor_id' => User::factory()->state(['role' => 'vendor']),
            'enquiry_id' => Enquiry::factory(),
            'offer_id' => Offer::factory(),
            'items' => [
                [
                    'description' => 'Hoarding Installation',
                    'quantity' => 1,
                    'rate' => 50000,
                ],
            ],
            'total_amount' => 50000,
            'tax' => 9000,
            'discount' => 0,
            'grand_total' => 59000,
            'has_milestones' => false,
            'payment_mode' => 'full',
            'milestone_count' => null,
            'milestone_summary' => null,
            'pdf_path' => null,
            'pdf_generated_at' => null,
            'status' => PurchaseOrder::STATUS_PENDING,
            'sent_at' => null,
            'confirmed_at' => null,
            'cancelled_at' => null,
            'cancelled_by' => null,
            'cancellation_reason' => null,
            'customer_approved_at' => null,
            'vendor_acknowledged_at' => null,
            'thread_id' => null,
            'thread_message_id' => null,
            'notes' => $this->faker->optional()->sentence(),
            'terms_and_conditions' => "1. Payment as per terms\n2. Delivery as scheduled\n3. Quality guaranteed",
        ];
    }

    /**
     * Indicate that the PO is sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseOrder::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    /**
     * Indicate that the PO is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'sent_at' => now()->subDays(2),
            'confirmed_at' => now(),
            'customer_approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the PO is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseOrder::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => 'system',
            'cancellation_reason' => 'Cancelled by customer',
        ]);
    }

    /**
     * Indicate that the PO has milestones.
     */
    public function withMilestones(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_milestones' => true,
            'payment_mode' => 'milestone',
            'milestone_count' => 3,
            'milestone_summary' => [
                ['name' => 'Advance Payment', 'percentage' => 30, 'amount' => 17700],
                ['name' => 'Mid Payment', 'percentage' => 40, 'amount' => 23600],
                ['name' => 'Final Payment', 'percentage' => 30, 'amount' => 17700],
            ],
        ]);
    }

    /**
     * Indicate that the PO has a PDF.
     */
    public function withPdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'pdf_path' => 'purchase-orders/' . $this->faker->uuid . '/purchase-order-PO-202512-0001.pdf',
            'pdf_generated_at' => now(),
        ]);
    }
}
