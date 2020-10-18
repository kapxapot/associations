<?php

namespace Brightwood\Serialization\Cards\Interfaces;

interface SerializerInterface
{
    function deserialize(
        RootDeserializerInterface $rootDeserializer,
        object $obj,
        array $data
    ) : object;
}
