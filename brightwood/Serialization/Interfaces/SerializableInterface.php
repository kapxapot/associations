<?php

namespace Brightwood\Serialization\Interfaces;

interface SerializableInterface extends \JsonSerializable
{
    /**
     * @param array[] $data
     */
    function serialize(array ...$data) : array;
}
