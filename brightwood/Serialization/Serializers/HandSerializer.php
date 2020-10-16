<?php

namespace Brightwood\Serialization\Serializers;

use Brightwood\Models\Cards\Sets\Hand;
use Brightwood\Serialization\Interfaces\JsonDeserializerInterface;

class HandSerializer extends CardListSerializer
{
    /**
     * @param Hand $obj
     */
    public function deserialize(
        JsonDeserializerInterface $deserializer,
        object $obj,
        array $data
    ) : Hand
    {
        /** @var Hand */
        $obj = parent::deserialize($deserializer, $obj, $data);

        return $obj;
    }
}
