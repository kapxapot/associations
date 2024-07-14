<?php

namespace Brightwood\Serialization\Cards\Serializers;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Sets\CardList;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\Interfaces\SerializerInterface;
use InvalidArgumentException;

class CardListSerializer implements SerializerInterface
{
    /**
     * @param CardList $obj
     *
     * @throws InvalidArgumentException
     */
    public function deserialize(
        RootDeserializerInterface $rootDeserializer,
        object $obj,
        array $data
    ): CardList
    {
        $rawCards = $data['cards'];

        $parsedCards = array_map(
            fn ($c) => $rootDeserializer->deserializeCard($c),
            $rawCards
        );

        return $obj
            ->withCards(
                CardCollection::make($parsedCards)
            );
    }
}
