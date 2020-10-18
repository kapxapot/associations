<?php

namespace Brightwood\Serialization\Cards\Interfaces;

interface SerializerSourceInterface
{
    /**
     * Registers new serializer for the specified class.
     */
    function register(string $class, SerializerInterface $serializer) : self;

    /**
     * Get a serializer for the class. Null if absent.
     */
    function getSerializer(string $class) : ?SerializerInterface;
}
