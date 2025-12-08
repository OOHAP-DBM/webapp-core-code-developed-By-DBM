<?php

namespace Modules\Enquiries\Repositories\Contracts;

use Modules\Enquiries\Models\Enquiry;
use Illuminate\Support\Collection;

interface EnquiryRepositoryInterface
{
    /**
     * Create a new enquiry
     */
    public function create(array $data): Enquiry;

    /**
     * Find enquiry by ID
     */
    public function find(int $id): ?Enquiry;

    /**
     * Get enquiries by customer
     */
    public function getByCustomer(int $customerId): Collection;

    /**
     * Get enquiries by hoarding (vendor can see their hoarding's enquiries)
     */
    public function getByHoarding(int $hoardingId): Collection;

    /**
     * Get enquiries for a vendor (all enquiries for their hoardings)
     */
    public function getByVendor(int $vendorId): Collection;

    /**
     * Update enquiry status
     */
    public function updateStatus(int $id, string $status): bool;

    /**
     * Get all enquiries with filters
     */
    public function getAll(array $filters = []): Collection;
}

