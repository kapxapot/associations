<?php

namespace Brightwood\Serialization\Cards\Serializers\Data;

use Brightwood\Models\Data\EightsData;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\Interfaces\SerializerInterface;

class EightsDataSerializer implements SerializerInterface
{
    /**
     * @param EightsData $obj
     */
    public function deserialize(
        RootDeserializerInterface $rootDeserializer,
        object $obj,
        array $data
    ): EightsData
    {
        return $obj
            ->withPlayerCount($data['player_count'])
            ->withGame(
                $rootDeserializer->deserialize($data['game'])
            );
    }
}
