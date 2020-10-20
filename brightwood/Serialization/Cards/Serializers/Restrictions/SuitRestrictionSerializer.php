<?php

namespace Brightwood\Serialization\Cards\Serializers\Restrictions;

use Brightwood\Models\Cards\Restrictions\SuitRestriction;
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
        return $obj->withSuit(
            $rootDeserializer->deserializeSuit($data['suit'])
        );
    }
}
