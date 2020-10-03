<?php

namespace Brightwood\Serialization;

use Brightwood\Serialization\Interfaces\SerializerInterface;
use Brightwood\Serialization\Interfaces\SerializerSourceInterface;
use Webmozart\Assert\Assert;

class SerializerSource implements SerializerSourceInterface
{
    /** @var array<string, SerializerInterface> */
    private array $map;

    /**
     * @param array<string, SerializerInterface>|null $map
     */
    public function __construct(
        ?array $map = null
    )
    {
        $this->map = [];

        if (empty($map)) {
            return;
        }

        foreach ($map as $class => $serializer) {
            $this->register($class, $serializer);
        }
    }

    /**
     * Registers new serializer for the specified class.
     */
    public function register(string $class, SerializerInterface $serializer) : self
    {
        Assert::stringNotEmpty($class);

        $this->map[$class] = $serializer;

        return $this;
    }

    /**
     * Get a serializer for the class. Null if absent.
     */
    public function getSerializer(string $class) : ?SerializerInterface
    {
        return $this->map[$class] ?? null;
    }
}
