<?php

namespace Brightwood\Serialization\Cards;

use Brightwood\Models\Cards\Card;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Webmozart\Assert\Assert;

class CardSerializer
{
    /**
     * @param string|array $rawCard
     * 
     * @throws \InvalidArgumentException
     */
    public function deserialize(
        RootDeserializerInterface $rootDeserializer,
        $rawCard
    ) : Card
    {
        if (is_string($rawCard)) {
            return Card::parse($rawCard);
        }

        Assert::isArray($rawCard);

        $card = Card::parse($rawCard['card']);
        $restriction = $rawCard['restriction'] ?? null;

        if ($restriction) {
            $card->addRestriction(
                $rootDeserializer->deserialize($restriction)
            );
        }

        return $card;
    }
}
