<?php

namespace Modules\Quotations\Repositories\Contracts;

use Modules\Quotations\Models\Quotation;
use Illuminate\Database\Eloquent\Collection;

interface QuotationRepositoryInterface
{
    public function create(array $data): Quotation;
    
    public function find(int $id): ?Quotation;
    
    public function getByOffer(int $offerId): Collection;
    
    public function getByVendor(int $vendorId): Collection;
    
    public function getByCustomer(int $customerId): Collection;
    
    public function getLatestVersion(int $offerId): int;
    
    public function getVersion(int $offerId, int $version): ?Quotation;
    
    public function updateStatus(int $id, string $status): bool;
    
    public function getAll(array $filters = []): Collection;
}

