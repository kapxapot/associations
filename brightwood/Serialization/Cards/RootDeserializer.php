<?php

namespace Brightwood\Serialization\Cards;

use Brightwood\Collections\Cards\PlayerCollection;
use Brightwood\Config\SerializationConfig;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Suit;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\Serializers\CardSerializer;
use Brightwood\Serialization\Cards\Serializers\SuitSerializer;
use Exception;
use InvalidArgumentException;
use Plasticode\Exceptions\InvalidConfigurationException;
use Webmozart\Assert\Assert;

class RootDeserializer implements RootDeserializerInterface
{
    private SerializationConfig $config;
    private CardSerializer $cardSerializer;
    private SuitSerializer $suitSerializer;
    private PlayerCollection $players;

    public function __construct(
        SerializationConfig $config,
        CardSerializer $cardSerializer,
        SuitSerializer $suitSerializer
    )
    {
        $this->config = $config;
        $this->cardSerializer = $cardSerializer;
        $this->suitSerializer = $suitSerializer;
        $this->players = PlayerCollection::empty();
    }

    /**
     * @throws InvalidArgumentException
     * @throws InvalidConfigurationException
     */
    public function deserialize(?array $jsonData) : ?object
    {
        if (is_null($jsonData)) {
            return null;
        }

        /** @var string $type */
        $type = $jsonData['type'] ?? '';

        Assert::stringNotEmpty(
            $type,
            'No type name found in the serialized data.'
        );

        $serializer = $this->config->getSerializer($type);

        if (is_null($serializer)) {
            throw new InvalidConfigurationException(
                'No serializer defined for class: ' . $type
            );
        }

        $obj = new $type();

        /** @var array $data */
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

    public function deserializeSuit(string $rawSuit) : Suit
    {
        return $this->suitSerializer->deserialize($rawSuit);
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
     * @throws Exception
     */
    function resolvePlayer(?string $id) : ?Player
    {
        if (strlen($id) === 0) {
            return null;
        }

        $player = $this->players->find($id);

        if ($player) {
            return $player;
        }

        throw new Exception('Player [' . $id . '] not found.');
    }
}
