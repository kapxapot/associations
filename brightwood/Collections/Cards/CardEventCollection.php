<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Events\Interfaces\CardEventInterface;
use Brightwood\Models\Cards\Players\Player;
use Plasticode\Collections\Basic\TypedCollection;

class CardEventCollection extends TypedCollection
{
    protected string $class = CardEventInterface::class;

    /**
     * @return string[]
     */
    public function messagesFor(?Player $player) : array
    {
        return $this
            ->scalarize(
                fn (CardEventInterface $e) => $e->messageFor($player)
            )
            ->toArray();
    }
}
