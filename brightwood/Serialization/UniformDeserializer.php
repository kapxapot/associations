<?php

namespace Brightwood\Serialization;

use Brightwood\Serialization\Interfaces\JsonDeserializerInterface;
use Brightwood\Serialization\Interfaces\SerializerSourceInterface;
use Plasticode\Exceptions\InvalidConfigurationException;
use Webmozart\Assert\Assert;

class UniformDeserializer implements JsonDeserializerInterface
{
    private SerializerSourceInterface $serializerSource;

    public function __construct(
        SerializerSourceInterface $serializerSource
    )
    {
        $this->serializerSource = $serializerSource;
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function deserialize(array $jsonData) : object
    {
        /** @var string */
        $type = $jsonData['type'] ?? '';

        Assert::stringNotEmpty($type);

        $serializer = $this->serializerSource->getSerializer($type);

        if (is_null($serializer)) {
            throw new InvalidConfigurationException(
                'No serializer defined for class: ' . $type
            );
        }

        $obj = new $type();

        /** @var array */
        $data = $jsonData['data'] ?? [];

        return $serializer->deserialize($this, $obj, $data);
    }
}
