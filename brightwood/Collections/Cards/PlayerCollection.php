<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Players\Player;

class PlayerCollection extends EquatableCollection
{
    protected string $class = Player::class;

    public function inspector() : ?Player
    {
        return $this->first(
            fn (Player $p) => $p->isInspector()
        );
    }
}
