<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Entity\AbstractEntity;
use Closure;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Yansongda\Supports\Str;

abstract class AbstractRepository
{
    /**
     * entity.
     *
     * @var \App\Model\Entity\AbstractEntity
     */
    protected $entity;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function __construct(string $entity)
    {
        if (class_exists($entity)) {
            $this->entity = new $entity();
        }
    }

    public function findOne(array $conditions, array $columns = ['*'], string $latest = 'id'): ?AbstractEntity
    {
        $query = $this->getQueryCondition($conditions);

        /** @var \App\Model\Entity\AbstractEntity $data */
        $data = $query->latest($latest)->first($columns);

        return $data;
    }

    public function findOneWithTrashed(array $conditions, array $columns = ['*']): ?Model
    {
        $query = $this->getQueryCondition($conditions, $this->entity->withTrashed());

        return $query->first($columns);
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
     * !!!When using this feature, you should always include the id column and any relevant foreign key columns in the
     * list of columns you wish to retrieve.
     *
     * @author Eagle Luo <eagle.luo@foxmail.com>
     *
     * @param array $relations 关系 eg: ['template' => ['id', 'name']]
     */
    public function findWithRelations(array $conditions, array $relations = [], array $columns = ['*'], ?Builder $builder = null): Collection
    {
        $query = $this->getQueryCondition($conditions, $builder);

        foreach ($relations as $relationName => $fields) {
            if (is_int($relationName)) {
                $query->with($fields);
            } else {
                $query->with($relationName.':'.implode(',', (array) $fields));
            }
        }

        return $query->get($columns);
    }

    public function count(array $conditions, ?Builder $builder = null): int
    {
        return $this->getQueryCondition($conditions, $builder)->count();
    }

    /**
     * paginate.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array|null $sorts 排序；使用多维数组进行多个字段排序. 举例： [['id', 'desc'], ['created_at', 'asc']]
     */
    public function paginate(array $conditions, ?int $perPage = null, ?array $sorts = null, array $columns = ['*'], ?Builder $builder = null): LengthAwarePaginatorInterface
    {
        $query = $this->getQueryCondition($conditions, $builder);

        if (!is_null($sorts)) {
            foreach ($sorts as $value) {
                if (is_array($value)) {
                    $query->orderBy(
                        Str::snake(reset($value)),
                        Str::startsWith(end($value), 'asc') ? 'asc' : 'desc'
                    );
                }
            }
        }

        return $query->paginate($perPage ?? $this->entity->getPerPage(), $columns, 'currentPage');
    }

    /**
     * 批量赋值新建.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function create(array $data): Model
    {
        return $this->entity->newQuery()->create($data);
    }

    /**
     * 批量赋值更新.
     *
     * @author yansongda <me@yansongda.cn>
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
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $condition  需要查询更新的条件
     * @param array $attributes 需要更新或者新增的内容
     */
    public function updateOrCreate(array $condition, array $attributes): Model
    {
        return $this->entity->newQuery()->updateOrCreate($condition, $attributes);
    }

    /**
     * 查找一条数据，如果不存在就创建.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $condition  需要查询更新的条件
     * @param array $attributes 需要新增的内容
     */
    public function firstOrCreate(array $condition, array $attributes): AbstractEntity
    {
        /** @var \App\Model\Entity\AbstractEntity $result */
        $result = $this->entity->newQuery()->firstOrCreate($condition, $attributes);

        return $result;
    }

    /**
     * delete.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function delete(array $condition): void
    {
        $models = $this->find($condition);
        foreach ($models as $model) {
            $model->delete();
        }
    }

    public function deleteByIds(int $vccId, array $ids): void
    {
        $data = $this->entity->newQuery()->where('vcc_id', $vccId)
            ->whereIn('id', $ids)
            ->get();

        foreach ($data as $model) {
            $model->delete();
        }
    }

    /**
     * 直接删除数据.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function directDelete(array $condition): void
    {
        $this->entity->newQuery()->where($condition)->delete();
    }

    public function chunk(array $conditions, int $chunk, Closure $closure): bool
    {
        return $this->getQueryCondition($conditions)->chunk($chunk, $closure);
    }

    protected function getQueryCondition(array $conditions, ?Builder $builder = null): Builder
    {
        if (!is_null($builder)) {
            return $builder;
        }

        $query = $this->entity->newQuery();

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
}
