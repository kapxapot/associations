<?php

namespace Brightwood\Models\Cards\Events\Basic;

use Brightwood\Collections\Cards\PlayerEventCollection;
use Brightwood\Models\Cards\Events\Interfaces\CardEventInterface;
use Brightwood\Models\Cards\Players\Player;
use Webmozart\Assert\Assert;

/**
 * Basic player event (public).
 */
abstract class PlayerEvent implements CardEventInterface
{
    protected Player $player;

    /**
     * The current event + additional player events, chained to this one.
     */
    protected PlayerEventCollection $chain;

    public function __construct(
        Player $player
    )
    {
        $this->player = $player;

        $this->chain = PlayerEventCollection::collect($this);
    }

    public function player() : Player
    {
        return $this->player;
    }

    abstract public function publicChunk() : string;

    abstract public function personalChunk() : string;

    public function messageFor(?Player $player) : string
    {
        return $this->player->equals($player)
            ? $this->personalMessage()
            : $this->publicMessage();
    }

    private function publicMessage() : string
    {
        $sentence = $this
            ->chain
            ->toSentence(
                fn (self $e) => $e->publicChunk()
            );

        return $this->player . ' ' . $sentence;
    }

    private function personalMessage() : string
    {
        $sentence = $this
            ->chain
            ->toSentence(
                fn (self $e) => $e->personalChunk()
            );

        return $this->player->personalName() . ' ' . $sentence;
    }

    /**
     * @return static
     */
    public function link(self $other) : self
    {
        Assert::true(
            $this->player->equals($other->player())
        );

        $this->chain = $this->chain->add($other);

        return $this;
    }
}
