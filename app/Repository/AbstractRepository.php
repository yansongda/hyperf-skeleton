<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Entity\AbstractEntity;
use Closure;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;

abstract class AbstractRepository
{
    protected AbstractEntity $entity;

    public function __construct(string $entity)
    {
        if (class_exists($entity)) {
            $this->entity = new $entity();
        }
    }

    public function findOne(array $conditions, array $columns = ['*'], string $latest = 'id', ?Builder $builder = null, ?string $connection = null): ?AbstractEntity
    {
        $query = $this->getQueryCondition($conditions, $builder, $connection);

        /** @var AbstractEntity $data */
        $data = $query->latest($latest)->take(1)->first($columns);

        return $data;
    }

    public function find(array $conditions, array $columns = ['*'], array $orders = [], ?int $offset = null, ?int $limit = null, ?Builder $builder = null): Collection
    {
        $query = $this->getQueryCondition($conditions, $builder);

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        if (!is_null($offset)) {
            $query->offset($offset);
        }

        foreach ($orders as $field => $order) {
            $query->orderBy($field, $order);
        }

        return $query->get($columns);
    }

    /**
     * 带上表关系查找所有数据.
     *
     * @param array  $relations 关系 eg: ['template' => ['id', 'name']]
     * @param ?array $extra     ['limit' => 1, 'offset' => 1, 'orders' => [$field => $order]]
     */
    public function findWithRelations(array $conditions, array $relations = [], array $columns = ['*'], ?array $extra = null, ?Builder $builder = null): Collection
    {
        $query = $this->getQueryCondition($conditions, $builder);

        foreach ($relations as $relationName => $fields) {
            if (is_int($relationName)) {
                $query->with($fields);
            } else {
                $query->with($relationName.':'.implode(',', (array) $fields));
            }
        }

        if (!is_null($extra['limit'] ?? null)) {
            $query->limit($extra['limit']);
        }

        if (!is_null($extra['offset'] ?? null)) {
            $query->offset($extra['offset']);
        }

        foreach ($extra['orders'] ?? [] as $field => $order) {
            $query->orderBy($field, $order);
        }

        return $query->get($columns);
    }

    public function count(array $conditions, ?Builder $builder = null): int
    {
        return $this->getQueryCondition($conditions, $builder)->count();
    }

    /**
     * @param null|array $sorts 排序；使用多维数组进行多个字段排序. 举例： ['id' => 'desc', 'created_at' => 'asc']
     */
    public function paginate(array $conditions, ?int $perPage = null, ?array $sorts = null, array $columns = ['*'], ?Builder $builder = null): LengthAwarePaginatorInterface
    {
        $query = $this->getQueryCondition($conditions, $builder);

        if (!is_null($sorts)) {
            foreach ($sorts as $field => $order) {
                $query->orderBy($field, $order);
            }
        }

        return $query->paginate($perPage ?? $this->entity->getPerPage(), $columns, 'currentPage');
    }

    /**
     * 批量赋值新建.
     */
    public function create(array $data): Model
    {
        return $this->getEntity()->newQuery()->create($data);
    }

    /**
     * 批量赋值更新.
     */
    public function update(Model $entity, array $data): bool
    {
        foreach ($data as $key => $value) {
            if ($entity->isFillable($key)) {
                $entity->{$key} = $value;
            }
        }

        return $entity->save();
    }

    /**
     * 更新或创建.
     *
     * @param array $condition  需要查询更新的条件
     * @param array $attributes 需要更新或者新增的内容
     */
    public function updateOrCreate(array $condition, array $attributes): Model
    {
        return $this->getEntity()->newQuery()->updateOrCreate($condition, $attributes);
    }

    /**
     * 查找一条数据，如果不存在就创建.
     *
     * @param array $condition  需要查询更新的条件
     * @param array $attributes 需要新增的内容
     */
    public function firstOrCreate(array $condition, array $attributes): AbstractEntity
    {
        /** @var AbstractEntity $result */
        $result = $this->getEntity()->newQuery()->firstOrCreate($condition, $attributes);

        return $result;
    }

    public function delete(array $condition): void
    {
        $models = $this->find($condition);

        foreach ($models as $model) {
            $model->delete();
        }
    }

    public function deleteByIds(int $vccId, array $ids): void
    {
        $data = $this->getEntity()->newQuery()->where('vcc_id', $vccId)
            ->whereIn('id', $ids)
            ->get();

        foreach ($data as $model) {
            $model->delete();
        }
    }

    public function directDelete(array $conditions): void
    {
        $this->getQueryCondition($conditions)->delete();
    }

    public function chunk(array $conditions, int $chunk, Closure $closure): bool
    {
        return $this->getQueryCondition($conditions)->chunk($chunk, $closure);
    }

    public function insert(array $data): bool
    {
        return $this->getEntity()->newQuery()->insert($data);
    }

    public function getQueryCondition(array $conditions, ?Builder $builder = null, ?string $connection = null): Builder
    {
        if (!is_null($builder)) {
            return $builder;
        }

        $entity = $this->getEntity();

        if (!is_null($connection)) {
            $entity->setConnection($connection);
        }

        $query = $entity->newQuery();

        foreach ($conditions as $field => $condition) {
            if (is_int($field)) {
                $query->where(...$condition);
            } elseif (is_array($condition)) {
                $query->whereIn($field, $condition);
            } else {
                $query->where($field, $condition);
            }
        }

        return $query;
    }

    protected function setEntity(AbstractEntity $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    protected function getEntity(): AbstractEntity
    {
        return $this->entity::new();
    }
}
