<?php

declare(strict_types=1);

namespace App\Model;

use ArrayAccess;
use Hyperf\Utils\Contracts\Arrayable as ArrayInterface;
use JsonSerializable as JsonSerializableInterface;
use Serializable as SerializableInterface;
use Yansongda\Supports\Traits\Accessable;
use Yansongda\Supports\Traits\Arrayable;
use Yansongda\Supports\Traits\Serializable;

abstract class AbstractModel implements ArrayInterface, JsonSerializableInterface, SerializableInterface, ArrayAccess
{
    use Accessable;
    use Arrayable;
    use Serializable;
}
