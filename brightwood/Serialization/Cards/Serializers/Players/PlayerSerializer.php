<?php

namespace Brightwood\Serialization\Cards\Serializers\Players;

use Brightwood\Models\Cards\Players\Player;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\Interfaces\SerializerInterface;

abstract class PlayerSerializer implements SerializerInterface
{
    /**
     * @param Player $obj
     */
    public function deserialize(
        RootDeserializerInterface $rootDeserializer,
        object $obj,
        array $data
    ) : Player
    {
        return $obj
            ->withId($data['id'])
            ->withIcon($data['icon'])
            ->withHand(
                $rootDeserializer->deserialize($data['hand'])
            )
            ->withIsInspector($data['is_inspector']);
    }
}
