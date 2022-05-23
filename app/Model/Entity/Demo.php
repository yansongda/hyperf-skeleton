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

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'demo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer'
    ];
}
