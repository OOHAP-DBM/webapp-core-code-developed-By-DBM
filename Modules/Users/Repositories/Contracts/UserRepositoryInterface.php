<?php

namespace Modules\Users\Repositories\Contracts;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by phone
     */
    public function findByPhone(string $phone): ?User;

    /**
     * Find user by email or phone
     */
    public function findByEmailOrPhone(string $identifier): ?User;

    /**
     * Get users by role
     */
    public function getUsersByRole(string $role): Collection;

    /**
     * Get active users
     */
    public function getActiveUsers(): Collection;

    /**
     * Get suspended users
     */
    public function getSuspendedUsers(): Collection;

    /**
     * Update user status
     */
    public function updateStatus(int $userId, string $status): bool;

    /**
     * Verify user email
     */
    public function verifyEmail(int $userId): bool;

    /**
     * Verify user phone
     */
    public function verifyPhone(int $userId): bool;
}

