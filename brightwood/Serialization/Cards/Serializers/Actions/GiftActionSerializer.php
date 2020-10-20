<?php

namespace Brightwood\Serialization\Cards\Serializers\Actions;

use Brightwood\Models\Cards\Actions\GiftAction;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\Interfaces\SerializerInterface;

class GiftActionSerializer implements SerializerInterface
{
    /**
     * @param GiftAction $obj
     */
    public function deserialize(
        RootDeserializerInterface $rootDeserializer,
        object $obj,
        array $data
    ) : GiftAction
    {
        return $obj
            ->withCard(
                $rootDeserializer->deserializeCard($data['card'])
            )
            ->withSender(
                $rootDeserializer->resolvePlayer($data['sender_id'])
            );
    }
}
