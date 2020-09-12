<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Suit;
use Webmozart\Assert\Assert;

class SuitCollection extends EquatableCollection
{
    protected string $class = Suit::class;

    public function get(int $id) : Suit
    {
        $suit = $this->first(
            fn (Suit $s) => $s->id() == $id
        );

        Assert::notNull($suit);

        return $suit;
    }
}
