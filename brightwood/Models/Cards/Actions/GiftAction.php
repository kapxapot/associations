<?php

namespace Brightwood\Models\Cards\Actions;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Serialization\Interfaces\SerializableInterface;
use Brightwood\Serialization\UniformSerializer;

abstract class GiftAction implements SerializableInterface
{
    protected Card $card;
    protected ?Player $sender;

    public function __construct(
        Card $card,
        ?Player $sender = null
    )
    {
        $this->card = $card;
        $this->sender = $sender;
    }

    public function card() : Card
    {
        return $this->card;
    }

    public function sender() : ?Player
    {
        return $this->sender;
    }

    /**
     * Returns announcement events, that will be consumed on gift creation.
     */
    abstract public function announcementEvents() : CardEventCollection;

    // SerializableInterface

    public function jsonSerialize()
    {
        return $this->serialize();
    }

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data) : array
    {
        return UniformSerializer::serialize(
            $this,
            [
                'card' => $this->card,
                'sender_id' => $this->sender
                    ? $this->sender->id()
                    : null
            ],
            ...$data
        );
    }
}
