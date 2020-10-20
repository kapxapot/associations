<?php

namespace Brightwood\Serialization\Cards\Interfaces;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Suit;

interface RootDeserializerInterface
{
    function deserialize(array $jsonData) : object;

    /**
     * @param string|array $rawCard
     */
    function deserializeCard($rawCard) : Card;

    function deserializeSuit(string $rawSuit) : Suit;

    /**
     * @return $this
     */
    function addPlayers(Player ...$players) : self;

    /**
     * The player can be resolved only if it was previously added using addPlayers() function.
     */
    function resolvePlayer(string $id) : Player;
}
