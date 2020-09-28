<?php

namespace Brightwood\Models\Cards\Events;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Events\Basic\PlayerEvent;
use Brightwood\Models\Cards\Events\Interfaces\CardEventInterface;
use Brightwood\Models\Cards\Players\Player;

class CardEventAccumulator
{
    private CardEventCollection $events;

    public function __construct(CardEventInterface ...$events)
    {
        $this->events = CardEventCollection::make($events);
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

    /**
     * Is there a skip event in the accumulator?
     */
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
            ->mergedEvents()
            ->map(
                fn (CardEventInterface $e) => $e->messageFor($player)
            )
            ->toArray();
    }

    public function mergedEvents() : CardEventCollection
    {
        $merged = new self();

        foreach ($this->events as $event) {
            $last = $merged->events()->last();

            if (
                !($last instanceof PlayerEvent)
                || !($event instanceof PlayerEvent)
                || !$last->player()->equals($event->player())
            ) {
                $merged->add($event);
                continue;
            }

            if (
                $last instanceof DrawEvent
                && $event instanceof DrawEvent
            ) {
                $last->glue($event);
                continue;
            }

            $last->link($event);
        }

        return $merged->events();
    }
}
