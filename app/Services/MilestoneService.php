<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationMilestone;
use App\Models\Booking;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * MilestoneService
 * 
 * PROMPT 70: Vendor-Controlled Milestone Payment Logic
 * 
 * Handles milestone payment creation, calculation, and tracking
 * WITHOUT modifying existing payment flow
 */
class MilestoneService
{
    /**
     * Create milestones for a quotation
     * 
     * @param Quotation $quotation
     * @param array $milestonesData [
     *   ['title' => '...', 'amount_type' => 'percentage|fixed', 'amount' => 50, 'due_date' => '...'],
     *   ...
     * ]
     * @return array Created milestones
     */
    public function createMilestones(Quotation $quotation, array $milestonesData): array
    {
        return DB::transaction(function () use ($quotation, $milestonesData) {
            // Delete existing milestones
            $quotation->milestones()->delete();

            if (empty($milestonesData)) {
                // No milestones - use full payment
                $quotation->update([
                    'has_milestones' => false,
                    'payment_mode' => 'full',
                    'milestone_count' => 0,
                    'milestone_summary' => null,
                ]);
                
                return [];
            }

            // Validate milestones
            $this->validateMilestones($milestonesData, $quotation->grand_total);

            $createdMilestones = [];
            $sequenceNo = 1;

            foreach ($milestonesData as $milestoneData) {
                // Calculate actual amount
                $calculatedAmount = $milestoneData['amount_type'] === 'percentage'
                    ? round(($quotation->grand_total * $milestoneData['amount']) / 100, 2)
                    : $milestoneData['amount'];

                $milestone = QuotationMilestone::create([
                    'quotation_id' => $quotation->id,
                    'title' => $milestoneData['title'],
                    'description' => $milestoneData['description'] ?? null,
                    'sequence_no' => $sequenceNo++,
                    'amount_type' => $milestoneData['amount_type'],
                    'amount' => $milestoneData['amount'],
                    'calculated_amount' => $calculatedAmount,
                    'status' => QuotationMilestone::STATUS_PENDING,
                    'due_date' => $milestoneData['due_date'] ?? null,
                    'vendor_notes' => $milestoneData['vendor_notes'] ?? null,
                ]);

                $createdMilestones[] = $milestone;
            }

            // Update quotation
            $quotation->update([
                'has_milestones' => true,
                'payment_mode' => 'milestone',
                'milestone_count' => count($createdMilestones),
            ]);

            // Recalculate summary
            $quotation->recalculateMilestoneSummary();

            Log::info('Milestones created for quotation', [
                'quotation_id' => $quotation->id,
                'milestone_count' => count($createdMilestones),
            ]);

            return $createdMilestones;
        });
    }

    /**
     * Validate milestone data
     */
    protected function validateMilestones(array $milestonesData, float $quotationTotal): void
    {
        $totalPercentage = 0;
        $totalFixed = 0;

        foreach ($milestonesData as $milestone) {
            if (!isset($milestone['title']) || empty($milestone['title'])) {
                throw new Exception('Milestone title is required');
            }

            if (!isset($milestone['amount_type']) || !in_array($milestone['amount_type'], ['percentage', 'fixed'])) {
                throw new Exception('Invalid amount type');
            }

            if (!isset($milestone['amount']) || $milestone['amount'] <= 0) {
                throw new Exception('Milestone amount must be greater than 0');
            }

            if ($milestone['amount_type'] === 'percentage') {
                $totalPercentage += $milestone['amount'];
                
                if ($milestone['amount'] > 100) {
                    throw new Exception('Percentage cannot exceed 100%');
                }
            } else {
                $totalFixed += $milestone['amount'];
            }
        }

        // Validate total percentage doesn't exceed 100%
        if ($totalPercentage > 100) {
            throw new Exception("Total percentage ({$totalPercentage}%) exceeds 100%");
        }

        // Validate total fixed amount doesn't exceed quotation total
        if ($totalFixed > $quotationTotal) {
            throw new Exception("Total milestone amount (₹{$totalFixed}) exceeds quotation total (₹{$quotationTotal})");
        }
    }

    /**
     * Initialize milestones when booking is created from quotation
     */
    public function initializeBookingMilestones(Booking $booking): void
    {
        if (!$booking->quotation_id) {
            return;
        }

        $quotation = $booking->quotation;

        if (!$quotation || !$quotation->hasMilestones()) {
            // No milestones - full payment mode
            $booking->update([
                'payment_mode' => 'full',
                'milestone_total' => 0,
                'milestone_paid' => 0,
                'milestone_amount_paid' => 0,
                'milestone_amount_remaining' => 0,
            ]);
            return;
        }

        // Milestone payment mode
        $milestones = $quotation->milestones;

        // Mark first milestone as due
        $firstMilestone = $milestones->first();
        if ($firstMilestone) {
            $firstMilestone->markAsDue();
        }

        $booking->update([
            'payment_mode' => 'milestone',
            'milestone_total' => $milestones->count(),
            'milestone_paid' => 0,
            'milestone_amount_paid' => 0,
            'milestone_amount_remaining' => $booking->total_amount,
            'current_milestone_id' => $firstMilestone?->id,
        ]);

        Log::info('Booking milestones initialized', [
            'booking_id' => $booking->id,
            'milestone_total' => $milestones->count(),
        ]);
    }

    /**
     * Process milestone payment
     */
    public function processMilestonePayment(
        QuotationMilestone $milestone,
        PaymentTransaction $paymentTransaction
    ): void {
        DB::transaction(function () use ($milestone, $paymentTransaction) {
            // Mark milestone as paid
            $milestone->update([
                'status' => QuotationMilestone::STATUS_PAID,
                'paid_at' => now(),
                'payment_transaction_id' => $paymentTransaction->id,
                'razorpay_order_id' => $paymentTransaction->gateway_order_id,
                'razorpay_payment_id' => $paymentTransaction->gateway_payment_id,
                'payment_details' => [
                    'transaction_id' => $paymentTransaction->id,
                    'payment_method' => $paymentTransaction->payment_method,
                    'paid_at' => now()->toIso8601String(),
                ],
            ]);

            // Update booking
            $booking = Booking::where('quotation_id', $milestone->quotation_id)->first();
            
            if ($booking) {
                // Generate milestone invoice
                $milestoneInvoiceService = app(MilestoneInvoiceService::class);
                $invoice = $milestoneInvoiceService->generateMilestoneInvoice($milestone, $booking);

                $booking->updateMilestoneStatus();

                // Add timeline event
                $booking->addTimelineEvent(
                    'milestone_paid',
                    "Milestone payment completed: {$milestone->title}",
                    [
                        'milestone_id' => $milestone->id,
                        'milestone_title' => $milestone->title,
                        'amount' => $milestone->calculated_amount,
                        'sequence_no' => $milestone->sequence_no,
                        'payment_transaction_id' => $paymentTransaction->id,
                        'invoice_number' => $invoice->invoice_number,
                    ]
                );
            }

            // Mark next milestone as due
            $nextMilestone = $this->getNextMilestone($milestone->quotation_id, $milestone->sequence_no);
            if ($nextMilestone) {
                $nextMilestone->markAsDue();
            }

            Log::info('Milestone payment processed', [
                'milestone_id' => $milestone->id,
                'booking_id' => $booking?->id,
                'payment_transaction_id' => $paymentTransaction->id,
            ]);
        });
    }

    /**
     * Get next milestone after given sequence number
     */
    protected function getNextMilestone(int $quotationId, int $currentSequence): ?QuotationMilestone
    {
        return QuotationMilestone::where('quotation_id', $quotationId)
            ->where('sequence_no', '>', $currentSequence)
            ->where('status', QuotationMilestone::STATUS_PENDING)
            ->orderBy('sequence_no')
            ->first();
    }

    /**
     * Calculate remaining amount for milestones
     */
    public function calculateRemainingAmount(Quotation $quotation): float
    {
        if (!$quotation->hasMilestones()) {
            return $quotation->grand_total;
        }

        $paidAmount = $quotation->milestones()
            ->where('status', QuotationMilestone::STATUS_PAID)
            ->sum('calculated_amount');

        return max(0, $quotation->grand_total - $paidAmount);
    }

    /**
     * Get milestone payment summary for quotation
     */
    public function getMilestoneSummary(Quotation $quotation): array
    {
        if (!$quotation->hasMilestones()) {
            return [
                'has_milestones' => false,
                'payment_mode' => 'full',
                'total_amount' => $quotation->grand_total,
            ];
        }

        $milestones = $quotation->milestones;
        $paidMilestones = $milestones->where('status', QuotationMilestone::STATUS_PAID);
        $dueMilestones = $milestones->whereIn('status', [
            QuotationMilestone::STATUS_DUE,
            QuotationMilestone::STATUS_OVERDUE
        ]);

        return [
            'has_milestones' => true,
            'payment_mode' => 'milestone',
            'total_milestones' => $milestones->count(),
            'paid_count' => $paidMilestones->count(),
            'due_count' => $dueMilestones->count(),
            'pending_count' => $milestones->where('status', QuotationMilestone::STATUS_PENDING)->count(),
            'total_amount' => $quotation->grand_total,
            'paid_amount' => $paidMilestones->sum('calculated_amount'),
            'remaining_amount' => $this->calculateRemainingAmount($quotation),
            'progress_percentage' => $milestones->count() > 0 
                ? (int) (($paidMilestones->count() / $milestones->count()) * 100)
                : 0,
            'milestones' => $milestones->map(fn($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'sequence_no' => $m->sequence_no,
                'amount' => $m->calculated_amount,
                'status' => $m->status,
                'due_date' => $m->due_date?->format('Y-m-d'),
                'paid_at' => $m->paid_at?->format('Y-m-d H:i:s'),
            ])->toArray(),
        ];
    }

    /**
     * Check and update overdue milestones
     */
    public function updateOverdueMilestones(): int
    {
        $count = QuotationMilestone::whereIn('status', [
                QuotationMilestone::STATUS_DUE,
                QuotationMilestone::STATUS_PENDING
            ])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->update(['status' => QuotationMilestone::STATUS_OVERDUE]);

        Log::info('Updated overdue milestones', ['count' => $count]);

        return $count;
    }

    /**
     * Recalculate milestone amounts when quotation total changes
     */
    public function recalculateMilestoneAmounts(Quotation $quotation): void
    {
        if (!$quotation->hasMilestones()) {
            return;
        }

        DB::transaction(function () use ($quotation) {
            foreach ($quotation->milestones as $milestone) {
                if ($milestone->isPaid()) {
                    continue; // Don't recalculate paid milestones
                }

                $newAmount = $milestone->calculateAmount($quotation->grand_total);
                
                $milestone->update([
                    'calculated_amount' => $newAmount,
                ]);
            }

            $quotation->recalculateMilestoneSummary();

            Log::info('Milestone amounts recalculated', [
                'quotation_id' => $quotation->id,
            ]);
        });
    }

    /**
     * Delete milestones and revert to full payment
     */
    public function deleteMilestones(Quotation $quotation): void
    {
        DB::transaction(function () use ($quotation) {
            // Delete all milestones
            $quotation->milestones()->delete();

            // Update quotation to full payment
            $quotation->update([
                'has_milestones' => false,
                'payment_mode' => 'full',
                'milestone_count' => 0,
                'milestone_summary' => null,
            ]);

            // Update booking if exists
            $booking = Booking::where('quotation_id', $quotation->id)->first();
            if ($booking) {
                $booking->update([
                    'payment_mode' => 'full',
                    'milestone_total' => 0,
                    'milestone_paid' => 0,
                    'milestone_amount_paid' => 0,
                    'milestone_amount_remaining' => 0,
                    'current_milestone_id' => null,
                ]);
            }

            Log::info('Milestones deleted, reverted to full payment', [
                'quotation_id' => $quotation->id,
            ]);
        });
    }
}
