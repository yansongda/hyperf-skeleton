<?php

declare(strict_types=1);

namespace App\Service\Model;

use App\Constants\ErrorCode;
use App\Exception\ApiException;
use App\Model\Entity\AbstractEntity;
use Closure;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;

/**
 * @author yansongda <me@yansongda.cn>
 *
 * @property \App\Repository\AbstractRepository $repository
 */
abstract class AbstractService
{
    /**
     * 获取所有数据.
     */
    public function find(array $condition, array $columns = ['*'], array $orders = [], ?int $offset = null, ?int $limit = null): Collection
    {
        return $this->repository->find($condition, $columns, $orders, $offset, $limit);
    }

    /**
     * 获取所有数据带上关系.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function findWithRelations(array $conditions, array $relations = [], array $columns = ['*']): Collection
    {
        return $this->repository->findWithRelations($conditions, $relations, $columns);
    }

    /**
     * count.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function count(array $condition): int
    {
        return $this->repository->count($condition);
    }

    /**
     * 查找一条数据.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \App\Exception\ApiException
     */
    public function findOne(array $condition, array $columns = ['*'], ?string $throw = null): ?AbstractEntity
    {
        $entity = $this->repository->findOne($condition, $columns);

        if (is_null($entity) && !is_null($throw)) {
            throw new ApiException(ErrorCode::DATA_NOT_FOUND, $throw);
        }

        return $entity;
    }

    public function chunk(array $conditions, int $chunk, Closure $closure): bool
    {
        return $this->repository->chunk(...func_get_args());
    }

    /**
     * 批量赋值新增.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $data 二维数组，批量创建
     */
    public function create(int $vccId, array $data): Collection
    {
        $results = [];

        foreach ($data as $item) {
            $results[] = $this->createOne($vccId, $item);
        }

        return new Collection($results);
    }

    /**
     * 单一新增.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function createOne(int $vccId, array $data): Model
    {
        $data['vcc_id'] = $vccId;

        return $this->repository->create($data);
    }

    /**
     * 更新单个记录.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function updateOne(Model $model, array $data, bool $getUpdated = false): Model|bool
    {
        $res = $this->repository->update($model, $data);

        if ($res && $getUpdated) {
            return $model->fresh();
        }

        return $res;
    }

    /**
     * 批量赋值更新.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \App\Exception\ApiException
     */
    public function update(array $condition, array $data): bool
    {
        $models = $this->find($condition);

        if (0 === $models->count()) {
            throw new ApiException(ErrorCode::DATA_NOT_FOUND);
        }

        foreach ($models as $model) {
            $this->repository->update($model, $data);
        }

        return true;
    }

    /**
     * 更新或新增.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function updateOrCreate(array $condition, array $data): Model
    {
        return $this->repository->updateOrCreate($condition, $data);
    }

    /**
     * 查询或新增.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function firstOrCreate(array $condition, array $data): AbstractEntity
    {
        return $this->repository->firstOrCreate($condition, $data);
    }

    /**
     * delete.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function delete(array $condition): void
    {
        if (!key_exists('vcc_id', $condition)) {
            return;
        }

        $this->repository->delete($condition);
    }

    /**
     * paginate.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function paginate(array $condition, int|string $perPage = null, ?array $sorts = null, array $columns = ['*']): array
    {
        $condition = (new \Yansongda\Supports\Collection($condition))
            ->except(['per_page', 'current_page', 'field', 'order'])
            ->toArray();

        $data = $this->repository->paginate(
            $condition,
            is_null($perPage) ? null : intval($perPage),
            $sorts,
            $columns
        );

        return [
            'current_page' => $data->currentPage(),
            'total_page' => intval(ceil($data->total() / $data->perPage())),
            'per_page' => $data->perPage(),
            'total' => $data->total(),
            'empty' => $data->isEmpty(),
            'results' => $data->items(),
        ];
    }
}
