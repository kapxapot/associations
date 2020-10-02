<?php

namespace Brightwood\Serialization;

use Webmozart\Assert\Assert;

class UniformDeserializer
{
    public static function deserialize(array $jsonData) : object
    {
        /** @var string */
        $type = $jsonData['type'] ?? '';

        /** @var array */
        $data = $jsonData['data'] ?? [];

        Assert::stringNotEmpty($type);

        // todo: do!
        return $type::deserialize($data);
    }
}
