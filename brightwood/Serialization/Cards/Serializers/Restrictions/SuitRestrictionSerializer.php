<?php

namespace Brightwood\Serialization\Cards\Serializers\Restrictions;

use Brightwood\Models\Cards\Restrictions\SuitRestriction;
use Brightwood\Models\Cards\Suit;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\Interfaces\SerializerInterface;

class SuitRestrictionSerializer implements SerializerInterface
{
    /**
     * @param SuitRestriction $obj
     */
    public function deserialize(
        RootDeserializerInterface $rootDeserializer,
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
