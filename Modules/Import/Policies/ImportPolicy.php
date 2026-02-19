<?php

namespace Modules\Import\Policies;

use App\Models\User;
use Modules\Import\Entities\InventoryImportBatch;

class ImportPolicy
{
    /**
     * Determine if user can manage import module.
     */
    protected function canManage(User $user): bool
    {
        return $user->can('import.manage');
    }

    /**
     * Determine if user is admin.
     */
    protected function isAdmin(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine if user owns the batch.
     */
    protected function isOwner(User $user, InventoryImportBatch $batch): bool
    {
        return (int) $user->id === (int) $batch->vendor_id;
    }

    /**
     * Determine if the user can view the import.
     *
     * @param User $user
     * @param InventoryImportBatch $batch
     * @return bool
     */
    public function view(User $user, InventoryImportBatch $batch)
    {
        if (!$this->canManage($user)) {
            return false;
        }

        return $this->isAdmin($user) || $this->isOwner($user, $batch);
    }

    /**
     * Determine if the user can create an import.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $this->canManage($user) && $user->can('import.batch.create');
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
        if (!$this->canManage($user) || !$user->can('import.batch.update')) {
            return false;
        }

        return $this->isAdmin($user) || $this->isOwner($user, $batch);
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
        if (!$this->canManage($user) || !$user->can('import.batch.delete')) {
            return false;
        }

        return $this->isAdmin($user) || $this->isOwner($user, $batch);
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
        if (!$this->canManage($user) || !$user->can('import.batch.approve')) {
            return false;
        }

        // Approval is vendor-owned only
        return !$this->isAdmin($user) && $this->isOwner($user, $batch);
    }
}

