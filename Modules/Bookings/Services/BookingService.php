<?php

namespace Modules\Bookings\Services;

use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Bookings\Repositories\Contracts\BookingRepositoryInterface;
use Modules\Bookings\Events\BookingCreated;
use Modules\Bookings\Events\BookingStatusChanged;

class BookingService
{
    protected BookingRepositoryInterface $repository;

    public function __construct(BookingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create booking from approved quotation with payment hold
     */
    public function createFromQuotation(int $quotationId, array $customerInput = []): Booking
    {
        return DB::transaction(function () use ($quotationId, $customerInput) {
            // Load quotation with relationships
            $quotation = Quotation::with([
                'offer.enquiry.hoarding',
                'customer',
                'vendor'
            ])->findOrFail($quotationId);

            // Validate quotation is approved
            if (!$quotation->isApproved()) {
                throw new \Exception('Only approved quotations can be booked');
            }

            $offer = $quotation->offer;
            $enquiry = $offer->enquiry;
            $hoarding = $enquiry->hoarding;

            // Get dates from offer snapshot
            $startDate = $offer->price_snapshot['preferred_start_date'] ?? null;
            $endDate = $offer->price_snapshot['preferred_end_date'] ?? null;
            $durationType = $offer->price_snapshot['duration_type'] ?? 'days';
            $durationDays = $offer->price_snapshot['duration_days'] ?? 0;

            if (!$startDate || !$endDate) {
                throw new \Exception('Booking dates not found in offer snapshot');
            }

            // Check availability (lock check with FOR UPDATE)
            $available = $this->repository->checkAvailability(
                $hoarding->id,
                $startDate,
                $endDate
            );

            if (!$available) {
                $conflicts = $this->repository->getConflictingBookings(
                    $hoarding->id,
                    $startDate,
                    $endDate
                );
                
                throw new \Exception('Hoarding not available for selected dates. Conflicts with ' . $conflicts->count() . ' existing booking(s)');
            }

            // Build immutable snapshot
            $snapshot = [
                'quotation_id' => $quotation->id,
                'quotation_version' => $quotation->version,
                'offer_id' => $offer->id,
                'offer_version' => $offer->version,
                'enquiry_id' => $enquiry->id,
                'hoarding_id' => $hoarding->id,
                'hoarding_title' => $hoarding->title,
                'hoarding_location' => $hoarding->location,
                'hoarding_dimensions' => $hoarding->width . 'x' . $hoarding->height,
                'customer_name' => $quotation->customer->name,
                'customer_email' => $quotation->customer->email,
                'vendor_name' => $quotation->vendor->name,
                'vendor_email' => $quotation->vendor->email,
                'line_items' => $quotation->items,
                'subtotal' => (float) $quotation->total_amount,
                'tax' => (float) $quotation->tax,
                'discount' => (float) $quotation->discount,
                'grand_total' => (float) $quotation->grand_total,
                'created_at' => now()->toIso8601String(),
            ];

            // Create booking with 30-minute hold
            $booking = $this->repository->create([
                'quotation_id' => $quotation->id,
                'customer_id' => $quotation->customer_id,
                'vendor_id' => $quotation->vendor_id,
                'hoarding_id' => $hoarding->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'duration_type' => $durationType,
                'duration_days' => $durationDays,
                'total_amount' => $quotation->grand_total,
                'status' => Booking::STATUS_PENDING_PAYMENT_HOLD,
                'hold_expiry_at' => now()->addMinutes(30),
                'booking_snapshot' => $snapshot,
                'customer_notes' => $customerInput['notes'] ?? null,
            ]);

            // Log initial status
            $this->logStatusChange($booking, null, Booking::STATUS_PENDING_PAYMENT_HOLD, Auth::id(), 'Booking created from quotation');

            // Dispatch event
            event(new BookingCreated($booking));

            return $booking->fresh();
        });
    }

    /**
     * Move booking to payment_hold status (when Razorpay order created)
     */
    public function moveToPaymentHold(int $bookingId, string $razorpayOrderId): Booking
    {
        return DB::transaction(function () use ($bookingId, $razorpayOrderId) {
            $booking = $this->repository->find($bookingId);

            if (!$booking) {
                throw new \Exception('Booking not found');
            }

            if (!$booking->isPendingPaymentHold()) {
                throw new \Exception('Only pending payment hold bookings can be moved to payment hold');
            }

            $oldStatus = $booking->status;
            $booking->status = Booking::STATUS_PAYMENT_HOLD;
            $booking->razorpay_order_id = $razorpayOrderId;
            $booking->save();

            $this->logStatusChange(
                $booking,
                $oldStatus,
                Booking::STATUS_PAYMENT_HOLD,
                Auth::id(),
                'Razorpay order created',
                ['razorpay_order_id' => $razorpayOrderId]
            );

            event(new BookingStatusChanged($booking, $oldStatus, Booking::STATUS_PAYMENT_HOLD));

            return $booking->fresh();
        });
    }

    /**
     * Confirm booking after payment
     */
    public function confirmBooking(int $bookingId, string $razorpayPaymentId): Booking
    {
        return DB::transaction(function () use ($bookingId, $razorpayPaymentId) {
            $booking = $this->repository->find($bookingId);

            if (!$booking) {
                throw new \Exception('Booking not found');
            }

            if (!$booking->canConfirm()) {
                throw new \Exception('Booking cannot be confirmed');
            }

            $oldStatus = $booking->status;
            $booking->status = Booking::STATUS_CONFIRMED;
            $booking->razorpay_payment_id = $razorpayPaymentId;
            $booking->confirmed_at = now();
            $booking->hold_expiry_at = null; // Clear hold
            $booking->save();

            $this->logStatusChange(
                $booking,
                $oldStatus,
                Booking::STATUS_CONFIRMED,
                Auth::id(),
                'Payment successful',
                ['razorpay_payment_id' => $razorpayPaymentId]
            );

            event(new BookingStatusChanged($booking, $oldStatus, Booking::STATUS_CONFIRMED));

            return $booking->fresh();
        });
    }

    /**
     * Cancel booking
     */
    public function cancelBooking(int $bookingId, string $reason = null): Booking
    {
        return DB::transaction(function () use ($bookingId, $reason) {
            $booking = $this->repository->find($bookingId);

            if (!$booking) {
                throw new \Exception('Booking not found');
            }

            if (!$booking->canCancel()) {
                throw new \Exception('Booking cannot be cancelled');
            }

            $oldStatus = $booking->status;
            $booking->status = Booking::STATUS_CANCELLED;
            $booking->cancelled_at = now();
            $booking->cancellation_reason = $reason;
            $booking->save();

            $this->logStatusChange(
                $booking,
                $oldStatus,
                Booking::STATUS_CANCELLED,
                Auth::id(),
                $reason ?? 'Booking cancelled',
                ['cancellation_reason' => $reason]
            );

            event(new BookingStatusChanged($booking, $oldStatus, Booking::STATUS_CANCELLED));

            return $booking->fresh();
        });
    }

    /**
     * Log status change
     */
    protected function logStatusChange(Booking $booking, ?string $fromStatus, string $toStatus, ?int $changedBy, string $notes = null, array $metadata = []): void
    {
        BookingStatusLog::create([
            'booking_id' => $booking->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => $changedBy,
            'notes' => $notes,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Release expired holds (cron job)
     */
    public function releaseExpiredHolds(): int
    {
        $expiredBookings = Booking::expiredHolds()->get();
        
        foreach ($expiredBookings as $booking) {
            $oldStatus = $booking->status;
            $booking->status = Booking::STATUS_CANCELLED;
            $booking->cancelled_at = now();
            $booking->cancellation_reason = 'Payment hold expired after 30 minutes';
            $booking->save();

            $this->logStatusChange(
                $booking,
                $oldStatus,
                Booking::STATUS_CANCELLED,
                null,
                'Payment hold expired - auto-cancelled by system'
            );
        }

        return $expiredBookings->count();
    }

    /**
     * Find booking
     */
    public function find(int $id): ?Booking
    {
        return $this->repository->find($id);
    }

    /**
     * Get customer bookings
     */
    public function getCustomerBookings(): Collection
    {
        return $this->repository->getByCustomer(Auth::id());
    }

    /**
     * Get vendor bookings
     */
    public function getVendorBookings(): Collection
    {
        return $this->repository->getByVendor(Auth::id());
    }

    /**
     * Get all bookings with filters
     */
    public function getAll(array $filters = []): Collection
    {
        return $this->repository->getAll($filters);
    }

    /**
     * Check if user can view booking
     */
    public function canView(Booking $booking, $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        // Admin can view all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Customer can view own
        if ($user->hasRole('customer') && $booking->customer_id === $user->id) {
            return true;
        }

        // Vendor can view own
        if ($user->hasRole('vendor') && $booking->vendor_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Find booking by Razorpay order ID
     */
    public function findByRazorpayOrderId(string $orderId): ?Booking
    {
        return Booking::where('razorpay_order_id', $orderId)->first();
    }

    /**
     * Update booking when payment is authorized (webhook)
     */
    public function updatePaymentAuthorized(Booking $booking, string $paymentId, int $holdMinutes): Booking
    {
        return DB::transaction(function () use ($booking, $paymentId, $holdMinutes) {
            // Update booking
            $booking->razorpay_payment_id = $paymentId;
            $booking->payment_status = 'authorized';
            $booking->payment_authorized_at = now();
            $booking->status = 'payment_hold';
            $booking->hold_expiry_at = now()->addMinutes($holdMinutes);
            $booking->save();

            // Log status change
            $this->logStatusChange(
                booking: $booking,
                newStatus: 'payment_hold',
                notes: "Payment authorized via webhook. Payment ID: {$paymentId}. Hold expires in {$holdMinutes} minutes."
            );

            return $booking->fresh();
        });
    }

    /**
     * Confirm booking after payment capture (webhook)
     */
    public function confirmBookingAfterCapture(Booking $booking, string $paymentId): Booking
    {
        return DB::transaction(function () use ($booking, $paymentId) {
            // Update payment status
            $booking->payment_status = 'captured';
            $booking->payment_captured_at = now();
            $booking->status = 'confirmed';
            $booking->save();

            // Log status change
            $this->logStatusChange(
                booking: $booking,
                newStatus: 'confirmed',
                notes: "Payment captured successfully via webhook. Payment ID: {$paymentId}."
            );

            // Fire event
            event(new BookingStatusChanged($booking, 'payment_hold', 'confirmed'));

            return $booking->fresh();
        });
    }

    /**
     * Mark payment as failed and release hold (webhook)
     */
    public function markPaymentFailed(
        Booking $booking,
        string $paymentId,
        string $errorCode,
        string $errorDescription
    ): Booking {
        return DB::transaction(function () use ($booking, $paymentId, $errorCode, $errorDescription) {
            // Update payment status
            $booking->payment_status = 'failed';
            $booking->payment_failed_at = now();
            $booking->payment_error_code = $errorCode;
            $booking->payment_error_description = $errorDescription;
            $booking->status = 'cancelled';
            $booking->save();

            // Log status change
            $this->logStatusChange(
                booking: $booking,
                newStatus: 'cancelled',
                notes: "Payment failed via webhook. Error: {$errorCode} - {$errorDescription}. Payment ID: {$paymentId}."
            );

            // Fire event
            event(new BookingStatusChanged($booking, 'payment_hold', 'cancelled'));

            return $booking->fresh();
        });
    }
}

