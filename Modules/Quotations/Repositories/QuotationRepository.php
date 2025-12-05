<?php

namespace Modules\Quotations\Repositories;

use App\Models\Quotation;
use Illuminate\Database\Eloquent\Collection;
use Modules\Quotations\Repositories\Contracts\QuotationRepositoryInterface;

class QuotationRepository implements QuotationRepositoryInterface
{
    public function create(array $data): Quotation
    {
        return Quotation::create($data);
    }

    public function find(int $id): ?Quotation
    {
        return Quotation::with(['offer.enquiry', 'customer', 'vendor'])->find($id);
    }

    public function getByOffer(int $offerId): Collection
    {
        return Quotation::where('offer_id', $offerId)
            ->with(['customer', 'vendor'])
            ->orderBy('version', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByVendor(int $vendorId): Collection
    {
        return Quotation::where('vendor_id', $vendorId)
            ->with(['offer.enquiry', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByCustomer(int $customerId): Collection
    {
        return Quotation::where('customer_id', $customerId)
            ->where('status', '!=', Quotation::STATUS_DRAFT)
            ->with(['offer.enquiry', 'vendor'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLatestVersion(int $offerId): int
    {
        return Quotation::where('offer_id', $offerId)
            ->max('version') ?? 0;
    }

    public function getVersion(int $offerId, int $version): ?Quotation
    {
        return Quotation::where('offer_id', $offerId)
            ->where('version', $version)
            ->with(['offer.enquiry', 'customer', 'vendor'])
            ->first();
    }

    public function updateStatus(int $id, string $status): bool
    {
        return Quotation::where('id', $id)->update(['status' => $status]);
    }

    public function getAll(array $filters = []): Collection
    {
        $query = Quotation::with(['offer.enquiry', 'customer', 'vendor']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['offer_id'])) {
            $query->where('offer_id', $filters['offer_id']);
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
