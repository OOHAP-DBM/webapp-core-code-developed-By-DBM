<?php

namespace Modules\Import\Policies;

use App\Models\User;
use Modules\Import\Entities\InventoryImportBatch;

class ImportPolicy
{
    /**
     * Determine if the user can view the import.
     *
     * @param User $user
     * @param InventoryImportBatch $batch
     * @return bool
     */
    public function view(User $user, InventoryImportBatch $batch)
    {
        return $user->id === $batch->vendor_id;
    }

    /**
     * Determine if the user can create an import.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine if the user can update the import.
     *
     * @param User $user
     * @param InventoryImportBatch $batch
     * @return bool
     */
    public function update(User $user, InventoryImportBatch $batch)
    {
        return $user->id === $batch->vendor_id;
    }

    /**
     * Determine if the user can delete the import.
     *
     * @param User $user
     * @param InventoryImportBatch $batch
     * @return bool
     */
    public function delete(User $user, InventoryImportBatch $batch)
    {
        return $user->id === $batch->vendor_id;
    }

    /**
     * Determine if the user can approve the import batch.
     *
     * @param User $user
     * @param InventoryImportBatch $batch
     * @return bool
     */
    public function approve(User $user, InventoryImportBatch $batch)
    {
        // Only batch owner (vendor) can approve
        return $user->id === $batch->vendor_id;
    }
}

