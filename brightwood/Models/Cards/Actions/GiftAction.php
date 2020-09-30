<?php

namespace Brightwood\Models\Cards\Actions;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Interfaces\SerializableInterface;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Traits\UniformSerialize;

abstract class GiftAction implements SerializableInterface
{
    use UniformSerialize;

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

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data) : array
    {
        return $this->serializeRoot(
            [
                'card' => $this->card,
                'sender_id' => $this->sender
                    ? $this->sender->id()
                    : null
            ]
        );
    }
}
