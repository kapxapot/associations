<?php

namespace Brightwood\Serialization\Cards\Interfaces;

interface SerializerSourceInterface
{
    /**
     * Registers a new serializer for the specified class.
     */
    public function register(string $class, SerializerInterface $serializer): self;

    /**
     * Returns a serializer for the class. `null` if absent.
     */
    public function getSerializer(string $class): ?SerializerInterface;
}
