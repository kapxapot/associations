<?php

namespace Brightwood\Serialization\Cards\Interfaces;

interface SerializerInterface
{
    public function deserialize(
        RootDeserializerInterface $rootDeserializer,
        object $obj,
        array $data
    ): object;
}
