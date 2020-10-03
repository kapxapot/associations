<?php

namespace Brightwood\Serialization\Interfaces;

interface JsonDeserializerInterface
{
    function deserialize(array $jsonData) : object;
}
