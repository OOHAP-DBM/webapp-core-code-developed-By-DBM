<?php

namespace Modules\Enquiries\Repositories;

use App\Models\Enquiry;
use App\Models\Hoarding;
use Modules\Enquiries\Repositories\Contracts\EnquiryRepositoryInterface;
use Illuminate\Support\Collection;

class EnquiryRepository implements EnquiryRepositoryInterface
{
    /**
     * Create a new enquiry
     */
    public function create(array $data): Enquiry
    {
        return Enquiry::create($data);
    }

    /**
     * Find enquiry by ID
     */
    public function find(int $id): ?Enquiry
    {
        return Enquiry::with(['customer', 'hoarding.vendor'])
            ->find($id);
    }

    /**
     * Get enquiries by customer
     */
    public function getByCustomer(int $customerId): Collection
    {
        return Enquiry::with(['hoarding.vendor'])
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get enquiries by hoarding
     */
    public function getByHoarding(int $hoardingId): Collection
    {
        return Enquiry::with(['customer', 'hoarding'])
            ->where('hoarding_id', $hoardingId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get enquiries for a vendor
     */
    public function getByVendor(int $vendorId): Collection
    {
        return Enquiry::with(['customer', 'hoarding'])
            ->whereHas('hoarding', function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Update enquiry status
     */
    public function updateStatus(int $id, string $status): bool
    {
        $enquiry = Enquiry::find($id);
        
        if (!$enquiry) {
            return false;
        }

        $enquiry->status = $status;
        return $enquiry->save();
    }

    /**
     * Get all enquiries with filters
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Enquiry::with(['customer', 'hoarding.vendor']);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by hoarding ID
        if (isset($filters['hoarding_id'])) {
            $query->where('hoarding_id', $filters['hoarding_id']);
        }

        // Filter by customer ID
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        // Filter by date range
        if (isset($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
