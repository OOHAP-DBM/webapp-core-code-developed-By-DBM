<?php

namespace Modules\Enquiries\Services;

use App\Models\Enquiry;
use App\Models\Hoarding;
use Modules\Enquiries\Repositories\Contracts\EnquiryRepositoryInterface;
use Modules\Enquiries\Events\EnquiryCreated;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnquiryService
{
    protected EnquiryRepositoryInterface $repository;

    public function __construct(EnquiryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new enquiry with hoarding snapshot
     */
    public function createEnquiry(array $data): Enquiry
    {
        // Get the hoarding
        $hoarding = Hoarding::findOrFail($data['hoarding_id']);

        // Capture snapshot of hoarding at enquiry time
        $snapshot = [
            'hoarding_title' => $hoarding->title,
            'hoarding_type' => $hoarding->type,
            'price' => $hoarding->price,
            'weekly_price' => $hoarding->weekly_price,
            'allows_weekly_booking' => $hoarding->allows_weekly_booking,
            'status' => $hoarding->status,
            'location' => $hoarding->location,
            'lat' => $hoarding->lat,
            'lng' => $hoarding->lng,
            'width' => $hoarding->width,
            'height' => $hoarding->height,
            'vendor_name' => $hoarding->vendor->name ?? null,
            'vendor_email' => $hoarding->vendor->email ?? null,
        ];

        $data['snapshot'] = $snapshot;
        $data['customer_id'] = $data['customer_id'] ?? Auth::id();
        $data['status'] = Enquiry::STATUS_PENDING;

        DB::beginTransaction();
        try {
            $enquiry = $this->repository->create($data);

            // Dispatch event
            event(new EnquiryCreated($enquiry));

            DB::commit();
            return $enquiry;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get enquiry by ID
     */
    public function find(int $id): ?Enquiry
    {
        return $this->repository->find($id);
    }

    /**
     * Get enquiries for current customer
     */
    public function getMyEnquiries(): Collection
    {
        return $this->repository->getByCustomer(Auth::id());
    }

    /**
     * Get enquiries for current vendor
     */
    public function getVendorEnquiries(): Collection
    {
        return $this->repository->getByVendor(Auth::id());
    }

    /**
     * Get enquiries by hoarding
     */
    public function getByHoarding(int $hoardingId): Collection
    {
        return $this->repository->getByHoarding($hoardingId);
    }

    /**
     * Update enquiry status
     */
    public function updateStatus(int $id, string $status): bool
    {
        // Validate status
        $validStatuses = [
            Enquiry::STATUS_PENDING,
            Enquiry::STATUS_ACCEPTED,
            Enquiry::STATUS_REJECTED,
            Enquiry::STATUS_CANCELLED,
        ];

        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        return $this->repository->updateStatus($id, $status);
    }

    /**
     * Accept enquiry
     */
    public function accept(int $id): bool
    {
        return $this->updateStatus($id, Enquiry::STATUS_ACCEPTED);
    }

    /**
     * Reject enquiry
     */
    public function reject(int $id): bool
    {
        return $this->updateStatus($id, Enquiry::STATUS_REJECTED);
    }

    /**
     * Cancel enquiry
     */
    public function cancel(int $id): bool
    {
        return $this->updateStatus($id, Enquiry::STATUS_CANCELLED);
    }

    /**
     * Get all enquiries with filters
     */
    public function getAll(array $filters = []): Collection
    {
        return $this->repository->getAll($filters);
    }

    /**
     * Check if user can view enquiry
     */
    public function canView(Enquiry $enquiry, $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        // Admin can view all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Customer can view their own enquiries
        if ($enquiry->customer_id === $user->id) {
            return true;
        }

        // Vendor can view enquiries for their hoardings
        if ($user->hasRole('vendor') && $enquiry->hoarding->vendor_id === $user->id) {
            return true;
        }

        return false;
    }
}
