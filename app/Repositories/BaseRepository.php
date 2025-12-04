<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function find(int $id, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->with($relations)->find($id, $columns);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): Model
    {
        return $this->model->with($relations)->findOrFail($id, $columns);
    }

    public function findBy(string $column, mixed $value, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->with($relations)->where($column, $value)->first($columns);
    }

    public function findWhere(array $conditions, array $columns = ['*'], array $relations = []): Collection
    {
        $query = $this->model->with($relations);
        
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }
        
        return $query->get($columns);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $record = $this->findOrFail($id);
        return $record->update($data);
    }

    public function delete(int $id): bool
    {
        $record = $this->findOrFail($id);
        return $record->delete();
    }

    public function deleteWhere(array $conditions): int
    {
        $query = $this->model->newQuery();
        
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }
        
        return $query->delete();
    }

    public function count(array $conditions = []): int
    {
        $query = $this->model->newQuery();
        
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }
        
        return $query->count();
    }

    public function exists(array $conditions): bool
    {
        $query = $this->model->newQuery();
        
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }
        
        return $query->exists();
    }

    public function first(array $conditions = [], array $columns = ['*'], array $relations = []): ?Model
    {
        $query = $this->model->with($relations);
        
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }
        
        return $query->first($columns);
    }

    public function firstOrCreate(array $conditions, array $data = []): Model
    {
        return $this->model->firstOrCreate($conditions, $data);
    }

    public function updateOrCreate(array $conditions, array $data): Model
    {
        return $this->model->updateOrCreate($conditions, $data);
    }

    /**
     * Apply filters to query builder
     */
    protected function applyFilters($query, array $filters): void
    {
        foreach ($filters as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            if (method_exists($this, $method = 'filter' . ucfirst($key))) {
                $this->$method($query, $value);
            } else {
                $query->where($key, $value);
            }
        }
    }

    /**
     * Get model instance
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Set model instance
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;
        return $this;
    }
}
