<?php

namespace Brightwood\Models\Cards\Interfaces;

interface SerializableInterface extends \JsonSerializable
{
    /**
     * @param array[] $data
     */
    function serialize(array ...$data) : array;
}
