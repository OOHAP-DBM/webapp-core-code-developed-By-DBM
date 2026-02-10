<?php

namespace Modules\Enquiries\Repositories;

use Modules\Enquiries\Models\Enquiry;
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


    public function createHeader(array $data, string $type): Enquiry
    {
        $user = auth()->user();

        return Enquiry::create([
            'customer_id'    => $user->id,
            'enquiry_type'   => $type,
            'source'         => $user->role ?? 'user',
            'status'         => Enquiry::STATUS_SUBMITTED,
            'customer_note'  => $data['message'] ?? null,
            'contact_number' => $data['customer_mobile'] ?? null,
        ]);
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
        return Enquiry::query()
        ->where('customer_id', auth()->id())
        ->withVendorCount()
        ->withCount('items')
        ->with('items.hoarding.vendor')
        ->latest()
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
    //  public function getByVendor(int $vendorId): Collection
    // {
    //     return Enquiry::with([
    //             'customer',
    //             'items' => function ($q) use ($vendorId) {
    //                 $q->whereHas('hoarding', function ($h) use ($vendorId) {
    //                     $h->where('vendor_id', $vendorId);
    //                 })->with('hoarding');
    //             }
    //         ])
    //         ->whereHas('items.hoarding', function ($q) use ($vendorId) {
    //             $q->where('vendor_id', $vendorId);
    //         })
    //         ->latest()
    //         ->get();
    // }
    public function getByVendor(int $vendorId): Collection
    {
    return Enquiry::with([
            'customer',
            'items' => function ($q) use ($vendorId) {
                $q->whereHas('hoarding', function ($h) use ($vendorId) {
                    $h->where('vendor_id', $vendorId);
                })->with('hoarding', 'package');
            }
        ])
        ->whereHas('items.hoarding', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })
        ->latest()
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
