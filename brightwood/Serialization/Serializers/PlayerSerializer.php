<?php

namespace Brightwood\Serialization\Serializers;

use Brightwood\Models\Cards\Players\Player;
use Brightwood\Serialization\Interfaces\JsonDeserializerInterface;
use Brightwood\Serialization\Interfaces\SerializerInterface;

abstract class PlayerSerializer implements SerializerInterface
{
    /**
     * @param Player $obj
     */
    public function deserialize(
        JsonDeserializerInterface $deserializer,
        object $obj,
        array $data
    ) : Player
    {
        return $obj
            ->withId($data['id'])
            ->withIcon($data['icon'])
            ->withHand(
                $deserializer->deserialize($data['hand'])
            )
            ->withIsInspector($data['is_inspector']);
    }
}
