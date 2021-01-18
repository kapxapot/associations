<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Events\Interfaces\CardEventInterface;
use Brightwood\Models\Cards\Players\Player;
use Plasticode\Collections\Generic\StringCollection;
use Plasticode\Collections\Generic\TypedCollection;

class CardEventCollection extends TypedCollection
{
    protected string $class = CardEventInterface::class;

    public function messagesFor(?Player $player): StringCollection
    {
        return $this->stringize(
            fn (CardEventInterface $e) => $e->messageFor($player)
        );
    }
}
