<?php

namespace Brightwood\Serialization\Interfaces;

use JsonSerializable;

interface SerializableInterface extends JsonSerializable
{
    /**
     * @param array[] $data
     */
    function serialize(array ...$data) : array;
}
