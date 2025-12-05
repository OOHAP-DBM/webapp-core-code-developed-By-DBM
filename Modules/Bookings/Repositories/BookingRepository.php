<?php

namespace Modules\Bookings\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Modules\Bookings\Repositories\Contracts\BookingRepositoryInterface;

class BookingRepository implements BookingRepositoryInterface
{
    public function create(array $data): Booking
    {
        return Booking::create($data);
    }

    public function find(int $id): ?Booking
    {
        return Booking::with([
            'quotation.offer.enquiry',
            'customer',
            'vendor',
            'hoarding',
            'statusLogs.changedBy'
        ])->find($id);
    }

    public function getByCustomer(int $customerId): Collection
    {
        return Booking::where('customer_id', $customerId)
            ->with(['quotation', 'hoarding', 'vendor'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByVendor(int $vendorId): Collection
    {
        return Booking::where('vendor_id', $vendorId)
            ->with(['quotation', 'hoarding', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByHoarding(int $hoardingId): Collection
    {
        return Booking::where('hoarding_id', $hoardingId)
            ->with(['customer', 'vendor'])
            ->orderBy('start_date', 'asc')
            ->get();
    }

    /**
     * Check if hoarding is available for booking dates
     */
    public function checkAvailability(int $hoardingId, string $startDate, string $endDate, ?int $excludeBookingId = null): bool
    {
        $conflicts = $this->getConflictingBookings($hoardingId, $startDate, $endDate, $excludeBookingId);
        return $conflicts->isEmpty();
    }

    /**
     * Get bookings that conflict with date range
     */
    public function getConflictingBookings(int $hoardingId, string $startDate, string $endDate, ?int $excludeBookingId = null): Collection
    {
        $query = Booking::where('hoarding_id', $hoardingId)
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->where('status', '!=', Booking::STATUS_REFUNDED)
            ->where(function ($q) use ($startDate, $endDate) {
                // Date ranges overlap if:
                // (StartA <= EndB) AND (EndA >= StartB)
                $q->where(function ($q2) use ($startDate, $endDate) {
                    $q2->where('start_date', '<=', $endDate)
                       ->where('end_date', '>=', $startDate);
                });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->get();
    }

    /**
     * Release expired payment holds
     */
    public function releaseExpiredHolds(): int
    {
        return Booking::where('status', Booking::STATUS_PAYMENT_HOLD)
            ->where('hold_expiry_at', '<', now())
            ->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancellation_reason' => 'Payment hold expired'
            ]);
    }

    /**
     * Get all bookings with filters
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Booking::with(['quotation', 'customer', 'vendor', 'hoarding']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (!empty($filters['hoarding_id'])) {
            $query->where('hoarding_id', $filters['hoarding_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('start_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('end_date', '<=', $filters['to_date']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
