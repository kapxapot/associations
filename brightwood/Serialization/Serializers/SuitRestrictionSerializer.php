<?php

namespace Brightwood\Serialization\Serializers;

use Brightwood\Models\Cards\Restrictions\SuitRestriction;
use Brightwood\Models\Cards\Suit;
use Brightwood\Serialization\Interfaces\JsonDeserializerInterface;
use Brightwood\Serialization\Interfaces\SerializerInterface;

class SuitRestrictionSerializer implements SerializerInterface
{
    /**
     * @param SuitRestriction $obj
     */
    public function deserialize(
        JsonDeserializerInterface $deserializer,
        object $obj,
        array $data
    ) : SuitRestriction
    {
        return $obj
            ->withSuit(
                Suit::parse($data['suit'])
            );
    }
}
