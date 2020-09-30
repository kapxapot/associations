<?php

namespace Brightwood\Models\Cards\Traits;

trait UniformSerialize
{
    /**
     * @param array[] $data All data that must be merged under 'data' array.
     */
    protected function serializeRoot(array ...$data) : array
    {
        return [
            'type' => static::class,
            'data' => array_merge(...$data)
        ];
    }

    /**
     * @param array[] $data
     */
    abstract public function serialize(array ...$data) : array;

    public function jsonSerialize()
    {
        return $this->serialize();
    }
}
