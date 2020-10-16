<?php

namespace Brightwood\Serialization\Serializers;

use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Serialization\Interfaces\JsonDeserializerInterface;

class BotSerializer extends PlayerSerializer
{
    /**
     * @param Bot $obj
     */
    public function deserialize(
        JsonDeserializerInterface $deserializer,
        object $obj,
        array $data
    ) : Bot
    {
        /** @var Bot */
        $obj = parent::deserialize($deserializer, $obj, $data);

        return $obj
            ->withName($data['name'])
            ->withGender($data['gender']);
    }
}
