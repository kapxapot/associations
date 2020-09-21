<?php

namespace Brightwood\Models\Cards\Events;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Events\Interfaces\CardEventInterface;
use Brightwood\Models\Cards\Players\Player;

class CardEventAccumulator
{
    private CardEventCollection $events;

    public function __construct()
    {
        $this->events = CardEventCollection::empty();
    }

    public function events() : CardEventCollection
    {
        return $this->events;
    }

    public function add(CardEventInterface $event) : self
    {
        $this->events = $this->events->add($event);

        return $this;
    }

    public function addMany(CardEventCollection $events) : self
    {
        $this->events = $this->events->concat($events);

        return $this;
    }

    public function hasSkip() : bool
    {
        return $this
            ->events
            ->anyFirst(
                fn ($e) => $e instanceof SkipEvent
            );
    }

    /**
     * @return string[]
     */
    public function messagesFor(?Player $player) : array
    {
        return $this
            ->events
            ->map(
                fn (CardEventInterface $e) => $e->messageFor($player)
            )
            ->toArray();
    }
}
