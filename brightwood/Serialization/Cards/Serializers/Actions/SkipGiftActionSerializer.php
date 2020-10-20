<?php

namespace Brightwood\Serialization\Cards\Serializers\Actions;

use Brightwood\Models\Cards\Actions\SkipGiftAction;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;

class SkipGiftActionSerializer extends GiftActionSerializer
{
    /**
     * @param SkipGiftAction $obj
     */
    public function deserialize(
        RootDeserializerInterface $rootDeserializer,
        object $obj,
        array $data
    ) : SkipGiftAction
    {
        /** @var SkipGiftAction */
        $obj = parent::deserialize($rootDeserializer, $obj, $data);

        return $obj->withReason(
            $data['reason']
        );
    }
}
