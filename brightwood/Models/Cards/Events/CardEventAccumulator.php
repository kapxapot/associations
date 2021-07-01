<?php

namespace Brightwood\Models\Cards\Events;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Events\Generic\PlayerEvent;
use Brightwood\Models\Cards\Events\Interfaces\CardEventInterface;
use Brightwood\Models\Cards\Players\Player;
use Plasticode\Collections\Generic\StringCollection;

class CardEventAccumulator
{
    private CardEventCollection $events;

    public function __construct(CardEventInterface ...$events)
    {
        $this->events = CardEventCollection::make($events);
    }

    public function events(): CardEventCollection
    {
        return $this->events;
    }

    /**
     * @return $this
     */
    public function add(CardEventInterface $event): self
    {
        $this->events = $this->events->add($event);

        return $this;
    }

    /**
     * @return $this
     */
    public function addMany(CardEventCollection $events): self
    {
        $this->events = $this->events->concat($events);

        return $this;
    }

    /**
     * Is there a skip event in the accumulator?
     */
    public function hasSkip(): bool
    {
        return $this
            ->events
            ->any(
                fn ($e) => $e instanceof SkipEvent
            );
    }

    public function messagesFor(?Player $player): StringCollection
    {
        return $this
            ->mergedEvents()
            ->messagesFor($player);
    }

    public function mergedEvents(): CardEventCollection
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
