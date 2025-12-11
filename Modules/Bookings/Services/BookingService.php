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
use Modules\Settings\Services\SettingsService;

class BookingService
{
    protected BookingRepositoryInterface $repository;
    protected SettingsService $settingsService;

    public function __construct(
        BookingRepositoryInterface $repository,
        SettingsService $settingsService
    ) {
        $this->repository = $repository;
        $this->settingsService = $settingsService;
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

            // Create booking with payment hold
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
                'hold_expiry_at' => now()->addMinutes($this->getBookingHoldMinutes()),
                'booking_snapshot' => $snapshot,
                'customer_notes' => $customerInput['notes'] ?? null,
            ]);

            // Initialize milestones if quotation has milestone payment mode
            if ($quotation->hasMilestones()) {
                $milestoneService = app(\App\Services\MilestoneService::class);
                $milestoneService->initializeBookingMilestones($booking);
            }

            // Create immutable price snapshot
            \App\Models\BookingPriceSnapshot::create([
                'booking_id' => $booking->id,
                'quotation_snapshot' => $snapshot,
                'services_price' => (float) $quotation->total_amount,
                'discounts' => (float) $quotation->discount,
                'taxes' => (float) $quotation->tax,
                'total_amount' => (float) $quotation->grand_total,
                'currency' => 'INR',
            ]);

            // Log initial status
            $this->logStatusChange($booking, null, Booking::STATUS_PENDING_PAYMENT_HOLD, Auth::id(), 'Booking created from quotation');

            // Dispatch event
            event(new BookingCreated($booking));

            return $booking->fresh(['priceSnapshot']);
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
            // Use settings-based hold time if holdMinutes not provided
            $holdTime = $holdMinutes > 0 ? $holdMinutes : $this->getBookingHoldMinutes();
            
            // Update booking
            $booking->razorpay_payment_id = $paymentId;
            $booking->payment_status = 'authorized';
            $booking->payment_authorized_at = now();
            $booking->status = 'payment_hold';
            $booking->hold_expiry_at = now()->addMinutes($holdTime);
            $booking->save();

            // Log status change
            $this->logStatusChange(
                $booking,
                null,
                'payment_hold',
                null,
                "Payment authorized via webhook. Payment ID: {$paymentId}. Hold expires in {$holdTime} minutes."
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
                $booking,
                null,
                'confirmed',
                null,
                "Payment captured successfully via webhook. Payment ID: {$paymentId}."
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
                $booking,
                null,
                'cancelled',
                null,
                "Payment failed via webhook. Error: {$errorCode} - {$errorDescription}. Payment ID: {$paymentId}."
            );

            // Fire event
            event(new BookingStatusChanged($booking, 'payment_hold', 'cancelled'));

            return $booking->fresh();
        });
    }

    /**
     * Cancel booking during payment hold (customer cancellation)
     */
    public function cancelDuringHold(int $bookingId, int $userId, string $reason = null): Booking
    {
        return DB::transaction(function () use ($bookingId, $userId, $reason) {
            $booking = $this->repository->find($bookingId);

            if (!$booking) {
                throw new \Exception('Booking not found');
            }

            // Validate booking is in payment hold
            if ($booking->status !== Booking::STATUS_PAYMENT_HOLD) {
                throw new \Exception('Only bookings in payment hold can be cancelled. Current status: ' . $booking->status);
            }

            // Validate hold hasn't expired
            if ($booking->hold_expiry_at && now()->isAfter($booking->hold_expiry_at)) {
                throw new \Exception('Payment hold has already expired');
            }

            // Validate payment is in authorized state
            if ($booking->payment_status !== 'authorized') {
                throw new \Exception('Payment is not in authorized state. Current payment status: ' . ($booking->payment_status ?? 'null'));
            }

            // Void the payment (verify it's still authorized)
            if ($booking->razorpay_payment_id) {
                $razorpayService = app(\App\Services\RazorpayService::class);
                $paymentDetails = $razorpayService->voidPayment($booking->razorpay_payment_id);

                // Fire event
                event(new \App\Events\BookingPaymentVoided(
                    $booking->id,
                    $booking->razorpay_payment_id,
                    $paymentDetails
                ));
            }

            // Update booking
            $oldStatus = $booking->status;
            $booking->status = Booking::STATUS_CANCELLED;
            $booking->payment_status = 'voided';
            $booking->hold_expiry_at = null;
            $booking->cancelled_at = now();
            $booking->save();

            // Log status change
            $this->logStatusChange(
                $booking,
                $oldStatus,
                Booking::STATUS_CANCELLED,
                $userId,
                'Customer cancelled during payment hold: ' . ($reason ?? 'No reason provided')
            );

            // Fire status changed event
            event(new BookingStatusChanged($booking, $oldStatus, Booking::STATUS_CANCELLED));

            return $booking->fresh();
        });
    }

    /**
     * Get booking hold minutes from settings
     *
     * @return int
     */
    protected function getBookingHoldMinutes(): int
    {
        return (int) $this->settingsService->get('booking_hold_minutes', 30);
    }

    /**
     * Get grace period minutes from settings
     *
     * @return int
     */
    public function getGracePeriodMinutes(): int
    {
        return (int) $this->settingsService->get('grace_period_minutes', 15);
    }

    /**
     * Get max future booking start months from settings
     *
     * @return int
     */
    public function getMaxFutureBookingStartMonths(): int
    {
        return (int) $this->settingsService->get('max_future_booking_start_months', 12);
    }

    /**
     * Get booking min duration days from settings
     *
     * @return int
     */
    public function getBookingMinDurationDays(): int
    {
        return (int) $this->settingsService->get('booking_min_duration_days', 7);
    }

    /**
     * Get booking max duration months from settings
     *
     * @return int
     */
    public function getBookingMaxDurationMonths(): int
    {
        return (int) $this->settingsService->get('booking_max_duration_months', 12);
    }

    /**
     * Check if weekly booking is allowed from settings
     *
     * @return bool
     */
    public function isWeeklyBookingAllowed(): bool
    {
        return (bool) $this->settingsService->get('allow_weekly_booking', false);
    }
}




