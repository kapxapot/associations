<?php

namespace Brightwood\Serialization\Factories;

use Brightwood\Models\Cards\Players\Player;
use Brightwood\Serialization\UniformDeserializer;

class PlayerSerializer extends Serializer
{
    /**
     * @param Player $obj
     */
    public static function deserialize(object $obj, array $data) : Player
    {
        return $obj
            ->withId($data['id'])
            ->withIcon($data['icon'])
            ->withHand(
                UniformDeserializer::deserialize($data['hand'])
            )
            ->withIsInspector($data['is_inspector']);
    }
}
