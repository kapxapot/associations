<?php

namespace Brightwood\Serialization\Cards\Serializers;

use Brightwood\Models\Cards\Suit;

class SuitSerializer
{
    /**
     * @throws \InvalidArgumentException
     */
    public function deserialize(
        string $rawSuit
    ) : Suit
    {
        return Suit::parse($rawSuit);
    }
}
