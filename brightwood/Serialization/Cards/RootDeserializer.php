<?php

namespace Brightwood\Serialization\Cards;

use Brightwood\Collections\Cards\PlayerCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\Interfaces\SerializerSourceInterface;
use Plasticode\Exceptions\InvalidConfigurationException;
use Webmozart\Assert\Assert;

class RootDeserializer implements RootDeserializerInterface
{
    private SerializerSourceInterface $serializerSource;
    private CardSerializer $cardSerializer;
    private PlayerCollection $players;

    public function __construct(
        SerializerSourceInterface $serializerSource,
        CardSerializer $cardSerializer
    )
    {
        $this->serializerSource = $serializerSource;
        $this->cardSerializer = $cardSerializer;
        $this->players = PlayerCollection::empty();
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function deserialize(array $jsonData) : object
    {
        /** @var string */
        $type = $jsonData['type'] ?? '';

        Assert::stringNotEmpty($type);

        $serializer = $this->serializerSource->getSerializer($type);

        if (is_null($serializer)) {
            throw new InvalidConfigurationException(
                'No serializer defined for class: ' . $type
            );
        }

        $obj = new $type();

        /** @var array */
        $data = $jsonData['data'] ?? [];

        return $serializer->deserialize($this, $obj, $data);
    }

    /**
     * @param string|array $rawCard
     */
    public function deserializeCard($rawCard) : Card
    {
        return $this->cardSerializer->deserialize($this, $rawCard);
    }

    /**
     * @return $this
     */
    public function addPlayers(Player ...$players) : self
    {
        $this->players = $this->players->add(...$players);

        return $this;
    }

    /**
     * @throws \Exception
     */
    function resolvePlayer(string $id) : Player
    {
        $player = $this->players->find($id);

        if ($player) {
            return $player;
        }

        throw new \Exception('Player [' . $id . '] not found.');
    }
}
