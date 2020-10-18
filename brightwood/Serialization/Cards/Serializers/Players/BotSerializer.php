<?php

namespace Brightwood\Serialization\Cards\Serializers\Players;

use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;

class BotSerializer extends PlayerSerializer
{
    /**
     * @param Bot $obj
     */
    public function deserialize(
        RootDeserializerInterface $rootDeserializer,
        object $obj,
        array $data
    ) : Bot
    {
        /** @var Bot */
        $obj = parent::deserialize($rootDeserializer, $obj, $data);

        return $obj
            ->withName($data['name'])
            ->withGender($data['gender']);
    }
}
