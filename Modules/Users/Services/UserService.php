<?php

namespace Modules\Users\Services;

use App\Models\User;
use Modules\Users\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Create a new user
     */
    public function createUser(array $data, string $role = 'customer'): User
    {
        return DB::transaction(function () use ($data, $role) {
            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Create user
            $user = $this->userRepository->create($data);

            // Assign role
            $user->assignRole($role);

            return $user;
        });
    }

    /**
     * Update user
     */
    public function updateUser(int $userId, array $data): bool
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($userId, $data);
    }

    /**
     * Delete user
     */
    public function deleteUser(int $userId): bool
    {
        return $this->userRepository->delete($userId);
    }

    /**
     * Suspend user
     */
    public function suspendUser(int $userId): bool
    {
        return $this->userRepository->updateStatus($userId, 'suspended');
    }

    /**
     * Activate user
     */
    public function activateUser(int $userId): bool
    {
        return $this->userRepository->updateStatus($userId, 'active');
    }

    /**
     * Find user by identifier (email or phone)
     */
    public function findUserByIdentifier(string $identifier): ?User
    {
        return $this->userRepository->findByEmailOrPhone($identifier);
    }

    /**
     * Verify credentials
     */
    public function verifyCredentials(string $identifier, string $password): ?User
    {
        $user = $this->findUserByIdentifier($identifier);

        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }

        return null;
    }

    /**
     * Change password
     */
    public function changePassword(int $userId, string $newPassword): bool
    {
        return $this->userRepository->update($userId, [
            'password' => Hash::make($newPassword),
        ]);
    }
}
