<?php

namespace Brightwood\Serialization;

class UniformSerializer
{
    /**
     * @param array[] $data All data that must be merged under 'data' array.
     */
    public static function serialize(object $obj, array ...$data) : array
    {
        return [
            'type' => get_class($obj),
            'data' => array_merge(...$data)
        ];
    }
}
