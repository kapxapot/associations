<?php

namespace Brightwood\Serialization\Serializers;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Sets\CardList;
use Brightwood\Serialization\Interfaces\JsonDeserializerInterface;
use Brightwood\Serialization\Interfaces\SerializerInterface;
use Webmozart\Assert\Assert;

abstract class CardListSerializer implements SerializerInterface
{
    /**
     * @param CardList $obj
     * 
     * @throws \InvalidArgumentException
     */
    public function deserialize(
        JsonDeserializerInterface $deserializer,
        object $obj,
        array $data
    ) : CardList
    {
        $rawCards = $data['cards'];

        $parsedCards = array_map(
            /**
             * @param string|array $rawCard
             */
            function ($rawCard) use ($deserializer) {
                if (is_string($rawCard)) {
                    return Card::parse($rawCard);
                }

                Assert::isArray($rawCard);

                $card = Card::parse($rawCard['card']);
                $restriction = $rawCard['restriction'] ?? null;

                if ($restriction) {
                    $card->addRestriction(
                        $deserializer->deserialize($restriction)
                    );
                }

                return $card;
            },
            $rawCards
        );

        return $obj
            ->withCards(
                CardCollection::make($parsedCards)
            );
    }
}
