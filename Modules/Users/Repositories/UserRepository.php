<?php

namespace Modules\Users\Repositories;

use App\Models\User;
use App\Repositories\BaseRepository;
use Modules\Users\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByPhone(string $phone): ?User
    {
        return $this->model->where('phone', $phone)->first();
    }

    public function findByEmailOrPhone(string $identifier): ?User
    {
        return $this->model
            ->where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();
    }

    public function getUsersByRole(string $role): Collection
    {
        return $this->model->role($role)->get();
    }

    public function getActiveUsers(): Collection
    {
        return $this->model->where('status', 'active')->get();
    }

    public function getSuspendedUsers(): Collection
    {
        return $this->model->where('status', 'suspended')->get();
    }

    public function updateStatus(int $userId, string $status): bool
    {
        return $this->update($userId, ['status' => $status]);
    }

    public function verifyEmail(int $userId): bool
    {
        return $this->update($userId, [
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
    }

    public function verifyPhone(int $userId): bool
    {
        return $this->update($userId, [
            'phone_verified_at' => now(),
            'status' => 'active',
        ]);
    }
}
