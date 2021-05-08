<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Players\Player;
use Plasticode\Collections\Generic\EquatableCollection;
use Plasticode\Semantics\Sentence;

class PlayerCollection extends EquatableCollection
{
    protected string $class = Player::class;

    public function inspector(): ?Player
    {
        return $this->first(
            fn (Player $p) => $p->isInspector()
        );
    }

    public function handsString(): string
    {
        return Sentence::join(
            $this->map(
                fn (Player $p) => $p->handString()
            )
        );
    }

    public function toSentence(): string
    {
        return Sentence::homogeneousJoin($this);
    }

    public function find(?string $id): ?Player
    {
        return $this->first(
            fn (Player $p) => $p->id() == $id
        );
    }
}
