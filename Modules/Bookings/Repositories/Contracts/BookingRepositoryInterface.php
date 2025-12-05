<?php

namespace Modules\Bookings\Repositories\Contracts;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

interface BookingRepositoryInterface
{
    public function create(array $data): Booking;
    
    public function find(int $id): ?Booking;
    
    public function getByCustomer(int $customerId): Collection;
    
    public function getByVendor(int $vendorId): Collection;
    
    public function getByHoarding(int $hoardingId): Collection;
    
    public function checkAvailability(int $hoardingId, string $startDate, string $endDate, ?int $excludeBookingId = null): bool;
    
    public function getConflictingBookings(int $hoardingId, string $startDate, string $endDate, ?int $excludeBookingId = null): Collection;
    
    public function releaseExpiredHolds(): int;
    
    public function getAll(array $filters = []): Collection;
}
