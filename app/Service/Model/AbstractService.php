<?php

declare(strict_types=1);

namespace App\Service\Model;

use App\Constants\ErrorCode;
use App\Exception\ApiException;
use App\Model\Entity\AbstractEntity;
use App\Repository\AbstractRepository;
use Closure;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;

/**
 * @property AbstractRepository $repository
 */
abstract class AbstractService
{
    /**
     * 获取所有数据.
     */
    public function find(array $conditions, array $columns = ['*'], array $orders = [], ?int $offset = null, ?int $limit = null): Collection
    {
        return $this->repository->find($conditions, $columns, $orders, $offset, $limit);
    }

    /**
     * 获取所有数据带上关系.
     */
    public function findWithRelations(array $conditions, array $relations = [], array $columns = ['*'], ?array $extra = null): Collection
    {
        return $this->repository->findWithRelations($conditions, $relations, $columns, $extra);
    }

    public function count(array $conditions): int
    {
        return $this->repository->count($conditions);
    }

    /**
     * 查找一条数据.
     *
     * @throws ApiException
     */
    public function findOne(array $conditions, array $columns = ['*'], ?string $throw = null): AbstractEntity
    {
        $entity = $this->repository->findOne($conditions, $columns);

        if (is_null($entity)) {
            throw new ApiException(ErrorCode::PARAMS_DATA_NOT_FOUND, $throw);
        }

        return $entity;
    }

    /**
     * 批量赋值新增.
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
     */
    public function createOne(int $vccId, array $data): Model
    {
        $data['vcc_id'] = $vccId;

        return $this->repository->create($data);
    }

    /**
     * 更新单个记录.
     */
    public function updateOne(Model $model, array $data, bool $getUpdated = false): bool|Model
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
     * @throws ApiException
     */
    public function update(array $conditions, array $data): bool
    {
        $models = $this->find($conditions);

        if (0 === $models->count()) {
            throw new ApiException(ErrorCode::PARAMS_DATA_NOT_FOUND);
        }

        foreach ($models as $model) {
            $this->repository->update($model, $data);
        }

        return true;
    }

    /**
     * 更新或新增.
     */
    public function updateOrCreate(array $conditions, array $data): Model
    {
        return $this->repository->updateOrCreate($conditions, $data);
    }

    /**
     * 查询或新增.
     */
    public function firstOrCreate(array $condition, array $data): AbstractEntity
    {
        return $this->repository->firstOrCreate($condition, $data);
    }

    public function delete(array $conditions): void
    {
        if (!key_exists('vcc_id', $conditions)) {
            return;
        }

        if (isset($conditions['ids']) && is_array($conditions['ids']) && count($conditions['ids']) > 0) {
            $this->repository->deleteByIds($conditions['vcc_id'], $conditions['ids']);

            return;
        }

        $this->repository->delete($conditions);
    }

    public function directDelete(array $conditions): void
    {
        $this->repository->directDelete($conditions);
    }

    public function insert(array $data): bool
    {
        return $this->repository->insert($data);
    }

    public function chunk(array $conditions, int $chunk, Closure $closure): bool
    {
        return $this->repository->chunk(...func_get_args());
    }

    public function paginate(array $conditions, null|int|string $perPage = null, ?array $sorts = null, array $columns = ['*']): array
    {
        $conditions = (new \Yansongda\Supports\Collection($conditions))
            ->except(['per_page', 'current_page', 'field', 'order'])
            ->toArray();

        $data = $this->repository->paginate(
            $conditions,
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
