<?php

namespace Modules\Shared\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Get all records
     */
    public function all(array $columns = ['*'], array $relations = []): Collection;

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator;

    /**
     * Find record by ID
     */
    public function find(int $id, array $columns = ['*'], array $relations = []): ?Model;

    /**
     * Find record by ID or fail
     */
    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): Model;

    /**
     * Find by specific column value
     */
    public function findBy(string $column, mixed $value, array $columns = ['*'], array $relations = []): ?Model;

    /**
     * Find where conditions match
     */
    public function findWhere(array $conditions, array $columns = ['*'], array $relations = []): Collection;

    /**
     * Create a new record
     */
    public function create(array $data): Model;

    /**
     * Update a record
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a record
     */
    public function delete(int $id): bool;

    /**
     * Delete where conditions match
     */
    public function deleteWhere(array $conditions): int;

    /**
     * Count records
     */
    public function count(array $conditions = []): int;

    /**
     * Check if record exists
     */
    public function exists(array $conditions): bool;

    /**
     * Get first record matching conditions
     */
    public function first(array $conditions = [], array $columns = ['*'], array $relations = []): ?Model;

    /**
     * Get or create a record
     */
    public function firstOrCreate(array $conditions, array $data = []): Model;

    /**
     * Update or create a record
     */
    public function updateOrCreate(array $conditions, array $data): Model;
}
