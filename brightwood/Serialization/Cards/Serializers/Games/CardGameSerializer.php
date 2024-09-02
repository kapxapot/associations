<?php

namespace Brightwood\Serialization\Cards\Serializers\Games;

use Brightwood\Collections\Cards\PlayerCollection;
use Brightwood\Models\Cards\Games\CardGame;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\Interfaces\SerializerInterface;
use Plasticode\Collections\Generic\Collection;

abstract class CardGameSerializer implements SerializerInterface
{
    /**
     * @param CardGame $obj
     */
    public function deserialize(
        RootDeserializerInterface $rootDeserializer,
        object $obj,
        array $data
    ): CardGame
    {
        $players = PlayerCollection::from(
            Collection::make($data['players'])->map(
                fn (array $playerData) =>
                    $rootDeserializer->getPlayer($playerData['data']['id'] ?? null)
                        ?? $rootDeserializer->deserialize($playerData)
            )
        );

        $rootDeserializer->addPlayers(...$players);

        return $obj
            ->withPlayers($players)
            ->withDeck(
                $rootDeserializer->deserialize($data['deck'])
            )
            ->withDiscard(
                $rootDeserializer->deserialize($data['discard'])
            )
            ->withTrash(
                $rootDeserializer->deserialize($data['trash'])
            )
            ->withStarter(
                $rootDeserializer->resolvePlayer($data['starter_id'])
            )
            ->withIsStarted($data['is_started'])
            ->withObserver(
                $rootDeserializer->resolvePlayer($data['observer_id'])
            );
    }
}
