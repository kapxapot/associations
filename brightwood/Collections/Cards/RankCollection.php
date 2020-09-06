<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Rank;
use Plasticode\Collections\Basic\TypedCollection;
use Webmozart\Assert\Assert;

class RankCollection extends TypedCollection
{
    protected string $class = Rank::class;

    public function get(int $id) : Rank
    {
        $rank = $this->first(
            fn (Rank $r) => $r->id() == $id
        );

        Assert::notNull($rank);

        return $rank;
    }
}
