<?php

namespace Brightwood\Serialization\Interfaces;

use JsonSerializable;

interface SerializableInterface extends JsonSerializable
{
    /**
     * @param array[] $data
     */
    public function serialize(array ...$data): array;
}
