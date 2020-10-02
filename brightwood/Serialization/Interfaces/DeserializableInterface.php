<?php

namespace Brightwood\Serialization\Interfaces;

interface DeserializableInterface
{
    /**
     * @return static
     */
    static function deserialize(array $data) : self;
}
