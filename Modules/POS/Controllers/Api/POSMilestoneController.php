<?php

namespace Modules\POS\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\POS\Models\POSBooking;
use App\Models\QuotationMilestone;
use App\Models\Quotation;
use App\Services\MilestoneService;

/**
 * POSMilestoneController
 *
 * Handles milestone creation & management for POS bookings.
 * Sits at: POST /vendor/pos/api/bookings/{id}/milestones
 *
 * Called AFTER a POS booking is created (booking_id is in the response).
 * The frontend sends milestone_data[] in the same createBooking payload;
 * POSBookingController::store() delegates here when payment_mode=milestone.
 */
class POSMilestoneController extends Controller
{
    public function __construct(protected MilestoneService $milestoneService) {}

    // ──────────────────────────────────────────────────────────────────────
    // POST /vendor/pos/api/bookings/{bookingId}/milestones
    // Body: { milestones: [{ title, amount_type, amount, due_date, vendor_notes }] }
    // ──────────────────────────────────────────────────────────────────────
    public function store(Request $request, int $bookingId)
    {
        $request->validate([
            'milestones'                  => 'required|array|min:1',
            'milestones.*.title'          => 'required|string|max:100',
            'milestones.*.amount_type'    => 'required|in:percentage,fixed',
            'milestones.*.amount'         => 'required|numeric|min:0.01',
            'milestones.*.due_date'       => 'nullable|date|after_or_equal:today',
            'milestones.*.description'    => 'nullable|string|max:500',
            'milestones.*.vendor_notes'   => 'nullable|string|max:500',
        ]);

        $booking = POSBooking::where('id', $bookingId)
            ->where('vendor_id', Auth::id())
            ->firstOrFail();

        // Guard: only allowed on unpaid or draft bookings
        if (!in_array($booking->payment_status, ['unpaid', 'partial'])) {
            return response()->json([
                'success' => false,
                'message' => 'Milestones can only be set on unpaid bookings.',
                'data'    => null,
            ], 422);
        }

        // Validate totals server-side
        $milestones   = $request->milestones;
        $totalAmount  = (float) $booking->total_amount;
        $amountType   = $milestones[0]['amount_type'];
        $allSameType  = collect($milestones)->every(fn($m) => $m['amount_type'] === $amountType);

        if (!$allSameType) {
            return response()->json([
                'success' => false,
                'message' => 'All milestones must use the same amount type (percentage or fixed).',
                'data'    => null,
            ], 422);
        }

        if ($amountType === 'percentage') {
            $totalPct = collect($milestones)->sum('amount');
            if (abs($totalPct - 100) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => "Milestone percentages must total 100%. Got {$totalPct}%.",
                    'data'    => null,
                ], 422);
            }
        } else {
            $totalFixed = collect($milestones)->sum('amount');
            if (abs($totalFixed - $totalAmount) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => "Milestone amounts must total ₹{$totalAmount}. Got ₹{$totalFixed}.",
                    'data'    => null,
                ], 422);
            }
        }

        // Create milestones directly (POS bookings may not have quotations)
        $created = $this->createPOSMilestones($booking, $milestones, $totalAmount);

        // Update booking to reflect milestone payment mode
        $booking->update([
            'payment_mode'             => 'milestone',
            'milestone_total'          => count($created),
            'milestone_paid'           => 0,
            'milestone_amount_paid'    => 0,
            'milestone_amount_remaining' => $totalAmount,
            'current_milestone_id'     => $created[0]->id ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => count($created) . ' milestones created successfully.',
            'data'    => [
                'booking_id'       => $booking->id,
                'payment_mode'     => 'milestone',
                'total_milestones' => count($created),
                'milestones'       => collect($created)->map(fn($m) => [
                    'id'                => $m->id,
                    'title'             => $m->title,
                    'sequence_no'       => $m->sequence_no,
                    'amount_type'       => $m->amount_type,
                    'amount'            => $m->amount,
                    'calculated_amount' => $m->calculated_amount,
                    'status'            => $m->status,
                    'due_date'          => $m->due_date?->format('Y-m-d'),
                ])->toArray(),
            ],
        ], 201);
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET /vendor/pos/api/bookings/{bookingId}/milestones
    // ──────────────────────────────────────────────────────────────────────
    public function index(int $bookingId)
    {
        $booking = POSBooking::where('id', $bookingId)
            ->where('vendor_id', Auth::id())
            ->firstOrFail();

        $milestones = QuotationMilestone::where('pos_booking_id', $bookingId)
            ->orderBy('sequence_no')
            ->get();

        $paidAmount = $milestones->where('status', 'paid')->sum('calculated_amount');

        return response()->json([
            'success' => true,
            'message' => 'Milestones fetched successfully.',
            'data'    => [
                'booking_id'       => $booking->id,
                'payment_mode'     => $booking->payment_mode,
                'total_amount'     => $booking->total_amount,
                'paid_amount'      => $paidAmount,
                'remaining_amount' => max(0, $booking->total_amount - $paidAmount),
                'total_milestones' => $milestones->count(),
                'paid_count'       => $milestones->where('status', 'paid')->count(),
                'milestones'       => $milestones->map(fn($m) => [
                    'id'                => $m->id,
                    'title'             => $m->title,
                    'sequence_no'       => $m->sequence_no,
                    'amount_type'       => $m->amount_type,
                    'amount'            => $m->amount,
                    'calculated_amount' => $m->calculated_amount,
                    'status'            => $m->status,
                    'due_date'          => $m->due_date?->format('Y-m-d'),
                    'paid_at'           => $m->paid_at?->format('Y-m-d H:i:s'),
                    'vendor_notes'      => $m->vendor_notes,
                ])->toArray(),
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // DELETE /vendor/pos/api/bookings/{bookingId}/milestones
    // Revert to full payment mode
    // ──────────────────────────────────────────────────────────────────────
    public function destroy(int $bookingId)
    {
        $booking = POSBooking::where('id', $bookingId)
            ->where('vendor_id', Auth::id())
            ->firstOrFail();

        if ($booking->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove milestones from a fully paid booking.',
                'data'    => null,
            ], 422);
        }

        // Check if any milestone is already paid
        $hasPaid = QuotationMilestone::where('pos_booking_id', $bookingId)
            ->where('status', 'paid')
            ->exists();

        if ($hasPaid) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove milestones — one or more milestones have already been paid.',
                'data'    => null,
            ], 422);
        }

        QuotationMilestone::where('pos_booking_id', $bookingId)->delete();

        $booking->update([
            'payment_mode'               => 'full',
            'milestone_total'            => 0,
            'milestone_paid'             => 0,
            'milestone_amount_paid'      => 0,
            'milestone_amount_remaining' => 0,
            'current_milestone_id'       => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Milestones removed. Booking reverted to full payment.',
            'data'    => null,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // PRIVATE: Create POS-specific milestones (no quotation needed)
    // ──────────────────────────────────────────────────────────────────────
    private function createPOSMilestones(POSBooking $booking, array $milestonesData, float $totalAmount): array
    {
        // Delete any existing milestones for this booking
        QuotationMilestone::where('pos_booking_id', $booking->id)->delete();

        $created    = [];
        $sequenceNo = 1;
        $isFirst    = true;

        foreach ($milestonesData as $data) {
            $calculatedAmount = $data['amount_type'] === 'percentage'
                ? round(($totalAmount * $data['amount']) / 100, 2)
                : (float) $data['amount'];

            $milestone = QuotationMilestone::create([
                'pos_booking_id'    => $booking->id,  // Add pos_booking_id to migration if not present
                'quotation_id'      => null,           // Not from a quotation
                'title'             => $data['title'],
                'description'       => $data['description'] ?? null,
                'sequence_no'       => $sequenceNo++,
                'amount_type'       => $data['amount_type'],
                'amount'            => $data['amount'],
                'calculated_amount' => $calculatedAmount,
                'status'            => $isFirst ? 'due' : 'pending',  // First milestone is immediately due
                'due_date'          => $data['due_date'] ?? null,
                'vendor_notes'      => $data['vendor_notes'] ?? null,
            ]);

            $created[] = $milestone;
            $isFirst   = false;
        }

        return $created;
    }
}