<?php

namespace Brightwood\Serialization\Interfaces;

interface SerializerInterface
{
    function deserialize(
        JsonDeserializerInterface $deserializer,
        object $obj,
        array $data
    ) : object;
}
