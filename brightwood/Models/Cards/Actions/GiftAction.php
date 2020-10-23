<?php

namespace Brightwood\Models\Cards\Actions;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Serialization\Interfaces\SerializableInterface;
use Brightwood\Serialization\UniformSerializer;
use Webmozart\Assert\Assert;

abstract class GiftAction implements SerializableInterface
{
    /**
     * Required - set either using the constructor, or using withCard().
     */
    private ?Card $card;

    private ?Player $sender;

    public function __construct(
        ?Card $card = null,
        ?Player $sender = null
    )
    {
        $this
            ->withCard($card)
            ->withSender($sender);
    }

    public function card() : Card
    {
        Assert::notNull($this->card);

        return $this->card;
    }

    /**
     * @return $this
     */
    public function withCard(?Card $card) : self
    {
        $this->card = $card;

        return $this;
    }

    public function sender() : ?Player
    {
        return $this->sender;
    }

    /**
     * @return $this
     */
    public function withSender(?Player $sender) : self
    {
        $this->sender = $sender;

        return $this;
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
                'card' => $this->card(),
                'sender_id' => $this->sender
                    ? $this->sender->id()
                    : null,
            ],
            ...$data
        );
    }
}
