<?php

namespace Brightwood\Serialization\Serializers;

use Brightwood\Models\Cards\Players\FemaleBot;
use Brightwood\Serialization\Interfaces\JsonDeserializerInterface;

class FemaleBotSerializer extends BotSerializer
{
    /**
     * @param FemaleBot $obj
     */
    public function deserialize(
        JsonDeserializerInterface $deserializer,
        object $obj,
        array $data
    ) : FemaleBot
    {
        /** @var FemaleBot */
        $obj = parent::deserialize($deserializer, $obj, $data);

        return $obj;
    }
}
