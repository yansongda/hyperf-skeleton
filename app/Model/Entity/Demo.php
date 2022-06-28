<?php

declare(strict_types=1);

namespace App\Model\Entity;

use App\Repository\DemoRepository;

/**
 * @property int $id
 */
class Demo extends AbstractEntity
{
    /**
     * @var string
     */
    protected $repository = DemoRepository::class;

    protected ?string $table = 'demo';

    protected array $fillable = [];

    protected array $hidden = [];

    protected array $casts = [
        'id' => 'integer',
    ];
}
