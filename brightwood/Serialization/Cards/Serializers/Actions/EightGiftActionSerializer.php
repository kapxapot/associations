<?php

namespace Brightwood\Serialization\Cards\Serializers\Actions;

use Brightwood\Models\Cards\Actions\Eights\EightGiftAction;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;

class EightGiftActionSerializer extends GiftActionSerializer
{
    /**
     * @param EightGiftAction $obj
     */
    public function deserialize(
        RootDeserializerInterface $rootDeserializer,
        object $obj,
        array $data
    ) : EightGiftAction
    {
        /** @var EightGiftAction */
        $obj = parent::deserialize($rootDeserializer, $obj, $data);

        return $obj->withSuit(
            $rootDeserializer->deserializeSuit($data['suit'])
        );
    }
}
