<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Players\Player;
use Plasticode\Collections\Generic\EquatableCollection;
use Plasticode\Collections\Generic\StringCollection;
use Plasticode\Semantics\Sentence;
use Plasticode\Util\Cases;

class PlayerCollection extends EquatableCollection
{
    protected string $class = Player::class;

    private Cases $cases;

    protected function __construct(?array $data)
    {
        parent::__construct($data);

        $this->cases = new Cases();
    }

    public function inspector(): ?Player
    {
        return $this->first(
            fn (Player $p) => $p->isInspector()
        );
    }

    public function handsStrings(): StringCollection
    {
        return $this->stringize(
            fn (Player $p) => sprintf(
                '%s: %s %s',
                $p,
                $p->handSize(),
                $this->cases->caseForNumber('карта', $p->handSize())
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
