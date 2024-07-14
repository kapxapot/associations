<?php

namespace Brightwood\Serialization\Cards\Interfaces;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Suit;
use InvalidArgumentException;
use Plasticode\Exceptions\InvalidConfigurationException;

interface RootDeserializerInterface
{
    /**
     * @throws InvalidArgumentException
     * @throws InvalidConfigurationException
     */
    public function deserialize(?array $jsonData): ?object;

    /**
     * @param string|array $rawCard
     */
    public function deserializeCard($rawCard): Card;

    public function deserializeSuit(string $rawSuit): Suit;

    /**
     * @return $this
     */
    public function addPlayers(Player ...$players): self;

    /**
     * A player can be resolved only if they were previously added
     * using the `addPlayers()` function.
     */
    public function resolvePlayer(?string $id): ?Player;
}
